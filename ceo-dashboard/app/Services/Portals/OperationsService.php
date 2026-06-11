<?php

namespace App\Services\Portals;

use App\Support\Snapshot;

/**
 * Delivery cockpit — a single informative roll-up across all three portals:
 *   - Staff portal  → workforce + jobs pipeline
 *   - BGR portal    → garden-room builds + progress
 *   - BCF portal    → climbing-frame orders/installs
 */
class OperationsService
{
    public function __construct(
        private StaffService $staff,
        private ClientProjectsService $projects,
    ) {
    }

    public function overview(): array
    {
        return Snapshot::remember('operations:overview', 5, function () {
            $staff    = $this->staff->overview();
            $dash     = $staff['dashboard'] ?? [];
            $jobsSum  = $staff['jobs'] ?? [];
            $delivery = $this->projects->overview();

            $bgr = collect($delivery['bgr_projects'] ?? []);
            $bcf = collect($delivery['bcf_orders'] ?? []);

            return [
                'workforce' => [
                    'active_staff'      => $dash['staff']['active'] ?? 0,
                    'total_staff'       => $dash['staff']['total'] ?? 0,
                    'clocked_in'        => $dash['attendance']['clocked_in_now'] ?? 0,
                    'hours_week'        => $dash['attendance']['hours_this_week'] ?? 0,
                    'pending_approvals' => $dash['attendance']['pending_approvals'] ?? 0,
                    'pending_leave'     => $dash['approvals']['pending_leave'] ?? 0,
                    'pending_overtime'  => $dash['approvals']['pending_overtime'] ?? 0,
                ],

                'jobs' => [
                    'today'       => $dash['jobs']['today'] ?? 0,
                    'in_progress' => $dash['jobs']['in_progress'] ?? 0,
                    'scheduled'   => $dash['jobs']['scheduled'] ?? 0,
                    'by_status'   => $jobsSum['jobs_by_status'] ?? [],
                ],

                'builds' => [ // BGR
                    'total'        => $bgr->count(),
                    // headline = GHL Project Progress pipeline (matches BGR portal)
                    'active'       => $delivery['totals']['bgr_builds'] ?? ($delivery['totals']['bgr_active'] ?? 0),
                    'avg_progress' => $delivery['totals']['avg_progress'] ?? 0,
                    'by_status'    => $bgr->countBy('status')->all(),
                    'in_progress'  => $bgr->whereNotIn('status', ['completed', 'cancelled'])
                                          ->sortByDesc('progress_pct')->take(6)->values()->all(),
                ],

                'orders' => [ // BCF
                    'total'    => $bcf->count(),
                    'birthday' => $bcf->where('birthday', true)->count(),
                    'recent'   => $bcf->take(6)->values()->all(),
                ],

                'projects_by_business' => $jobsSum['projects_by_business'] ?? [],
            ];
        });
    }
}
