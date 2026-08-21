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
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

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
            --status-bg: rgba(245, 158, 11, 0.12);
            --status-border: rgba(245, 158, 11, 0.32);
            --status-text: #fbbf24;
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

        /* Ambient Glow Background */
        .ambient-glow-1 {
            position: absolute;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(196, 142, 66, 0.18) 0%, rgba(196, 142, 66, 0) 70%);
            top: -120px;
            left: 50%;
            transform: translateX(-50%);
            filter: blur(40px);
            pointer-events: none;
            z-index: 0;
        }

        .ambient-glow-2 {
            position: absolute;
            width: 380px;
            height: 380px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(234, 179, 8, 0.10) 0%, rgba(234, 179, 8, 0) 70%);
            bottom: -100px;
            right: 10%;
            filter: blur(50px);
            pointer-events: none;
            z-index: 0;
        }

        /* Card Container */
        .maintenance-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 580px;
            background: var(--surface-glass);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid var(--surface-border);
            border-radius: 24px;
            padding: 44px 36px;
            text-align: center;
            box-shadow: 
                0 32px 64px rgba(0, 0, 0, 0.56),
                inset 0 1px 0 var(--surface-highlight);
        }

        @media (max-width: 480px) {
            .maintenance-card {
                padding: 32px 20px;
                border-radius: 20px;
            }
        }

        /* Brand Header */
        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(226, 209, 192, 0.12);
            border-radius: 999px;
            margin-bottom: 24px;
        }

        .brand-badge span {
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--text-muted);
        }

        /* Animated Icon */
        .icon-wrapper {
            position: relative;
            width: 88px;
            height: 88px;
            margin: 0 auto 28px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .icon-pulse {
            position: absolute;
            inset: 0;
            border-radius: 24px;
            background: linear-gradient(135deg, rgba(243, 212, 155, 0.18), rgba(196, 142, 66, 0.06));
            border: 1px solid rgba(243, 212, 155, 0.28);
            animation: pulseRing 3s ease-in-out infinite;
        }

        .icon-box {
            position: relative;
            width: 72px;
            height: 72px;
            border-radius: 20px;
            background: linear-gradient(135deg, #241d17 0%, #15120f 100%);
            border: 1px solid rgba(243, 212, 155, 0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--gold-start);
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.45);
        }

        .icon-box svg {
            width: 36px;
            height: 36px;
            animation: rotateSlow 20s linear infinite;
        }

        @keyframes pulseRing {
            0%, 100% {
                transform: scale(1);
                opacity: 0.6;
            }
            50% {
                transform: scale(1.15);
                opacity: 0.15;
            }
        }

        @keyframes rotateSlow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        /* Typography */
        .main-title {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.25;
            margin-bottom: 12px;
            background: linear-gradient(135deg, #ffffff 30%, var(--text-muted) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        @media (max-width: 480px) {
            .main-title {
                font-size: 22px;
            }
        }

        .description-bm {
            font-size: 14.5px;
            line-height: 1.65;
            color: var(--text-muted);
            margin-bottom: 14px;
        }

        .description-en {
            font-size: 13px;
            line-height: 1.6;
            color: var(--text-sub);
            margin-bottom: 26px;
            font-style: italic;
        }

        /* Status Pill */
        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 10px 18px;
            background: var(--status-bg);
            border: 1px solid var(--status-border);
            border-radius: 12px;
            margin-bottom: 28px;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--status-text);
            box-shadow: 0 0 10px var(--status-text);
            animation: blinkDot 1.6s ease-in-out infinite;
        }

        @keyframes blinkDot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.3; transform: scale(0.75); }
        }

        .status-text {
            font-size: 13px;
            font-weight: 600;
            color: var(--status-text);
            letter-spacing: 0.01em;
        }

        /* Action Buttons */
        .action-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 24px;
        }

        .btn-refresh {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            width: 100%;
            padding: 13px 20px;
            background: linear-gradient(135deg, var(--gold-start) 0%, var(--gold-end) 100%);
            color: #17120c;
            font-weight: 700;
            font-size: 14.5px;
            border-radius: 12px;
            border: none;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 8px 20px rgba(196, 142, 66, 0.28);
        }

        .btn-refresh:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 26px rgba(196, 142, 66, 0.38);
        }

        .btn-refresh:active {
            transform: translateY(0);
        }

        .btn-refresh svg {
            width: 18px;
            height: 18px;
            transition: transform 0.4s ease;
        }

        .btn-refresh:hover svg {
            transform: rotate(180deg);
        }

        .auto-refresh-timer {
            font-size: 12px;
            color: var(--text-sub);
        }

        /* Footer */
        .maintenance-footer {
            border-top: 1px solid rgba(226, 209, 192, 0.10);
            padding-top: 18px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .footer-org {
            font-size: 12.5px;
            font-weight: 600;
            color: var(--text-muted);
        }

        .footer-contact {
            font-size: 11.5px;
            color: var(--text-sub);
        }

        .footer-contact a {
            color: var(--gold-start);
            text-decoration: none;
        }

        .footer-contact a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

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
