@extends('layouts.app')

@section('title', 'Finance')

@section('content')
<div class="app-content-header">
    <div class="container-fluid d-flex align-items-center flex-wrap gap-2">
        <h3 class="mb-0">Finance</h3>
        <span class="badge text-bg-primary ms-auto">{{ $account === 'all' ? 'All Accounts' : ($accounts[$account]['name'] ?? $account) }}</span>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        @if(!empty($finance['error']))
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle"></i>
                GHL payments: <strong>{{ $finance['error'] }}</strong>.
                Enable the <em>payments</em> scope on the GHL private-integration token to populate this page.
            </div>
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card text-bg-success h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-3 fw-bold">£{{ number_format((float)($finance['cash_in'] ?? 0)) }}</div>
                            <div class="text-uppercase small opacity-75">Cash In (succeeded)</div>
                            @include('partials.card-link', ['tool' => 'ghl'])
                        </div>
                        <i class="bi bi-cash-stack fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-bg-info h-100">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fs-3 fw-bold">{{ number_format($finance['transactions'] ?? 0) }}</div>
                            <div class="text-uppercase small opacity-75">Transactions</div>
                        </div>
                        <i class="bi bi-receipt fs-1 opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>

        @if($account === 'all' && !empty($finance['accounts']))
            <div class="card mt-3">
                <div class="card-header"><h3 class="card-title">Per-Account Breakdown</h3></div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Account</th><th class="text-end">Cash In</th><th class="text-end">Transactions</th></tr></thead>
                        <tbody>
                            @foreach($finance['accounts'] as $key => $a)
                                <tr style="cursor:pointer" onclick="location.href='{{ route('finance', ['account' => $key]) }}'" title="View {{ $accounts[$key]['name'] ?? $key }} only">
                                    <td>{{ $accounts[$key]['name'] ?? $key }} <i class="bi bi-box-arrow-up-right small text-muted"></i></td>
                                    <td class="text-end">£{{ number_format((float)($a['cash_in'] ?? 0)) }}</td>
                                    <td class="text-end">{{ number_format($a['transactions'] ?? 0) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
</div>
@endsection
