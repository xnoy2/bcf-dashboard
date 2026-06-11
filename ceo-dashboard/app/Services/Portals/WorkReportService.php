<?php

namespace App\Services\Portals;

use App\Support\Snapshot;

/**
 * Unified work report across the live portals — every staff job, BGR build
 * and BCF order, with who it's assigned to and where it stands.
 * Replaces the retired Trello reports as the single executive report view.
 */
class WorkReportService
{
    public function __construct(
        private StaffService $staff,
        private ClientProjectsService $projects,
    ) {
    }

    public function rows(): array
    {
        return Snapshot::remember('work_report:all', 5, function () {
            $rows = [];

            // Staff portal jobs (carry assignee, van, project, schedule)
            foreach ($this->staff->overview()['jobs_list'] ?? [] as $j) {
                $for = trim(($j['project'] ?? '') . (($j['van'] ?? null) ? ' · van ' . $j['van'] : ''), ' ·');
                $rows[] = [
                    'type'     => 'Staff Job',
                    'title'    => $j['title'] ?: '—',
                    'for'      => $for ?: '—',
                    'assigned' => $j['staff'] ?: 'Unassigned',
                    'status'   => self::normalise($j['status'] ?? ''),
                    'date'     => $j['date'] ?? null,
                ];
            }

            $delivery = $this->projects->overview();

            // BGR garden-room builds
            foreach ($delivery['bgr_projects'] ?? [] as $p) {
                $rows[] = [
                    'type'     => 'BGR Build',
                    'title'    => $p['name'] . ' — ' . $p['current_stage'] . ' (' . $p['progress_pct'] . '%)',
                    'for'      => $p['client'],
                    'assigned' => '—', // portal has no worker assignments on builds yet
                    'status'   => self::normalise($p['status'] ?? ''),
                    'date'     => $p['estimated'] ?? null,
                ];
            }

            // BCF climbing-frame orders
            foreach ($delivery['bcf_orders'] ?? [] as $o) {
                $rows[] = [
                    'type'     => 'BCF Order',
                    'title'    => $o['order_number'] . ' · ' . \Illuminate\Support\Str::limit($o['product'], 40),
                    'for'      => $o['client'],
                    'assigned' => $o['worker'] ?: 'Unassigned',
                    'status'   => self::normalise($o['status'] ?? ''),
                    'date'     => $o['install_date'] ?? null,
                ];
            }

            // Newest dated work first; undated items sink to the bottom.
            usort($rows, fn ($a, $b) => strtotime($b['date'] ?? '1970-01-01') <=> strtotime($a['date'] ?? '1970-01-01'));

            return $rows;
        });
    }

    /** Map each portal's status vocabulary onto one executive set. */
    private static function normalise(string $s): string
    {
        return match (strtolower($s)) {
            'completed', 'complete', 'done', 'installed' => 'Completed',
            'in_progress', 'active', 'in build'          => 'In Progress',
            'scheduled'                                  => 'Scheduled',
            'cancelled'                                  => 'Cancelled',
            default                                      => 'Pending',
        };
    }
}
