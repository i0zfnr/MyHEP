<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale() ?? 'ms') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Halaman Tidak Ditemui (404) | MyHEP - Politeknik Besut</title>
    <meta name="theme-color" content="#171412">
    <link rel="preconnect" href="https://fonts.googleapis.com">
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
            --gold-start: #f3d49b;
            --gold-end: #c48e42;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-base); color: var(--text-main); min-height: 100vh; min-height: 100dvh;
            display: flex; align-items: center; justify-content: center; padding: 24px 16px;
        }
        .card {
            width: 100%; max-width: 540px; background: var(--surface-glass); backdrop-filter: blur(28px);
            border: 1px solid var(--surface-border); border-radius: 24px; padding: 44px 36px; text-align: center;
            box-shadow: 0 32px 64px rgba(0, 0, 0, 0.56), inset 0 1px 0 var(--surface-highlight);
        }
        .brand-badge {
            display: inline-flex; align-items: center; gap: 8px; padding: 6px 14px;
            background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(226, 209, 192, 0.12);
            border-radius: 999px; margin-bottom: 24px; font-size: 11.5px; font-weight: 700;
            letter-spacing: 0.08em; text-transform: uppercase; color: var(--text-muted);
        }
        .code-display {
            font-size: 64px; font-weight: 800; line-height: 1; margin-bottom: 12px;
            background: linear-gradient(135deg, var(--gold-start), var(--gold-end));
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .main-title { font-size: 20px; font-weight: 700; margin-bottom: 12px; color: var(--text-main); }
        .desc { font-size: 14px; line-height: 1.6; color: var(--text-muted); margin-bottom: 28px; }
        .btn-home {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 13px 24px; background: linear-gradient(135deg, var(--gold-start) 0%, var(--gold-end) 100%);
            color: #17120c; font-weight: 700; font-size: 14px; border-radius: 12px; text-decoration: none;
            box-shadow: 0 8px 20px rgba(196, 142, 66, 0.28);
        }
    </style>
</head>
<body>
    <main class="card">
        <div class="brand-badge">POLITEKNIK BESUT &bull; MyHEP</div>
        <div class="code-display">404</div>
        <h1 class="main-title">Halaman Tidak Ditemui</h1>
        <p class="desc">Pautan yang anda akses mungkin telah dipadam, ditukar alamat, atau tidak wujud dalam sistem.</p>
        <a href="/" class="btn-home">Kembali ke Laman Utama</a>
    </main>
</body>
</html>
