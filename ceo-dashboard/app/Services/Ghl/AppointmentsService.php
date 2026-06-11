<?php

namespace App\Services\Ghl;

use App\Support\Snapshot;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Calendars / appointments from GoHighLevel.
 *
 * NOTE: requires the GHL private-integration token to have the "calendars"
 * (and "calendars/events") scope. Without it the API returns 401 and this
 * returns a scope-missing marker so the UI can prompt to enable it.
 */
class AppointmentsService
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
        return Snapshot::remember("appointments:$account", 10, function () use ($account) {
            $accounts = config('integrations.accounts', []);

            if ($account === 'all') {
                $final = $this->empty();
                $final['accounts'] = [];
                foreach ($accounts as $key => $acc) {
                    $data = $this->forLocation($acc['ghl']['api_key'], $acc['ghl']['location_id']);
                    foreach (['total', 'upcoming', 'showed', 'no_show'] as $k) {
                        $final[$k] += $data[$k] ?? 0;
                    }
                    $final['accounts'][$key] = $data;
                    $final['scope_missing'] = $final['scope_missing'] || ($data['scope_missing'] ?? false);
                }
                return $final;
            }

            if (! isset($accounts[$account])) {
                return $this->empty();
            }

            $ghl = $accounts[$account]['ghl'];
            return $this->forLocation($ghl['api_key'], $ghl['location_id']);
        });
    }

    private function forLocation(?string $apiKey, ?string $locationId): array
    {
        if (! $apiKey || ! $locationId) {
            return $this->empty();
        }

        $headers = [
            'Authorization' => "Bearer $apiKey",
            'Version'       => $this->version,
        ];

        // The events endpoint requires a calendarId, so list the location's
        // calendars first, then pull each calendar's events in parallel.
        $calRes = Http::withHeaders($headers)->timeout(30)
            ->get("{$this->baseUrl}/calendars/", ['locationId' => $locationId]);

        if ($calRes->status() === 401) {
            return array_merge($this->empty(), ['scope_missing' => true]);
        }

        $calendars = $calRes->json('calendars', []);

        if (empty($calendars)) {
            return $this->empty();
        }

        $start = (time() - 30 * 86400) * 1000;
        $end   = (time() + 60 * 86400) * 1000;

        $responses = Http::pool(fn (Pool $pool) => array_map(
            fn ($cal) => $pool->as($cal['id'])
                ->withHeaders($headers)
                ->timeout(30)
                ->get("{$this->baseUrl}/calendars/events", [
                    'locationId' => $locationId,
                    'calendarId' => $cal['id'],
                    'startTime'  => $start,
                    'endTime'    => $end,
                ]),
            $calendars,
        ));

        $events = [];
        foreach ($responses as $res) {
            if ($res instanceof Response && $res->successful()) {
                $events = array_merge($events, $res->json('events', []));
            }
        }
        $now = time();
        $summary = $this->empty();
        $summary['total'] = count($events);

        foreach ($events as $e) {
            $startTs = strtotime($e['startTime'] ?? '');
            $status  = strtolower($e['appointmentStatus'] ?? $e['status'] ?? '');

            if ($startTs > $now) {
                $summary['upcoming']++;
            }
            if (in_array($status, ['showed', 'confirmed'])) {
                $summary['showed']++;
            } elseif (str_contains($status, 'noshow') || $status === 'no-show') {
                $summary['no_show']++;
            }
        }

        return $summary;
    }

    private function empty(): array
    {
        return [
            'total'         => 0,
            'upcoming'      => 0,
            'showed'        => 0,
            'no_show'       => 0,
            'scope_missing' => false,
        ];
    }
}
