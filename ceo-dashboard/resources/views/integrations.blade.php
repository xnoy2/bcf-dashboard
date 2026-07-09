@extends('layouts.app')

@section('title', 'Integrations')

@section('content')
<div class="app-content-header">
    <div class="container-fluid d-flex align-items-center flex-wrap gap-2">
        <h3 class="mb-0">Integrations</h3>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid" style="max-width: 960px;">

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show py-2">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-diagram-3 fs-5"></i>
                <h3 class="card-title mb-0">GoHighLevel</h3>
                @if($connection)
                    <span class="badge text-bg-success ms-auto"><i class="bi bi-check-circle"></i> Connected</span>
                @elseif($configured)
                    <span class="badge text-bg-secondary ms-auto">Not connected</span>
                @else
                    <span class="badge text-bg-warning ms-auto">Not configured</span>
                @endif
            </div>

            <div class="card-body">
                @unless($configured)
                    <p class="text-muted mb-0">
                        The GoHighLevel OAuth app isn't configured on this server. Set <code>GHL_CLIENT_ID</code> and
                        <code>GHL_CLIENT_SECRET</code> in the environment, then reload this page.
                    </p>
                @else
                    <p class="text-muted">
                        Connect once at the <strong>agency</strong> level and the dashboard automatically discovers every
                        sub-account under your agency — no per-account tokens.
                    </p>

                    @if(! $connection)
                        <a href="{{ route('integrations.connect') }}" class="btn btn-primary">
                            <i class="bi bi-plug"></i> Connect GoHighLevel
                        </a>
                    @else
                        <div class="d-flex flex-wrap gap-3 align-items-center">
                            <div class="small text-muted">
                                Agency ID: <code>{{ $connection->company_id ?? '—' }}</code><br>
                                Connected {{ $connection->created_at?->diffForHumans() }}
                            </div>
                            <div class="ms-auto d-flex gap-2">
                                <form method="POST" action="{{ route('integrations.sync') }}">
                                    @csrf
                                    <button class="btn btn-outline-primary btn-sm"><i class="bi bi-arrow-repeat"></i> Sync sub-accounts</button>
                                </form>
                                <a href="{{ route('integrations.connect') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-clockwise"></i> Re-authorise</a>
                                <form method="POST" action="{{ route('integrations.disconnect') }}"
                                      onsubmit="return confirm('Disconnect GoHighLevel? Discovered sub-accounts will be removed.');">
                                    @csrf
                                    <button class="btn btn-outline-danger btn-sm"><i class="bi bi-x-circle"></i> Disconnect</button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endunless
            </div>
        </div>

        @if($connection)
            <div class="card mt-3">
                <div class="card-header">
                    <h3 class="card-title mb-0">Sub-accounts <small class="text-muted">({{ $locations->count() }})</small></h3>
                </div>
                <div class="card-body p-0" style="max-height: 60vh; overflow: auto;">
                    <table class="table table-striped mb-0">
                        <thead class="sticky-top bg-body">
                            <tr><th>Name</th><th>Location ID</th><th>Last synced</th></tr>
                        </thead>
                        <tbody>
                            @forelse($locations as $loc)
                                <tr>
                                    <td>{{ $loc->name ?: '—' }}</td>
                                    <td><small class="text-muted">{{ $loc->location_id }}</small></td>
                                    <td><small class="text-muted">{{ $loc->synced_at?->diffForHumans() ?? '—' }}</small></td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-3">No sub-accounts discovered yet — click <strong>Sync sub-accounts</strong>.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
