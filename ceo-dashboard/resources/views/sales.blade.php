@extends('layouts.app')

@section('title', 'Sales Pipeline')

@section('content')
<div class="app-content-header">
    <div class="container-fluid d-flex align-items-center flex-wrap gap-2">
        <h3 class="mb-0">Sales Pipeline</h3>
        <span class="badge text-bg-primary ms-auto">{{ $account === 'all' ? 'All Accounts' : ($accounts[$account]['name'] ?? $account) }}</span>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        <div class="row g-3">
            @php
                $cards = [
                    ['Open', $pipeline['open_count'] ?? 0, 'primary', false],
                    ['Won', $pipeline['won_count'] ?? 0, 'success', false],
                    ['Lost', $pipeline['lost_count'] ?? 0, 'danger', false],
                    ['Win Rate', ($pipeline['win_rate'] ?? 0).'%', 'info', false],
                    ['Pipeline Value', $pipeline['pipeline_value'] ?? 0, 'secondary', true],
                    ['Avg Deal', $pipeline['avg_deal_size'] ?? 0, 'warning', true],
                ];
            @endphp
            @foreach($cards as [$label, $value, $color, $money])
                <div class="col-6 col-xl-2">
                    <div class="card text-bg-{{ $color }} h-100">
                        <div class="card-body">
                            <div class="fs-4 fw-bold">{{ $money ? '£'.number_format((float)$value) : $value }}</div>
                            <div class="small text-uppercase opacity-75">{{ $label }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Charts --}}
        <div class="row g-3 mt-1">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Leads Trend <small class="text-muted fw-normal" id="trendNote"></small></h3></div>
                    <div class="card-body"><div style="height:240px"><canvas id="salesWeekly"></canvas></div></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Lead Sources <small class="text-muted fw-normal" id="sourceNote"></small></h3></div>
                    <div class="card-body"><div style="height:240px"><canvas id="salesSources"></canvas></div></div>
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
                <i class="bi bi-info-circle"></i> Click a slice in <strong>Lead Sources</strong> or a point on <strong>Leads Trend</strong> to filter this list. Click the same one again (or the ×) to clear.
            </div>
        </div>

        {{-- Appointments (scope-gated) --}}
        <div class="card mt-3">
            <div class="card-header d-flex align-items-center flex-wrap gap-2">
                <h3 class="card-title mb-0">Appointments <small class="text-muted">(GHL Calendars)</small></h3>
                <span class="ms-auto">@include('partials.card-link', ['tool' => 'ghl'])</span>
            </div>
            <div class="card-body">
                @if(!empty($appointments['scope_missing']))
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle"></i>
                        Enable the <strong>calendars</strong> scope on the GHL token to populate appointment data
                        (total, upcoming, show/no-show). The integration is ready.
                    </div>
                @else
                    <div class="row text-center">
                        <div class="col"><div class="fs-3 fw-bold">{{ $appointments['total'] }}</div><div class="small text-muted">Total</div></div>
                        <div class="col"><div class="fs-3 fw-bold">{{ $appointments['upcoming'] }}</div><div class="small text-muted">Upcoming</div></div>
                        <div class="col"><div class="fs-3 fw-bold text-success">{{ $appointments['showed'] }}</div><div class="small text-muted">Showed</div></div>
                        <div class="col"><div class="fs-3 fw-bold text-danger">{{ $appointments['no_show'] }}</div><div class="small text-muted">No-show</div></div>
                    </div>
                @endif
            </div>
        </div>

        <div class="row g-3 mt-1">
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Stage Funnel</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead><tr><th>Stage</th><th class="text-end">Count</th></tr></thead>
                            <tbody>
                                @forelse(($pipeline['stages'] ?? []) as $stage => $count)
                                    <tr><td>{{ $stage }}</td><td class="text-end">{{ number_format($count) }}</td></tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-3">No opportunities.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Lead Sources</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead><tr><th>Source</th><th class="text-end">Leads</th></tr></thead>
                            <tbody>
                                @forelse(($pipeline['lead_sources'] ?? []) as $src => $count)
                                    <tr><td>{{ $src }}</td><td class="text-end">{{ number_format($count) }}</td></tr>
                                @empty
                                    <tr><td colspan="2" class="text-center text-muted py-3">No data.</td></tr>
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
    const sW = { labels: @json($pipeline['weekly_labels'] ?? []), data: @json($pipeline['weekly_trend'] ?? []) };
    const sS = { labels: @json(array_keys($pipeline['lead_sources'] ?? [])), data: @json(array_values($pipeline['lead_sources'] ?? [])) };
    const P = window.CEO_PALETTE;

    // ---------- Lead Explorer ----------
    const LEADS   = @json($pipeline['leads'] ?? []);
    const showBiz = @json($account === 'all');
    const MAXROWS = 300;
    let lf = { source: null, date: null, q: '' };
    // Accept deep-links from the CEO Overview charts (?source= / ?date=)
    const _qs = new URLSearchParams(location.search);
    lf.source = _qs.get('source'); lf.date = _qs.get('date');
    const esc = s => String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    function renderLeads() {
        const q = lf.q.toLowerCase();
        const rows = [];
        let matched = 0;
        for (const L of LEADS) {
            if (lf.source && L.s !== lf.source) continue;
            if (lf.date && L.d !== lf.date) continue;
            if (q && !String(L.n).toLowerCase().includes(q)) continue;
            matched++;
            if (rows.length < MAXROWS) {
                rows.push('<tr><td>' + esc(L.n) + '</td>'
                    + (showBiz ? '<td><span class="badge text-bg-light">' + esc(L.a || '') + '</span></td>' : '')
                    + '<td>' + esc(L.s) + '</td>'
                    + '<td><small>' + esc(L.st) + '</small></td>'
                    + '<td class="text-end">' + (L.v ? '£' + Number(L.v).toLocaleString() : '—') + '</td>'
                    + '<td>' + esc(L.d) + '</td></tr>');
            }
        }
        document.getElementById('leadRows').innerHTML = rows.join('')
            || '<tr><td colspan="6" class="text-center text-muted py-3">No leads match.</td></tr>';
        document.getElementById('leadCount').textContent =
            matched > MAXROWS ? ('showing ' + MAXROWS + ' of ' + matched.toLocaleString()) : (matched.toLocaleString() + ' lead' + (matched === 1 ? '' : 's'));

        const chips = [];
        if (lf.source) chips.push('<span class="badge text-bg-primary">' + esc(lf.source) + ' <a href="#" class="text-white text-decoration-none" onclick="clearLf(\'source\');return false;">×</a></span>');
        if (lf.date)   chips.push('<span class="badge text-bg-warning">' + esc(lf.date) + ' <a href="#" class="text-dark text-decoration-none" onclick="clearLf(\'date\');return false;">×</a></span>');
        document.getElementById('leadChips').innerHTML = chips.join(' ');
    }
    window.clearLf = k => { lf[k] = null; applyFilters(); };
    document.getElementById('leadSearch')?.addEventListener('input', e => { lf.q = e.target.value; applyFilters(); });
    const focusExplorer = () => document.getElementById('leadExplorer')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

    // ---------- Cross-filtering: clicking one chart redraws the other ----------
    const countsByLabel = (list, labels) => {
        const m = Object.fromEntries(labels.map(l => [l, 0]));
        for (const L of list) if (m[L.d] !== undefined) m[L.d]++;
        return labels.map(l => m[l]);
    };

    function updateCharts() {
        const q = lf.q.toLowerCase();
        const passQ = L => !q || String(L.n).toLowerCase().includes(q);

        // Trend respects the source filter (shows only that source over time)
        const tLeads = LEADS.filter(L => passQ(L) && (!lf.source || L.s === lf.source));
        trendChart.data.datasets[0].data = countsByLabel(tLeads, sW.labels);
        document.getElementById('trendNote').textContent = lf.source ? '— ' + lf.source + ' only' : '';
        trendChart.update();

        // Donut respects the date filter (shows that day's source mix),
        // and pops the selected slice out for feedback.
        const dLeads = LEADS.filter(L => passQ(L) && (!lf.date || L.d === lf.date));
        const counts = {};
        for (const L of dLeads) counts[L.s] = (counts[L.s] || 0) + 1;
        srcChart.data.datasets[0].data = sS.labels.map(l => counts[l] || 0);
        srcChart.data.datasets[0].offset = sS.labels.map(l => l === lf.source ? 18 : 0);
        document.getElementById('sourceNote').textContent = lf.date ? '— ' + lf.date : '';
        srcChart.update();
    }

    function applyFilters() {
        renderLeads();
        updateCharts();
    }

    // ---------- Charts (clickable) ----------
    const pointer = (evt, els) => { evt.native.target.style.cursor = els.length ? 'pointer' : 'default'; };

    const trendChart = new Chart(document.getElementById('salesWeekly'), {
        type: 'line',
        data: { labels: sW.labels, datasets: [{ label: 'Leads', data: sW.data, borderColor: P[1], backgroundColor: 'rgba(25,135,84,.15)', fill: true, tension: .35 }] },
        options: {
            plugins: { legend: { display: false } },
            onHover: pointer,
            onClick: (e, els) => {
                if (!els.length) return;
                const d = sW.labels[els[0].index];
                lf.date = (lf.date === d ? null : d);
                applyFilters(); focusExplorer();
            }
        }
    });
    const srcChart = new Chart(document.getElementById('salesSources'), {
        type: 'doughnut',
        data: { labels: sS.labels, datasets: [{ data: sS.data, backgroundColor: P }] },
        options: {
            onHover: pointer,
            onClick: (e, els) => {
                if (!els.length) return;
                const s = sS.labels[els[0].index];
                lf.source = (lf.source === s ? null : s);
                applyFilters(); focusExplorer();
            }
        }
    });

    applyFilters(); // initial render (newest leads, unfiltered charts)
    if (lf.source || lf.date) setTimeout(focusExplorer, 250); // deep-linked: jump to results
</script>
@endpush
