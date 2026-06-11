<?php

namespace App\Services;

use App\Services\Cloudflare\SecurityService;
use App\Services\Ghl\FinanceService;
use App\Services\Ghl\GhlService;
use App\Services\GoDaddy\GoDaddyService;
use App\Services\Portals\ClientProjectsService;
use App\Services\Portals\OperationsService;
use App\Services\Portals\StaffService;

/**
 * Composes a single executive "cockpit" view model from every data source.
 * Reads the already-cached snapshots, so it's cheap to assemble.
 */
class CeoOverviewService
{
    public function __construct(
        private GhlService $ghl,
        private FinanceService $finance,
        private StaffService $staff,
        private ClientProjectsService $clientProjects,
        private OperationsService $operations,
        private SecurityService $security,
        private GoDaddyService $godaddy,
    ) {
    }

    public function build(string $account = 'all'): array
    {
        $pipeline = $this->ghl->pipelineSummary($account);
        $finance  = $this->finance->summary($account);
        $staff    = $this->staff->overview();
        $delivery = $this->clientProjects->overview();
        $ops      = $this->operations->overview();
        $security = $this->security->overview();
        $renewals = $this->godaddy->overview()['summary'] ?? [];

        return [
            'renewals' => $renewals,
            'pipeline' => $pipeline,
            'finance'  => $finance,
            'staff'    => $staff,
            'delivery' => $delivery,
            'ops'      => $ops,
            'security' => $security,

            // Headline KPIs for the hero row.
            'kpis' => [
                'pipeline_value' => $pipeline['pipeline_value'] ?? 0,
                'weekly_leads'   => $pipeline['weekly_leads'] ?? 0,
                'last_week_leads' => $pipeline['last_week_leads'] ?? 0,
                'open_deals'     => $pipeline['open_count'] ?? 0,
                'cash_in'        => $finance['cash_in'] ?? 0,
                // Matches the BGR portal's Projects page (GHL Project Progress pipeline)
                'active_builds'  => $delivery['totals']['bgr_builds'] ?? ($delivery['totals']['bgr_active'] ?? 0),
                'pending_builds' => $delivery['totals']['bgr_pending'] ?? 0,
                'total_builds'   => $delivery['totals']['bgr_projects'] ?? 0,
                'avg_progress'   => $delivery['totals']['avg_progress'] ?? 0,
                'bcf_orders'     => $delivery['totals']['bcf_orders'] ?? 0,
                'staff_active'   => $staff['staff']['active_staff'] ?? 0,
                'clocked_in'     => $staff['attendance']['clocked_in_now'] ?? 0,
                'todays_jobs'    => $ops['jobs']['today'] ?? 0,
                'ssl'            => $security['compliance']['ssl'] ?? null,
                'dns'            => $security['compliance']['dns'] ?? null,
                'alerts'         => $security['alerts'] ?? 0,
            ],

            // Chart-ready datasets.
            'charts' => [
                'weekly' => [
                    'labels' => $pipeline['weekly_labels'] ?? [],
                    'data'   => $pipeline['weekly_trend'] ?? [],
                ],
                'sources' => [
                    'labels' => array_keys($pipeline['lead_sources'] ?? []),
                    'data'   => array_values($pipeline['lead_sources'] ?? []),
                ],
                'stages' => [
                    'labels' => array_keys($pipeline['stages'] ?? []),
                    'data'   => array_values($pipeline['stages'] ?? []),
                ],
                'pipelines' => $pipeline['pipelines'] ?? [],
            ],
        ];
    }
}
