<?php

namespace App\Http\Controllers;

use App\Services\Ghl\AppointmentsService;
use App\Services\Ghl\GhlService;
use Illuminate\Http\Request;

class SalesController extends Controller
{
    public function __construct(
        private GhlService $ghl,
        private AppointmentsService $appointments,
    ) {
    }

    public function index(Request $request)
    {
        $accounts = config('integrations.accounts');
        $account  = $this->resolveAccount($request);

        return view('sales', [
            'account'      => $account,
            'accounts'     => $accounts,
            'pipeline'     => $this->ghl->pipelineSummary($account),
            'appointments' => $this->appointments->summary($account),
        ]);
    }
}
