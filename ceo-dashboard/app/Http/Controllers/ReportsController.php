<?php

namespace App\Http\Controllers;

use App\Services\Portals\WorkReportService;

class ReportsController extends Controller
{
    public function __construct(private WorkReportService $report)
    {
    }

    public function index()
    {
        $rows = $this->report->rows();
        $by   = collect($rows)->countBy('status');

        return view('reports', [
            'accounts' => config('integrations.accounts'),
            'account'  => 'all',
            'rows'     => $rows,
            'stats'    => [
                'total'       => count($rows),
                'completed'   => $by['Completed'] ?? 0,
                'in_progress' => $by['In Progress'] ?? 0,
                'upcoming'    => ($by['Pending'] ?? 0) + ($by['Scheduled'] ?? 0),
            ],
        ]);
    }
}
