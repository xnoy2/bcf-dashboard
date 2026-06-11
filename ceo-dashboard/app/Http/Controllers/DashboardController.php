<?php

namespace App\Http\Controllers;

use App\Services\CeoOverviewService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private CeoOverviewService $overview)
    {
    }

    public function index(Request $request)
    {
        $accounts = config('integrations.accounts');
        $account  = $this->resolveAccount($request);

        return view('dashboard', array_merge(
            ['account' => $account, 'accounts' => $accounts],
            $this->overview->build($account),
        ));
    }
}
