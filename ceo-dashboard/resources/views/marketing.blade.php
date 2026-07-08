@extends('layouts.app')

@section('title', 'Marketing')

@section('content')
<div class="app-content-header">
    <div class="container-fluid d-flex align-items-center flex-wrap gap-2">
        <h3 class="mb-0">Marketing</h3>
        <span class="badge text-bg-primary ms-auto">{{ $account === 'all' ? 'All Accounts' : ($accounts[$account]['name'] ?? $account) }}</span>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        @php $delta = $weeklyLeads - $lastWeek; @endphp
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card text-bg-primary h-100">
                    <div class="card-body">
                        <div class="fs-3 fw-bold">{{ number_format($weeklyLeads) }}</div>
                        <div class="text-uppercase small opacity-75">Leads This Week</div>
                        @include('partials.card-link', ['tool' => 'ghl'])
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-bg-secondary h-100">
                    <div class="card-body">
                        <div class="fs-3 fw-bold">{{ number_format($lastWeek) }}</div>
                        <div class="text-uppercase small opacity-75">Leads Last Week</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-bg-{{ $delta >= 0 ? 'success' : 'danger' }} h-100">
                    <div class="card-body">
                        <div class="fs-3 fw-bold">{{ $delta >= 0 ? '+' : '' }}{{ number_format($delta) }}</div>
                        <div class="text-uppercase small opacity-75">Week-over-Week</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Leads Trend <small class="text-muted fw-normal" id="trendNote"></small></h3></div>
                    <div class="card-body"><div style="height:260px"><canvas id="mktWeekly"></canvas></div></div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Lead Sources <small class="text-muted fw-normal" id="sourceNote"></small></h3></div>
                    <div class="card-body"><div style="height:260px"><canvas id="mktSources"></canvas></div></div>
                </div>
            </div>
        </div>

        {{-- Lead Explorer — populated by clicking the charts above --}}
        <div class="card mt-3" id="leadExplorer">
            <div class="card-header d-flex align-items-center flex-wrap gap-2">
                <h3 class="card-title mb-0">Lead Explorer</h3>
                <span id="leadChips" class="d-flex gap-2"></span>
                <div class="ms-auto d-flex align-items-center gap-2">
                    <span class="small text-muted" id="leadCount"></span>
                    <input type="search" id="leadSearch" class="form-control form-control-sm" placeholder="Search name…" style="width:190px">
                </div>
            </div>
            <div class="card-body p-0" style="max-height:48vh; overflow:auto;">
                <table class="table table-striped table-hover mb-0">
                    <thead class="sticky-top bg-body">
                        <tr>
                            <th>Lead</th>
                            @if($account === 'all')<th>Business</th>@endif
                            <th>Source</th><th>Stage</th><th class="text-end">Value</th><th>Created</th>
                        </tr>
                    </thead>
                    <tbody id="leadRows"></tbody>
                </table>
            </div>
            <div class="card-footer small text-muted">
                <i class="bi bi-info-circle"></i> Click a slice in <strong>Lead Sources</strong> or a point on <strong>Leads Trend</strong> to filter — both charts and this list update together.
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header"><h3 class="card-title">Lead Sources Detail</h3></div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead><tr><th>Source</th><th class="text-end">Leads</th></tr></thead>
                    <tbody>
                        @forelse($leadSources as $src => $count)
                            <tr><td>{{ $src }}</td><td class="text-end">{{ number_format($count) }}</td></tr>
                        @empty
                            <tr><td colspan="2" class="text-center text-muted py-3">No lead data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Funnels — monitor newly created / updated funnels across accounts --}}
        @php $funnelAccounts = collect($funnels)->pluck('account')->unique()->sort()->values(); @endphp
        <div class="card mt-3" id="funnelsCard">
            <div class="card-header d-flex align-items-center flex-wrap gap-2">
                <h3 class="card-title mb-0">Funnels <small class="text-muted" id="funnelCount">({{ count($funnels) }})</small></h3>
                <span class="text-muted small d-none d-lg-inline">newest first</span>
                <div class="ms-auto d-flex align-items-center gap-2">
                    @if($account === 'all' && $funnelAccounts->count() > 1)
                        <select id="funnelAccount" class="form-select form-select-sm" style="width:190px">
                            <option value="">All accounts</option>
                            @foreach($funnelAccounts as $acc)
                                <option value="{{ $acc }}">{{ $accounts[strtolower($acc)]['name'] ?? $acc }}</option>
                            @endforeach
                        </select>
                    @endif
                    <input type="search" id="funnelSearch" class="form-control form-control-sm" placeholder="Filter funnels…" style="width:200px">
                </div>
            </div>
            <div class="card-body p-0" style="max-height:60vh; overflow:auto;">
                <table class="table table-striped table-hover mb-0" id="funnelTable">
                    <thead class="sticky-top bg-body">
                        <tr>
                            <th>Funnel</th>
                            <th>Account</th>
                            <th>Created</th>
                            <th>Created By</th>
                            <th>Last Updated</th>
                            <th class="text-end">Steps</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($funnels as $f)
                            <tr data-account="{{ $f['account'] }}">
                                <td>{{ $f['name'] }}</td>
                                <td><span class="badge text-bg-light">{{ $f['account'] }}</span></td>
                                <td>{{ $f['created_label'] }}</td>
                                <td>
                                    @if($f['created_by'])
                                        <span class="text-truncate d-inline-block" style="max-width:230px" title="{{ $f['created_by'] }}">{{ $f['created_by'] }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $f['updated_label'] }}</td>
                                <td class="text-end">{{ $f['steps'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No funnels found for this account.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer small text-muted d-flex justify-content-end">
                @include('partials.card-link', ['tool' => 'ghl', 'text' => 'Open in GoHighLevel'])
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const P = window.CEO_PALETTE;
    const sW = { labels: @json($weeklyLabels), data: @json($weeklyTrend) };
    const sS = { labels: @json(array_keys($leadSources)), data: @json(array_values($leadSources)) };
    const LEADS   = @json($leads ?? []);
    const showBiz = @json($account === 'all');
    const MAXROWS = 300;
    let lf = { source: null, date: null, q: '' };
    const _qs = new URLSearchParams(location.search);
    lf.source = _qs.get('source'); lf.date = _qs.get('date');
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    function renderLeads() {
        const q = lf.q.toLowerCase();
        const rows = []; let matched = 0;
        for (const L of LEADS) {
            if (lf.source && L.s !== lf.source) continue;
            if (lf.date && L.d !== lf.date) continue;
            if (q && !String(L.n).toLowerCase().includes(q)) continue;
            matched++;
            if (rows.length < MAXROWS) {
                rows.push('<tr><td>' + esc(L.n) + '</td>'
                    + (showBiz ? '<td><span class="badge text-bg-light">' + esc(L.a || '') + '</span></td>' : '')
                    + '<td>' + esc(L.s) + '</td><td><small>' + esc(L.st) + '</small></td>'
                    + '<td class="text-end">' + (L.v ? '£' + Number(L.v).toLocaleString() : '—') + '</td>'
                    + '<td>' + esc(L.d) + '</td></tr>');
            }
        }
        document.getElementById('leadRows').innerHTML = rows.join('') || '<tr><td colspan="6" class="text-center text-muted py-3">No leads match.</td></tr>';
        document.getElementById('leadCount').textContent = matched > MAXROWS ? ('showing ' + MAXROWS + ' of ' + matched.toLocaleString()) : (matched.toLocaleString() + ' lead' + (matched === 1 ? '' : 's'));
        const chips = [];
        if (lf.source) chips.push('<span class="badge text-bg-primary">' + esc(lf.source) + ' <a href="#" class="text-white text-decoration-none" onclick="clearLf(\'source\');return false;">×</a></span>');
        if (lf.date)   chips.push('<span class="badge text-bg-warning">' + esc(lf.date) + ' <a href="#" class="text-dark text-decoration-none" onclick="clearLf(\'date\');return false;">×</a></span>');
        document.getElementById('leadChips').innerHTML = chips.join(' ');
    }
    window.clearLf = k => { lf[k] = null; applyFilters(); };
    document.getElementById('leadSearch')?.addEventListener('input', e => { lf.q = e.target.value; applyFilters(); });
    const focusExplorer = () => document.getElementById('leadExplorer')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    const countsByLabel = (list, labels) => {
        const m = Object.fromEntries(labels.map(l => [l, 0]));
        for (const L of list) if (m[L.d] !== undefined) m[L.d]++;
        return labels.map(l => m[l]);
    };
    function updateCharts() {
        const q = lf.q.toLowerCase();
        const passQ = L => !q || String(L.n).toLowerCase().includes(q);
        const tLeads = LEADS.filter(L => passQ(L) && (!lf.source || L.s === lf.source));
        trendChart.data.datasets[0].data = countsByLabel(tLeads, sW.labels);
        document.getElementById('trendNote').textContent = lf.source ? '— ' + lf.source + ' only' : '';
        trendChart.update();
        const dLeads = LEADS.filter(L => passQ(L) && (!lf.date || L.d === lf.date));
        const counts = {}; for (const L of dLeads) counts[L.s] = (counts[L.s] || 0) + 1;
        srcChart.data.datasets[0].data = sS.labels.map(l => counts[l] || 0);
        srcChart.data.datasets[0].offset = sS.labels.map(l => l === lf.source ? 18 : 0);
        document.getElementById('sourceNote').textContent = lf.date ? '— ' + lf.date : '';
        srcChart.update();
    }
    function applyFilters() { renderLeads(); updateCharts(); }
    const pointer = (evt, els) => { evt.native.target.style.cursor = els.length ? 'pointer' : 'default'; };

    const trendChart = new Chart(document.getElementById('mktWeekly'), {
        type: 'line',
        data: { labels: sW.labels, datasets: [{ label: 'Leads', data: sW.data, borderColor: P[0], backgroundColor: 'rgba(13,110,253,.15)', fill: true, tension: .35 }] },
        options: {
            plugins: { legend: { display: false } }, onHover: pointer,
            onClick: (e, els) => { if (!els.length) return; const d = sW.labels[els[0].index]; lf.date = (lf.date === d ? null : d); applyFilters(); focusExplorer(); }
        }
    });
    const srcChart = new Chart(document.getElementById('mktSources'), {
        type: 'doughnut',
        data: { labels: sS.labels, datasets: [{ data: sS.data, backgroundColor: P }] },
        options: {
            onHover: pointer,
            onClick: (e, els) => { if (!els.length) return; const s = sS.labels[els[0].index]; lf.source = (lf.source === s ? null : s); applyFilters(); focusExplorer(); }
        }
    });
    applyFilters();

    // Funnels table — filter by search text and (in the All Accounts view) business.
    const funnelSearch = document.getElementById('funnelSearch');
    const funnelAccount = document.getElementById('funnelAccount');
    function filterFunnels() {
        const q = (funnelSearch?.value || '').toLowerCase();
        const acc = funnelAccount?.value || '';
        const rows = document.querySelectorAll('#funnelTable tbody tr[data-account]');
        let shown = 0;
        rows.forEach(tr => {
            const visible = tr.textContent.toLowerCase().includes(q) && (!acc || tr.dataset.account === acc);
            tr.style.display = visible ? '' : 'none';
            if (visible) shown++;
        });
        const countEl = document.getElementById('funnelCount');
        if (countEl) countEl.textContent = (shown === rows.length) ? '(' + rows.length + ')' : '(' + shown + ' of ' + rows.length + ')';
    }
    funnelSearch?.addEventListener('input', filterFunnels);
    funnelAccount?.addEventListener('change', filterFunnels);
</script>
@endpush
