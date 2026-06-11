@extends('layouts.app')

@section('title', 'Operations')

@section('content')
<div class="app-content-header">
    <div class="container-fluid d-flex align-items-center flex-wrap gap-2">
        <h3 class="mb-0">Operations <small class="text-muted">— Delivery across Staff, BGR &amp; BCF</small></h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        @php
            $w = $ops['workforce'] ?? [];
            $j = $ops['jobs'] ?? [];
            $b = $ops['builds'] ?? [];
            $o = $ops['orders'] ?? [];
        @endphp

        {{-- KPI row --}}
        <div class="row g-3">
            <div class="col-6 col-xl-3"><div class="card text-bg-primary h-100"><div class="card-body d-flex justify-content-between">
                <div><div class="fs-3 fw-bold">{{ $w['active_staff'] ?? 0 }}</div><div class="small text-uppercase opacity-75">Active Staff</div>
                    <div class="small opacity-75">{{ $w['clocked_in'] ?? 0 }} clocked in now</div>
                    @include('partials.card-link', ['tool' => 'staff'])</div>
                <i class="bi bi-people fs-1 opacity-50"></i>
            </div></div></div>
            <div class="col-6 col-xl-3"><div class="card text-bg-info h-100"><div class="card-body d-flex justify-content-between">
                <div><div class="fs-3 fw-bold">{{ $j['in_progress'] ?? 0 }}</div><div class="small text-uppercase opacity-75">Jobs In Progress</div>
                    <div class="small opacity-75">{{ $j['scheduled'] ?? 0 }} scheduled · {{ $j['today'] ?? 0 }} today</div></div>
                <i class="bi bi-tools fs-1 opacity-50"></i>
            </div></div></div>
            <div class="col-6 col-xl-3"><div class="card text-bg-success h-100"><div class="card-body d-flex justify-content-between">
                <div><div class="fs-3 fw-bold">{{ $b['active'] ?? 0 }}</div><div class="small text-uppercase opacity-75">Active Builds (BGR)</div>
                    <div class="small opacity-75">{{ $b['avg_progress'] ?? 0 }}% avg progress</div>
                    @include('partials.card-link', ['tool' => 'bgr_portal'])</div>
                <i class="bi bi-house-gear fs-1 opacity-50"></i>
            </div></div></div>
            <div class="col-6 col-xl-3"><div class="card text-bg-warning h-100"><div class="card-body d-flex justify-content-between">
                <div><div class="fs-3 fw-bold">{{ $o['total'] ?? 0 }}</div><div class="small text-uppercase opacity-75">BCF Orders</div>
                    <div class="small opacity-75">@if(($o['birthday'] ?? 0)){{ $o['birthday'] }} 🎂 birthday booking{{ $o['birthday'] == 1 ? '' : 's' }}@else no birthday bookings @endif</div>
                    @include('partials.card-link', ['tool' => 'bcf_portal'])</div>
                <i class="bi bi-box-seam fs-1 opacity-50"></i>
            </div></div></div>
        </div>

        {{-- Workforce + charts --}}
        <div class="row g-3 mt-1">
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Workforce</h3></div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">Active / total staff <strong>{{ $w['active_staff'] ?? 0 }} / {{ $w['total_staff'] ?? 0 }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between">Clocked in now <strong>{{ $w['clocked_in'] ?? 0 }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between">Hours this week <strong>{{ $w['hours_week'] ?? 0 }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between">Pending approvals
                            <strong class="{{ ($w['pending_approvals'] ?? 0) ? 'text-danger' : '' }}">{{ $w['pending_approvals'] ?? 0 }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between">Pending leave / overtime <strong>{{ $w['pending_leave'] ?? 0 }} / {{ $w['pending_overtime'] ?? 0 }}</strong></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Jobs Pipeline</h3></div>
                    <div class="card-body"><div style="height:240px"><canvas id="jobsChart"></canvas></div></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Builds by Status (BGR)</h3></div>
                    <div class="card-body"><div style="height:240px"><canvas id="buildsChart"></canvas></div></div>
                </div>
            </div>
        </div>

        {{-- Active builds + recent orders --}}
        <div class="row g-3 mt-1">
            <div class="col-lg-7">
                <div class="card h-100" id="opsBuildsCard">
                    <div class="card-header d-flex align-items-center flex-wrap gap-2">
                        <h3 class="card-title mb-0">Active Builds — progress <small class="text-muted fw-normal" id="opsBuildNote"></small></h3>
                        <button type="button" class="btn btn-sm btn-link ms-auto d-none" id="opsBuildClear">clear</button>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0" id="opsBuildsTable">
                            <thead><tr><th>Project</th><th>Client</th><th>Stage</th><th style="width:160px">Progress</th></tr></thead>
                            <tbody>
                                @forelse($b['in_progress'] ?? [] as $p)
                                    <tr data-status="{{ $p['status'] ?? '' }}">
                                        <td>{{ $p['name'] }}</td>
                                        <td>{{ $p['client'] }}</td>
                                        <td><small>{{ $p['current_stage'] }}</small></td>
                                        <td><div class="progress" style="height:16px;">
                                            <div class="progress-bar bg-success" style="width:{{ $p['progress_pct'] }}%">{{ $p['progress_pct'] }}%</div>
                                        </div></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-3">No active builds.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Recent BCF Orders</h3></div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead><tr><th>Order</th><th>Client</th><th>Product</th></tr></thead>
                            <tbody>
                                @forelse($o['recent'] ?? [] as $ord)
                                    <tr style="cursor:pointer" onclick="location.href='{{ route('client-projects') }}'" title="Open Client Projects">
                                        <td>{{ $ord['order_number'] }} @if($ord['birthday'])<span title="Birthday booking">🎂</span>@endif</td>
                                        <td>{{ $ord['client'] }}</td>
                                        <td><small>{{ \Illuminate\Support\Str::limit($ord['product'], 28) }}</small></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center text-muted py-3">No orders.</td></tr>
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
    const P = window.CEO_PALETTE;
    const jobs = @json($j['by_status'] ?? (object)[]);
    const builds = @json($b['by_status'] ?? (object)[]);

    const pointer = (e, els) => { e.native.target.style.cursor = els.length ? 'pointer' : 'default'; };

    // Jobs pipeline: prefer by_status map; fall back to the live in_progress/scheduled/today.
    // Clicking a slice deep-links into the Staff page's Job Schedule, pre-filtered.
    const jobLabels = Object.keys(jobs).length ? Object.keys(jobs) : ['Today','In progress','Scheduled'];
    const jobData = Object.keys(jobs).length ? Object.values(jobs) : [{{ $j['today'] ?? 0 }}, {{ $j['in_progress'] ?? 0 }}, {{ $j['scheduled'] ?? 0 }}];
    new Chart(document.getElementById('jobsChart'), {
        type: 'doughnut',
        data: { labels: jobLabels, datasets: [{ data: jobData, backgroundColor: P }] },
        options: {
            onHover: pointer,
            onClick: (e, els) => { if (els.length) location.href = '{{ route('staff') }}?job_status=' + encodeURIComponent(jobLabels[els[0].index]); }
        }
    });

    // Builds-by-status: clicking a slice filters the Active Builds table below.
    let opsStatusF = null;
    const buildLabels = Object.keys(builds);
    function applyOpsBuildFilter() {
        let shown = 0;
        document.querySelectorAll('#opsBuildsTable tbody tr').forEach(tr => {
            if (tr.dataset.status === undefined) return;
            const ok = !opsStatusF || tr.dataset.status === opsStatusF;
            tr.style.display = ok ? '' : 'none';
            if (ok) shown++;
        });
        document.getElementById('opsBuildNote').textContent = opsStatusF ? '— ' + opsStatusF + ' (' + shown + ')' : '';
        document.getElementById('opsBuildClear').classList.toggle('d-none', !opsStatusF);
    }
    document.getElementById('opsBuildClear')?.addEventListener('click', () => { opsStatusF = null; applyOpsBuildFilter(); });

    new Chart(document.getElementById('buildsChart'), {
        type: 'doughnut',
        data: { labels: buildLabels, datasets: [{ data: Object.values(builds), backgroundColor: P }] },
        options: {
            onHover: pointer,
            onClick: (e, els) => {
                if (!els.length) return;
                const s = buildLabels[els[0].index];
                opsStatusF = (opsStatusF === s ? null : s);
                applyOpsBuildFilter();
                document.getElementById('opsBuildsCard')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    });
</script>
@endpush
