<?php

namespace App\Services\GoDaddy;

use App\Support\Snapshot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * GoDaddy domain portfolio + renewals (Production API).
 * Aggregates every configured GoDaddy account into one CEO view.
 */
class GoDaddyService
{
    public function overview(): array
    {
        return Snapshot::remember('renewals:all', 30, function () {
            $base     = config('integrations.godaddy.base_url');
            $accounts = config('integrations.godaddy.accounts', []);

            $domains = [];
            foreach ($accounts as $acc) {
                if (empty($acc['key']) || empty($acc['secret'])) {
                    continue;
                }
                foreach ($this->fetchAccount($base, $acc) as $row) {
                    $domains[] = $row;
                }
            }

            // Active domains first, soonest expiry first; cancelled sink to bottom.
            usort($domains, function ($a, $b) {
                if ($a['active'] !== $b['active']) {
                    return $b['active'] <=> $a['active'];
                }
                return ($a['days_until'] ?? PHP_INT_MAX) <=> ($b['days_until'] ?? PHP_INT_MAX);
            });

            return [
                'domains' => $domains,
                'summary' => $this->summarise($domains),
            ];
        });
    }

    private function fetchAccount(string $base, array $acc): array
    {
        $res = Http::withHeaders([
            'Authorization' => "sso-key {$acc['key']}:{$acc['secret']}",
            'Accept'        => 'application/json',
        ])->timeout(30)->get("$base/v1/domains", ['limit' => 1000]);

        if (! $res->successful() || ! is_array($res->json())) {
            return [];
        }

        $now = Carbon::now();

        return collect($res->json())->map(function ($d) use ($acc, $now) {
            $expires = ! empty($d['expires']) ? Carbon::parse($d['expires']) : null;
            $active  = ($d['status'] ?? '') === 'ACTIVE';

            return [
                'domain'      => $d['domain'] ?? '',
                'account'     => $acc['label'],
                'status'      => $d['status'] ?? 'UNKNOWN',
                'active'      => $active,
                'expires'     => $expires?->toDateString(),
                'days_until'  => $expires ? (int) $now->copy()->startOfDay()->diffInDays($expires, false) : null,
                'renew_auto'  => (bool) ($d['renewAuto'] ?? false),
                'renewable'   => (bool) ($d['renewable'] ?? false),
                'privacy'     => (bool) ($d['privacy'] ?? false),
            ];
        })->all();
    }

    private function summarise(array $domains): array
    {
        $active = array_filter($domains, fn ($d) => $d['active']);

        $expiringWithin = fn (int $days) => count(array_filter(
            $active,
            fn ($d) => $d['days_until'] !== null && $d['days_until'] >= 0 && $d['days_until'] <= $days,
        ));

        $expired = count(array_filter($active, fn ($d) => $d['days_until'] !== null && $d['days_until'] < 0));

        return [
            'total'        => count($domains),
            'active'       => count($active),
            'cancelled'    => count(array_filter($domains, fn ($d) => $d['status'] === 'CANCELLED')),
            'expiring_30'  => $expiringWithin(30),
            'expiring_90'  => $expiringWithin(90),
            'auto_renew_off' => count(array_filter($active, fn ($d) => ! $d['renew_auto'])),

            // Renewal-urgency buckets (active domains only) for the chart.
            'urgency' => [
                'expired' => $expired,
                'le_30'   => $expiringWithin(30),
                'le_90'   => max(0, $expiringWithin(90) - $expiringWithin(30)),
                'healthy' => max(0, count($active) - $expired - $expiringWithin(90)),
            ],
        ];
    }
}
