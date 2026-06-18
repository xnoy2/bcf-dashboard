<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CeoOverviewService;
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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Read-only JSON API over the cached dashboard snapshots.
 *
 * Every action returns the same envelope:
 *   { "data": {...}, "meta": { "account": "all", "generated_at": "..." } }
 *
 * All reads come from the snapshot cache (DB), so they are fast and do not
 * call upstream providers on the request path — data is refreshed out of band
 * by the scheduler's `dashboard:warm`.
 */
class ApiController extends Controller
{
    public function __construct(
        private GhlService $ghl,
        private FinanceService $finance,
        private AppointmentsService $appointments,
        private CeoOverviewService $overview,
        private SecurityService $security,
        private StaffService $staff,
        private ClientProjectsService $clientProjects,
        private OperationsService $operations,
        private WorkReportService $workReport,
        private GoDaddyService $godaddy,
    ) {
    }

    public function ping(): JsonResponse
    {
        return response()->json([
            'data' => [
                'ok'        => true,
                'service'   => 'CEO Dashboard API',
                'version'   => 'v1',
                'accounts'  => array_merge(['all'], array_keys(config('integrations.accounts', []))),
                'endpoints' => [
                    'GET /api/v1/overview?account=all|bcf|bgr|rg',
                    'GET /api/v1/pipeline?account=all|bcf|bgr|rg',
                    'GET /api/v1/finance?account=all|bcf|bgr|rg',
                    'GET /api/v1/appointments?account=all|bcf|bgr|rg',
                    'GET /api/v1/security',
                    'GET /api/v1/staff',
                    'GET /api/v1/client-projects',
                    'GET /api/v1/operations',
                    'GET /api/v1/work-report',
                    'GET /api/v1/renewals',
                ],
            ],
            'meta' => ['generated_at' => optional(Snapshot::latest())->toIso8601String()],
        ]);
    }

    public function overview(Request $request): JsonResponse
    {
        $account = $this->account($request);

        return $this->respond($this->overview->build($account), "pipeline:$account", $account);
    }

    public function pipeline(Request $request): JsonResponse
    {
        $account = $this->account($request);

        return $this->respond($this->ghl->pipelineSummary($account), "pipeline:$account", $account);
    }

    public function finance(Request $request): JsonResponse
    {
        $account = $this->account($request);

        return $this->respond($this->finance->summary($account), "finance:$account", $account);
    }

    public function appointments(Request $request): JsonResponse
    {
        $account = $this->account($request);

        return $this->respond($this->appointments->summary($account), "appointments:$account", $account);
    }

    public function security(): JsonResponse
    {
        return $this->respond($this->security->overview(), 'security:all');
    }

    public function staff(): JsonResponse
    {
        return $this->respond($this->staff->overview(), 'staff:overview');
    }

    public function clientProjects(): JsonResponse
    {
        return $this->respond($this->clientProjects->overview(), 'client_projects:overview');
    }

    public function operations(): JsonResponse
    {
        return $this->respond($this->operations->overview(), 'operations:overview');
    }

    public function workReport(): JsonResponse
    {
        return $this->respond($this->workReport->rows(), 'work_report:all');
    }

    public function renewals(): JsonResponse
    {
        return $this->respond($this->godaddy->overview(), 'renewals:all');
    }

    /**
     * Validate the ?account filter against the configured accounts.
     * Defaults to "all"; an explicit unknown value is a 422.
     */
    private function account(Request $request): string
    {
        $account = $request->query('account', 'all');
        $allowed = array_merge(['all'], array_keys(config('integrations.accounts', [])));

        abort_unless(
            is_string($account) && in_array($account, $allowed, true),
            422,
            'Invalid account. Allowed: ' . implode(', ', $allowed) . '.'
        );

        return $account;
    }

    private function respond(array $data, string $snapshotKey, ?string $account = null): JsonResponse
    {
        $meta = ['generated_at' => optional(Snapshot::generatedAt($snapshotKey))->toIso8601String()];
        if ($account !== null) {
            $meta['account'] = $account;
        }

        return response()->json(['data' => $data, 'meta' => $meta]);
    }
}
