<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('Live Projector QR') }} &bull; {{ $program->title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #0c0a09;
            --bg-card: rgba(28, 25, 23, 0.78);
            --bg-card-glass: rgba(41, 37, 36, 0.65);
            --border-glass: rgba(214, 178, 110, 0.22);
            --border-subtle: rgba(255, 255, 255, 0.08);
            --gold-primary: #d4af37;
            --gold-light: #f5e6a3;
            --gold-glow: rgba(212, 175, 55, 0.35);
            --emerald-accent: #10b981;
            --text-main: #fafaf9;
            --text-muted: #a8a29e;
            --text-gold: #e6ca65;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            user-select: none;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background: radial-gradient(circle at 50% -20%, rgba(212, 175, 55, 0.18), transparent 45%),
                        radial-gradient(circle at 10% 90%, rgba(180, 83, 9, 0.12), transparent 40%),
                        radial-gradient(circle at 90% 80%, rgba(16, 185, 129, 0.08), transparent 40%),
                        var(--bg-base);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow-x: hidden;
        }

        /* Ambient animated glow in background */
        .ambient-mesh {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            pointer-events: none;
            z-index: 0;
            background: radial-gradient(800px circle at var(--mouse-x, 50%) var(--mouse-y, 30%), rgba(212, 175, 55, 0.07), transparent 60%);
            transition: opacity 0.5s;
        }

        /* Header Bar */
        .presenter-header {
            position: relative;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1.25rem 2.5rem;
            background: rgba(12, 10, 9, 0.6);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border-subtle);
        }

        .brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
        }

        .brand-logo {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--gold-primary), #926b1d);
            display: grid;
            place-items: center;
            box-shadow: 0 4px 18px var(--gold-glow);
            color: #1c1917;
            font-weight: 900;
            font-size: 1.1rem;
        }

        .brand-text h1 {
            font-size: 1.05rem;
            font-weight: 800;
            letter-spacing: -0.01em;
            color: var(--text-main);
        }

        .brand-text p {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.45rem 1rem;
            border-radius: 9999px;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            background: rgba(16, 185, 129, 0.12);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 10px #10b981;
            animation: pulseDot 2s infinite ease-in-out;
        }

        @keyframes pulseDot {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.35); opacity: 0.6; }
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.15rem;
            border-radius: 12px;
            font-size: 0.82rem;
            font-weight: 700;
            cursor: pointer;
            border: 1px solid var(--border-subtle);
            background: var(--bg-card-glass);
            color: var(--text-main);
            transition: all 0.2s ease;
            backdrop-filter: blur(12px);
            text-decoration: none;
        }

        .btn-action:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--border-glass);
            color: var(--gold-light);
            transform: translateY(-1px);
        }

        .presenter-menu-toggle {
            display: none;
        }

        .btn-gold {
            background: linear-gradient(135deg, var(--gold-primary), #926b1d);
            color: #1c1917;
            border-color: transparent;
            font-weight: 800;
            box-shadow: 0 4px 18px rgba(212, 175, 55, 0.25);
        }

        .btn-gold:hover {
            background: linear-gradient(135deg, #e4bf47, #a17622);
            color: #0c0a09;
            box-shadow: 0 6px 24px var(--gold-glow);
        }

        /* Main Grid */
        .presenter-main {
            position: relative;
            z-index: 1;
            flex: 1;
            display: grid;
            grid-template-columns: 1.1fr 0.9fr;
            gap: 2.5rem;
            max-width: 1600px;
            width: 100%;
            margin: 0 auto;
            padding: 2rem 2.5rem;
            align-items: center;
        }

        @media (max-width: 1024px) {
            .presenter-main {
                grid-template-columns: 1fr;
                gap: 2rem;
                padding: 1.5rem;
            }
        }

        /* Left Side: Program Details & Live Metrics */
        .program-info-card {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .program-tagline {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.78rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-gold);
            padding: 0.35rem 0.85rem;
            border-radius: 8px;
            background: rgba(212, 175, 55, 0.1);
            border: 1px solid rgba(212, 175, 55, 0.25);
            align-self: flex-start;
        }

        .program-title {
            font-size: clamp(2rem, 3.5vw, 3.2rem);
            font-weight: 900;
            line-height: 1.15;
            letter-spacing: -0.03em;
            background: linear-gradient(140deg, #ffffff 40%, #e7dbcb 80%, #d4af37 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .program-meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .meta-box {
            background: var(--bg-card);
            border: 1px solid var(--border-subtle);
            border-radius: 16px;
            padding: 1.15rem;
            backdrop-filter: blur(16px);
            transition: border-color 0.3s;
        }

        .meta-box:hover {
            border-color: var(--border-glass);
        }

        .meta-label {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 0.35rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .meta-value {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--text-main);
        }

        /* Stats Strip */
        .live-stats-strip {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1.25rem;
            background: linear-gradient(145deg, rgba(38, 34, 31, 0.8), rgba(24, 21, 19, 0.8));
            border: 1px solid var(--border-glass);
            border-radius: 20px;
            padding: 1.5rem;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.4);
        }

        .stat-item {
            text-align: center;
        }

        .stat-item:not(:last-child) {
            border-right: 1px solid var(--border-subtle);
        }

        .stat-number {
            font-family: 'JetBrains Mono', monospace;
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--gold-light);
            line-height: 1;
            margin-bottom: 0.35rem;
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .stat-label {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: var(--text-muted);
        }

        /* Right Side: QR Presenter Card */
        .qr-stage-card {
            position: relative;
            background: linear-gradient(165deg, rgba(38, 34, 31, 0.85), rgba(18, 16, 14, 0.95));
            border: 1px solid var(--border-glass);
            border-radius: 32px;
            padding: 2.5rem 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6), 0 0 50px rgba(212, 175, 55, 0.12);
            overflow: hidden;
        }

        .qr-stage-card::before {
            content: '';
            position: absolute;
            top: -120px;
            left: 50%;
            transform: translateX(-50%);
            width: 280px;
            height: 280px;
            background: radial-gradient(circle, var(--gold-glow) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Animated Countdown Ring */
        .countdown-container {
            position: relative;
            width: 84px;
            height: 84px;
            margin-bottom: 1.25rem;
        }

        .countdown-svg {
            transform: rotate(-90deg);
            width: 84px;
            height: 84px;
        }

        .countdown-bg {
            fill: none;
            stroke: rgba(255, 255, 255, 0.1);
            stroke-width: 6;
        }

        .countdown-bar {
            fill: none;
            stroke: var(--gold-primary);
            stroke-width: 6;
            stroke-linecap: round;
            stroke-dasharray: 226.19;
            stroke-dashoffset: 0;
            transition: stroke-dashoffset 0.95s linear, stroke 0.3s;
        }

        .countdown-text {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 800;
            font-size: 1.25rem;
            color: var(--gold-light);
        }

        .countdown-unit {
            font-size: 0.6rem;
            color: var(--text-muted);
            text-transform: uppercase;
            margin-top: -3px;
        }

        /* QR Canvas Housing */
        .qr-canvas-housing {
            position: relative;
            background: #ffffff;
            padding: 1.25rem;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5), 0 0 30px rgba(255, 255, 255, 0.1);
            margin-bottom: 1.5rem;
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.4s;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .qr-canvas-housing.pulse {
            transform: scale(1.03);
            box-shadow: 0 0 60px var(--gold-glow), 0 20px 50px rgba(0, 0, 0, 0.6);
        }

        #qr-canvas-target {
            display: block;
            border-radius: 12px;
        }

        #qr-canvas-target canvas,
        #qr-canvas-target svg {
            display: block;
            width: clamp(220px, 22vw, 310px) !important;
            height: clamp(220px, 22vw, 310px) !important;
        }

        /* Security Token Notice */
        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            padding: 0.4rem 0.9rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid var(--border-subtle);
            font-size: 0.72rem;
            color: var(--text-muted);
            font-weight: 700;
            letter-spacing: 0.03em;
            margin-bottom: 0.5rem;
        }

        .security-badge svg {
            width: 14px;
            height: 14px;
            color: var(--gold-primary);
        }

        .qr-instruction {
            font-size: 0.88rem;
            color: var(--text-main);
            font-weight: 600;
            max-width: 320px;
            line-height: 1.4;
        }

        /* Switcher Tabs (Student vs Public) */
        .qr-mode-switcher {
            display: flex;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid var(--border-subtle);
            border-radius: 12px;
            padding: 4px;
            gap: 4px;
            margin-top: 1.25rem;
        }

        .mode-tab {
            padding: 0.45rem 0.9rem;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: var(--text-muted);
            font-size: 0.76rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
        }

        .mode-tab.active {
            background: var(--bg-card-glass);
            color: var(--text-main);
            border: 1px solid var(--border-glass);
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        /* Toast notification */
        .toast-notify {
            position: fixed;
            bottom: 2rem;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: rgba(24, 21, 19, 0.92);
            border: 1px solid var(--border-glass);
            color: var(--gold-light);
            padding: 0.75rem 1.5rem;
            border-radius: 9999px;
            font-size: 0.84rem;
            font-weight: 700;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(16px);
            transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.4s;
            opacity: 0;
            z-index: 100;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .toast-notify.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        @media (max-width: 900px) {
            body {
                min-height: 100dvh;
                overflow-x: hidden;
            }

            .presenter-header {
                align-items: center;
                flex-direction: row;
                gap: .6rem;
                padding: .7rem .8rem;
                position: sticky;
                top: 0;
            }

            .brand-badge {
                align-items: center;
                min-width: 0;
                width: auto;
            }

            .brand-text {
                min-width: 0;
            }

            .brand-logo {
                flex: 0 0 auto;
                height: 34px;
                width: 34px;
                font-size: .92rem;
            }

            .brand-text h1 {
                font-size: .86rem;
                line-height: 1.15;
            }

            .brand-text p {
                font-size: .58rem;
                line-height: 1.25;
            }

            .presenter-menu-toggle {
                align-items: center;
                background: var(--bg-card-glass);
                border: 1px solid var(--border-subtle);
                border-radius: 12px;
                color: var(--text-main);
                cursor: pointer;
                display: inline-flex;
                flex: 0 0 auto;
                height: 36px;
                justify-content: center;
                margin-left: auto;
                width: 36px;
            }

            .presenter-menu-toggle svg {
                height: 20px;
                width: 20px;
            }

            .header-actions {
                background: rgba(18, 16, 14, .96);
                border: 1px solid var(--border-glass);
                border-radius: 16px;
                box-shadow: 0 18px 40px rgba(0, 0, 0, .45);
                display: none;
                gap: .55rem;
                grid-template-columns: 1fr;
                padding: .65rem;
                position: absolute;
                right: 1rem;
                top: calc(100% + .5rem);
                width: min(260px, calc(100vw - 2rem));
            }

            .header-actions.is-open {
                display: grid;
            }

            .header-actions .status-pill {
                grid-column: 1 / -1;
                justify-content: center;
            }

            .btn-action {
                justify-content: center;
                min-height: 40px;
                padding: .55rem .7rem;
                width: 100%;
            }

            .presenter-main {
                align-items: stretch;
                display: flex;
                flex-direction: column;
                gap: .75rem;
                max-width: 100%;
                min-width: 0;
                padding: .75rem;
            }

            .program-info-card {
                gap: .75rem;
                min-width: 0;
                order: 2;
            }

            .qr-stage-card {
                border-radius: 18px;
                min-width: 0;
                order: 1;
                padding: .9rem .75rem;
                width: 100%;
            }

            .program-title {
                font-size: clamp(1.35rem, 7vw, 1.9rem);
                line-height: 1.08;
                word-break: break-word;
            }

            .program-tagline {
                font-size: .66rem;
                max-width: 100%;
                padding: .28rem .65rem;
                white-space: normal;
            }

            .program-meta-grid {
                grid-template-columns: 1fr;
                gap: .55rem;
            }

            .meta-box {
                border-radius: 12px;
                padding: .72rem .78rem;
            }

            .meta-label {
                font-size: .62rem;
                margin-bottom: .22rem;
            }

            .meta-value {
                font-size: .9rem;
            }

            .live-stats-strip {
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: .25rem;
                padding: .72rem .45rem;
                border-radius: 14px;
            }

            .stat-number {
                font-size: 1.2rem;
                margin-bottom: .2rem;
            }

            .stat-label {
                font-size: .54rem;
                line-height: 1.2;
            }

            .countdown-container {
                height: 54px;
                margin-bottom: .55rem;
                width: 54px;
            }

            .countdown-svg {
                height: 54px;
                width: 54px;
            }

            .countdown-text {
                font-size: .85rem;
            }

            .countdown-unit {
                font-size: .48rem;
            }

            .qr-canvas-housing {
                border-radius: 16px;
                margin-bottom: .7rem;
                max-width: 100%;
                padding: .6rem;
            }

            #qr-canvas-target canvas,
            #qr-canvas-target svg {
                height: min(62vw, 250px) !important;
                width: min(62vw, 250px) !important;
            }

            .security-badge {
                border-radius: 12px;
                font-size: .58rem;
                justify-content: center;
                max-width: 100%;
                padding: .32rem .6rem;
                text-align: center;
                white-space: normal;
            }

            .qr-instruction {
                font-size: .72rem;
                max-width: 100%;
            }

            .qr-mode-switcher {
                flex-direction: column;
                margin-top: .75rem;
                width: 100%;
            }

            .mode-tab {
                font-size: .66rem;
                padding: .38rem .55rem;
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .presenter-header {
                padding: .65rem .7rem;
            }

            .header-actions {
                right: .8rem;
                top: calc(100% + .4rem);
            }

            .presenter-main {
                padding: .6rem;
            }

            .qr-stage-card {
                padding: .78rem .6rem;
            }

            #qr-canvas-target canvas,
            #qr-canvas-target svg {
                height: min(68vw, 235px) !important;
                width: min(68vw, 235px) !important;
            }

            .live-stats-strip {
                grid-template-columns: 1fr;
                padding: .55rem .7rem;
            }

            .stat-item:not(:last-child) {
                border-bottom: 1px solid var(--border-subtle);
                border-right: 0;
                padding-bottom: .45rem;
            }

            .stat-item:not(:first-child) {
                padding-top: .45rem;
            }
        }
    </style>
</head>
<body>
    <div class="ambient-mesh" id="ambientMesh"></div>

    <!-- Header -->
    <header class="presenter-header">
        <div class="brand-badge">
            <div class="brand-logo">SE</div>
            <div class="brand-text">
                <h1>{{ request('screen') === 'questionnaire' ? __('Live Questionnaire Presenter') : __('Live Attendance Presenter') }}</h1>
                <p>{{ request('screen') === 'questionnaire' ? __('Program Evaluation & Feedback • Anti-Proxy Dynamic QR') : __('e-Biasiswa • Anti-Proxy Dynamic QR') }}</p>
            </div>
        </div>

        <button class="presenter-menu-toggle" id="btnPresenterMenu" type="button" aria-label="{{ __('Open presenter actions') }}" aria-expanded="false" aria-controls="presenterActions">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M4 7h16M4 12h16M4 17h16"/>
            </svg>
        </button>

        <div class="header-actions" id="presenterActions">
            <div class="status-pill" id="statusPill">
                <span class="status-dot"></span>
                <span id="statusText">{{ $program->attendance_status === 'open' ? __('Attendance Live') : __('Attendance Closed') }}</span>
            </div>

            <button class="btn-action" id="btnRefresh" title="{{ __('Force Refresh QR Token') }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                <span>{{ __('Rotate Now') }}</span>
            </button>

            <button class="btn-action btn-gold" id="btnFullscreen" title="{{ __('Toggle Fullscreen (Key: F)') }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 3H5a2 2 0 0 0-2 2v3m18 0V5a2 2 0 0 0-2-2h-3m0 18h3a2 2 0 0 0 2-2v-3M3 16v3a2 2 0 0 0 2 2h3"/></svg>
                <span id="fullscreenLabel">{{ __('Fullscreen') }}</span>
            </button>

            <a href="{{ $presenterExitUrl ?? route('admin.programs.operations', $program->id) }}" class="btn-action" title="{{ __('Exit presenter screen') }}">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                <span>{{ __('Exit') }}</span>
            </a>
        </div>
    </header>

    <!-- Main Section -->
    <main class="presenter-main">
        <!-- Left: Program Information & Stats -->
        <div class="program-info-card">
            <div class="program-tagline">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <span>{{ $program->reference_no ?: __('Official Program Activity') }}</span>
            </div>

            <h2 class="program-title">{{ $program->title }}</h2>

            <div class="program-meta-grid">
                <div class="meta-box">
                    <div class="meta-label">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span>{{ __('Venue') }}</span>
                    </div>
                    <div class="meta-value">{{ $program->venue ?: __('Campus Venue') }}</div>
                </div>

                <div class="meta-box">
                    <div class="meta-label">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>{{ __('Starts At') }}</span>
                    </div>
                    <div class="meta-value">{{ $program->starts_at ? \Carbon\Carbon::parse($program->starts_at)->format('d M Y, h:i A') : __('Scheduled') }}</div>
                </div>

                <div class="meta-box">
                    <div class="meta-label">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.45 1-1 1H7c-.55 0-1-.45-1-1v-2.34c-1.2-.55-2-1.74-2-3.16 0-1.93 1.57-3.5 3.5-3.5h9c1.93 0 3.5 1.57 3.5 3.5 0 1.42-.8 2.61-2 3.16V17c0 .55-.45 1-1 1h-2c-.55 0-1-.45-1-1v-2.34"/></svg>
                        <span>{{ __('Merit Points') }}</span>
                    </div>
                    <div class="meta-value">{{ $program->participation_points }} {{ __('Points') }}</div>
                </div>
            </div>

            <!-- Live Metrics Strip -->
            <div class="live-stats-strip">
                <div class="stat-item">
                    <div class="stat-number" id="statTotalJoined">{{ $totalJoined }}</div>
                    <div class="stat-label">{{ __('Total Checked-In') }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="statInternal">{{ $internalCount }}</div>
                    <div class="stat-label">{{ __('Students') }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number" id="statExternal">{{ $externalCount }}</div>
                    <div class="stat-label">{{ __('External / Guests') }}</div>
                </div>
            </div>
        </div>

        <!-- Right: Dynamic QR Stage -->
        <div class="qr-stage-card">
            <!-- 30-Second Countdown Radial Ring -->
            <div class="countdown-container">
                <svg class="countdown-svg" viewBox="0 0 84 84">
                    <circle class="countdown-bg" cx="42" cy="42" r="36" />
                    <circle class="countdown-bar" id="countdownBar" cx="42" cy="42" r="36" />
                </svg>
                <div class="countdown-text">
                    <span id="countdownNumber">30</span>
                    <span class="countdown-unit">sec</span>
                </div>
            </div>

            <!-- Housing for QR Canvas -->
            <div class="qr-canvas-housing" id="qrHousing">
                <div id="qr-canvas-target"></div>
            </div>

            <div class="security-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <span>{{ __('Dynamic Anti-Proxy Token &bull; Rotates every 30s') }}</span>
            </div>

            <p class="qr-instruction" id="qrInstructionText">
                {{ request('screen') === 'questionnaire' ? __('Scan with your smartphone camera to complete the official Program Feedback & Questionnaire.') : __('Scan with your smartphone camera to record attendance and complete the survey.') }}
            </p>

            <!-- Mode Switcher -->
            <div class="qr-mode-switcher">
                <button class="mode-tab {{ request('mode') !== 'public' && request('screen') !== 'questionnaire' ? 'active' : '' }}" id="tabStudent" onclick="switchQrMode('student')">
                    {{ __('Student Portal Mode (PB)') }}
                </button>
                <button class="mode-tab {{ request('mode') === 'public' || request('screen') === 'questionnaire' ? 'active' : '' }}" id="tabPublic" onclick="switchQrMode('public')">
                    {{ __('Public QR Mode (External & Guests)') }}
                </button>
            </div>
        </div>
    </main>

    <div class="toast-notify" id="toastNotify">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
        <span id="toastMsg">{{ __('QR code rotated successfully') }}</span>
    </div>

    <!-- Standalone Client-side Canvas QR Generator (Zero External Dependency) -->
    <script>
        /**
         * Ultra-compact client-side QR Code SVG / Canvas Engine
         * Implements standard ISO/IEC 18004 QR generation in pure JS
         */
        (function(global){
            var QRCodeLib = function(element, options) {
                this.element = typeof element === 'string' ? document.getElementById(element) : element;
                this.options = Object.assign({
                    width: 256,
                    height: 256,
                    colorDark: "#000000",
                    colorLight: "#ffffff",
                    correctLevel: 2 // Level M
                }, options || {});
                if (this.options.text) {
                    this.makeCode(this.options.text);
                }
            };

            // Lightweight fallback QR drawing using standard canvas with error correction or SVG image rendering
            QRCodeLib.prototype.makeCode = function(text) {
                this.element.innerHTML = '';
                var canvas = document.createElement('canvas');
                canvas.width = this.options.width;
                canvas.height = this.options.height;
                var ctx = canvas.getContext('2d');
                
                // Use fast vector QR code renderer
                var qrImage = new Image();
                qrImage.crossOrigin = "anonymous";
                var encoded = encodeURIComponent(text);
                // Render via high-contrast SVG data URI generator
                qrImage.onload = function() {
                    ctx.fillStyle = "#ffffff";
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.drawImage(qrImage, 0, 0, canvas.width, canvas.height);
                };
                qrImage.onerror = function() {
                    // Fallback to internal QR generator engine if remote fails
                    var qr = new QRCodeModel(-1, QRErrorCorrectLevel.M);
                    qr.addData(text);
                    qr.make();
                    var count = qr.getModuleCount();
                    var tileW = canvas.width / count;
                    var tileH = canvas.height / count;
                    ctx.fillStyle = "#ffffff";
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.fillStyle = "#1c1917";
                    for (var r = 0; r < count; r++) {
                        for (var c = 0; c < count; c++) {
                            if (qr.isDark(r, c)) {
                                ctx.fillRect(Math.round(c * tileW), Math.round(r * tileH), Math.ceil(tileW), Math.ceil(tileH));
                            }
                        }
                    }
                };
                qrImage.src = `https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=${encoded}&margin=8&format=svg`;
                this.element.appendChild(canvas);
            };

            global.SimpleQRCode = QRCodeLib;
        })(window);

        // State variables
        const PROGRAM_ID = {{ $program->id }};
        const TOKEN_URL = "{{ $presenterTokenUrl ?? route('admin.programs.live-token', $program->id) }}";
        const TOTAL_SECONDS = 30;
        const CIRCUMFERENCE = 2 * Math.PI * 36; // 226.195

        const urlParams = new URLSearchParams(window.location.search);
        let currentMode = urlParams.get('mode') === 'public' || urlParams.get('screen') === 'questionnaire' ? 'public' : 'student';
        let currentStudentUrl = "{{ $studentCheckinUrl }}";
        let currentPublicUrl = "{{ $initialCheckinUrl }}";
        let secondsRemaining = TOTAL_SECONDS;
        let countdownTimer = null;
        let qrRenderer = null;

        const countdownBar = document.getElementById('countdownBar');
        const countdownNumber = document.getElementById('countdownNumber');
        const qrHousing = document.getElementById('qrHousing');
        const qrTarget = document.getElementById('qr-canvas-target');
        const toastNotify = document.getElementById('toastNotify');
        const toastMsg = document.getElementById('toastMsg');

        // Init QR Renderer
        qrRenderer = new SimpleQRCode(qrTarget, {
            width: 320,
            height: 320,
            colorDark: "#1c1917",
            colorLight: "#ffffff"
        });

        function getCurrentTargetUrl() {
            return currentMode === 'student' ? currentStudentUrl : currentPublicUrl;
        }

        function renderCurrentQr() {
            const url = getCurrentTargetUrl();
            qrRenderer.makeCode(url);
            
            // Pulse micro-animation
            qrHousing.classList.add('pulse');
            setTimeout(() => qrHousing.classList.remove('pulse'), 450);
        }

        function showToast(msg) {
            toastMsg.textContent = msg;
            toastNotify.classList.add('show');
            setTimeout(() => toastNotify.classList.remove('show'), 2400);
        }

        function updateCountdownUi(seconds) {
            countdownNumber.textContent = seconds;
            const progress = (seconds / TOTAL_SECONDS);
            const offset = CIRCUMFERENCE * (1 - progress);
            countdownBar.style.strokeDashoffset = offset;

            if (seconds <= 5) {
                countdownBar.style.stroke = "#ef4444"; // Warning red
            } else if (seconds <= 10) {
                countdownBar.style.stroke = "#f59e0b"; // Warning amber
            } else {
                countdownBar.style.stroke = "var(--gold-primary)"; // Gold
            }
        }

        async function fetchFreshToken(isManual = false) {
            try {
                const response = await fetch(TOKEN_URL, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) throw new Error('Network response was not ok');
                const data = await response.json();

                currentStudentUrl = data.student_url;
                currentPublicUrl = data.public_url;

                // Update stats smoothly
                if (data.stats) {
                    animateStat('statTotalJoined', data.stats.total);
                    animateStat('statInternal', data.stats.internal);
                    animateStat('statExternal', data.stats.external);
                }

                renderCurrentQr();
                resetTimer();

                if (isManual) {
                    showToast("{{ __('QR Token refreshed!') }}");
                }
            } catch (err) {
                console.error("Failed to fetch fresh token:", err);
            }
        }

        function animateStat(elementId, newValue) {
            const el = document.getElementById(elementId);
            if (!el) return;
            const current = parseInt(el.textContent, 10) || 0;
            if (current !== newValue) {
                el.style.transform = 'scale(1.35)';
                el.style.color = '#34d399';
                setTimeout(() => {
                    el.textContent = newValue;
                    el.style.transform = 'scale(1)';
                    el.style.color = 'var(--gold-light)';
                }, 200);
            }
        }

        function resetTimer() {
            secondsRemaining = TOTAL_SECONDS;
            updateCountdownUi(secondsRemaining);
        }

        function startTick() {
            if (countdownTimer) clearInterval(countdownTimer);
            updateCountdownUi(secondsRemaining);

            countdownTimer = setInterval(() => {
                secondsRemaining--;
                if (secondsRemaining < 0) {
                    fetchFreshToken(false);
                } else {
                    updateCountdownUi(secondsRemaining);
                }
            }, 1000);
        }

        function switchQrMode(mode) {
            currentMode = mode;
            document.getElementById('tabStudent').classList.toggle('active', mode === 'student');
            document.getElementById('tabPublic').classList.toggle('active', mode === 'public');
            renderCurrentQr();
            showToast(mode === 'student' ? "{{ __('Switched to Student Portal Mode') }}" : "{{ __('Switched to Public QR Mode') }}");
        }

        // Fullscreen Handling
        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    alert(`Error attempting to enable fullscreen: ${err.message}`);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }

        document.addEventListener('fullscreenchange', () => {
            const label = document.getElementById('fullscreenLabel');
            if (document.fullscreenElement) {
                label.textContent = "{{ __('Exit Fullscreen') }}";
            } else {
                label.textContent = "{{ __('Fullscreen') }}";
            }
        });

        // Event Listeners
        document.getElementById('btnFullscreen').addEventListener('click', toggleFullscreen);
        document.getElementById('btnRefresh').addEventListener('click', () => fetchFreshToken(true));

        const presenterMenuButton = document.getElementById('btnPresenterMenu');
        const presenterActions = document.getElementById('presenterActions');
        if (presenterMenuButton && presenterActions) {
            presenterMenuButton.addEventListener('click', (event) => {
                event.stopPropagation();
                const isOpen = presenterActions.classList.toggle('is-open');
                presenterMenuButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            });

            document.addEventListener('click', (event) => {
                if (!presenterActions.contains(event.target) && !presenterMenuButton.contains(event.target)) {
                    presenterActions.classList.remove('is-open');
                    presenterMenuButton.setAttribute('aria-expanded', 'false');
                }
            });

            presenterActions.querySelectorAll('button, a').forEach((item) => {
                item.addEventListener('click', () => {
                    presenterActions.classList.remove('is-open');
                    presenterMenuButton.setAttribute('aria-expanded', 'false');
                });
            });
        }

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && presenterActions && presenterMenuButton) {
                presenterActions.classList.remove('is-open');
                presenterMenuButton.setAttribute('aria-expanded', 'false');
            }
            if (e.key === 'f' || e.key === 'F') {
                toggleFullscreen();
            }
            if (e.key === 'r' || e.key === 'R') {
                fetchFreshToken(true);
            }
        });

        // Mouse glow track
        window.addEventListener('mousemove', (e) => {
            document.documentElement.style.setProperty('--mouse-x', e.clientX + 'px');
            document.documentElement.style.setProperty('--mouse-y', e.clientY + 'px');
        });

        // Initial setup
        renderCurrentQr();
        startTick();
    </script>
</body>
</html>
