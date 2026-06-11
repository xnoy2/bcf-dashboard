<?php

namespace App\Http\Controllers;

use App\Services\Ghl\GhlService;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    public function __construct(private GhlService $ghl)
    {
    }

    public function index(Request $request)
    {
        $accounts = config('integrations.accounts');
        $account  = $request->query('account', 'all');
        if ($account !== 'all' && ! isset($accounts[$account])) {
            $account = 'all';
        }

        $pipeline = $this->ghl->pipelineSummary($account);

        return view('marketing', [
            'account'      => $account,
            'accounts'     => $accounts,
            'leadSources'  => $pipeline['lead_sources'] ?? [],
            'weeklyLabels' => $pipeline['weekly_labels'] ?? [],
            'weeklyTrend'  => $pipeline['weekly_trend'] ?? [],
            'weeklyLeads'  => $pipeline['weekly_leads'] ?? 0,
            'lastWeek'     => $pipeline['last_week_leads'] ?? 0,
            'leads'        => $pipeline['leads'] ?? [],
        ]);
    }
}
