<?php

namespace App\Services\Ghl;

use App\Support\Snapshot;
use Illuminate\Support\Facades\Http;

/**
 * GoHighLevel (LeadConnector) integration.
 *
 * Clean port of the legacy dist/api/get_pipeline.php logic onto Laravel's
 * HTTP client + cache. Supports per-account ("bcf"|"bgr"|"bwd") and the
 * CEO roll-up ("all").
 */
class GhlService
{
    private string $baseUrl;
    private string $version;

    public function __construct()
    {
        $this->baseUrl = (string) config('integrations.ghl.base_url');
        $this->version = (string) config('integrations.ghl.version');
    }

    /**
     * Pipeline summary for an account, or the combined CEO view ("all").
     * Cached for 2 minutes to keep the dashboard snappy.
     */
    public function pipelineSummary(string $account = 'bcf'): array
    {
        return Snapshot::remember("pipeline:$account", 5, function () use ($account) {
            $accounts = config('integrations.accounts', []);

            if ($account === 'all') {
                return $this->combined($accounts);
            }

            if (! isset($accounts[$account])) {
                return ['error' => 'Invalid account'];
            }

            $ghl = $accounts[$account]['ghl'];
            return $this->processAccount($ghl['api_key'], $ghl['location_id'], $this->priorityPipeline($account));
        });
    }

    /**
     * When a contact has opportunities in several pipelines, the Lead Explorer
     * keeps just one row — the one in this "progress" pipeline for the account.
     * Matched case-insensitively as a substring (e.g. "BCF Build Progress").
     */
    private function priorityPipeline(string $account): ?string
    {
        return [
            'bcf' => 'Build Progress',
            'bgr' => 'Project Progress',
        ][$account] ?? null;
    }

    private function combined(array $accounts): array
    {
        $final = $this->emptySummary();
        $final['accounts'] = [];

        $stages = $sources = $trend = [];

        foreach ($accounts as $key => $acc) {
            $data = $this->processAccount($acc['ghl']['api_key'], $acc['ghl']['location_id'], $this->priorityPipeline($key));

            foreach (['weekly_leads', 'new_leads', 'pipeline_value', 'closed_sales', 'revenue', 'quotes_issued', 'last_week_leads', 'open_count', 'won_count', 'lost_count'] as $k) {
                $final[$k] += $data[$k] ?? 0;
            }

            // Merge distributions across accounts.
            foreach ($data['stages'] ?? [] as $name => $count) {
                $stages[$name] = ($stages[$name] ?? 0) + $count;
            }
            foreach ($data['lead_sources'] ?? [] as $name => $count) {
                $sources[$name] = ($sources[$name] ?? 0) + $count;
            }
            foreach (($data['weekly_labels'] ?? []) as $i => $label) {
                $trend[$label] = ($trend[$label] ?? 0) + ($data['weekly_trend'][$i] ?? 0);
            }

            // Collect every pipeline, labelled with its business.
            foreach ($data['pipelines'] ?? [] as $pl) {
                $final['pipelines'][] = [
                    'id'     => "$key:{$pl['id']}",
                    'name'   => strtoupper($key) . ' · ' . $pl['name'],
                    'labels' => $pl['labels'],
                    'counts' => $pl['counts'],
                ];
            }

            // Merge lead records (tagged with their business), and drop them
            // from the per-account copy so the payload isn't stored twice.
            foreach ($data['leads'] ?? [] as $ld) {
                $ld['a'] = strtoupper($key);
                $final['leads'][] = $ld;
            }
            unset($data['leads']);

            $final['accounts'][$key] = $data;
        }

        usort($final['leads'], fn ($x, $y) => ($y['ts'] ?? 0) <=> ($x['ts'] ?? 0));

        // Order the weekly trend chronologically by label ("M d").
        uksort($trend, fn ($a, $b) => strtotime($a) <=> strtotime($b));

        $final['stages']        = $stages;
        $final['lead_sources']  = $sources;
        $final['weekly_labels'] = array_keys($trend);
        $final['weekly_trend']  = array_values($trend);

        $won  = $final['won_count'];
        $lost = $final['lost_count'];
        $final['win_rate']      = ($won + $lost) > 0 ? round($won / ($won + $lost) * 100, 1) : 0.0;
        $final['avg_deal_size'] = $won > 0 ? round($final['revenue'] / $won) : 0;

        return $final;
    }

    private function processAccount(?string $apiKey, ?string $locationId, ?string $priorityPipeline = null): array
    {
        if (! $apiKey || ! $locationId) {
            return $this->emptySummary();
        }

        $pipelines = $this->fetchPipelines($apiKey, $locationId);

        $stageMap = [];
        $pipelineNames = [];
        foreach ($pipelines as $pl) {
            $pipelineNames[$pl['id']] = $pl['name'];
            foreach ($pl['stages'] as $s) {
                $stageMap[$s['id']] = $s['name'];
            }
        }

        $opportunities = $this->fetchOpportunities($apiKey, $locationId);

        if (count($opportunities) === 0) {
            return $this->emptySummary();
        }

        $summary = $this->aggregate($opportunities, $stageMap, $pipelineNames, $priorityPipeline);

        // Per-pipeline funnel: opportunity counts per stage, in stage order,
        // so the UI can show one pipeline at a time.
        $byStageId = [];
        foreach ($opportunities as $opp) {
            $sid = $opp['pipelineStageId'] ?? null;
            if ($sid) {
                $byStageId[$sid] = ($byStageId[$sid] ?? 0) + 1;
            }
        }

        $summary['pipelines'] = array_map(fn ($pl) => [
            'id'     => $pl['id'],
            'name'   => $pl['name'],
            'labels' => array_column($pl['stages'], 'name'),
            'counts' => array_map(fn ($s) => $byStageId[$s['id']] ?? 0, $pl['stages']),
        ], $pipelines);

        return $summary;
    }

    /** Pipelines with ordered stages: [['id','name','stages'=>[['id','name'],…]],…] */
    private function fetchPipelines(string $apiKey, string $locationId): array
    {
        $res = $this->client($apiKey)
            ->get("{$this->baseUrl}/opportunities/pipelines", ['locationId' => $locationId]);

        $pipelines = [];
        foreach ($res->json('pipelines', []) as $pipeline) {
            $stages = collect($pipeline['stages'] ?? [])
                ->sortBy('position')
                ->map(fn ($s) => ['id' => $s['id'], 'name' => $s['name']])
                ->values()
                ->all();

            $pipelines[] = [
                'id'     => $pipeline['id'],
                'name'   => $pipeline['name'],
                'stages' => $stages,
            ];
        }

        return $pipelines;
    }

    /** Paginate through all opportunities for the location. */
    private function fetchOpportunities(string $apiKey, string $locationId): array
    {
        $all = [];
        $startAfter = null;
        $startAfterId = null;
        $guard = 0; // hard stop against runaway pagination

        do {
            // NOTE: the search endpoint requires snake_case `location_id`
            // (unlike /opportunities/pipelines which uses `locationId`).
            // The cursor needs BOTH startAfter (timestamp) and startAfterId;
            // passing only the id makes GHL return page 1 forever.
            $query = ['location_id' => $locationId, 'limit' => 100];
            if ($startAfter !== null && $startAfterId !== null) {
                $query['startAfter'] = $startAfter;
                $query['startAfterId'] = $startAfterId;
            }

            $res  = $this->client($apiKey)->get("{$this->baseUrl}/opportunities/search", $query);
            $body = $res->json();
            $batch = $body['opportunities'] ?? [];

            if (empty($batch)) {
                break;
            }

            $all = array_merge($all, $batch);

            // Advance the cursor from the response meta; stop when no next page.
            $meta = $body['meta'] ?? [];
            $startAfter   = $meta['startAfter'] ?? null;
            $startAfterId = $meta['startAfterId'] ?? null;
            $hasNext = ! empty($meta['nextPageUrl']) && ! empty($meta['nextPage']);

        } while ($hasNext && count($batch) === 100 && ++$guard < 100);

        return $all;
    }

    private function aggregate(array $opportunities, array $stageMap, array $pipelineNames = [], ?string $priorityPipeline = null): array
    {
        $summary = $this->emptySummary();
        $stages = $weeklyTrend = $leadSources = $leads = [];

        $startOfWeek     = strtotime('monday this week');
        $endOfWeek       = strtotime('sunday this week 23:59:59');
        $startOfLastWeek = strtotime('monday last week');
        $endOfLastWeek   = strtotime('sunday last week 23:59:59');

        $byStatus = [];

        foreach ($opportunities as $opp) {
            $createdAt = strtotime($opp['createdAt'] ?? '');
            $value     = (float) ($opp['monetaryValue'] ?? 0);
            $stageName = $stageMap[$opp['pipelineStageId'] ?? null] ?? 'Unknown';
            $status    = strtolower($opp['status'] ?? 'open');

            $summary['pipeline_value'] += $value;
            $stages[$stageName] = ($stages[$stageName] ?? 0) + 1;
            $byStatus[$status]  = ($byStatus[$status] ?? 0) + 1;

            // Won/revenue come from the opportunity status (reliable),
            // not a stage-name guess.
            if ($status === 'won') {
                $summary['closed_sales']++;
                $summary['revenue'] += $value;
            }
            if (stripos($stageName, 'quote') !== false) {
                $summary['quotes_issued']++;
            }
            if ($createdAt >= $startOfWeek && $createdAt <= $endOfWeek) {
                $summary['weekly_leads']++;
            }
            if ($createdAt >= $startOfLastWeek && $createdAt <= $endOfLastWeek) {
                $summary['last_week_leads']++;
            }

            $label = date('M d', $createdAt);
            $weeklyTrend[$label] = ($weeklyTrend[$label] ?? 0) + 1;

            $source = $this->normaliseSource($opp['source'] ?? 'unknown');
            $leadSources[$source] = ($leadSources[$source] ?? 0) + 1;

            // Lightweight per-lead record for the drill-down explorer
            // (short keys keep the cached payload small). `cid`/`pl` are used
            // only for de-duplication and stripped before the payload is stored.
            $leads[] = [
                'n'   => $opp['name'] ?? ($opp['contact']['name'] ?? '—'),
                's'   => $source,
                'st'  => $stageName,
                'v'   => $value,
                'd'   => $label,
                'ts'  => $createdAt,
                'cid' => $opp['contactId'] ?? ($opp['contact']['id'] ?? null),
                'pl'  => $pipelineNames[$opp['pipelineId'] ?? ''] ?? '',
            ];
        }

        $won  = $byStatus['won'] ?? 0;
        $lost = $byStatus['lost'] ?? 0;

        $summary['new_leads']     = $stages['New Lead'] ?? 0;
        $summary['stages']        = $stages;
        $summary['by_status']     = $byStatus;
        $summary['open_count']    = $byStatus['open'] ?? 0;
        $summary['won_count']     = $won;
        $summary['lost_count']    = $lost;
        $summary['win_rate']      = ($won + $lost) > 0 ? round($won / ($won + $lost) * 100, 1) : 0.0;
        $summary['avg_deal_size'] = $won > 0 ? round($summary['revenue'] / $won) : 0;
        $summary['weekly_trend']  = array_values($weeklyTrend);
        $summary['weekly_labels'] = array_keys($weeklyTrend);
        $summary['lead_sources']  = $leadSources;

        // Collapse multiple opportunities for the same contact into one row,
        // keeping the priority ("progress") pipeline where available.
        $leads = $this->dedupeLeads($leads, $priorityPipeline);

        usort($leads, fn ($x, $y) => $y['ts'] <=> $x['ts']); // newest first
        $summary['leads'] = $leads;

        return $summary;
    }

    /**
     * One row per contact, with two collapsing passes:
     *
     *  1. Same GHL contact id across several pipelines → keep the record in
     *     $priorityPipeline (e.g. "Build Progress") if present, else the newest.
     *  2. The same person can exist as separate contact records (different ids):
     *     one still in the sales pipeline, one already promoted to the progress
     *     pipeline. When a name has any row in $priorityPipeline, that single
     *     (newest progress) row wins and every other row for that name is dropped.
     *
     * Contacts without an id fall back to a name key in pass 1.
     */
    private function dedupeLeads(array $leads, ?string $priorityPipeline): array
    {
        // --- Pass 1: collapse by contact id. ---
        $groups = [];
        foreach ($leads as $ld) {
            $key = $ld['cid'] ?: ('name:' . mb_strtolower(trim((string) $ld['n'])));
            $groups[$key][] = $ld;
        }

        $rows = [];
        foreach ($groups as $group) {
            if (count($group) === 1) {
                $rows[] = $group[0];
                continue;
            }

            $chosen = null;
            // First choice: the newest record in the priority pipeline.
            if ($priorityPipeline) {
                foreach ($group as $g) {
                    if ($this->inPipeline($g['pl'], $priorityPipeline)
                        && ($chosen === null || $g['ts'] > $chosen['ts'])) {
                        $chosen = $g;
                    }
                }
            }
            // Fallback: the newest record overall.
            if ($chosen === null) {
                foreach ($group as $g) {
                    if ($chosen === null || $g['ts'] > $chosen['ts']) {
                        $chosen = $g;
                    }
                }
            }
            $rows[] = $chosen;
        }

        // --- Pass 2: collapse same-name records when a progress row exists. ---
        if ($priorityPipeline) {
            // Only collapse on a real name. Blank/placeholder names ("—") are
            // not identities, so distinct unnamed leads must never be merged.
            $nameKey = function ($r): string {
                $name = mb_strtolower(trim((string) $r['n']));
                return ($name === '' || $name === '—') ? '' : $name;
            };

            // Names that have at least one row in the priority pipeline.
            $hasProgress = [];
            foreach ($rows as $r) {
                if (($name = $nameKey($r)) !== '' && $this->inPipeline($r['pl'], $priorityPipeline)) {
                    $hasProgress[$name] = true;
                }
            }

            $kept = [];
            $progressIndex = []; // name => position of its kept progress row in $kept
            foreach ($rows as $r) {
                $name = $nameKey($r);

                // No real name, or no progress row for this name → keep untouched.
                if ($name === '' || ! isset($hasProgress[$name])) {
                    $kept[] = $r;
                    continue;
                }
                // A progress row exists for this name → drop non-progress rows.
                if (! $this->inPipeline($r['pl'], $priorityPipeline)) {
                    continue;
                }
                // Keep only the newest progress row per name.
                if (isset($progressIndex[$name])) {
                    if ($r['ts'] > $kept[$progressIndex[$name]]['ts']) {
                        $kept[$progressIndex[$name]] = $r;
                    }
                } else {
                    $progressIndex[$name] = count($kept);
                    $kept[] = $r;
                }
            }
            $rows = $kept;
        }

        // Strip the de-dup helper keys so the cached payload stays lean.
        foreach ($rows as &$r) {
            unset($r['cid'], $r['pl']);
        }
        unset($r);

        return $rows;
    }

    /** Case-insensitive substring match of a pipeline name (e.g. "Build Progress"). */
    private function inPipeline(string $pipeline, string $needle): bool
    {
        return $pipeline !== '' && stripos($pipeline, $needle) !== false;
    }

    private function normaliseSource(string $source): string
    {
        $source = strtolower($source);
        return match (true) {
            str_contains($source, 'facebook'), str_contains($source, 'meta') => 'Meta Ads',
            str_contains($source, 'google')  => 'Google',
            str_contains($source, 'tiktok')  => 'TikTok',
            str_contains($source, 'organic') => 'Organic',
            default => 'Referral',
        };
    }

    private function client(string $apiKey)
    {
        return Http::withHeaders([
            'Authorization' => "Bearer $apiKey",
            'Version'       => $this->version,
            'Content-Type'  => 'application/json',
        ])->timeout(30);
    }

    private function emptySummary(): array
    {
        return [
            'weekly_leads'   => 0,
            'new_leads'      => 0,
            'pipeline_value' => 0,
            'closed_sales'   => 0,
            'revenue'        => 0,
            'quotes_issued'  => 0,
            'last_week_leads' => 0,
            'open_count'     => 0,
            'won_count'      => 0,
            'lost_count'     => 0,
            'win_rate'       => 0.0,
            'avg_deal_size'  => 0,
            'by_status'      => [],
            'stages'         => [],
            'pipelines'      => [],
            'leads'          => [],
            'weekly_trend'   => [],
            'weekly_labels'  => [],
            'lead_sources'   => [],
        ];
    }
}
