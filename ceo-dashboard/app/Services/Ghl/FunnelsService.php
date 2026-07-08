<?php

namespace App\Services\Ghl;

use App\Support\Snapshot;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

/**
 * Funnels across the connected GoHighLevel accounts, for the executive view.
 *
 * For each funnel we surface: name, date created, and the creator's email
 * (resolved from the location's user list). GHL stores `createdBy` as a user
 * id — funnels created by an agency/deleted user, or with no creator recorded,
 * simply show no email (a GHL data limitation, handled gracefully).
 *
 * Read-only; cached via the DB snapshot cache so pages stay instant.
 */
class FunnelsService
{
    private string $baseUrl;
    private string $version;

    public function __construct()
    {
        $this->baseUrl = (string) config('integrations.ghl.base_url');
        $this->version = (string) config('integrations.ghl.version');
    }

    /** Funnels for one account, or the combined CEO roll-up ("all"). */
    public function summary(string $account = 'all'): array
    {
        return Snapshot::remember("funnels:$account", 30, function () use ($account) {
            $accounts = config('integrations.accounts', []);

            if ($account === 'all') {
                $all = [];
                foreach ($accounts as $key => $acc) {
                    array_push($all, ...$this->fetchAccount($acc['ghl'] ?? [], strtoupper($key)));
                }

                return $this->finalize($all);
            }

            if (! isset($accounts[$account])) {
                return ['funnels' => [], 'total' => 0, 'error' => 'Invalid account'];
            }

            return $this->finalize($this->fetchAccount($accounts[$account]['ghl'] ?? [], strtoupper($account)));
        });
    }

    /** Fetch + normalise every funnel for a single location. */
    private function fetchAccount(array $ghl, string $label): array
    {
        $apiKey = $ghl['api_key'] ?? null;
        $locationId = $ghl['location_id'] ?? null;
        if (! $apiKey || ! $locationId) {
            return [];
        }

        $funnels = $this->fetchFunnels($apiKey, $locationId);
        if (empty($funnels)) {
            return [];
        }

        $emails = $this->userEmailMap($apiKey, $locationId);

        $out = [];
        foreach ($funnels as $f) {
            if (! empty($f['deleted'])) {
                continue;
            }

            $created = $f['dateAdded'] ?? null;
            $updated = $f['dateUpdated'] ?? ($f['updatedAt'] ?? null);
            $by      = $f['createdBy'] ?? null;

            $out[] = [
                'id'            => $f['_id'] ?? null,
                'name'          => $f['name'] ?? '(untitled funnel)',
                'account'       => $label,
                'created'       => $created,
                'created_ts'    => $created ? strtotime($created) : 0,
                'created_label' => $this->fmtDate($created),
                'updated_label' => $this->fmtDate($updated),
                'created_by'    => ($by && ! empty($emails[$by])) ? $emails[$by] : null,
                'steps'         => is_array($f['steps'] ?? null) ? count($f['steps']) : null,
                'url'           => $f['url'] ?? null,
            ];
        }

        return $out;
    }

    /** Paginate through /funnels/funnel/list for the location. */
    private function fetchFunnels(string $apiKey, string $locationId): array
    {
        $all = [];
        $offset = 0;
        $guard = 0;

        do {
            try {
                $batch = $this->client($apiKey)
                    ->get("{$this->baseUrl}/funnels/funnel/list", [
                        'locationId' => $locationId,
                        'limit'      => 100,
                        'offset'     => $offset,
                    ])
                    ->json('funnels', []);
            } catch (\Throwable $e) {
                report($e);
                break;
            }

            if (empty($batch)) {
                break;
            }

            $all = array_merge($all, $batch);
            $offset += 100;
        } while (count($batch) === 100 && ++$guard < 50);

        return $all;
    }

    /** Map of user id => email for the location (best-effort). */
    private function userEmailMap(string $apiKey, string $locationId): array
    {
        try {
            $users = $this->client($apiKey)
                ->get("{$this->baseUrl}/users/", ['locationId' => $locationId])
                ->json('users', []);
        } catch (\Throwable $e) {
            report($e);
            return [];
        }

        $map = [];
        foreach ($users as $u) {
            if (! empty($u['id'])) {
                $map[$u['id']] = $u['email'] ?? null;
            }
        }

        return $map;
    }

    /** Sort newest-first so recently created funnels lead the list. */
    private function finalize(array $funnels): array
    {
        usort($funnels, fn ($a, $b) => ($b['created_ts'] ?? 0) <=> ($a['created_ts'] ?? 0));

        return ['funnels' => $funnels, 'total' => count($funnels)];
    }

    private function fmtDate(?string $iso): string
    {
        if (! $iso) {
            return '—';
        }

        try {
            return Carbon::parse($iso)->format('M j, Y');
        } catch (\Throwable $e) {
            return '—';
        }
    }

    private function client(string $apiKey)
    {
        return Http::withHeaders([
            'Authorization' => "Bearer $apiKey",
            'Version'       => $this->version,
            'Content-Type'  => 'application/json',
        ])->timeout(30);
    }
}
