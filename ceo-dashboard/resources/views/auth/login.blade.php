<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | {{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/img/d_logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/d_logo.png') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/css/adminlte.min.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700;900&family=Roboto:wght@400;500;700&display=swap">
    <link rel="stylesheet" href="{{ asset('css/executive.css') }}">
    <style>
        /* ============ Stage ============ */
        body.exec-login {
            margin: 0; min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: radial-gradient(120% 120% at 20% 10%, #2C2138 0%, #1E1727 55%, #171220 100%) !important;
            font-family: "Roboto", sans-serif;
            overflow: hidden; position: relative;
        }

        /* Aurora orbs drifting behind everything */
        .aurora { position: fixed; border-radius: 50%; filter: blur(90px); pointer-events: none; z-index: 0; will-change: transform; }
        .aurora-1 { width: 600px; height: 600px; background: #6A4E86; opacity: .50; top: -160px; left: -140px; animation: drift1 26s ease-in-out infinite; }
        .aurora-2 { width: 520px; height: 520px; background: #C8A24B; opacity: .22; bottom: -180px; right: -120px; animation: drift2 30s ease-in-out infinite; }
        .aurora-3 { width: 420px; height: 420px; background: #9A5E7C; opacity: .25; top: 45%; left: 55%; animation: drift3 34s ease-in-out infinite; }
        @keyframes drift1 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(70px,40px) scale(1.12); } }
        @keyframes drift2 { 0%,100% { transform: translate(0,0) scale(1); } 50% { transform: translate(-60px,-50px) scale(1.08); } }
        @keyframes drift3 { 0%,100% { transform: translate(0,0); } 50% { transform: translate(-80px,30px); } }

        /* Faint dot grid for a "command-center" texture */
        .grid-overlay {
            position: fixed; inset: 0; z-index: 0; pointer-events: none;
            background-image: radial-gradient(rgba(255,255,255,.07) 1px, transparent 1px);
            background-size: 34px 34px;
            -webkit-mask-image: radial-gradient(70% 70% at 50% 45%, #000 30%, transparent 100%);
            mask-image: radial-gradient(70% 70% at 50% 45%, #000 30%, transparent 100%);
        }

        /* ============ The card ============ */
        .login-card {
            position: relative; z-index: 2;
            display: flex; width: min(960px, 94vw); min-height: 560px;
            border-radius: 26px; overflow: hidden;
            border: 1px solid rgba(200,162,75,.35);
            box-shadow: 0 40px 90px rgba(0,0,0,.55), 0 0 0 1px rgba(255,255,255,.04), 0 0 70px rgba(200,162,75,.10);
            animation: cardIn .85s cubic-bezier(.2,.8,.25,1) both;
        }
        @keyframes cardIn { from { opacity: 0; transform: translateY(26px) scale(.97); } to { opacity: 1; transform: none; } }

        /* ---- Left: brand panel ---- */
        .panel-brand {
            flex: 0 0 44%;
            background: linear-gradient(160deg, #543D6E 0%, #3B2A4A 55%, #2A1E36 100%);
            color: #fff; padding: 2.8rem;
            display: flex; flex-direction: column; justify-content: space-between;
            position: relative; overflow: hidden;
        }
        .panel-brand::before { /* gold light bloom */
            content: ""; position: absolute; width: 380px; height: 380px; border-radius: 50%;
            top: -130px; right: -110px;
            background: radial-gradient(circle, rgba(210,172,87,.35), transparent 70%);
            animation: bloom 7s ease-in-out infinite alternate;
        }
        @keyframes bloom { from { opacity: .7; transform: scale(1); } to { opacity: 1; transform: scale(1.15); } }

        .brand-top { position: relative; z-index: 1; }
        .brand-emblem {
            width: 76px; height: 76px; border-radius: 19px; padding: 10px;
            background: #fff; border: 2px solid #C8A24B;
            box-shadow: 0 10px 26px rgba(0,0,0,.35), 0 0 0 6px rgba(210,172,87,.14);
            position: relative; overflow: hidden;
            animation: fadeUp .7s ease both;
        }
        .brand-emblem img { width: 100%; height: 100%; object-fit: contain; }
        .brand-emblem::after { /* shine sweep */
            content: ""; position: absolute; inset: -20%;
            background: linear-gradient(115deg, transparent 42%, rgba(255,255,255,.85) 50%, transparent 58%);
            transform: translateX(-130%);
            animation: shine 4.6s ease-in-out 1.2s infinite;
        }
        @keyframes shine { 0% { transform: translateX(-130%); } 28% { transform: translateX(130%); } 100% { transform: translateX(130%); } }

        .brand-word {
            font-family: "Lato", sans-serif; font-weight: 900;
            font-size: 2.15rem; line-height: 1.08; margin: 1.2rem 0 0;
            color: #fff !important; text-shadow: 0 2px 18px rgba(0,0,0,.3);
            animation: fadeUp .7s ease .1s both;
        }
        .brand-word span { color: #D2AC57 !important; }
        .brand-tag {
            text-transform: uppercase; letter-spacing: .3em; font-size: .66rem;
            color: #D2AC57; font-weight: 700; margin-top: .45rem;
            animation: fadeUp .7s ease .2s both;
        }

        /* Living mini chart */
        .brand-chart { position: relative; z-index: 1; margin: 1.9rem 0 0; animation: fadeUp .7s ease .35s both; }
        .brand-chart .bc-label { font-size: .62rem; letter-spacing: .16em; text-transform: uppercase; color: rgba(255,255,255,.65); margin-bottom: .5rem; }
        .bars { display: flex; align-items: flex-end; gap: 7px; height: 64px; }
        .bars span {
            flex: 1; border-radius: 4px 4px 2px 2px;
            background: linear-gradient(180deg, #E3C57E, #C8A24B);
            transform-origin: bottom;
            animation: grow .9s cubic-bezier(.2,.8,.3,1) both, sway 4.5s ease-in-out infinite alternate;
        }
        .bars span:nth-child(1) { height: 34%; animation-delay: .45s, 1.4s; }
        .bars span:nth-child(2) { height: 58%; animation-delay: .55s, 1.7s; }
        .bars span:nth-child(3) { height: 44%; animation-delay: .65s, 2.0s; }
        .bars span:nth-child(4) { height: 78%; animation-delay: .75s, 2.3s; }
        .bars span:nth-child(5) { height: 62%; animation-delay: .85s, 2.6s; }
        .bars span:nth-child(6) { height: 100%; animation-delay: .95s, 2.9s; }
        @keyframes grow { from { transform: scaleY(0); } to { transform: scaleY(1); } }
        @keyframes sway { from { transform: scaleY(1); } to { transform: scaleY(.82); } }

        /* Bottom status row */
        .brand-status { position: relative; z-index: 1; display: flex; align-items: center; gap: .6rem; animation: fadeUp .7s ease .5s both; }
        .live-dot {
            width: 10px; height: 10px; border-radius: 50%; background: #58D68D; flex: 0 0 auto;
            box-shadow: 0 0 0 0 rgba(88,214,141,.55); animation: livePulse 2.2s ease-out infinite;
        }
        @keyframes livePulse { 0% { box-shadow: 0 0 0 0 rgba(88,214,141,.55); } 70% { box-shadow: 0 0 0 9px rgba(88,214,141,0); } 100% { box-shadow: 0 0 0 0 rgba(88,214,141,0); } }
        .brand-status .st-main { font-weight: 700; font-size: .88rem; }
        .brand-status .st-sub { font-size: .7rem; color: rgba(255,255,255,.6); }

        /* ---- Right: form panel ---- */
        .panel-form {
            flex: 1; background: #FFFFFF; padding: 3rem 3.2rem;
            display: flex; flex-direction: column; justify-content: center;
        }
        .greet-eyebrow {
            text-transform: uppercase; letter-spacing: .24em; font-size: .66rem;
            color: #B98E3C; font-weight: 700; margin-bottom: .4rem;
            animation: fadeUp .6s ease .25s both;
        }
        .welcome { font-family: "Lato", sans-serif; font-weight: 900; font-size: 1.95rem; color: #2A2230; margin: 0; animation: fadeUp .6s ease .33s both; }
        .welcome-sub { color: #837A8C; margin: .3rem 0 1.7rem; font-size: .95rem; animation: fadeUp .6s ease .41s both; }

        .fld { animation: fadeUp .6s ease both; }
        .fld:nth-of-type(1) { animation-delay: .5s; }
        .fld:nth-of-type(2) { animation-delay: .58s; }
        .form-label { font-weight: 600; font-size: .8rem; color: #2A2230; margin-bottom: .35rem; }
        .input-group-text { background: #fff; border-right: 0; color: #837A8C; }
        .form-control { border-left: 0; padding: .62rem .8rem; }
        .input-group:focus-within .input-group-text,
        .input-group:focus-within .form-control { border-color: #C8A24B; }
        .form-control:focus { box-shadow: 0 0 0 .18rem rgba(200,162,75,.18); }

        .fld-extra { animation: fadeUp .6s ease .66s both; }
        .btn-signin {
            position: relative; overflow: hidden;
            background: linear-gradient(135deg, #6A4E86, #3B2A4A);
            border: 0; color: #fff; font-weight: 700; padding: .72rem; border-radius: 11px;
            letter-spacing: .02em; box-shadow: 0 8px 22px rgba(59,42,74,.30);
            transition: transform .15s ease, box-shadow .2s ease, filter .15s ease;
            animation: fadeUp .6s ease .74s both;
        }
        .btn-signin::after { /* gold shimmer sweep */
            content: ""; position: absolute; inset: 0;
            background: linear-gradient(115deg, transparent 40%, rgba(255,255,255,.35) 50%, transparent 60%);
            transform: translateX(-140%);
            animation: shine 5.5s ease-in-out 2s infinite;
        }
        .btn-signin:hover { color: #fff; transform: translateY(-1px); filter: brightness(1.1); box-shadow: 0 12px 28px rgba(59,42,74,.38); }
        .btn-signin .bi { transition: transform .15s ease; }
        .btn-signin:hover .bi { transform: translateX(3px); }

        .login-foot { color: #9A92A4; font-size: .76rem; margin-top: 1.6rem; text-align: center; animation: fadeUp .6s ease .82s both; }

        @keyframes fadeUp { from { opacity: 0; transform: translateY(16px); } to { opacity: 1; transform: none; } }

        /* ============ Splash intro (plays before the login card) ============ */
        .splash {
            position: fixed; inset: 0; z-index: 60;
            display: flex; align-items: center; justify-content: center;
            background: radial-gradient(120% 120% at 20% 10%, #2C2138 0%, #1E1727 55%, #171220 100%);
            transition: opacity .55s ease, visibility .55s ease;
            /* safety net: even if JS fails, the splash hides itself */
            animation: splashSafety .6s ease 4s forwards;
        }
        @keyframes splashSafety { to { opacity: 0; visibility: hidden; } }
        .splash.done { opacity: 0; visibility: hidden; pointer-events: none; }
        .splash-inner { text-align: center; }
        .splash-emblem {
            width: 96px; height: 96px; margin: 0 auto 18px; border-radius: 24px;
            background: #fff; border: 2px solid #C8A24B; padding: 12px;
            box-shadow: 0 14px 40px rgba(0,0,0,.45), 0 0 0 8px rgba(210,172,87,.15);
            animation: splashPop .7s cubic-bezier(.2,1.4,.3,1) both, glowPulse 2.4s ease-in-out .7s infinite;
        }
        .splash-emblem img { width: 100%; height: 100%; object-fit: contain; }
        .splash-word {
            font-family: "Lato", sans-serif; font-weight: 900; font-size: 2rem; color: #fff;
            animation: fadeUp .6s ease .25s both;
        }
        .splash-word span { color: #D2AC57; }
        .splash-tag {
            text-transform: uppercase; letter-spacing: .3em; font-size: .64rem;
            color: #D2AC57; font-weight: 700; margin-top: .4rem;
            animation: fadeUp .55s ease .4s both;
        }
        .splash-bar {
            width: 180px; height: 3px; margin: 24px auto 0; border-radius: 4px;
            background: rgba(255,255,255,.14); overflow: hidden;
            animation: fadeUp .5s ease .5s both;
        }
        .splash-bar span {
            display: block; height: 100%; width: 100%; border-radius: 4px;
            background: linear-gradient(90deg, #C8A24B, #E7D4A2);
            transform: translateX(-101%);
            animation: barFill 1s ease .55s forwards;
        }
        @keyframes splashPop { from { opacity: 0; transform: scale(.55) rotate(-6deg); } to { opacity: 1; transform: none; } }
        @keyframes barFill { to { transform: translateX(0); } }

        /* Hold every card animation until the splash has finished */
        body:not(.ready) .login-card,
        body:not(.ready) .login-card * { animation-play-state: paused !important; }

        /* ============ Responsive ============ */
        @media (max-width: 860px) {
            body.exec-login { overflow: auto; }
            .login-card { flex-direction: column; min-height: 0; margin: 1.2rem 0; }
            .panel-brand { flex: none; padding: 2rem; }
            .brand-chart { display: none; }
            .brand-word { font-size: 1.7rem; }
            .panel-form { padding: 2rem 1.6rem; }
        }

        /* ============ Reduced motion ============ */
        @media (prefers-reduced-motion: reduce) {
            .aurora, .login-card, .brand-emblem, .brand-emblem::after, .brand-word, .brand-tag,
            .brand-chart, .bars span, .brand-status, .live-dot, .greet-eyebrow, .welcome,
            .welcome-sub, .fld, .fld-extra, .btn-signin, .btn-signin::after, .login-foot,
            .panel-brand::before { animation: none !important; }
            .splash { display: none !important; }
        }
    </style>
</head>
<body class="exec-login">

    {{-- Splash intro --}}
    <div class="splash" id="splash">
        <div class="splash-inner">
            <div class="splash-emblem"><img src="{{ asset('assets/img/d_logo.png') }}" alt="CEO Dashboard"></div>
            <div class="splash-word">CEO <span>Dashboard</span></div>
            <div class="splash-tag">Executive Portal</div>
            <div class="splash-bar"><span></span></div>
        </div>
    </div>

    {{-- Animated backdrop --}}
    <div class="aurora aurora-1" data-depth="14"></div>
    <div class="aurora aurora-2" data-depth="22"></div>
    <div class="aurora aurora-3" data-depth="30"></div>
    <div class="grid-overlay"></div>

    {{-- Card --}}
    <div class="login-card">

        {{-- Brand side --}}
        <aside class="panel-brand">
            <div class="brand-top">
                <div class="brand-emblem"><img src="{{ asset('assets/img/d_logo.png') }}" alt="CEO Dashboard"></div>
                <h1 class="brand-word">CEO <span>Dashboard</span></h1>
                <div class="brand-tag">Executive Portal</div>

                <div class="brand-chart">
                    <div class="bc-label">Performance · live</div>
                    <div class="bars"><span></span><span></span><span></span><span></span><span></span><span></span></div>
                </div>
            </div>

            <div class="brand-status">
                <span class="live-dot"></span>
                <div>
                    <div class="st-main">All systems live</div>
                    <div class="st-sub">BCF · BGR · RG Farms synced</div>
                </div>
            </div>
        </aside>

        {{-- Form side --}}
        <main class="panel-form">
            @php
                $h = now()->hour;
                $eyebrow = $h < 12 ? 'Good morning' : ($h < 18 ? 'Good afternoon' : 'Good evening');
            @endphp
            <div class="greet-eyebrow">{{ $eyebrow }}</div>
            <h2 class="welcome">Welcome back</h2>
            <p class="welcome-sub">Sign in to your executive dashboard</p>

            @if ($errors->any())
                <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="mb-3 fld">
                    <label class="form-label" for="email">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" class="form-control" placeholder="you@company.com" required autofocus>
                    </div>
                </div>
                <div class="mb-3 fld">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                    </div>
                </div>
                <div class="form-check mb-3 fld-extra">
                    <input class="form-check-input" type="checkbox" name="remember" id="remember">
                    <label class="form-check-label" for="remember">Keep me signed in</label>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-signin">Sign In <i class="bi bi-arrow-right-short"></i></button>
                </div>
            </form>

            <div class="login-foot">&copy; {{ date('Y') }} {{ config('app.name') }} · Secure executive access</div>
        </main>
    </div>

    <script>
        // Splash intro: hold the login card until the brand splash finishes,
        // then fade it out and let the card play its entrance.
        (function () {
            var splash = document.getElementById('splash');
            var go = function () {
                if (splash) splash.classList.add('done');
                document.body.classList.add('ready');
            };
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { go(); return; }
            setTimeout(go, 1650);
        })();

        // Gentle parallax: aurora orbs follow the mouse at different depths.
        (function () {
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
            var orbs = document.querySelectorAll('.aurora');
            document.addEventListener('mousemove', function (e) {
                var x = e.clientX / window.innerWidth - .5;
                var y = e.clientY / window.innerHeight - .5;
                orbs.forEach(function (o) {
                    var d = parseFloat(o.dataset.depth || 15);
                    o.style.translate = (x * d) + 'px ' + (y * d) + 'px';
                });
            }, { passive: true });
        })();
    </script>
</body>
</html>
