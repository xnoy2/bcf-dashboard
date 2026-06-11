@extends('layouts.app')

@section('title', 'Renewals')

@section('content')
<div class="app-content-header">
    <div class="container-fluid d-flex align-items-center flex-wrap gap-2">
        <h3 class="mb-0">Renewals <small class="text-muted">(GoDaddy Domains)</small></h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        @php
            $s = $renewals['summary'] ?? [];
            $domains = $renewals['domains'] ?? [];
            $accountList = collect($domains)->pluck('account')->unique()->values();

            $alertDays = config('integrations.renewal_alerts.days', 10);
            $urgentDomains = collect($domains)
                ->filter(fn ($d) => ($d['active'] ?? false) && $d['days_until'] !== null && $d['days_until'] <= $alertDays)
                ->sortBy('days_until');
        @endphp

        {{-- Urgent expiry notification --}}
        @if($urgentDomains->isNotEmpty())
            <div class="alert alert-danger d-flex flex-wrap align-items-start gap-2 mb-3">
                <i class="bi bi-exclamation-octagon-fill fs-4"></i>
                <div>
                    <strong>{{ $urgentDomains->count() }} domain{{ $urgentDomains->count() === 1 ? '' : 's' }} need{{ $urgentDomains->count() === 1 ? 's' : '' }} urgent renewal</strong>
                    (expired or within {{ $alertDays }} days):
                    @foreach($urgentDomains as $d)
                        <span class="badge text-bg-{{ ($d['days_until'] < 0) ? 'dark' : 'danger' }} ms-1">
                            {{ $d['domain'] }} · {{ $d['days_until'] < 0 ? 'expired ' . abs($d['days_until']) . 'd ago' : $d['days_until'] . ' days' }}
                        </span>
                    @endforeach
                    <div class="small mt-1 opacity-75">
                        <i class="bi bi-envelope"></i> A daily alert email goes to
                        <strong>{{ implode(', ', config('integrations.renewal_alerts.emails', [])) ?: 'no one — set RENEWAL_ALERT_EMAILS' }}</strong> at 08:00.
                    </div>
                </div>
            </div>
        @endif

        {{-- KPI row --}}
        <div class="row g-3">
            <div class="col-6 col-lg-3"><div class="card text-bg-primary"><div class="card-body">
                <div class="fs-3 fw-bold">{{ $s['active'] ?? 0 }}</div><div class="small text-uppercase opacity-75">Active Domains</div>
                <div class="small opacity-75">{{ $s['total'] ?? 0 }} total · {{ $s['cancelled'] ?? 0 }} cancelled</div>
                @include('partials.card-link', ['tool' => 'godaddy'])
            </div></div></div>
            <div class="col-6 col-lg-3"><div class="card text-bg-danger"><div class="card-body">
                <div class="fs-3 fw-bold">{{ $s['expiring_30'] ?? 0 }}</div><div class="small text-uppercase opacity-75">Expiring &le; 30 days</div>
            </div></div></div>
            <div class="col-6 col-lg-3"><div class="card text-bg-warning"><div class="card-body">
                <div class="fs-3 fw-bold">{{ $s['expiring_90'] ?? 0 }}</div><div class="small text-uppercase opacity-75">Expiring &le; 90 days</div>
            </div></div></div>
            <div class="col-6 col-lg-3"><div class="card text-bg-secondary"><div class="card-body">
                <div class="fs-3 fw-bold">{{ $s['auto_renew_off'] ?? 0 }}</div><div class="small text-uppercase opacity-75">Auto-renew OFF</div>
                <div class="small opacity-75">active domains at risk</div>
            </div></div></div>
        </div>

        @if(empty($domains))
            <div class="alert alert-warning mt-3">No domains returned from GoDaddy. Check API access (Production accounts need ~10+ domains).</div>
        @endif

        <div class="row g-3 mt-1">
            <div class="col-lg-9">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center flex-wrap gap-2">
                        <h3 class="card-title mb-0">Domains <small class="text-muted">(active first, soonest expiry)</small></h3>
                        <div class="d-flex gap-2 ms-auto">
                            <select id="accountFilter" class="form-select form-select-sm" style="width:160px">
                                <option value="">All GoDaddy accounts</option>
                                @foreach($accountList as $acc)
                                    <option value="{{ $acc }}">{{ $acc }}</option>
                                @endforeach
                            </select>
                            <input type="search" id="domSearch" class="form-control form-control-sm" placeholder="Filter…" style="width:200px">
                        </div>
                    </div>
                    <div id="urgencyIndicator" class="px-3 py-2 border-bottom bg-body-secondary d-none">
                        <i class="bi bi-funnel-fill"></i> Showing
                        <span class="badge text-bg-primary" id="urgencyLabel"></span>
                        <span class="text-muted small" id="urgencyCount"></span>
                        <button type="button" class="btn btn-sm btn-link py-0" id="urgencyClear">clear filter</button>
                    </div>
                    <div class="card-body p-0" style="max-height:62vh; overflow:auto;">
                        <table class="table table-striped table-hover mb-0" id="domTable">
                            <thead class="sticky-top bg-body">
                                <tr><th>Domain</th><th>Account</th><th>Status</th><th>Expires</th><th>Renews In</th><th>Auto</th></tr>
                            </thead>
                            <tbody>
                                @foreach($domains as $d)
                                    @php
                                        $du = $d['days_until'];
                                        $urgency = !$d['active'] ? 'secondary'
                                            : ($du === null ? 'secondary'
                                            : ($du <= 30 ? 'danger' : ($du <= 90 ? 'warning' : 'success')));
                                        $bucket = (! $d['active'] || $du === null) ? 'other'
                                            : ($du < 0 ? 'expired' : ($du <= 30 ? 'le30' : ($du <= 90 ? 'le90' : 'healthy')));
                                    @endphp
                                    <tr class="{{ $d['active'] ? '' : 'text-muted' }}" data-account="{{ $d['account'] }}" data-urgency="{{ $bucket }}">
                                        <td>{{ $d['domain'] }}</td>
                                        <td><span class="badge text-bg-light">{{ $d['account'] }}</span></td>
                                        <td>
                                            <span class="badge text-bg-{{ $d['active'] ? 'success' : 'secondary' }}">{{ $d['status'] }}</span>
                                        </td>
                                        <td>{{ $d['expires'] ?? '—' }}</td>
                                        <td>
                                            @if($du === null) —
                                            @elseif($du < 0) <span class="badge text-bg-dark">expired</span>
                                            @else <span class="badge text-bg-{{ $urgency }}">{{ $du }} days</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($d['renew_auto'])
                                                <i class="bi bi-check-circle-fill text-success" title="Auto-renew on"></i>
                                            @else
                                                <i class="bi bi-x-circle-fill text-danger" title="Auto-renew off"></i>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Renewal Health</h3></div>
                    <div class="card-body">
                        <div style="height:220px"><canvas id="renewalChart"></canvas></div>
                        <p class="text-muted small mt-2 mb-0">Active domains by time until renewal — so you can see at a glance how many need attention.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    @php $u = $s['urgency'] ?? []; @endphp
    const URGENCY_BUCKETS = ['expired', 'le30', 'le90', 'healthy'];
    const URGENCY_LABELS  = ['Expired', '≤ 30 days', '31–90 days', 'Healthy (90d+)'];

    const search = document.getElementById('domSearch');
    const acctFilter = document.getElementById('accountFilter');
    const indicator = document.getElementById('urgencyIndicator');
    const urgencyLabelEl = document.getElementById('urgencyLabel');
    const urgencyCountEl = document.getElementById('urgencyCount');
    let urgencyFilter = '';

    function applyFilters() {
        const q = (search.value || '').toLowerCase();
        const acc = acctFilter.value;
        let shown = 0;
        document.querySelectorAll('#domTable tbody tr').forEach(tr => {
            const matchText = tr.textContent.toLowerCase().includes(q);
            const matchAcct = !acc || tr.dataset.account === acc;
            const matchUrg  = !urgencyFilter || tr.dataset.urgency === urgencyFilter;
            const visible = matchText && matchAcct && matchUrg;
            tr.style.display = visible ? '' : 'none';
            if (visible) shown++;
        });

        if (urgencyFilter) {
            const i = URGENCY_BUCKETS.indexOf(urgencyFilter);
            urgencyLabelEl.textContent = URGENCY_LABELS[i] ?? urgencyFilter;
            urgencyCountEl.textContent = '· ' + shown + ' domain' + (shown === 1 ? '' : 's');
            indicator.classList.remove('d-none');
        } else {
            indicator.classList.add('d-none');
        }
    }

    function setUrgency(bucket) {
        urgencyFilter = (urgencyFilter === bucket) ? '' : bucket; // toggle
        applyFilters();
    }

    const renewalChart = new Chart(document.getElementById('renewalChart'), {
        type: 'doughnut',
        data: {
            labels: URGENCY_LABELS,
            datasets: [{
                data: [{{ $u['expired'] ?? 0 }}, {{ $u['le_30'] ?? 0 }}, {{ $u['le_90'] ?? 0 }}, {{ $u['healthy'] ?? 0 }}],
                backgroundColor: ['#212529', '#dc3545', '#ffc107', '#198754']
            }]
        },
        options: {
            onClick: (evt, elements) => {
                if (elements.length) {
                    setUrgency(URGENCY_BUCKETS[elements[0].index]);
                }
            },
            onHover: (evt, elements) => {
                evt.native.target.style.cursor = elements.length ? 'pointer' : 'default';
            }
        }
    });

    // Recompute the donut from rows matching the account + search filters
    // (ignores the urgency drill-down so the chart stays a stable reference).
    function recomputeDonut() {
        const q = (search.value || '').toLowerCase();
        const acc = acctFilter.value;
        const c = { expired: 0, le30: 0, le90: 0, healthy: 0 };
        document.querySelectorAll('#domTable tbody tr').forEach(tr => {
            const matchText = tr.textContent.toLowerCase().includes(q);
            const matchAcct = !acc || tr.dataset.account === acc;
            if (matchText && matchAcct && c.hasOwnProperty(tr.dataset.urgency)) {
                c[tr.dataset.urgency]++;
            }
        });
        renewalChart.data.datasets[0].data = [c.expired, c.le30, c.le90, c.healthy];
        renewalChart.update();
    }

    function onFilterChange() {
        recomputeDonut();
        applyFilters();
    }

    search?.addEventListener('input', onFilterChange);
    acctFilter?.addEventListener('change', onFilterChange);
    document.getElementById('urgencyClear')?.addEventListener('click', () => setUrgency(urgencyFilter));
</script>
@endpush
