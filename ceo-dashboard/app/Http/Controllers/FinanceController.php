<?php

namespace App\Http\Controllers;

use App\Services\Ghl\FinanceService;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function __construct(private FinanceService $finance)
    {
    }

    public function index(Request $request)
    {
        $accounts = config('integrations.accounts');
        $account  = $request->query('account', 'all');
        if ($account !== 'all' && ! isset($accounts[$account])) {
            $account = 'all';
        }

        return view('finance', [
            'account'  => $account,
            'accounts' => $accounts,
            'finance'  => $this->finance->summary($account),
        ]);
    }
}
