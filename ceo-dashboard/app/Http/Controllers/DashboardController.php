<?php

namespace App\Http\Controllers;

use App\Models\CalendarEntry;
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

        // Calendar widget: overdue first, then the soonest upcoming jobs.
        $calendar = CalendarEntry::query()
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->where(fn ($q) => $q->overdue()->orWhere(fn ($w) => $w->dueWithin(30)))
            ->orderByRaw('COALESCE(end_date, start_date) asc')
            ->limit(8)
            ->get();

        return view('dashboard', array_merge(
            ['account' => $account, 'accounts' => $accounts, 'calendarJobs' => $calendar],
            $this->overview->build($account),
        ));
    }
}
