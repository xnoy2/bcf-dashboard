@extends('layouts.app')

@section('title', 'Staff')

@section('content')
<div class="app-content-header">
    <div class="container-fluid d-flex align-items-center flex-wrap gap-2">
        <h3 class="mb-0">Staff <small class="text-muted">(Staff Portal)</small></h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        @php
            $s  = $staff['staff'] ?? [];
            $at = $staff['attendance'] ?? [];
            $pr = $staff['payroll'] ?? [];
            $jb = $staff['jobs'] ?? [];
        @endphp

        {{-- Headcount --}}
        <div class="row g-3">
            <div class="col-6 col-lg-3"><div class="card text-bg-primary"><div class="card-body">
                <div class="fs-3 fw-bold">{{ $s['total_staff'] ?? 0 }}</div><div class="small text-uppercase opacity-75">Total Staff</div>
                @include('partials.card-link', ['tool' => 'staff'])
            </div></div></div>
            <div class="col-6 col-lg-3"><div class="card text-bg-success"><div class="card-body">
                <div class="fs-3 fw-bold">{{ $s['active_staff'] ?? 0 }}</div><div class="small text-uppercase opacity-75">Active</div>
            </div></div></div>
            <div class="col-6 col-lg-3"><div class="card text-bg-secondary"><div class="card-body">
                <div class="fs-3 fw-bold">{{ $s['inactive_staff'] ?? 0 }}</div><div class="small text-uppercase opacity-75">Inactive</div>
            </div></div></div>
            <div class="col-6 col-lg-3"><div class="card text-bg-info"><div class="card-body">
                <div class="fs-3 fw-bold">{{ $s['new_this_month'] ?? 0 }}</div><div class="small text-uppercase opacity-75">New This Month</div>
            </div></div></div>
        </div>

        <div class="row g-3 mt-1">
            {{-- Attendance --}}
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Attendance (Live)</h3></div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">Clocked in now <strong>{{ $at['clocked_in_now'] ?? 0 }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between">Hours today <strong>{{ $at['hours_today'] ?? 0 }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between">Hours this week <strong>{{ $at['hours_this_week'] ?? 0 }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between">Pending approvals <strong>{{ $at['pending_approvals'] ?? 0 }}</strong></li>
                    </ul>
                </div>
            </div>
            {{-- Payroll --}}
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Payroll</h3></div>
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">Gross pay <strong>£{{ number_format((float)($pr['total_gross_pay'] ?? 0)) }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between">Net pay <strong>£{{ number_format((float)($pr['total_net_pay'] ?? 0)) }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between">Total hours <strong>{{ $pr['total_hours'] ?? 0 }}</strong></li>
                        <li class="list-group-item d-flex justify-content-between">Overtime hours <strong>{{ $pr['total_overtime_hours'] ?? 0 }}</strong></li>
                    </ul>
                </div>
            </div>
            {{-- Roles chart --}}
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Team by Role <small class="text-muted fw-normal" id="roleNote"></small></h3></div>
                    <div class="card-body"><div style="height:220px"><canvas id="roleChart"></canvas></div></div>
                    <div class="card-footer py-1 small text-muted">click a slice to filter the roster</div>
                </div>
            </div>
        </div>

        {{-- Team roster --}}
        @php $roster = $staff['roster'] ?? []; @endphp
        <div class="card mt-3">
            <div class="card-header d-flex align-items-center flex-wrap gap-2">
                <h3 class="card-title mb-0">Team Roster <small class="text-muted">({{ count($roster) }})</small></h3>
                <input type="search" id="staffSearch" class="form-control form-control-sm ms-auto" placeholder="Filter…" style="width:220px">
            </div>
            <div class="card-body p-0" style="max-height:55vh; overflow:auto;">
                <table class="table table-striped table-hover mb-0" id="staffTable">
                    <thead class="sticky-top bg-body">
                        <tr><th>ID</th><th>Name</th><th>Role(s)</th><th>Hire Date</th><th class="text-end">Contracted Hrs</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse($roster as $m)
                            <tr>
                                <td><small class="text-muted">{{ $m['employee_id'] }}</small></td>
                                <td>{{ $m['name'] }}</td>
                                <td>{{ $m['roles'] }}</td>
                                <td>{{ $m['hire_date'] ?? '—' }}</td>
                                <td class="text-end">{{ $m['contracted'] ?? '—' }}</td>
                                <td>
                                    <span class="badge text-bg-{{ $m['is_active'] ? 'success' : 'secondary' }}">
                                        {{ $m['is_active'] ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No staff returned.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Job schedule (calendar) --}}
        @php $jobsList = $staff['jobs_list'] ?? []; @endphp
        <div class="card mt-3">
            <div class="card-header d-flex align-items-center flex-wrap gap-2">
                <h3 class="card-title mb-0">Job Schedule <small class="text-muted">({{ count($jobsList) }} jobs)</small> <small class="text-muted fw-normal" id="jobNote"></small></h3>
                <input type="search" id="jobSearch" class="form-control form-control-sm ms-auto" placeholder="Filter…" style="width:220px">
            </div>
            <div class="card-body p-0" style="max-height:55vh; overflow:auto;">
                <table class="table table-striped table-hover mb-0" id="jobTable">
                    <thead class="sticky-top bg-body">
                        <tr><th>Date</th><th>Job</th><th>Status</th><th>Assigned Staff</th><th>Van</th><th>Project</th></tr>
                    </thead>
                    <tbody>
                        @forelse($jobsList as $j)
                            @php $jc = ['scheduled'=>'info','in_progress'=>'warning','completed'=>'success','cancelled'=>'secondary'][$j['status']] ?? 'secondary'; @endphp
                            <tr data-jstatus="{{ $j['status'] }}">
                                <td>{{ $j['date'] ?? '—' }}@if($j['start'])<small class="text-muted d-block">{{ $j['start'] }}@if($j['end'])–{{ $j['end'] }}@endif</small>@endif</td>
                                <td>{{ $j['title'] }}</td>
                                <td><span class="badge text-bg-{{ $jc }} text-capitalize">{{ str_replace('_',' ',$j['status']) }}</span></td>
                                <td>{{ $j['staff'] ?: '—' }}</td>
                                <td>{{ $j['van'] ?? '—' }}</td>
                                <td>{{ $j['project'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No jobs scheduled.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-muted small">
                <i class="bi bi-info-circle"></i> Van &amp; project show once assigned in the Staff Portal. Training records aren’t exposed by the portal API.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    @php
        $roleSrc = $s['by_role'] ?? [];
        $roleLabels = []; $roleData = [];
        foreach ($roleSrc as $k => $v) {
            if (is_array($v)) { $roleLabels[] = $v['role'] ?? $k; $roleData[] = $v['count'] ?? 0; }
            else { $roleLabels[] = $k; $roleData[] = $v; }
        }
    @endphp
    const roleLabels = @json($roleLabels);
    let roleF = null;

    function applyStaffFilters() {
        const q = (document.getElementById('staffSearch')?.value || '').toLowerCase();
        document.querySelectorAll('#staffTable tbody tr').forEach(tr => {
            const matchQ = tr.textContent.toLowerCase().includes(q);
            const roleCell = (tr.children[2]?.textContent || '').toLowerCase();
            const matchRole = !roleF || roleCell.includes(roleF.toLowerCase());
            tr.style.display = (matchQ && matchRole) ? '' : 'none';
        });
        document.getElementById('roleNote').textContent = roleF ? '— ' + roleF + ' (click again to clear)' : '';
        roleChart.data.datasets[0].offset = roleLabels.map(l => l === roleF ? 16 : 0);
        roleChart.update();
    }

    const roleChart = new Chart(document.getElementById('roleChart'), {
        type: 'doughnut',
        data: { labels: roleLabels, datasets: [{ data: @json($roleData), backgroundColor: window.CEO_PALETTE }] },
        options: {
            onHover: (e, els) => { e.native.target.style.cursor = els.length ? 'pointer' : 'default'; },
            onClick: (e, els) => {
                if (!els.length) return;
                const r = roleLabels[els[0].index];
                roleF = (roleF === r ? null : r);
                applyStaffFilters();
                document.getElementById('staffTable')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    });

    document.getElementById('staffSearch')?.addEventListener('input', applyStaffFilters);

    document.getElementById('jobSearch')?.addEventListener('input', function (e) {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll('#jobTable tbody tr').forEach(tr => {
            tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    // Deep-link from Operations' jobs chart: ?job_status=scheduled etc.
    const _jq = new URLSearchParams(location.search).get('job_status');
    if (_jq) {
        document.querySelectorAll('#jobTable tbody tr').forEach(tr => {
            if (tr.dataset.jstatus && tr.dataset.jstatus !== _jq) tr.style.display = 'none';
        });
        document.getElementById('jobNote').textContent = '— ' + _jq.replace('_', ' ') + ' only';
        setTimeout(() => document.getElementById('jobTable')?.scrollIntoView({ behavior: 'smooth', block: 'center' }), 250);
    }
</script>
@endpush
