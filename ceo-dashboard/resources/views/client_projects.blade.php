@extends('layouts.app')

@section('title', 'Client Projects')

@section('content')
<div class="app-content-header">
    <div class="container-fluid d-flex align-items-center flex-wrap gap-2">
        <h3 class="mb-0">Client Projects</h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        @php $t = $data['totals'] ?? []; @endphp
        <div class="row g-3">
            <div class="col-6 col-lg-3"><div class="card text-bg-success"><div class="card-body">
                <div class="fs-3 fw-bold">{{ $t['bgr_builds'] ?? ($t['bgr_projects'] ?? 0) }}</div><div class="small text-uppercase opacity-75">BGR Builds</div>
                
                @include('partials.card-link', ['tool' => 'bgr_portal'])
            </div></div></div>
            <div class="col-6 col-lg-3"><div class="card text-bg-primary"><div class="card-body">
                <div class="fs-3 fw-bold">{{ $t['bgr_active'] ?? 0 }}</div><div class="small text-uppercase opacity-75">In Progress</div>
                <div class="small opacity-75">{{ $t['bgr_pending'] ?? 0 }} pending · {{ $t['bgr_projects'] ?? 0 }} portal records</div>
            </div></div></div>
            <div class="col-6 col-lg-3"><div class="card text-bg-info"><div class="card-body">
                <div class="fs-3 fw-bold">{{ $t['avg_progress'] ?? 0 }}%</div><div class="small text-uppercase opacity-75">Avg Progress</div>
            </div></div></div>
            <div class="col-6 col-lg-3"><div class="card text-bg-warning"><div class="card-body">
                <div class="fs-3 fw-bold">{{ $t['bcf_orders'] ?? 0 }}</div><div class="small text-uppercase opacity-75">BCF Orders</div>
                @include('partials.card-link', ['tool' => 'bcf_portal'])
            </div></div></div>
        </div>

        {{-- Executive insight strip --}}
        <div class="row g-2 mt-1">
            <div class="col-6 col-lg-3"><div class="border rounded p-2 text-center bg-body h-100">
                <div class="fs-5 fw-bold text-primary">{{ $t['bgr_ghl_linked'] ?? 0 }}/{{ $t['bgr_projects'] ?? 0 }}</div>
                <div class="small text-muted"><i class="bi bi-link-45deg"></i> Builds linked to a GHL sale</div>
            </div></div>
            <div class="col-6 col-lg-3"><div class="border rounded p-2 text-center bg-body h-100">
                <div class="fs-5 fw-bold text-success">{{ $t['bgr_completed'] ?? 0 }}</div>
                <div class="small text-muted">Builds completed</div>
            </div></div>
            <div class="col-6 col-lg-3"><div class="border rounded p-2 text-center bg-body h-100">
                <div class="fs-5 fw-bold text-secondary">{{ $t['bgr_not_started'] ?? 0 }}</div>
                <div class="small text-muted">Not started (0%)</div>
            </div></div>
            <div class="col-6 col-lg-3"><div class="border rounded p-2 text-center bg-body h-100">
                <div class="fs-5 fw-bold text-warning">{{ $t['bcf_birthday'] ?? 0 }} 🎂</div>
                <div class="small text-muted">BCF birthday bookings</div>
            </div></div>
        </div>

        {{-- Executive analytics --}}
        @php
            $statusCounts = collect($data['bgr_projects'] ?? [])->countBy('status');
            $byStage = $data['bgr_by_stage'] ?? [];
            $buckets = $data['bgr_progress_buckets'] ?? [];
        @endphp
        <div class="row g-3 mt-1">
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Builds by Status</h3></div>
                    <div class="card-body"><div style="height:240px"><canvas id="buildStatus"></canvas></div></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center flex-wrap gap-2">
                        <h3 class="card-title mb-0">Build Stage Funnel</h3>
                        <select id="funnelSource" class="form-select form-select-sm ms-auto" style="width:130px">
                            <option value="bgr">BGR builds</option>
                            <option value="bcf">BCF orders</option>
                        </select>
                    </div>
                    <div class="card-body"><div style="height:240px"><canvas id="stageFunnel"></canvas></div></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Progress Distribution <small class="text-muted fw-normal">(BGR + BCF)</small></h3></div>
                    <div class="card-body"><div style="height:240px"><canvas id="progressDist"></canvas></div></div>
                </div>
            </div>
        </div>

        {{-- BGR builds --}}
        <div class="card mt-3" id="buildsCard">
            <div class="card-header d-flex align-items-center flex-wrap gap-2">
                <h3 class="card-title mb-0">Bespoke Garden Rooms — Builds <small class="text-muted fw-normal" id="buildNote"></small></h3>
                <button type="button" class="btn btn-sm btn-link ms-auto d-none" id="buildClear">clear filter</button>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0" id="buildsTable">
                    <thead><tr><th>Project</th><th>Client</th><th>Status</th><th>Stage</th><th style="width:200px">Progress</th></tr></thead>
                    <tbody>
                        @forelse(($data['bgr_projects'] ?? []) as $p)
                            @php
                                $pct = (int) $p['progress_pct'];
                                $bucket = $pct === 0 ? 'Not started' : ($pct === 100 ? 'Complete' : ($pct <= 33 ? '1–33%' : ($pct <= 66 ? '34–66%' : '67–99%')));
                            @endphp
                            <tr data-status="{{ $p['status'] }}" data-stage="{{ $p['current_stage'] }}" data-bucket="{{ $bucket }}">
                                <td>{{ $p['name'] }} @if($p['ghl_link'])<i class="bi bi-link-45deg text-primary" title="Linked to GHL opportunity"></i>@endif</td>
                                <td>{{ $p['client'] }}</td>
                                <td><span class="badge text-bg-secondary text-capitalize">{{ $p['status'] }}</span></td>
                                <td>{{ $p['current_stage'] }} <small class="text-muted">({{ $p['stages'] }})</small></td>
                                <td>
                                    <div class="progress" style="height:18px;">
                                        <div class="progress-bar bg-success" style="width: {{ $p['progress_pct'] }}%">{{ $p['progress_pct'] }}%</div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No builds.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- BCF orders --}}
        <div class="card mt-3" id="bcfOrdersCard">
            <div class="card-header"><h3 class="card-title">Ballycastle Climbing Frames — Orders <small class="text-muted fw-normal" id="bcfNote"></small></h3></div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0" id="bcfOrdersTable">
                    <thead><tr><th>Order</th><th>Client</th><th>Product</th><th>Status</th><th style="width:200px">Progress</th></tr></thead>
                    <tbody>
                        @forelse(($data['bcf_orders'] ?? []) as $o)
                            @php
                                $oc = ['complete' => 'success', 'installed' => 'success', 'in build' => 'primary', 'active' => 'primary', 'scheduled' => 'info', 'pending' => 'secondary'][$o['status'] ?? 'pending'] ?? 'secondary';
                                $bar = ['complete' => 'bg-success', 'installed' => 'bg-success', 'in build' => 'bg-primary', 'active' => 'bg-primary', 'scheduled' => 'bg-info', 'pending' => 'bg-secondary'][$o['status'] ?? 'pending'] ?? 'bg-secondary';
                                $op = (int) $o['progress'];
                                $obucket = $op === 0 ? 'Not started' : ($op === 100 ? 'Complete' : ($op <= 33 ? '1–33%' : ($op <= 66 ? '34–66%' : '67–99%')));
                            @endphp
                            <tr data-stage="{{ $o['current_stage'] ?? '' }}" data-bucket="{{ $obucket }}">
                                <td>{{ $o['order_number'] }} @if($o['birthday'])<span title="Birthday booking">🎂</span>@endif</td>
                                <td>{{ $o['client'] }}</td>
                                <td title="{{ $o['product'] }}"><small>{{ \Illuminate\Support\Str::limit($o['product'], 50) }}</small></td>
                                <td>
                                    <span class="badge text-bg-{{ $oc }} text-capitalize">{{ $o['status'] }}</span>
                                    @if(!empty($o['current_stage']) && $o['current_stage'] !== 'Complete')<small class="text-muted d-block">{{ $o['current_stage'] }}</small>@endif
                                    @if($o['status'] === 'scheduled' && $o['install_date'])<small class="text-muted d-block">{{ $o['install_date'] }}</small>@endif
                                </td>
                                <td>
                                    <div class="progress" style="height:18px;">
                                        <div class="progress-bar {{ $bar }}" style="width: {{ $o['progress'] }}%">{{ $o['progress'] }}%</div>
                                    </div>
                                    @if($o['stage_ref'])<small class="text-muted">stages {{ $o['stage_ref'] }}</small>@endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-3">No orders.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer small text-muted">
                <i class="bi bi-info-circle"></i> Progress uses build stages once BCF adds them per order in the portal; until then it reflects installation status (installed / scheduled / pending).
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const P = window.CEO_PALETTE;
    const pointer = (e, els) => { e.native.target.style.cursor = els.length ? 'pointer' : 'default'; };

    // ---------- Build filters: any chart click narrows the builds table ----------
    let bf = { status: null, stage: null, bucket: null };

    function applyBuildFilters() {
        let shown = 0;
        document.querySelectorAll('#buildsTable tbody tr').forEach(tr => {
            if (!tr.dataset.status) return; // "no builds" placeholder row
            const ok = (!bf.status || tr.dataset.status === bf.status)
                && (!bf.stage || tr.dataset.stage === bf.stage)
                && (!bf.bucket || tr.dataset.bucket === bf.bucket);
            tr.style.display = ok ? '' : 'none';
            if (ok) shown++;
        });
        const parts = [bf.status, bf.stage, bf.bucket].filter(Boolean);
        document.getElementById('buildNote').textContent = parts.length ? '— ' + parts.join(' · ') + ' (' + shown + ')' : '';
        document.getElementById('buildClear').classList.toggle('d-none', !parts.length);
    }
    document.getElementById('buildClear')?.addEventListener('click', () => { bf = { status: null, stage: null, bucket: null }; applyBuildFilters(); });
    const toggleBf = (key, val) => {
        bf[key] = (bf[key] === val ? null : val);
        applyBuildFilters();
        document.getElementById('buildsCard')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    };

    const statusLabels = @json($statusCounts->keys());
    new Chart(document.getElementById('buildStatus'), {
        type: 'doughnut',
        data: { labels: statusLabels, datasets: [{ data: @json($statusCounts->values()), backgroundColor: P }] },
        options: { onHover: pointer, onClick: (e, els) => { if (els.length) toggleBf('status', statusLabels[els[0].index]); } }
    });

    // Stage funnel — switchable between BGR builds and BCF orders.
    const funnelData = {
        bgr: @json((object) $byStage),
        bcf: @json((object) ($data['bcf_by_stage'] ?? [])),
    };
    let funnelMode = 'bgr';
    let bcfF = { stage: null, bucket: null };

    function applyBcfFilters() {
        let shown = 0;
        document.querySelectorAll('#bcfOrdersTable tbody tr').forEach(tr => {
            if (tr.dataset.stage === undefined) return;
            const ok = (!bcfF.stage || tr.dataset.stage === bcfF.stage)
                && (!bcfF.bucket || tr.dataset.bucket === bcfF.bucket);
            tr.style.display = ok ? '' : 'none';
            if (ok) shown++;
        });
        const parts = [bcfF.stage, bcfF.bucket].filter(Boolean);
        document.getElementById('bcfNote').textContent = parts.length ? '— ' + parts.join(' · ') + ' (' + shown + ')' : '';
        if (parts.length) document.getElementById('bcfOrdersCard')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    const funnelChart = new Chart(document.getElementById('stageFunnel'), {
        type: 'bar',
        data: { labels: Object.keys(funnelData.bgr), datasets: [{ label: 'Builds', data: Object.values(funnelData.bgr), backgroundColor: P[0] }] },
        options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { ticks: { precision: 0 } } },
            onHover: pointer,
            onClick: (e, els) => {
                if (!els.length) return;
                const lbl = funnelChart.data.labels[els[0].index];
                if (funnelMode === 'bgr') {
                    toggleBf('stage', lbl);                 // filters the BGR builds table
                } else {
                    bcfF.stage = (bcfF.stage === lbl ? null : lbl);
                    applyBcfFilters();                      // filters the BCF orders table
                }
            } }
    });

    document.getElementById('funnelSource')?.addEventListener('change', function () {
        funnelMode = this.value;
        const src = funnelData[funnelMode];
        funnelChart.data.labels = Object.keys(src);
        funnelChart.data.datasets[0].data = Object.values(src);
        funnelChart.data.datasets[0].backgroundColor = funnelMode === 'bgr' ? P[0] : P[1];
        funnelChart.data.datasets[0].label = funnelMode === 'bgr' ? 'Builds' : 'Orders';
        funnelChart.update();
        // clear the other mode's stage filter so tables aren't left hidden
        if (funnelMode === 'bgr') { bcfF.stage = null; applyBcfFilters(); }
        else if (bf.stage) { toggleBf('stage', bf.stage); }
    });

    // Progress distribution — BGR builds + BCF orders combined.
    // Clicking a bucket filters BOTH tables to that progress band.
    const buckets = @json((object) $buckets);
    const bcfBuckets = @json((object) ($data['bcf_progress_buckets'] ?? []));
    const bucketLabels = Object.keys(buckets);

    function toggleBucketBoth(lbl) {
        toggleBf('bucket', lbl);     // BGR table (toggles bf.bucket on/off)
        bcfF.bucket = bf.bucket;     // mirror the same state onto the BCF table
        applyBcfFilters();
    }

    new Chart(document.getElementById('progressDist'), {
        type: 'bar',
        data: { labels: bucketLabels, datasets: [
            { label: 'BGR builds', data: Object.values(buckets), backgroundColor: P[0] },
            { label: 'BCF orders', data: bucketLabels.map(l => bcfBuckets[l] || 0), backgroundColor: P[1] },
        ]},
        options: { plugins: { legend: { display: true } }, scales: { y: { ticks: { precision: 0 } } },
            onHover: pointer, onClick: (e, els) => { if (els.length) toggleBucketBoth(bucketLabels[els[0].index]); } }
    });
</script>
@endpush
