<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Pos Kawalan • Kod QR Pergerakan Pelajar') }} | MyHEP</title>
    <meta name="theme-color" content="#0c0b0a">
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
            --panel-bg: rgba(22, 19, 16, 0.85);
            --panel-border: rgba(226, 209, 192, 0.16);
            --text-primary: #fdf8f3;
            --text-muted: #b8a899;
            --text-sub: #7f7165;
            --gold-start: #f3d49b;
            --gold-end: #c48e42;
            --green-active: #10b981;
            --green-bg: rgba(16, 185, 129, 0.14);
            --green-border: rgba(16, 185, 129, 0.35);
            --red-inactive: #ef4444;
            --red-bg: rgba(239, 68, 68, 0.14);
            --red-border: rgba(239, 68, 68, 0.35);
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            overflow-x: hidden;
            position: relative;
        }

        /* Ambient Glows */
        .ambient-glow-1 {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(196, 142, 66, 0.14) 0%, transparent 70%);
            top: -120px;
            left: 50%;
            transform: translateX(-50%);
            filter: blur(50px);
            pointer-events: none;
            z-index: 0;
        }

        .ambient-glow-2 {
            position: absolute;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.08) 0%, transparent 70%);
            bottom: -80px;
            right: 15%;
            filter: blur(60px);
            pointer-events: none;
            z-index: 0;
        }

        /* Main Kiosk Card */
        .kiosk-card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 620px;
            background: var(--panel-bg);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid var(--panel-border);
            border-radius: 28px;
            padding: 36px 32px;
            box-shadow: 
                0 32px 64px rgba(0, 0, 0, 0.58),
                inset 0 1px 0 rgba(255, 255, 255, 0.08);
            text-align: center;
        }

        @media (max-width: 540px) {
            .kiosk-card {
                padding: 28px 20px;
                border-radius: 22px;
            }
        }

        /* Header */
        .kiosk-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--panel-border);
            margin-bottom: 24px;
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-primary);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            background: var(--green-bg);
            border: 1px solid var(--green-border);
            color: var(--green-active);
        }

        .status-pill.inactive {
            background: var(--red-bg);
            border-color: var(--red-border);
            color: var(--red-inactive);
        }

        .status-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: currentColor;
            box-shadow: 0 0 8px currentColor;
            animation: pulseDot 2s ease-in-out infinite;
        }

        @keyframes pulseDot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.3; transform: scale(0.8); }
        }

        /* Checkpoint Title */
        .checkpoint-title {
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--gold-start);
            margin-bottom: 6px;
        }

        .kiosk-title {
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.02em;
            line-height: 1.25;
            margin-bottom: 24px;
            background: linear-gradient(135deg, #ffffff 40%, var(--text-muted) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* QR Frame Box */
        .qr-frame {
            position: relative;
            background: #ffffff;
            border-radius: 24px;
            padding: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 
                0 20px 48px rgba(0, 0, 0, 0.42),
                0 0 0 1px rgba(255, 255, 255, 0.15);
            margin-bottom: 24px;
            width: min(80vw, 360px);
            height: min(80vw, 360px);
            max-width: 100%;
        }

        .qr-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 12px;
            transition: opacity 0.25s ease;
        }

        .qr-image.refreshing {
            opacity: 0.4;
        }

        /* Rolling Countdown Ring Bar */
        .countdown-bar {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 10px 18px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--panel-border);
            border-radius: 14px;
            margin-bottom: 20px;
        }

        .timer-svg {
            width: 28px;
            height: 28px;
            transform: rotate(-90deg);
        }

        .timer-track {
            fill: none;
            stroke: rgba(255, 255, 255, 0.12);
            stroke-width: 3;
        }

        .timer-circle {
            fill: none;
            stroke: var(--gold-start);
            stroke-width: 3;
            stroke-linecap: round;
            stroke-dasharray: 75.398;
            stroke-dashoffset: 0;
            transition: stroke-dashoffset 0.95s linear, stroke 0.3s ease;
        }

        .countdown-text {
            font-size: 13.5px;
            font-weight: 600;
            color: var(--text-muted);
        }

        .countdown-number {
            font-weight: 800;
            color: var(--gold-start);
            font-feature-settings: "tnum";
            font-variant-numeric: tabular-nums;
        }

        /* Instruction & Security Badge */
        .instruction-box {
            font-size: 13px;
            line-height: 1.6;
            color: var(--text-sub);
            margin-bottom: 18px;
        }

        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text-sub);
            padding: 5px 12px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(226, 209, 192, 0.08);
            border-radius: 999px;
        }

        .security-badge svg {
            width: 13px;
            height: 13px;
            color: var(--gold-start);
        }
    </style>
</head>
<body>
    <div class="ambient-glow-1"></div>
    <div class="ambient-glow-2"></div>

    <main class="kiosk-card">
        <!-- Header -->
        <div class="kiosk-header">
            <div class="brand-badge">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9 9 0 100-18 9 9 0 000 18z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3"/>
                </svg>
                <span>MyHEP &bull; POLITEKNIK BESUT</span>
            </div>
            <div class="status-pill {{ $checkpoint && $checkpoint->is_active ? '' : 'inactive' }}" id="statusPill">
                <span class="status-dot"></span>
                <span id="statusLabel">{{ $checkpoint && $checkpoint->is_active ? __('Aktif') : __('Tidak Aktif') }}</span>
            </div>
        </div>

        <!-- Checkpoint Title -->
        <div class="checkpoint-title" id="checkpointTitle">{{ $checkpoint?->name ?? __('Pos Kawalan Utama') }}</div>
        <h1 class="kiosk-title">{{ __('Imbas QR Pergerakan Pelajar') }}</h1>

        <!-- QR Display Container -->
        <div class="qr-frame">
            <img id="qrImage" class="qr-image" alt="Dynamic Movement QR Code" src="https://api.qrserver.com/v1/create-qr-code/?size=600x600&data={{ urlencode($scanUrl ?? '') }}">
        </div>

        <!-- 30-Second Rolling Countdown -->
        <div class="countdown-bar">
            <svg class="timer-svg" viewBox="0 0 30 30">
                <circle class="timer-track" cx="15" cy="15" r="12" />
                <circle class="timer-circle" id="timerRing" cx="15" cy="15" r="12" />
            </svg>
            <div class="countdown-text">
                Kod QR dijana semula dalam <span class="countdown-number" id="countdownNumber">30</span>s
            </div>
        </div>

        <p class="instruction-box">
            Sila imbas kod QR di atas menggunakan kamera telefon pintar untuk merekodkan pergerakan keluar / masuk kampus atau asrama.
        </p>

        <div class="security-badge">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            <span>Rolling HMAC-SHA256 Token &bull; Tangkap Layar Disekat</span>
        </div>
    </main>

    <script>
    (function() {
        const ROTATION_SECONDS = 30;
        let currentRemaining = ROTATION_SECONDS;
        const statusUrl = @json(route('admin.movements.qr.status'));
        const qrImage = document.getElementById('qrImage');
        const countdownNumber = document.getElementById('countdownNumber');
        const timerRing = document.getElementById('timerRing');
        const statusPill = document.getElementById('statusPill');
        const statusLabel = document.getElementById('statusLabel');
        const checkpointTitle = document.getElementById('checkpointTitle');
        const circumference = 2 * Math.PI * 12; // ~75.398

        let currentScanUrl = @json($scanUrl ?? '');

        function updateTimerDisplay(remaining) {
            if (countdownNumber) countdownNumber.textContent = remaining;
            if (timerRing) {
                const fraction = Math.max(0, remaining / ROTATION_SECONDS);
                const offset = circumference * (1 - fraction);
                timerRing.style.strokeDashoffset = offset;
                timerRing.style.stroke = remaining <= 5 ? '#f87171' : 'var(--gold-start)';
            }
        }

        async function fetchFreshQr() {
            try {
                if (qrImage) qrImage.classList.add('refreshing');
                const res = await fetch(statusUrl, {
                    headers: { 'Accept': 'application/json' },
                    cache: 'no-store'
                });
                if (!res.ok) throw new Error('Status fetch failed');
                const data = await res.json();

                if (data.checkpoint && checkpointTitle) {
                    checkpointTitle.textContent = data.checkpoint.name || 'Pos Kawalan';
                }

                if (statusPill && statusLabel) {
                    if (data.checkpoint && data.checkpoint.is_valid) {
                        statusPill.className = 'status-pill';
                        statusLabel.textContent = @json(__('Aktif'));
                    } else {
                        statusPill.className = 'status-pill inactive';
                        statusLabel.textContent = @json(__('Tidak Aktif'));
                    }
                }

                if (data.scan_url && data.scan_url !== currentScanUrl) {
                    currentScanUrl = data.scan_url;
                    if (qrImage) {
                        qrImage.src = 'https://api.qrserver.com/v1/create-qr-code/?size=600x600&data=' + encodeURIComponent(data.scan_url);
                    }
                }

                currentRemaining = Number(data.expires_in) || ROTATION_SECONDS;
                updateTimerDisplay(currentRemaining);
            } catch (err) {
                console.warn('QR refresh error:', err);
            } finally {
                if (qrImage) {
                    setTimeout(() => qrImage.classList.remove('refreshing'), 300);
                }
            }
        }

        // Interval timer countdown
        setInterval(function() {
            currentRemaining--;
            if (currentRemaining <= 0) {
                currentRemaining = ROTATION_SECONDS;
                fetchFreshQr();
            } else {
                updateTimerDisplay(currentRemaining);
            }
        }, 1000);

        // Visibility listener
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                fetchFreshQr();
            }
        });

        updateTimerDisplay(ROTATION_SECONDS);
    })();
    </script>
</body>
</html>
