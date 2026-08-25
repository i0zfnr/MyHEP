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

</head>
<body class="error-page">
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
