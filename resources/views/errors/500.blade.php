<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale() ?? 'ms') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ralat Sistem (500) | MyHEP - Politeknik Besut</title>
    <meta name="theme-color" content="#171412">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg-base: #0c0b0a;
            --surface-glass: rgba(26, 23, 20, 0.72);
            --surface-border: rgba(226, 209, 192, 0.16);
            --surface-highlight: rgba(255, 255, 255, 0.06);
            --text-main: #fdf8f3;
            --text-muted: #b8a899;
            --text-sub: #7f7165;
            --gold-start: #f3d49b;
            --gold-end: #c48e42;
            --error-bg: rgba(239, 68, 68, 0.12);
            --error-border: rgba(239, 68, 68, 0.32);
            --error-text: #f87171;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-base);
            color: var(--text-main);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
            overflow-x: hidden;
        }
        .ambient-glow-1 {
            position: absolute;
            width: 480px; height: 480px; border-radius: 50%;
            background: radial-gradient(circle, rgba(239, 68, 68, 0.14) 0%, rgba(239, 68, 68, 0) 70%);
            top: -120px; left: 50%; transform: translateX(-50%); filter: blur(40px); pointer-events: none;
        }
        .card {
            position: relative; z-index: 1; width: 100%; max-width: 560px;
            background: var(--surface-glass); backdrop-filter: blur(28px); -webkit-backdrop-filter: blur(28px);
            border: 1px solid var(--surface-border); border-radius: 24px; padding: 44px 36px; text-align: center;
            box-shadow: 0 32px 64px rgba(0, 0, 0, 0.56), inset 0 1px 0 var(--surface-highlight);
        }
        @media (max-width: 480px) { .card { padding: 32px 20px; border-radius: 20px; } }
        .brand-badge {
            display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px;
            background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(226, 209, 192, 0.12);
            border-radius: 999px; margin-bottom: 24px; font-size: 11.5px; font-weight: 700;
            letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted);
        }
        .icon-box {
            width: 72px; height: 72px; border-radius: 20px;
            background: linear-gradient(135deg, #241414 0%, #150f0f 100%);
            border: 1px solid rgba(239, 68, 68, 0.35); display: flex; align-items: center;
            justify-content: center; color: var(--error-text); margin: 0 auto 24px;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.45);
        }
        .icon-box svg { width: 36px; height: 36px; }
        .main-title {
            font-size: 24px; font-weight: 800; letter-spacing: -0.02em; margin-bottom: 12px;
            background: linear-gradient(135deg, #ffffff 30%, var(--text-muted) 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .desc { font-size: 14px; line-height: 1.65; color: var(--text-muted); margin-bottom: 24px; }
        .status-pill {
            display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px;
            background: var(--error-bg); border: 1px solid var(--error-border); border-radius: 12px;
            margin-bottom: 24px; font-size: 13px; font-weight: 600; color: var(--error-text);
        }
        .btn-group { display: flex; flex-direction: column; gap: 10px; }
        .btn-primary {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 13px 20px; background: linear-gradient(135deg, var(--gold-start) 0%, var(--gold-end) 100%);
            color: #17120c; font-weight: 700; font-size: 14px; border-radius: 12px; text-decoration: none;
            border: none; cursor: pointer; transition: all 0.2s; box-shadow: 0 8px 20px rgba(196, 142, 66, 0.28);
        }
        .btn-primary:hover { transform: translateY(-1px); }
        .btn-secondary {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 12px 20px; background: rgba(255, 255, 255, 0.06); border: 1px solid var(--surface-border);
            color: var(--text-main); font-weight: 600; font-size: 14px; border-radius: 12px; text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="ambient-glow-1"></div>
    <main class="card">
        <div class="brand-badge">POLITEKNIK BESUT &bull; MyHEP</div>
        <div class="icon-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <h1 class="main-title">Ralat Pelayan (500)</h1>
        <div class="status-pill">500 &bull; Server Error</div>
        <p class="desc">Sistem mengalami ralat dalaman sementara. Sila cuba muat semula halaman ini atau kembali ke laman utama.</p>
        <div class="btn-group">
            <button type="button" class="btn-primary" onclick="window.location.reload();">Muat Semula Halaman</button>
            <a href="/" class="btn-secondary">Kembali ke Laman Utama</a>
        </div>
    </main>
</body>
</html>
