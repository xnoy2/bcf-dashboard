<?php

namespace App\Console\Commands;

use App\Services\Cloudflare\SecurityService;
use App\Services\Ghl\AppointmentsService;
use App\Services\Ghl\FinanceService;
use App\Services\Ghl\GhlService;
use App\Services\GoDaddy\GoDaddyService;
use App\Services\Portals\ClientProjectsService;
use App\Services\Portals\OperationsService;
use App\Services\Portals\StaffService;
use App\Services\Portals\WorkReportService;
use App\Support\Snapshot;
use Illuminate\Console\Command;

class WarmDashboard extends Command
{
    protected $signature = 'dashboard:warm';
    protected $description = 'Pre-fetch all dashboard data sources into snapshot cache so pages load instantly.';

    public function handle(
        GhlService $ghl,
        FinanceService $finance,
        AppointmentsService $appointments,
        WorkReportService $workReport,
        SecurityService $security,
        StaffService $staff,
        ClientProjectsService $clientProjects,
        OperationsService $operations,
        GoDaddyService $godaddy,
    ): int {
        // Force every service to fetch fresh and overwrite its snapshot.
        Snapshot::forceRefresh();

        $accounts = array_keys(config('integrations.accounts', []));
        $targets  = array_merge(['all'], $accounts);

        foreach ($targets as $account) {
            $this->line("Pipeline:     $account");
            $ghl->pipelineSummary($account);

            $this->line("Finance:      $account");
            $finance->summary($account);

            $this->line("Appointments: $account");
            $appointments->summary($account);
        }

        $this->line('Security (Cloudflare)…');
        $security->overview();

        $this->line('Staff portal…');
        $staff->overview();

        $this->line('Client projects (BGR + BCF)…');
        $clientProjects->overview();

        $this->line('Operations…');
        $operations->overview();

        $this->line('Work report…');
        $workReport->rows();

        $this->line('Renewals (GoDaddy)…');
        $godaddy->overview();

        $this->info('Dashboard snapshots warmed.');

        return self::SUCCESS;
    }
}
