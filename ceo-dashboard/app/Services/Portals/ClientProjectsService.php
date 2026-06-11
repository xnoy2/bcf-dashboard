<?php

namespace App\Services\Portals;

use App\Services\Ghl\GhlService;
use App\Support\Snapshot;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Client-facing delivery data:
 *  - BGR Client Portal  → /projects  (garden room builds, with stages/progress)
 *  - BCF Client Portal  → /worker-api/orders (climbing-frame installs)
 */
class ClientProjectsService
{
    /** Canonical BCF stage labels in build order (captured from order details). */
    private array $bcfStageOrder = [];

    public function __construct(private GhlService $ghl)
    {
    }

    /**
     * The BGR portal's own "Projects" page counts opportunities in the GHL
     * "Project Progress" pipeline — that's the number the business recognises.
     * Use it as the headline builds count so the dashboard matches the portal.
     */
    private function ghlBuildsCount(): ?int
    {
        // Serve the cached GHL pipeline even during a forced portal refresh —
        // a manual "refresh" of portal data shouldn't wait ~60s on a full
        // GHL refetch (the scheduled warm keeps that snapshot current).
        $wasForced = Snapshot::isForced();
        if ($wasForced) {
            Snapshot::forceRefresh(false);
        }
        try {
            $summary = $this->ghl->pipelineSummary('bgr');
        } finally {
            if ($wasForced) {
                Snapshot::forceRefresh(true);
            }
        }

        foreach ($summary['pipelines'] ?? [] as $pl) {
            if (stripos($pl['name'], 'project progress') !== false) {
                return array_sum($pl['counts']);
            }
        }

        return null;
    }

    public function overview(): array
    {
        return Snapshot::remember('client_projects:overview', 5, function () {
            $bgr = $this->bgrProjects();
            $bcf = $this->bcfOrders();

            $b = collect($bgr);
            $ghlBuilds = $this->ghlBuildsCount();

            return [
                'bgr_projects' => $bgr,
                'bcf_orders'   => $bcf,
                'totals'       => [
                    'bgr_projects'  => $b->count(),
                    'bcf_orders'    => count($bcf),
                    // Headline builds count = GHL "Project Progress" pipeline,
                    // matching the BGR portal's Projects page. Falls back to
                    // the portal projects table if GHL is unavailable.
                    'bgr_builds'    => $ghlBuilds ?? $b->whereIn('status', ['active', 'pending'])->count(),
                    // "Active" = the portal's own status field, nothing broader.
                    // (pending = not started yet, so it must NOT count as active)
                    'bgr_active'    => $b->where('status', 'active')->count(),
                    'bgr_pending'   => $b->where('status', 'pending')->count(),
                    'avg_progress'  => $b->count() ? round($b->avg('progress_pct')) : 0,
                    'bgr_completed' => $b->where('status', 'completed')->count(),
                    'bgr_not_started' => $b->where('progress_pct', 0)->count(),
                    'bgr_ghl_linked' => $b->filter(fn ($p) => ! empty($p['ghl_link']))->count(),
                    'bcf_birthday'  => collect($bcf)->where('birthday', true)->count(),
                ],
                // Executive analytics
                'bgr_by_stage' => $b->countBy('current_stage')->all(),
                // BCF funnel: orders sitting at each canonical stage, in build
                // order, plus a Complete bucket when any are finished.
                'bcf_by_stage' => (function () use ($bcf) {
                    $c = collect($bcf);
                    $out = [];
                    foreach ($this->bcfStageOrder as $label) {
                        $out[$label] = $c->where('current_stage', $label)->count();
                    }
                    $completed = $c->where('current_stage', 'Complete')->count();
                    if ($completed) {
                        $out['Complete'] = $completed;
                    }
                    return $out;
                })(),
                'bcf_progress_buckets' => (function () use ($bcf) {
                    $c = collect($bcf);
                    return [
                        'Not started' => $c->where('progress', 0)->count(),
                        '1–33%'       => $c->whereBetween('progress', [1, 33])->count(),
                        '34–66%'      => $c->whereBetween('progress', [34, 66])->count(),
                        '67–99%'      => $c->whereBetween('progress', [67, 99])->count(),
                        'Complete'    => $c->where('progress', 100)->count(),
                    ];
                })(),
                'bgr_progress_buckets' => [
                    'Not started'   => $b->where('progress_pct', 0)->count(),
                    '1–33%'         => $b->whereBetween('progress_pct', [1, 33])->count(),
                    '34–66%'        => $b->whereBetween('progress_pct', [34, 66])->count(),
                    '67–99%'        => $b->whereBetween('progress_pct', [67, 99])->count(),
                    'Complete'      => $b->where('progress_pct', 100)->count(),
                ],
            ];
        });
    }

    private function bgrProjects(): array
    {
        $client = PortalClient::for('bgr_client');

        // The endpoint is paginated (15/page) and ignores per_page, so follow
        // the pages until meta.last_page to gather every project.
        $rows = [];
        $page = 1;
        $lastPage = 1;
        $guard = 0;

        do {
            $res = $client->get('projects', ['page' => $page])->json();
            $rows = array_merge($rows, $res['data'] ?? (array_is_list((array) $res) ? (array) $res : []));
            $lastPage = (int) ($res['meta']['last_page'] ?? 1);
            $page++;
        } while ($page <= $lastPage && ++$guard < 25);

        return collect($rows)->map(fn ($p) => [
            'id'           => $p['id'] ?? null,
            'name'         => $p['name'] ?? '',
            'client'       => $p['client']['name'] ?? '—',
            'status'       => $p['status'] ?? 'unknown',
            'progress_pct' => (int) ($p['progress_pct'] ?? 0),
            'current_stage' => $p['current_stage']['name'] ?? '—',
            'stages'       => ($p['completed_stages'] ?? 0) . '/' . ($p['total_stages'] ?? $p['stages_count'] ?? 0),
            'estimated'    => $p['estimated_completion'] ?? null,
            'ghl_link'     => $p['ghl_opportunity_id'] ?? null,
        ])->all();
    }

    private function bcfOrders(): array
    {
        $client = PortalClient::for('bcf_client');
        $res = $client->get('orders')->json();
        $rows = $res['orders'] ?? $res['data'] ?? $res ?? [];

        // The list endpoint omits contract_amount and stages — fetch each
        // order's detail in parallel to enrich with value + build progress.
        $ids = collect($rows)->pluck('id')->filter()->values()->all();
        $details = [];
        if ($ids) {
            $responses = Http::pool(fn (Pool $pool) => array_map(
                fn ($id) => $pool->as($id)
                    ->withHeaders(['x-api-key' => config('integrations.portals.bcf_client.auth.value')])
                    ->timeout(30)
                    ->get(rtrim(config('integrations.portals.bcf_client.base_url'), '/') . "/orders/$id"),
                $ids,
            ));
            foreach ($responses as $id => $r) {
                if ($r instanceof Response && $r->successful()) {
                    // The detail payload nests the order and its build stages
                    // as SIBLINGS: { order: {...}, stages: [...] }.
                    $payload = $r->json() ?? [];
                    $details[$id] = [
                        'order'  => $payload['order'] ?? $payload,
                        'stages' => $payload['stages'] ?? [],
                    ];
                }
            }
        }

        return collect($rows)->map(function ($o) use ($details) {
            $detail = $details[$o['id'] ?? ''] ?? [];
            $d      = $detail['order'] ?? [];
            $stages = collect($detail['stages'] ?? [])->sortBy('stage_number');
            $total  = $stages->count();
            $done   = $stages->where('status', 'done')->count();

            if ($total > 0) {
                // Real build-stage progress — mirrors the portal's
                // "X of Y stages complete".
                $progress = (int) round($done / $total * 100);
                $status   = $progress === 100 ? 'complete' : ($done > 0 ? 'in build' : 'pending');
                $stageRef = "$done/$total";
                $currentStage = $stages->firstWhere('status', '!=', 'done')['label'] ?? 'Complete';

                if (empty($this->bcfStageOrder)) {
                    $this->bcfStageOrder = $stages->pluck('label')->values()->all();
                }
            } else {
                // No stages configured yet — derive delivery status from the
                // installation date so the CEO still sees where each order is.
                $install = $o['installation_date'] ?? null;
                $ts = $install ? strtotime($install) : null;
                if ($ts && $ts < time()) {
                    [$status, $progress] = ['installed', 100];
                } elseif ($ts) {
                    [$status, $progress] = ['scheduled', 50];
                } else {
                    [$status, $progress] = ['pending', 0];
                }
                $stageRef = null;
                $currentStage = null;
            }

            return [
                'order_number' => $o['order_number'] ?? ('#' . ($o['id'] ?? '')),
                'client'       => $o['client']['name'] ?? '—',
                'product'      => $o['product_order'] ?? '—',
                'install_date' => $o['installation_date'] ?? null,
                'worker'       => $o['worker']['name'] ?? ($o['worker'] ?? 'Unassigned'),
                'address'      => $o['address'] ?? '',
                'birthday'     => (bool) ($o['is_birthday_booking'] ?? false),
                'value'        => isset($d['contract_amount']) ? (float) $d['contract_amount'] : null,
                'status'       => $status,
                'progress'     => $progress,
                'stage_ref'    => $stageRef,
                'current_stage' => $currentStage,
            ];
        })->all();
    }
}
