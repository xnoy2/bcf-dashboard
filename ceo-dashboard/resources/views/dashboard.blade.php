@extends('layouts.app')

@section('title', 'CEO Overview')

@section('content')
<div class="app-content-header">
    <div class="container-fluid d-flex justify-content-between align-items-center">
        <h3 class="mb-0">CEO Overview</h3>
        <span class="badge text-bg-primary">{{ $account === 'all' ? 'All Accounts' : ($accounts[$account]['name'] ?? $account) }}</span>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        @php
            $k = $kpis;
            $leadDelta = ($k['weekly_leads'] ?? 0) - ($k['last_week_leads'] ?? 0);
        @endphp

        {{-- Hero KPI row --}}
        <div class="row g-3">
            <div class="col-6 col-xl-3">
                <a href="{{ route('sales', ['account' => $account]) }}" class="card-link">
                <div class="card text-bg-success h-100 clickable-card"><div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fs-3 fw-bold">£{{ number_format((float)$k['pipeline_value']) }}</div>
                            <div class="small text-uppercase opacity-75">Open Pipeline Value</div>
                        </div><i class="bi bi-cash-stack fs-1 opacity-50"></i>
                    </div>
                    <div class="small mt-1 opacity-75">{{ number_format($k['open_deals']) }} open deals</div>
                </div></div>
                </a>
            </div>
            <div class="col-6 col-xl-3">
                <a href="{{ route('marketing', ['account' => $account]) }}" class="card-link">
                <div class="card text-bg-primary h-100 clickable-card"><div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fs-3 fw-bold">{{ number_format($k['weekly_leads']) }}</div>
                            <div class="small text-uppercase opacity-75">Leads This Week</div>
                        </div><i class="bi bi-person-plus fs-1 opacity-50"></i>
                    </div>
                    <div class="small mt-1">
                        <span class="badge text-bg-light">
                            <i class="bi bi-{{ $leadDelta >= 0 ? 'arrow-up text-success' : 'arrow-down text-danger' }}"></i>
                            {{ $leadDelta >= 0 ? '+' : '' }}{{ $leadDelta }} vs last week
                        </span>
                    </div>
                </div></div>
                </a>
            </div>
            <div class="col-6 col-xl-3">
                <a href="{{ route('client-projects') }}" class="card-link">
                <div class="card text-bg-info h-100 clickable-card"><div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fs-3 fw-bold">{{ $k['active_builds'] }}</div>
                            <div class="small text-uppercase opacity-75">Active Builds (BGR)</div>
                        </div><i class="bi bi-house-gear fs-1 opacity-50"></i>
                    </div>
                    <div class="small mt-1 opacity-75">{{ $k['avg_progress'] }}% avg progress · {{ $k['bcf_orders'] }} BCF orders</div>
                </div></div>
                </a>
            </div>
            <div class="col-6 col-xl-3">
                <a href="{{ route('staff') }}" class="card-link">
                <div class="card text-bg-secondary h-100 clickable-card"><div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <div class="fs-3 fw-bold">{{ $k['staff_active'] }}</div>
                            <div class="small text-uppercase opacity-75">Active Staff</div>
                        </div><i class="bi bi-people fs-1 opacity-50"></i>
                    </div>
                    <div class="small mt-1 opacity-75">{{ $k['clocked_in'] }} clocked in · {{ $k['todays_jobs'] }} jobs today</div>
                </div></div>
                </a>
            </div>
        </div>

        {{-- Calendar: upcoming jobs & urgent actions --}}
        <div class="card mt-3">
            <div class="card-header d-flex align-items-center">
                <h3 class="card-title mb-0"><i class="bi bi-calendar3"></i> Upcoming Jobs &amp; Urgent Actions</h3>
                <a href="{{ route('calendar') }}" class="ms-auto small">open calendar →</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <tbody>
                        @forelse(($calendarJobs ?? []) as $j)
                            @php
                                $due = $j->dueDate(); $overdue = $j->isOverdue();
                                $sc = ['scheduled'=>'info','in_progress'=>'warning','completed'=>'success','cancelled'=>'secondary'][$j->status] ?? 'secondary';
                            @endphp
                            <tr style="cursor:pointer" onclick="location.href='{{ route('calendar') }}'">
                                <td style="width:90px" class="text-center">
                                    @if($overdue)
                                        <span class="badge text-bg-danger">{{ abs($due->diffInDays(now())) }}d overdue</span>
                                    @elseif($due->isToday())
                                        <span class="badge text-bg-warning">today</span>
                                    @else
                                        <span class="badge text-bg-light">in {{ (int) round(now()->diffInDays($due, false)) }}d</span>
                                    @endif
                                </td>
                                <td>{{ $j->client_name }} @if($j->is_birthday)🎂@endif
                                    <small class="text-muted d-block">{{ \Illuminate\Support\Str::limit($j->order_details ?: $j->address, 50) }}</small>
                                </td>
                                <td class="text-muted small">{{ $due->format('d M') }}</td>
                                <td>{{ $j->assigned_to ?: '—' }}</td>
                                <td><span class="badge text-bg-{{ $sc }} text-capitalize">{{ str_replace('_',' ',$j->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td class="text-center text-muted py-3">No upcoming jobs. <a href="{{ route('calendar') }}">Add one →</a></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Charts --}}
        <div class="row g-3 mt-1">
            <div class="col-lg-3">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Leads Trend</h3></div>
                    <div class="card-body"><div style="height:260px"><canvas id="weeklyChart"></canvas></div></div>
                    <div class="card-footer py-1 small text-muted">click a point → that day's leads</div>
                </div>
            </div>
            {{-- GoDaddy renewals KPI --}}
            @php $rw = $renewals ?? []; @endphp
            <div class="col-lg-3">
                <a href="{{ route('renewals') }}" class="card-link">
                <div class="card h-100 clickable-card">
                    <div class="card-header"><h3 class="card-title">Renewals (GoDaddy)</h3></div>
                    <div class="card-body">
                        <div class="d-flex align-items-baseline gap-2">
                            <div class="fs-2 fw-bold {{ ($rw['expiring_30'] ?? 0) ? 'text-danger' : 'text-success' }}">{{ $rw['expiring_30'] ?? 0 }}</div>
                            <div class="small text-muted">expiring ≤ 30 days</div>
                        </div>
                        <ul class="list-unstyled small mt-3 mb-0">
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Expiring ≤ 90 days</span>
                                <span class="badge text-bg-warning">{{ $rw['expiring_90'] ?? 0 }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Auto-renew off</span>
                                <span class="badge text-bg-secondary">{{ $rw['auto_renew_off'] ?? 0 }}</span>
                            </li>
                            <li class="d-flex justify-content-between py-1">
                                <span class="text-muted">Active domains</span>
                                <span class="badge text-bg-success">{{ $rw['active'] ?? 0 }}</span>
                            </li>
                        </ul>
                        <div class="text-center mt-2"><span class="small text-muted">click for full renewals →</span></div>
                    </div>
                </div>
                </a>
            </div>
            <div class="col-lg-3">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Lead Sources</h3></div>
                    <div class="card-body"><div style="height:260px"><canvas id="sourcesChart"></canvas></div></div>
                    <div class="card-footer py-1 small text-muted">click a slice → that source's leads</div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card h-100 clickable-card" onclick="location.href='{{ route('security') }}'">
                    <div class="card-header"><h3 class="card-title">Security</h3></div>
                    <div class="card-body">
                        @foreach(['ssl' => 'SSL', 'dns' => 'DNS / Email'] as $key => $label)
                            @php $pct = $k[$key]; @endphp
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small"><span>{{ $label }}</span><span>{{ $pct === null ? 'n/a' : $pct.'%' }}</span></div>
                                <div class="progress" style="height:10px;">
                                    <div class="progress-bar bg-{{ $pct === null ? 'secondary' : ($pct >= 80 ? 'success' : 'warning') }}" style="width:{{ $pct ?? 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                        <div class="text-center mt-3">
                            <span class="badge text-bg-{{ ($k['alerts'] ?? 0) ? 'danger' : 'success' }} fs-6">
                                {{ $k['alerts'] ?? 0 }} active alert{{ ($k['alerts'] ?? 0) == 1 ? '' : 's' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stage funnel + per-account --}}
        <div class="row g-3 mt-1">
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center flex-wrap gap-2">
                        <h3 class="card-title mb-0">Pipeline by Stage</h3>
                        <select id="pipelineSelect" class="form-select form-select-sm ms-auto" style="max-width: 230px;">
                            <option value="">All pipelines (combined)</option>
                            @foreach(($charts['pipelines'] ?? []) as $i => $pl)
                                <option value="{{ $i }}">{{ $pl['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="card-body"><div style="height:280px"><canvas id="stagesChart"></canvas></div></div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Per-Account Breakdown</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-hover mb-0">
                            <thead><tr><th>Account</th><th class="text-end">Weekly Leads</th><th class="text-end">Open Deals</th><th class="text-end">Pipeline Value</th></tr></thead>
                            <tbody>
                                @forelse(($pipeline['accounts'] ?? []) as $key => $a)
                                    <tr style="cursor:pointer" onclick="location.href='{{ route('dashboard', ['account' => $key]) }}'" title="View {{ $accounts[$key]['name'] ?? $key }} only">
                                        <td>{{ $accounts[$key]['name'] ?? $key }} <i class="bi bi-box-arrow-up-right small text-muted"></i></td>
                                        <td class="text-end">{{ number_format($a['weekly_leads'] ?? 0) }}</td>
                                        <td class="text-end">{{ number_format($a['open_count'] ?? 0) }}</td>
                                        <td class="text-end">£{{ number_format((float)($a['pipeline_value'] ?? 0)) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">Single account selected.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const weekly  = @json($charts['weekly']);
    const sources = @json($charts['sources']);
    const stages  = @json($charts['stages']);
    const P = window.CEO_PALETTE;

    const pointerHover = (evt, els) => { evt.native.target.style.cursor = 'pointer'; };

    // Segment clicks deep-link into the Sales Lead Explorer, pre-filtered.
    const salesUrl = '{{ route('sales', ['account' => $account]) }}';

    new Chart(document.getElementById('weeklyChart'), {
        type: 'line',
        data: { labels: weekly.labels, datasets: [{ label: 'Leads', data: weekly.data,
            borderColor: P[0], backgroundColor: 'rgba(13,110,253,.15)', fill: true, tension: .35 }] },
        options: {
            plugins: { legend: { display: false } }, onHover: pointerHover,
            onClick: (e, els) => { if (els.length) location.href = salesUrl + '&date=' + encodeURIComponent(weekly.labels[els[0].index]); }
        }
    });

    new Chart(document.getElementById('sourcesChart'), {
        type: 'doughnut',
        data: { labels: sources.labels, datasets: [{ data: sources.data, backgroundColor: P }] },
        options: {
            onHover: pointerHover,
            onClick: (e, els) => { if (els.length) location.href = salesUrl + '&source=' + encodeURIComponent(sources.labels[els[0].index]); }
        }
    });

    const stagesChart = new Chart(document.getElementById('stagesChart'), {
        type: 'bar',
        data: { labels: stages.labels, datasets: [{ label: 'Opportunities', data: stages.data, backgroundColor: P[0] }] },
        options: {
            indexAxis: 'y',
            plugins: { legend: { display: false } },
            scales: { x: { ticks: { precision: 0 } } },
            // Clicking a stage bar drills into the Sales Pipeline page.
            onClick: () => location.href = '{{ route('sales', ['account' => $account]) }}',
            onHover: pointerHover
        }
    });

    // Pipeline selector: show one pipeline's full stage funnel, or the combined view.
    const pipelines = @json($charts['pipelines'] ?? []);
    document.getElementById('pipelineSelect')?.addEventListener('change', function () {
        const v = this.value;
        const src = v === ''
            ? { labels: stages.labels, data: stages.data }
            : { labels: pipelines[v].labels, data: pipelines[v].counts };
        stagesChart.data.labels = src.labels;
        stagesChart.data.datasets[0].data = src.data;
        stagesChart.data.datasets[0].backgroundColor = v === '' ? P[0] : P[1];
        stagesChart.update();
    });
</script>
@endpush
