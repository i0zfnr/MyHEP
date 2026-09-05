<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale() ?? 'ms') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Ralat Sistem (500) | MyHEP - Politeknik Besut</title>
    <meta name="theme-color" content="#f7f2ed">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <style>
        :root {
            color-scheme: light dark;
            --safe-top: env(safe-area-inset-top, 0px);
            --safe-right: env(safe-area-inset-right, 0px);
            --safe-bottom: env(safe-area-inset-bottom, 0px);
            --safe-left: env(safe-area-inset-left, 0px);
            --canvas: #f7f2ed;
            --surface: rgba(255, 255, 255, .82);
            --ink: #211b1d;
            --muted: #756a6c;
            --line: rgba(92, 62, 65, .15);
            --accent: #b43d67;
            --accent-soft: #f9e2e8;
            --button-ink: #ffffff;
        }
        * { box-sizing: border-box; }
        html { min-height: 100%; background: var(--canvas); }
        body {
            min-width: 320px;
            min-height: 100vh;
            min-height: 100dvh;
            margin: 0;
            color: var(--ink);
            background: var(--canvas);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        }
        .error-page {
            min-height: 100vh;
            min-height: 100dvh;
            padding: calc(24px + var(--safe-top)) calc(20px + var(--safe-right)) calc(24px + var(--safe-bottom)) calc(20px + var(--safe-left));
            display: grid;
            place-items: center;
            overflow: hidden;
            position: relative;
        }
        .error-page::before, .error-page::after {
            content: "";
            position: fixed;
            width: 230px;
            height: 230px;
            border-radius: 50%;
            background: var(--accent-soft);
            filter: blur(42px);
            opacity: .66;
            pointer-events: none;
        }
        .error-page::before { top: -105px; right: -76px; }
        .error-page::after { bottom: -126px; left: -120px; opacity: .38; }
        .error-card {
            width: min(100%, 440px);
            position: relative;
            z-index: 1;
            padding: 28px;
            border: 1px solid var(--line);
            border-radius: 28px;
            background: var(--surface);
            box-shadow: 0 18px 48px rgba(59, 37, 39, .12), inset 0 1px rgba(255,255,255,.7);
        }
        .brand { display: flex; align-items: center; gap: 10px; color: var(--ink); }
        .brand-mark {
            width: 34px; height: 34px; display: grid; place-items: center;
            border-radius: 11px; color: var(--accent); background: var(--accent-soft);
            border: 1px solid color-mix(in srgb, var(--accent) 18%, transparent); font-weight: 800; font-size: 14px;
        }
        .brand strong { display: block; font-size: 15px; letter-spacing: -.02em; }
        .brand span { display: block; margin-top: 2px; color: var(--muted); font-size: 10px; font-weight: 700; letter-spacing: .08em; }
        .status-icon {
            width: 72px; height: 72px; margin: 44px 0 25px; display: grid; place-items: center;
            border: 1px solid color-mix(in srgb, var(--accent) 23%, transparent); border-radius: 24px;
            color: var(--accent); background: var(--accent-soft); box-shadow: 0 10px 22px rgba(180, 61, 103, .13);
        }
        .status-icon svg { width: 34px; height: 34px; }
        .eyebrow { color: var(--accent); font-size: 12px; font-weight: 800; letter-spacing: .09em; text-transform: uppercase; }
        h1 { max-width: 11ch; margin: 9px 0 12px; font-size: clamp(28px, 7vw, 36px); line-height: 1.08; letter-spacing: -.045em; }
        p { margin: 0; color: var(--muted); font-size: 15px; line-height: 1.58; }
        .status { margin-top: 18px; color: var(--muted); font-size: 12px; font-weight: 700; }
        .actions { display: grid; gap: 10px; margin-top: 28px; }
        .action {
            min-height: 48px; display: flex; align-items: center; justify-content: center; padding: 12px 16px;
            border-radius: 15px; font-size: 14px; font-weight: 750; text-decoration: none; cursor: pointer;
            transition: transform .18s ease, background-color .18s ease, border-color .18s ease;
        }
        .action:focus-visible { outline: 3px solid color-mix(in srgb, var(--accent) 45%, transparent); outline-offset: 3px; }
        .action:hover { transform: translateY(-1px); }
        .action-primary { border: 1px solid var(--accent); color: var(--button-ink); background: var(--accent); }
        .action-secondary { border: 1px solid var(--line); color: var(--ink); background: transparent; }
        @media (prefers-color-scheme: dark) {
            :root { --canvas: #151214; --surface: rgba(35, 29, 31, .9); --ink: #fff8f8; --muted: #c9babd; --line: rgba(255,255,255,.12); --accent: #f27aa2; --accent-soft: rgba(164, 57, 96, .24); --button-ink: #29131d; }
            .error-card { box-shadow: 0 18px 48px rgba(0,0,0,.34), inset 0 1px rgba(255,255,255,.08); }
            .error-page::before, .error-page::after { opacity: .25; }
        }
        @media (prefers-reduced-motion: reduce) { .action { transition: none; } }
    </style>
</head>
<body class="error-page">
    <main class="error-card" role="alert" aria-live="polite">
        <div class="brand">
            <div class="brand-mark" aria-hidden="true">M</div>
            <div><strong>MyHEP</strong><span>POLITEKNIK BESUT</span></div>
        </div>
        <div class="status-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div class="eyebrow">Ralat sementara</div>
        <h1>Halaman ini tidak dapat diselesaikan.</h1>
        <p>Maklumat anda belum disahkan disimpan. Sila cuba semula; jika masalah berterusan, kembali ke laman utama dan cuba lagi kemudian.</p>
        <div class="status">Ralat 500 &middot; Server Error</div>
        <div class="actions">
            <button type="button" class="action action-primary" onclick="window.location.reload();">Cuba Semula</button>
            <a href="{{ url('/') }}" class="action action-secondary">Kembali ke Laman Utama</a>
        </div>
    </main>
</body>
</html>
