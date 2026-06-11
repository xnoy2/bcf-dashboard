@extends('layouts.app')

@section('title', 'IT Security')

@section('content')
<div class="app-content-header">
    <div class="container-fluid d-flex align-items-center flex-wrap gap-2">
        <h3 class="mb-0">IT Security <small class="text-muted">(Cloudflare + GoDaddy estate)</small></h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        @if(!empty($security['error']))
            <div class="alert alert-warning">{{ $security['error'] }}</div>
        @endif

        {{-- KPI row --}}
        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="card text-bg-primary h-100"><div class="card-body">
                    <div class="fs-3 fw-bold">{{ $security['domains'] ?? 0 }}</div>
                    <div class="text-uppercase small opacity-75">Domains Monitored</div>
                    <div class="d-flex gap-3">
                        @include('partials.card-link', ['tool' => 'cloudflare', 'text' => ($security['cf_zones'] ?? 0) . ' Cloudflare'])
                        @include('partials.card-link', ['tool' => 'godaddy', 'text' => ($security['gd_domains'] ?? 0) . ' GoDaddy'])
                    </div>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card text-bg-success h-100"><div class="card-body">
                    <div class="fs-3 fw-bold">{{ $security['ssl_healthy'] ?? 0 }}</div>
                    <div class="text-uppercase small opacity-75">Responding on HTTPS</div>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card text-bg-info h-100 clickable-card" onclick="document.getElementById('appsCard')?.scrollIntoView({behavior:'smooth'})"><div class="card-body">
                    <div class="fs-3 fw-bold">{{ $security['web_apps']['online'] ?? 0 }}/{{ $security['web_apps']['total'] ?? 0 }}</div>
                    <div class="text-uppercase small opacity-75">Web Apps Online</div>
                    <div class="small opacity-75">subdomains &amp; portals — click to view</div>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card text-bg-{{ ($security['alerts'] ?? 0) ? 'danger' : 'secondary' }} h-100"><div class="card-body">
                    <div class="fs-3 fw-bold">{{ $security['alerts'] ?? 0 }}</div>
                    <div class="text-uppercase small opacity-75">Active Alerts</div>
                    <div class="small opacity-75">email health: {{ $security['email_health'] ?? '—' }}</div>
                </div></div>
            </div>
        </div>

        <div class="row g-3 mt-1">
            {{-- Compliance --}}
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header"><h3 class="card-title">Compliance</h3></div>
                    <div class="card-body">
                        @php
                            $bars = [
                                'HTTPS reachable' => $security['compliance']['ssl'] ?? null,
                                'Email DNS (SPF/DKIM/DMARC/MX)' => $security['compliance']['dns'] ?? null,
                                'TLS certificates healthy' => $security['compliance']['certs'] ?? null,
                                'Web apps online' => $security['compliance']['apps'] ?? null,
                            ];
                        @endphp
                        @foreach($bars as $label => $pct)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between small"><span>{{ $label }}</span><span>{{ $pct === null ? 'n/a' : $pct.'%' }}</span></div>
                                <div class="progress" style="height:10px;">
                                    <div class="progress-bar bg-{{ $pct === null ? 'secondary' : ($pct >= 80 ? 'success' : ($pct >= 50 ? 'warning' : 'danger')) }}" style="width:{{ $pct ?? 0 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                        @if(($security['cert_warn'] ?? 0) > 0)
                            <div class="alert alert-warning py-2 small mb-0"><i class="bi bi-exclamation-triangle"></i> {{ $security['cert_warn'] }} certificate(s) expiring within 14 days</div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Domains --}}
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center flex-wrap gap-2">
                        <h3 class="card-title mb-0">Domains <small class="text-muted">({{ count($security['domains_list'] ?? []) }})</small></h3>
                        <input type="search" id="domSearch" class="form-control form-control-sm ms-auto" placeholder="Filter…" style="width:200px">
                    </div>
                    <div class="card-body p-0" style="max-height:52vh; overflow:auto;">
                        <table class="table table-striped mb-0" id="domTable">
                            <thead class="sticky-top bg-body">
                                <tr><th>Domain</th><th>Source</th><th>HTTPS</th><th>SPF</th><th>DKIM</th><th>DMARC</th><th>MX</th><th>DNSSEC</th><th>Email Grade</th></tr>
                            </thead>
                            <tbody>
                                @forelse(($security['domains_list'] ?? []) as $d)
                                    <tr>
                                        <td>{{ $d['name'] }}</td>
                                        <td><span class="badge text-bg-light">{{ $d['source'] }}</span></td>
                                        @foreach(['ssl','spf','dkim','dmarc','mx'] as $k)
                                            <td>
                                                @if($d[$k] ?? false)<i class="bi bi-check-circle-fill text-success"></i>
                                                @else<i class="bi bi-x-circle-fill text-danger"></i>@endif
                                            </td>
                                        @endforeach
                                        <td>
                                            @if(($d['dnssec'] ?? null) === null)<span class="text-muted">—</span>
                                            @elseif($d['dnssec'] === 'active')<i class="bi bi-shield-fill-check text-success" title="DNSSEC active"></i>
                                            @elseif($d['dnssec'] === 'pending')<span title="Enabled in Cloudflare, but the DS record hasn't been added at the registrar yet"><i class="bi bi-hourglass-split text-warning"></i> <small class="text-warning">pending</small></span>
                                            @else<i class="bi bi-shield-slash text-danger" title="DNSSEC off"></i>@endif
                                        </td>
                                        <td>
                                            @php $gc = ['A'=>'success','B'=>'info','C'=>'warning','D'=>'danger'][$d['grade'] ?? 'D']; @endphp
                                            <span class="badge text-bg-{{ $gc }}">{{ $d['grade'] ?? '—' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="9" class="text-center text-muted py-3">No domains.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Web apps & subdomains --}}
        <div class="card mt-3" id="appsCard">
            <div class="card-header d-flex align-items-center flex-wrap gap-2">
                <h3 class="card-title mb-0">Web Apps &amp; Subdomains <small class="text-muted">({{ count($security['apps_list'] ?? []) }})</small></h3>
                <input type="search" id="appSearch" class="form-control form-control-sm ms-auto" placeholder="Filter…" style="width:200px">
            </div>
            <div class="card-body p-0" style="max-height:55vh; overflow:auto;">
                <table class="table table-striped table-hover mb-0" id="appTable">
                    <thead class="sticky-top bg-body">
                        <tr><th>Host</th><th>Type</th><th>Cloudflare Proxy</th><th>Status</th><th class="text-end">Response</th><th>Cert Expires</th></tr>
                    </thead>
                    <tbody>
                        @forelse(($security['apps_list'] ?? []) as $a)
                            <tr>
                                <td><a href="https://{{ $a['host'] }}" target="_blank" rel="noopener" class="text-decoration-none">{{ $a['host'] }} <i class="bi bi-box-arrow-up-right small text-muted"></i></a></td>
                                <td><span class="badge text-bg-light">{{ $a['type'] }}</span></td>
                                <td>
                                    @if($a['proxied'])<i class="bi bi-cloud-fill text-warning" title="Proxied via Cloudflare"></i> <small class="text-muted">proxied</small>
                                    @else<span class="text-muted small">direct</span>@endif
                                </td>
                                <td>
                                    @if($a['ok'])<span class="badge text-bg-success">Online{{ $a['status'] ? ' · '.$a['status'] : '' }}</span>
                                    @else<span class="badge text-bg-danger">Unreachable</span>@endif
                                </td>
                                <td class="text-end">{{ $a['ms'] !== null ? $a['ms'].' ms' : '—' }}</td>
                                <td>
                                    @if($a['cert_days'] === null)<span class="text-muted">—</span>
                                    @else
                                        <span class="badge text-bg-{{ $a['cert_days'] <= 14 ? 'danger' : ($a['cert_days'] <= 30 ? 'warning' : 'success') }}">{{ $a['cert_days'] }} days</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-3">No web apps found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer small text-muted">
                <i class="bi bi-info-circle"></i> Every A/AAAA/CNAME record in the Cloudflare zones, health-checked over HTTPS with TLS certificate expiry.
            </div>
        </div>

        {{-- Alerts --}}
        @if(!empty($security['alerts_list']))
            <div class="card mt-3 border-danger">
                <div class="card-header bg-danger text-white"><h3 class="card-title">Active Alerts</h3></div>
                <ul class="list-group list-group-flush">
                    @foreach($security['alerts_list'] as $a)
                        <li class="list-group-item"><i class="bi bi-exclamation-triangle text-danger"></i> {{ $a }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

    </div>
</div>
@endsection

@push('scripts')
<script>
    function wireFilter(inputId, tableId) {
        document.getElementById(inputId)?.addEventListener('input', function (e) {
            const q = e.target.value.toLowerCase();
            document.querySelectorAll('#' + tableId + ' tbody tr').forEach(tr => {
                tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none';
            });
        });
    }
    wireFilter('domSearch', 'domTable');
    wireFilter('appSearch', 'appTable');
</script>
@endpush
