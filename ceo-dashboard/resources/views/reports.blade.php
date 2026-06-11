@extends('layouts.app')

@section('title', 'Work Reports')

@section('content')
<div class="app-content-header">
    <div class="container-fluid d-flex align-items-center flex-wrap gap-2">
        <h3 class="mb-0">Work Reports <small class="text-muted">(Staff · BGR · BCF)</small></h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row g-3">
            @php
                $cards = [
                    ['All Work Items', $stats['total'], 'secondary', ''],
                    ['Completed', $stats['completed'], 'success', 'Completed'],
                    ['In Progress', $stats['in_progress'], 'warning', 'In Progress'],
                    ['Pending / Scheduled', $stats['upcoming'], 'info', 'Pending|Scheduled'],
                ];
            @endphp
            @foreach($cards as [$label, $value, $color, $statusKey])
                <div class="col-6 col-lg-3">
                    <div class="card text-bg-{{ $color }} clickable-card report-kpi" data-status="{{ $statusKey }}" title="{{ $statusKey ? 'Show only: '.str_replace('|', ' / ', $statusKey) : 'Show everything' }}">
                        <div class="card-body">
                            <div class="fs-3 fw-bold">{{ number_format($value) }}</div>
                            <div class="text-uppercase small opacity-75">{{ $label }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card mt-3">
            <div class="card-header d-flex align-items-center flex-wrap gap-2">
                <h3 class="card-title mb-0">All Work <small class="text-muted fw-normal" id="reportNote"></small></h3>
                <div class="ms-auto d-flex align-items-center gap-2 flex-wrap">
                    <select id="typeFilter" class="form-select form-select-sm" style="width:150px">
                        <option value="">All types</option>
                        <option>Staff Job</option>
                        <option>BGR Build</option>
                        <option>BCF Order</option>
                    </select>
                    <input type="search" id="reportSearch" class="form-control form-control-sm" placeholder="Search…" style="width:190px">
                    <button type="button" id="exportCsv" class="btn btn-sm btn-primary">
                        <i class="bi bi-download"></i> Export CSV
                    </button>
                </div>
            </div>
            <div class="card-body p-0" style="max-height:62vh; overflow:auto;">
                <table class="table table-striped table-hover mb-0" id="reportTable">
                    <thead class="sticky-top bg-body">
                        <tr><th>Item</th><th>Type</th><th>Client / Project</th><th>Assigned To</th><th>Status</th><th>Date</th></tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $r)
                            @php
                                $sc = ['Completed' => 'success', 'In Progress' => 'warning', 'Scheduled' => 'info', 'Cancelled' => 'secondary', 'Pending' => 'secondary'][$r['status']] ?? 'secondary';
                                $tc = ['Staff Job' => 'primary', 'BGR Build' => 'success', 'BCF Order' => 'warning'][$r['type']] ?? 'light';
                            @endphp
                            <tr data-type="{{ $r['type'] }}" data-status="{{ $r['status'] }}">
                                <td>{{ $r['title'] }}</td>
                                <td><span class="badge text-bg-{{ $tc }}">{{ $r['type'] }}</span></td>
                                <td>{{ $r['for'] }}</td>
                                <td>{{ $r['assigned'] }}</td>
                                <td><span class="badge text-bg-{{ $sc }}">{{ $r['status'] }}</span></td>
                                <td>{{ $r['date'] ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No work items found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer small text-muted d-flex flex-wrap gap-2 align-items-center">
                <span><i class="bi bi-info-circle"></i> One view of every job, build and order across the portals.
                Click a card above to filter by status; <strong>Export CSV</strong> downloads exactly what's shown.</span>
                <span class="ms-auto d-flex gap-3">
                    @include('partials.card-link', ['tool' => 'staff', 'text' => 'Staff Portal'])
                    @include('partials.card-link', ['tool' => 'bgr_portal', 'text' => 'BGR Portal'])
                    @include('partials.card-link', ['tool' => 'bcf_portal', 'text' => 'BCF Portal'])
                </span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let statusF = '';

    function applyReportFilters() {
        const q = (document.getElementById('reportSearch')?.value || '').toLowerCase();
        const t = document.getElementById('typeFilter')?.value || '';
        const statuses = statusF ? statusF.split('|') : null;
        let shown = 0;
        document.querySelectorAll('#reportTable tbody tr').forEach(tr => {
            if (!tr.dataset.type) return;
            const ok = tr.textContent.toLowerCase().includes(q)
                && (!t || tr.dataset.type === t)
                && (!statuses || statuses.includes(tr.dataset.status));
            tr.style.display = ok ? '' : 'none';
            if (ok) shown++;
        });
        const parts = [];
        if (statusF) parts.push(statusF.replace('|', ' / '));
        if (t) parts.push(t);
        document.getElementById('reportNote').textContent = parts.length ? '— ' + parts.join(' · ') + ' (' + shown + ')' : '';
        document.querySelectorAll('.report-kpi').forEach(c => {
            c.style.outline = (c.dataset.status === statusF && statusF) ? '3px solid rgba(200,162,75,.8)' : '';
        });
    }

    document.getElementById('reportSearch')?.addEventListener('input', applyReportFilters);
    document.getElementById('typeFilter')?.addEventListener('change', applyReportFilters);
    document.querySelectorAll('.report-kpi').forEach(card => {
        card.addEventListener('click', () => {
            const s = card.dataset.status;
            statusF = (statusF === s ? '' : s);
            applyReportFilters();
            document.getElementById('reportTable')?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        });
    });

    // Export the currently visible (filtered) rows as CSV.
    document.getElementById('exportCsv')?.addEventListener('click', () => {
        const rows = [['Item', 'Type', 'Client / Project', 'Assigned To', 'Status', 'Date']];
        document.querySelectorAll('#reportTable tbody tr').forEach(tr => {
            if (tr.style.display === 'none' || !tr.dataset.type) return;
            rows.push([...tr.children].map(td => '"' + td.textContent.trim().replace(/\s+/g, ' ').replace(/"/g, '""') + '"'));
        });
        const blob = new Blob(['﻿' + rows.map(r => r.join(',')).join('\r\n')], { type: 'text/csv;charset=utf-8' });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'work-report-' + new Date().toISOString().slice(0, 10) + '.csv';
        a.click();
        URL.revokeObjectURL(a.href);
    });
</script>
@endpush
