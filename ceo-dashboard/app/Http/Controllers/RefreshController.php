<?php

namespace App\Http\Controllers;

use App\Services\Ghl\FinanceService;
use App\Services\GoDaddy\GoDaddyService;
use App\Services\Portals\ClientProjectsService;
use App\Services\Portals\OperationsService;
use App\Services\Portals\StaffService;
use App\Services\Portals\WorkReportService;
use App\Support\Snapshot;
use Illuminate\Http\Request;

/**
 * Manual "refresh now" from the topbar.
 * Fast portal-backed pages refresh synchronously (seconds); heavy GHL/security
 * data refreshes via a detached background warm instead, so the request never
 * hangs for minutes.
 */
class RefreshController extends Controller
{
    public function __invoke(Request $request)
    {
        $page    = (string) $request->input('page', '');
        $account = (string) $request->input('account', 'all');

        $sync = [
            'staff'           => fn () => app(StaffService::class)->overview(),
            'client-projects' => fn () => app(ClientProjectsService::class)->overview(),
            'operations'      => fn () => app(OperationsService::class)->overview(),
            'reports'         => fn () => app(WorkReportService::class)->rows(),
            'renewals'        => fn () => app(GoDaddyService::class)->overview(),
            'finance'         => fn () => app(FinanceService::class)->summary($account),
        ];

        if (isset($sync[$page])) {
            Snapshot::forceRefresh();
            try {
                $sync[$page]();
            } finally {
                Snapshot::forceRefresh(false);
            }

            return back()->with('refreshed', 'Live data pulled just now — this page is fully up to date.');
        }

        // Heavy pages (GHL fan-out, full security scan): refresh in background.
        Snapshot::triggerBackgroundWarm(ignoreThrottle: true);

        return back()->with('refreshed', 'Refreshing all dashboard data in the background — reload in a minute or two for the freshest numbers.');
    }
}
