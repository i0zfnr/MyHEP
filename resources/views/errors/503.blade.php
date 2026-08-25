<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale() ?? 'ms') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Penyelenggaraan Sistem | MyHEP - Politeknik Besut</title>
    <meta name="theme-color" content="#171412">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

</head>
<body class="error-page">

    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <main class="maintenance-card" role="alert" aria-live="polite">

        <!-- Brand Header -->
        <div class="brand-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/>
            </svg>
            <span>POLITEKNIK BESUT &bull; MyHEP</span>
        </div>

        <!-- Animated Maintenance Vector Icon -->
        <div class="icon-wrapper">
            <div class="icon-pulse"></div>
            <div class="icon-box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </div>
        </div>

        <!-- Headings -->
        <h1 class="main-title">Penyelenggaraan Sistem Berjadual</h1>

        <p class="description-bm">
            Sistem <strong>MyHEP</strong> sedang menjalani kerja-kerja penyelenggaraan dan penaiktarafan berkala bagi meningkatkan kestabilan, keselamatan, dan kelancaran perkhidmatan.
        </p>

        <p class="description-en">
            MyHEP is currently undergoing scheduled system maintenance to enhance platform reliability and performance. We will be back online shortly.
        </p>

        <!-- Status Indicator -->
        <div class="status-pill">
            <span class="status-dot"></span>
            <span class="status-text">Status: Kerja Penyelenggaraan Sedang Berjalan</span>
        </div>

        <!-- Action & Auto Refresh -->
        <div class="action-group">
            <button type="button" class="btn-refresh" onclick="window.location.reload();">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/>
                </svg>
                <span>Muat Semula Halaman / Refresh</span>
            </button>
            <div class="auto-refresh-timer">
                Halaman akan dimuat semula secara automatik dalam <strong id="countdownText" style="color: var(--gold-start);">30</strong> saat.
            </div>
        </div>

        <!-- Footer Info -->
        <footer class="maintenance-footer">
            <div class="footer-org">Jabatan Hal Ehwal Pelajar (JHEP) &bull; Politeknik Besut</div>
            <div class="footer-contact">
                Sebarang pertanyaan kecemasan: <a href="mailto:support@polibesut.edu.my">support@polibesut.edu.my</a>
            </div>
        </footer>

    </main>

    <script>
        (function() {
            let seconds = 30;
            const el = document.getElementById('countdownText');
            const interval = setInterval(function() {
                seconds--;
                if (el) el.textContent = seconds;
                if (seconds <= 0) {
                    clearInterval(interval);
                    window.location.reload();
                }
            }, 1000);
        })();
    </script>
</body>
</html>
