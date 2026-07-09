<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/d_logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/d_logo.png') }}">
    {{-- Apply saved theme before paint to avoid a flash --}}
    <script>(function(){try{var t=localStorage.getItem('ceo-theme')||'light';document.documentElement.setAttribute('data-theme',t);}catch(e){}})();</script>
    <title>@yield('title', 'CEO Dashboard') | {{ config('app.name') }}</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/css/adminlte.min.css') }}">
    {{-- Executive typography --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&family=Roboto:wght@400;500;700&display=swap">
    {{-- Aubergine & Gold executive theme (must load after AdminLTE) --}}
    <link rel="stylesheet" href="{{ asset('css/executive.css') }}">
    <style>
        /* Vertically centre the dismiss (×) in thin (py-2) alert banners. */
        .alert-dismissible .btn-close { top: 50%; right: .35rem; transform: translateY(-50%); padding: .6rem; }
    </style>
    @stack('styles')
</head>
<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">
<div class="app-wrapper">

    {{-- Header --}}
    <nav class="app-header navbar navbar-expand bg-body">
        <div class="container-fluid">
            <ul class="navbar-nav align-items-center">
                <li class="nav-item">
                    <a class="nav-link" data-lte-toggle="sidebar" href="#" role="button">
                        <i class="bi bi-list"></i>
                    </a>
                </li>
                @auth
                    @php
                        $hour = now()->hour;
                        $greet = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
                        $who = trim((string) (auth()->user()->name ?? ''));
                        if ($who === '' || strtolower($who) === 'ceo') {
                            $who = \Illuminate\Support\Str::of(auth()->user()->email ?? 'there')->before('@')->headline();
                        }
                    @endphp
                    <li class="nav-item ms-2 d-none d-sm-block">
                        <div class="greeting-title">{{ $greet }}, {{ $who }}</div>
                        <div class="greeting-sub">{{ now()->format('l, j F Y') }}</div>
                    </li>
                @endauth
            </ul>
            <ul class="navbar-nav ms-auto align-items-center">
                {{-- Data freshness --}}
                @php $asOf = \App\Support\Snapshot::latest(); @endphp
                <li class="nav-item me-3 d-none d-md-block">
                    <span class="text-muted small" title="Snapshot cache time">
                        <i class="bi bi-clock-history"></i>
                        Data as of {{ $asOf ? $asOf->diffForHumans() : 'never — run dashboard:warm' }}
                    </span>
                </li>
                {{-- Account switcher --}}
                @isset($accounts)
                    <li class="nav-item me-2">
                        <form method="GET" action="{{ url()->current() }}" id="accountForm">
                            <select name="account" class="form-select form-select-sm" onchange="document.getElementById('accountForm').submit()">
                                <option value="all" @selected(($account ?? 'all') === 'all')>All Accounts (CEO)</option>
                                @foreach($accounts as $key => $acc)
                                    <option value="{{ $key }}" @selected(($account ?? '') === $key)>{{ $acc['name'] }}</option>
                                @endforeach
                            </select>
                        </form>
                    </li>
                @endisset
                {{-- Manual data refresh --}}
                <li class="nav-item ms-2">
                    <form method="POST" action="{{ route('refresh-data') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="page" value="{{ request()->route()?->getName() }}">
                        <input type="hidden" name="account" value="{{ $account ?? 'all' }}">
                        <button type="submit" class="theme-toggle" title="Pull fresh data for this page now"
                                onclick="this.querySelector('i').classList.add('spin'); this.disabled = true; this.form.submit();">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </form>
                </li>
                {{-- Dark / bright toggle (icon only) --}}
                <li class="nav-item ms-2">
                    <button id="themeToggle" type="button" class="theme-toggle" title="Toggle dark / bright mode" aria-label="Toggle theme">
                        <i class="bi bi-moon-stars"></i>
                        <i class="bi bi-brightness-high"></i>
                    </button>
                </li>
            </ul>
        </div>
    </nav>

    {{-- Sidebar --}}
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <a href="{{ route('dashboard') }}" class="brand-link d-flex align-items-center gap-2">
                <img src="{{ asset('assets/img/d_logo.png') }}" alt="CEO Dashboard" class="brand-logo-img">
                <span class="brand-text">CEO Dashboard</span>
            </a>
        </div>
        <div class="sidebar-wrapper">
            <nav class="mt-2">
                <ul class="nav sidebar-menu flex-column" role="menu">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-speedometer2"></i><p>CEO Overview</p>
                        </a>
                    </li>
                    @php
                        try {
                            $calUrgent = \App\Models\CalendarEntry::overdue()->count()
                                + \App\Models\CalendarEntry::dueWithin(2)->count();
                        } catch (\Throwable $e) { $calUrgent = 0; }
                    @endphp
                    <li class="nav-item">
                        <a href="{{ route('calendar') }}" class="nav-link {{ request()->routeIs('calendar') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-calendar3"></i>
                            <p>Calendar @if($calUrgent)<span class="badge text-bg-danger ms-1">{{ $calUrgent }}</span>@endif</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('workspaces.index') }}" class="nav-link {{ request()->routeIs('workspaces.*', 'boards.*', 'members.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-columns-gap"></i><p>Boards</p>
                        </a>
                    </li>
                    <li class="nav-header text-uppercase small text-secondary mt-2">Growth</li>
                    <li class="nav-item">
                        <a href="{{ route('sales') }}" class="nav-link {{ request()->routeIs('sales') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-graph-up-arrow"></i><p>Sales Pipeline</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('marketing') }}" class="nav-link {{ request()->routeIs('marketing') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-megaphone"></i><p>Marketing</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('finance') }}" class="nav-link {{ request()->routeIs('finance') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-cash-coin"></i><p>Finance</p>
                        </a>
                    </li>
                    <li class="nav-header text-uppercase small text-secondary mt-2">Delivery</li>
                    <li class="nav-item">
                        <a href="{{ route('operations') }}" class="nav-link {{ request()->routeIs('operations') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-kanban"></i><p>Operations</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('client-projects') }}" class="nav-link {{ request()->routeIs('client-projects') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-house-gear"></i><p>Client Projects</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('staff') }}" class="nav-link {{ request()->routeIs('staff') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-people"></i><p>Staff</p>
                        </a>
                    </li>
                    <li class="nav-header text-uppercase small text-secondary mt-2">Oversight</li>
                    <li class="nav-item">
                        <a href="{{ route('reports') }}" class="nav-link {{ request()->routeIs('reports') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-clipboard-data"></i><p>Reports</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('security') }}" class="nav-link {{ request()->routeIs('security') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-shield-lock"></i><p>IT Security</p>
                        </a>
                    </li>
                    @php
                        try {
                            $renewUrgent = collect(app(\App\Services\GoDaddy\GoDaddyService::class)->overview()['domains'] ?? [])
                                ->filter(fn ($d) => ($d['active'] ?? false)
                                    && $d['days_until'] !== null
                                    && $d['days_until'] <= config('integrations.renewal_alerts.days', 10))
                                ->count();
                        } catch (\Throwable $e) {
                            $renewUrgent = 0;
                        }
                    @endphp
                    <li class="nav-item">
                        <a href="{{ route('renewals') }}" class="nav-link {{ request()->routeIs('renewals') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-calendar-check"></i>
                            <p>Renewals @if($renewUrgent)<span class="badge text-bg-danger ms-1">{{ $renewUrgent }}</span>@endif</p>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('integrations.index') }}" class="nav-link {{ request()->routeIs('integrations.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-diagram-3"></i><p>Integrations</p>
                        </a>
                    </li>

                    {{-- Logout pinned to the bottom of the sidebar --}}
                    <li class="nav-item mt-3 pt-2 border-top border-secondary border-opacity-25">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="nav-link w-100 text-start border-0 bg-transparent text-danger">
                                <i class="nav-icon bi bi-box-arrow-right"></i><p>Logout</p>
                            </button>
                        </form>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>

    {{-- Main --}}
    <main class="app-main">
        @if(session('refreshed'))
            <div class="alert alert-success alert-dismissible fade show mx-3 mt-3 mb-0 py-2">
                <i class="bi bi-check-circle"></i> {{ session('refreshed') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </main>

    <footer class="app-footer d-flex align-items-center flex-wrap gap-2">
        <span><strong>CEO Dashboard</strong> <span class="text-muted">· Executive Portal</span></span>
        <span class="ms-auto text-muted d-none d-sm-inline">
            <i class="bi bi-arrow-repeat"></i> Live data · auto-refreshes every 5 minutes
        </span>
        <span class="text-muted">&copy; {{ date('Y') }}</span>
    </footer>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js" crossorigin="anonymous"></script>
<script src="{{ asset('vendor/adminlte/js/adminlte.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" crossorigin="anonymous"></script>
<script>
    // Shared Chart.js defaults for a clean, executive look.
    var __ceoDark = document.documentElement.getAttribute('data-theme') === 'dark';
    if (window.Chart) {
        Chart.defaults.font.family = '"Roboto", system-ui, sans-serif';
        Chart.defaults.color = __ceoDark ? '#A99FB3' : '#7A7080';
        Chart.defaults.borderColor = __ceoDark ? '#33293F' : '#ECEAF0';
        Chart.defaults.plugins.legend.position = 'bottom';
        Chart.defaults.maintainAspectRatio = false;
        // Aubergine & Gold executive palette
        window.CEO_PALETTE = ['#3B2A4A','#C8A24B','#4E7C59','#5E7C99','#B5495B','#CD8B3C','#8A7F8E','#6A4E86','#E7D4A2'];
    }

    // Near-realtime: reload every 5 minutes while the tab is visible (and the
    // user isn't mid-typing/filtering). Paired with the 5-minute background
    // data warm, changes made in the connected tools appear automatically.
    setInterval(function () {
        // Pages that manage their own live state (e.g. Boards) opt out so an
        // auto-reload never interrupts drag-and-drop or in-place editing.
        if (window.__ceoNoAutoReload) return;
        if (document.visibilityState !== 'visible') return;
        var el = document.activeElement;
        if (el && ['INPUT', 'SELECT', 'TEXTAREA'].includes(el.tagName)) return;
        location.reload();
    }, 300000);

    // Dark / bright toggle — persists choice; reload re-colors charts.
    document.getElementById('themeToggle')?.addEventListener('click', function () {
        var cur = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
        var next = cur === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        try { localStorage.setItem('ceo-theme', next); } catch (e) {}
    });
</script>
@stack('scripts')
</body>
</html>
