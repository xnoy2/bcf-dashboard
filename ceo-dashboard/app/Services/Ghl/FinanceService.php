<?php

namespace App\Services\Ghl;

use App\Support\Snapshot;
use Illuminate\Support\Facades\Http;

/**
 * Finance from GoHighLevel payments (port of dist/api/get_finance.php).
 *
 * NOTE: requires the GHL private-integration token to have the "payments"
 * scope. Without it the API returns 401 and cash_in falls back to 0.
 */
class FinanceService
{
    private string $baseUrl;
    private string $version;

    public function __construct()
    {
        $this->baseUrl = (string) config('integrations.ghl.base_url');
        $this->version = (string) config('integrations.ghl.version');
    }

    public function summary(string $account = 'all'): array
    {
        return Snapshot::remember("finance:$account", 5, function () use ($account) {
            $accounts = config('integrations.accounts', []);

            if ($account === 'all') {
                $final = ['cash_in' => 0, 'transactions' => 0, 'accounts' => []];
                foreach ($accounts as $key => $acc) {
                    $data = $this->forLocation($acc['ghl']['api_key'], $acc['ghl']['location_id']);
                    $final['cash_in']      += $data['cash_in'];
                    $final['transactions'] += $data['transactions'];
                    $final['accounts'][$key] = $data;
                }
                return $final;
            }

            if (! isset($accounts[$account])) {
                return ['error' => 'Invalid account', 'cash_in' => 0, 'transactions' => 0];
            }

            $ghl = $accounts[$account]['ghl'];
            return $this->forLocation($ghl['api_key'], $ghl['location_id']);
        });
    }

    private function forLocation(?string $apiKey, ?string $locationId): array
    {
        if (! $apiKey || ! $locationId) {
            return ['cash_in' => 0, 'transactions' => 0];
        }

        $res = Http::withHeaders([
            'Authorization' => "Bearer $apiKey",
            'Version'       => $this->version,
        ])->timeout(30)->get("{$this->baseUrl}/payments/transactions", [
            'altId'   => $locationId,
            'altType' => 'location',
        ]);

        // 401 = token lacks "payments" scope; surface gracefully.
        if ($res->status() === 401) {
            return ['cash_in' => 0, 'transactions' => 0, 'error' => 'payments scope missing'];
        }

        $transactions = $res->json('data', $res->json('transactions', []));
        $cashIn = 0;

        foreach ($transactions as $t) {
            if (($t['status'] ?? '') === 'succeeded') {
                $cashIn += (float) ($t['amount'] ?? 0);
            }
        }

        return [
            'cash_in'      => $cashIn,
            'transactions' => count($transactions),
        ];
    }
}
