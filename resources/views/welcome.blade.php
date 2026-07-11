<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#171412">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ __('home.page_title') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/newlogo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/pwa/icon-180.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            /* Desert Rock Palette */
            --desert-rock:    #A48D78;
            --sandstone:      #CBB9A4;
            --creamed-oat:    #E6DAC8;
            --porcelain:      #F4F1EA;
            --feather:        #FAF9F6;

            /* Semantic tokens */
            --bg:             var(--feather);
            --surface:        #FFFFFF;
            --surface-warm:   var(--porcelain);
            --border:         var(--creamed-oat);
            --border-strong:  var(--sandstone);
            --text-primary:   #0F172A;
            --text-secondary: #64748B;
            --text-muted:     #64748B;
            --accent:         var(--desert-rock);
            --accent-hover:   #8C7463;

            --radius-sm:  10px;
            --radius-md:  16px;
            --radius-lg:  24px;
            --radius-xl:  32px;
        }
        body[data-theme="dark"] {
            --bg:             #0f0e0d;
            --surface:        #171412;
            --surface-warm:   #201b17;
            --border:         rgba(226, 209, 192, .16);
            --border-strong:  rgba(226, 209, 192, .28);
            --text-primary:   #f7efe8;
            --text-secondary: #c8b8a9;
            --text-muted:     #a99888;
            --accent:         #d7bfa8;
            --accent-hover:   #f2dfca;
            color-scheme: dark;
        }

        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text-primary);
            background: var(--bg);
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }
        body[data-theme="dark"] .hero,
        body[data-theme="dark"] .section,
        body[data-theme="dark"] footer {
            background: var(--bg);
            color: var(--text-primary);
        }
        body[data-theme="dark"] .card,
        body[data-theme="dark"] .stat-card,
        body[data-theme="dark"] .portal-card,
        body[data-theme="dark"] .role-card {
            background: var(--surface);
            border-color: var(--border);
        }
        body[data-theme="dark"] .headline,
        body[data-theme="dark"] .section-title,
        body[data-theme="dark"] h1,
        body[data-theme="dark"] h2,
        body[data-theme="dark"] h3 {
            color: var(--text-primary);
        }
        body[data-theme="dark"] .subtitle,
        body[data-theme="dark"] p,
        body[data-theme="dark"] .brand-text p {
            color: var(--text-secondary);
        }
        body[data-theme="dark"] .lang-switch select {
            background: rgba(24, 21, 18, .88);
            border-color: var(--border);
            color: var(--text-primary);
        }

        /* ── Language Switch ── */
        .lang-switch {
            position: absolute;
            top: 1.5rem;
            right: 1.75rem;
            z-index: 10;
        }
        .lang-switch select {
            appearance: none;
            background: rgba(255,255,255,0.85);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 0.4rem 1rem;
            font-family: inherit;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            cursor: pointer;
            letter-spacing: 0.08em;
            outline: none;
            transition: border-color 0.2s;
        }
        .lang-switch select:hover { border-color: var(--border-strong); }
        .lang-switch select:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }

        /* ── Hero ── */
        .hero {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.15fr 0.85fr;
            align-items: center;
            gap: 4rem;
            padding: 6rem 7vw 5rem;
            position: relative;
            overflow: hidden;
            background: var(--feather);
        }

        /* Soft warm grain texture via SVG filter */
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 70% 60% at 15% 20%, rgba(203,185,164,0.22) 0%, transparent 70%),
                radial-gradient(ellipse 50% 70% at 90% 80%, rgba(230,218,200,0.25) 0%, transparent 65%);
            pointer-events: none;
        }

        /* Decorative arc */
        .hero::after {
            content: '';
            position: absolute;
            right: -120px;
            top: -80px;
            width: 520px;
            height: 520px;
            border-radius: 50%;
            border: 1px solid var(--creamed-oat);
            opacity: 0.6;
            pointer-events: none;
        }

        .hero-content, .hero-visual { position: relative; z-index: 1; }

        /* Brand */
        .brand {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 2.75rem;
            animation: fadeUp 0.65s ease both;
        }
        .brand-logo {
            width: 180px;
            height: auto;
            object-fit: contain;
            filter: drop-shadow(0 4px 12px rgba(164,141,120,0.15));
        }
        .brand-divider {
            display: none;
        }
        .brand-text h1 {
            font-size: 2.5rem;
            font-weight: 900;
            letter-spacing: -0.03em;
            background: linear-gradient(135deg, var(--desert-rock) 0%, #F2C999 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            line-height: 1.1;
        }
        .brand-text p {
            font-size: 1rem;
            font-weight: 700;
            color: #64748B;
            letter-spacing: 0;
            text-transform: uppercase;
            margin-top: 0.3rem;
        }

        /* Headline */
        .headline {
            font-size: clamp(2.5rem, 5vw, 4rem);
            font-weight: 900;
            line-height: 1.1;
            letter-spacing: -0.03em;
            margin-bottom: 1.5rem;
            color: #0F172A;
            animation: fadeUp 0.75s ease 0.1s both;
        }
        .headline em {
            font-style: normal;
            background: linear-gradient(135deg, var(--desert-rock) 0%, #F2C999 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .subtitle {
            font-size: 1.2rem;
            font-weight: 600;
            color: #64748B;
            line-height: 1.8;
            max-width: 560px;
            margin-bottom: 2.75rem;
            animation: fadeUp 0.75s ease 0.2s both;
        }

        /* CTA */
        .cta-group {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            animation: fadeUp 0.75s ease 0.3s both;
        }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.875rem 2.25rem;
            background: var(--desert-rock);
            color: #FAF9F6;
            border-radius: 999px;
            font-family: inherit;
            font-size: 0.9rem;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: background 0.25s, transform 0.2s, box-shadow 0.25s;
            box-shadow: 0 6px 20px rgba(164,141,120,0.3);
        }
        .btn-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(164,141,120,0.38);
        }
        .btn-primary:active { transform: translateY(0); }
        .btn-primary:focus-visible { outline: 3px solid var(--sandstone); outline-offset: 3px; }

        .btn-primary svg { width: 16px; height: 16px; flex-shrink: 0; }

        .stat-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--text-secondary);
        }
        .stat-pill .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #7BAF8A;
        }

        /* Hero Visual */
        .hero-visual {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            animation: fadeUp 0.8s ease 0.35s both;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .feature-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.75rem 1.375rem;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.25s;
            box-shadow: 0 2px 12px rgba(42,33,24,0.06);
        }
        .feature-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 16px 36px rgba(42,33,24,0.1);
            border-color: var(--sandstone);
        }
        .feature-card:first-child { transform: rotate(-1deg); }
        .feature-card:first-child:hover { transform: translateY(-6px) rotate(0deg); }
        .feature-card:last-child { transform: rotate(1deg); }
        .feature-card:last-child:hover { transform: translateY(-6px) rotate(0deg); }

        .icon-wrap {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            border: 1px solid var(--border);
            background: var(--surface-warm);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .icon-wrap svg { width: 22px; height: 22px; color: var(--desert-rock); }

        .feature-card h2 {
            
            font-size: 1.15rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
        }
        .feature-card p {
            font-size: 0.85rem;
            color: var(--text-muted);
            line-height: 1.65;
        }

        .badge-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 999px;
            padding: 0.7rem 1.375rem;
            box-shadow: 0 2px 10px rgba(42,33,24,0.05);
        }
        .badge-row span { font-size: 0.8rem; font-weight: 500; color: var(--text-muted); letter-spacing: 0.06em; }
        .badge-row .badge {
            background: var(--desert-rock);
            color: #FAF9F6;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.1em;
            padding: 0.35rem 1rem;
            border-radius: 999px;
        }

        /* ── Section Divider ── */
        .section-divider {
            width: min(1100px, calc(100% - 3rem));
            margin: 0 auto;
            height: 1px;
            background: var(--border);
        }

        /* ── Content Sections ── */
        .content-sections {
            width: min(1100px, calc(100% - 3rem));
            margin: 2.5rem auto 3rem;
            display: grid;
            gap: 1.25rem;
        }

        .section-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            padding: 1.75rem 1.875rem;
            box-shadow: 0 2px 14px rgba(42,33,24,0.05);
        }

        .section-title {
            font-size: clamp(1.1rem, 3.2vw, 1.45rem);
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        .section-desc {
            font-size: 0.95rem;
            color: var(--text-secondary);
            line-height: 1.75;
        }

        /* Feature Grid */
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.875rem;
            margin-top: 1.125rem;
        }
        .feature-box {
            background: var(--surface-warm);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1.125rem 1rem;
            transition: border-color 0.2s, transform 0.2s, box-shadow 0.2s;
        }
        .feature-box:hover {
            border-color: var(--sandstone);
            transform: translateY(-3px);
            box-shadow: 0 8px 22px rgba(42,33,24,0.08);
        }
        .feature-head {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            margin-bottom: 0.5rem;
        }
        .feature-icon {
            width: 32px;
            height: 32px;
            border-radius: 9px;
            background: var(--surface);
            border: 1px solid var(--border);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .feature-icon svg { width: 15px; height: 15px; color: var(--desert-rock); }
        .feature-head strong { font-size: 0.9rem; font-weight: 600; color: var(--text-primary); }
        .feature-box p { font-size: 0.83rem; color: var(--text-muted); line-height: 1.6; }

        /* Flow Steps */
        .flow-row {
            margin-top: 1.125rem;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.75rem;
        }
        .flow-step {
            background: var(--surface-warm);
            border: 1px dashed var(--sandstone);
            border-radius: var(--radius-md);
            padding: 1rem 0.875rem;
            position: relative;
        }
        .flow-index {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--desert-rock);
            color: #FAF9F6;
            font-size: 0.72rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.5rem;
        }
        .flow-step p { font-size: 0.84rem; color: var(--text-secondary); font-weight: 500; line-height: 1.5; }

        /* Role Grid */
        .role-grid {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.875rem;
        }
        .role-box {
            background: var(--surface-warm);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1.125rem 1rem;
        }
        .role-box h4 {
            font-size: 1rem;
            font-weight: 800;
            color: var(--desert-rock);
            margin-bottom: 0.375rem;
        }
        .role-box p { font-size: 0.85rem; color: var(--text-muted); line-height: 1.55; }

        /* Benefits */
        .benefit-list {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.625rem 1rem;
        }
        .benefit-item {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            font-size: 0.9rem;
            font-weight: 500;
            color: var(--text-secondary);
        }
        .benefit-item svg { width: 16px; height: 16px; color: #7BAF8A; flex-shrink: 0; }

        /* Impact Grid */
        .impact-grid {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.875rem;
        }
        .impact-box {
            background: var(--surface-warm);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1.25rem 1rem;
            text-align: center;
        }
        .impact-box h4 { font-size: 0.78rem; font-weight: 500; color: var(--text-muted); letter-spacing: 0.08em; text-transform: uppercase; margin-bottom: 0.5rem; }
        .impact-box strong {  font-size: 2rem; font-weight: 700; color: var(--desert-rock); display: block; line-height: 1; }
        .impact-box span { font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem; display: block; }

        /* CTA Card */
        .cta-card {
            background: var(--desert-rock);
            border-color: transparent;
            position: relative;
            overflow: hidden;
        }
        .cta-card::before {
            content: '';
            position: absolute;
            right: -60px;
            top: -60px;
            width: 280px;
            height: 280px;
            border-radius: 50%;
            border: 1px solid rgba(250,249,246,0.15);
            pointer-events: none;
        }
        .cta-card::after {
            content: '';
            position: absolute;
            right: 20px;
            bottom: -80px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            border: 1px solid rgba(250,249,246,0.1);
            pointer-events: none;
        }
        .cta-card .section-title { color: var(--feather); position: relative; z-index: 1; }
        .cta-card .section-desc { color: rgba(250,249,246,0.78); position: relative; z-index: 1; }

        .cta-actions {
            margin-top: 1.25rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            position: relative;
            z-index: 1;
        }
        .cta-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border-radius: 999px;
            padding: 0.7rem 1.375rem;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            letter-spacing: 0.03em;
            transition: background 0.2s, transform 0.2s;
            cursor: pointer;
        }
        .cta-btn:hover { transform: translateY(-1px); }
        .cta-btn:focus-visible { outline: 2px solid var(--feather); outline-offset: 2px; }
        .cta-btn svg { width: 15px; height: 15px; }
        .cta-btn.primary { background: var(--feather); color: var(--desert-rock); }
        .cta-btn.primary:hover { background: #FFFFFF; }
        .cta-btn.secondary { background: rgba(250,249,246,0.12); color: var(--feather); border: 1px solid rgba(250,249,246,0.3); }
        .cta-btn.secondary:hover { background: rgba(250,249,246,0.2); }

        /* Support Grid */
        .support-grid {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.875rem;
        }
        .support-box {
            background: var(--surface-warm);
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            padding: 1rem;
        }
        .support-box h5 { font-size: 0.72rem; font-weight: 600; color: var(--text-muted); letter-spacing: 0.1em; text-transform: uppercase; margin-bottom: 0.375rem; }
        .support-box p { font-size: 0.9rem; font-weight: 600; color: var(--text-primary); word-break: break-word; }

        /* Footer */
        .welcome-footer {
            width: min(1100px, calc(100% - 3rem));
            margin: 0 auto 2rem;
            padding: 1.1rem 0 0;
            border-top: 1px solid var(--border);
            color: var(--text-muted);
        }
        .footer-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }
        .footer-meta span { font-size: 0.8rem; color: var(--text-muted); }
        .footer-links { display: flex; gap: 1rem; flex-wrap: wrap; }
        .footer-links a {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-decoration: none;
            transition: color 0.2s;
        }
        .footer-links a:hover { color: var(--desert-rock); }
        .footer-links a:focus-visible { outline: 2px solid var(--desert-rock); outline-offset: 2px; border-radius: 2px; }
        .footer-copy {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(230, 218, 200, .62);
            font-size: .82rem;
            color: var(--text-muted);
            text-align: center;
        }

        /* Animations */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Reduced motion */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .hero { grid-template-columns: 1fr; gap: 3.5rem; padding: 5rem 6vw 4rem; }
            .feature-grid,
            .role-grid,
            .impact-grid,
            .support-grid { grid-template-columns: 1fr 1fr; }
            .flow-row { grid-template-columns: 1fr 1fr; }
        }

        @media (max-width: 768px) {
            .hero { padding: 4.5rem 5vw 3.5rem; }
            .brand { flex-direction: column; align-items: flex-start; gap: 0.75rem; }
            .brand-logo { width: 140px; }
            .cta-group { flex-direction: column; align-items: flex-start; }
            .btn-primary { width: 100%; justify-content: center; }
            .cards-grid { grid-template-columns: 1fr; }
            .feature-card:first-child,
            .feature-card:last-child { transform: none; }
            .content-sections { width: calc(100% - 1.5rem); }
            .feature-grid,
            .role-grid,
            .impact-grid,
            .support-grid,
            .flow-row { grid-template-columns: 1fr; }
            .benefit-list { grid-template-columns: 1fr; }
            .welcome-footer { width: calc(100% - 1.5rem); }
            .footer-meta { justify-content: center; text-align: center; }
            .footer-links { justify-content: center; }
            .section-card { padding: 1.375rem 1.25rem; }
        }

        @media (max-width: 480px) {
            .headline { font-size: 2.1rem; }
            .brand-text h1 { font-size: 1.8rem; }
            .brand-text p { font-size: .78rem; }
        }
    </style>
</head>
<body data-theme="{{ session('theme', 'light') }}">
@php
    $homeStats = $homeStats ?? [
        'students_managed' => 0,
        'open_actions' => 0,
        'digital_records' => 0,
        'server_time' => now()->format('Y-m-d H:i:s'),
        'system_online' => true,
    ];
@endphp

    <!-- ── HERO ── -->
    <section class="hero">

        <form method="POST" action="{{ route('locale.update') }}" class="lang-switch">
            @csrf
            <select name="locale" onchange="this.form.submit()" aria-label="Select language">
                <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>EN</option>
                <option value="ms" {{ app()->getLocale() === 'ms' ? 'selected' : '' }}>BM</option>
            </select>
        </form>

        <div class="hero-content">

            <div class="brand">
                <img src="{{ asset('images/logo-politeknik-besut.png') }}" alt="Logo Politeknik Besut" class="brand-logo">
                <div class="brand-divider" aria-hidden="true"></div>
                <div class="brand-text">
                    <h1>{{ __('home.brand_name') }}</h1>
                    <p>{{ __('home.brand_sub') }}</p>
                </div>
            </div>

            <h2 class="headline">
                {{ __('home.headline_prefix') }} <em>{{ __('home.headline_focus') }}</em> {{ __('home.headline_suffix') }}
            </h2>

            <p class="subtitle">{{ __('home.subtitle') }}</p>

            <div class="cta-group">
                <a href="{{ route('login') }}" class="btn-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                    {{ __('home.login_button') }}
                </a>
                <div class="stat-pill" data-home-live data-live-url="{{ route('system-overview.live') }}" aria-label="System status: online">
                    <span class="dot" aria-hidden="true"></span>
                    <span data-home-stat="system-status">{{ __('home.official_label') }} · StudentEdge · {{ __('home.live_label') }}</span>
                </div>
            </div>

        </div>

        <div class="hero-visual" aria-hidden="true">

            <div class="cards-grid">
                <div class="feature-card">
                    <div class="icon-wrap">
                        <!-- Lucide: graduation-cap -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5"/></svg>
                    </div>
                    <h2>{{ __('home.card_scholarship_title') }}</h2>
                    <p>{{ __('home.card_scholarship_desc') }}</p>
                </div>

                <div class="feature-card">
                    <div class="icon-wrap">
                        <!-- Lucide: shield-check -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    </div>
                    <h2>{{ __('home.card_discipline_title') }}</h2>
                    <p>{{ __('home.card_discipline_desc') }}</p>
                </div>
            </div>

            <div class="badge-row">
                <span>{{ __('home.official_label') }}</span>
                <span class="badge">StudentEdge</span>
            </div>

        </div>
    </section>

    <!-- ── MAIN CONTENT ── -->
    <main class="content-sections">

        <section class="section-card" aria-labelledby="about-title">
            <h3 class="section-title" id="about-title">{{ __('home.about_title') }}</h3>
            <p class="section-desc">{{ __('home.about_desc') }}</p>
        </section>

        <section class="section-card cta-card" aria-labelledby="cta-title">
            <h3 class="section-title" id="cta-title">{{ __('home.cta_title') }}</h3>
            <p class="section-desc">{{ __('home.cta_desc') }}</p>
            <div class="cta-actions">
                <a href="{{ route('login') }}" class="cta-btn primary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                    {{ __('home.cta_login') }}
                </a>
                <a href="{{ route('bug-reports.create') }}" class="cta-btn secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 12h9m-9 4h5.25M6 3.75h8.25L18 7.5v12.75A2.25 2.25 0 0115.75 22.5h-9A2.25 2.25 0 014.5 20.25V6A2.25 2.25 0 016.75 3.75H6z"/><path stroke-linecap="round" stroke-linejoin="round" d="M14.25 3.75V7.5H18"/></svg>
                    {{ __('home.cta_report') }}
                </a>
                <a href="mailto:support@polibesut.edu.my?subject=StudentEdge%20Support" class="cta-btn secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/></svg>
                    {{ __('home.cta_contact') }}
                </a>
            </div>
        </section>

        <section class="section-card" aria-labelledby="support-title">
            <h3 class="section-title" id="support-title">{{ __('home.support_title') }}</h3>
            <div class="support-grid">
                <article class="support-box"><h5>{{ __('home.support_email') }}</h5><p>support@polibesut.edu.my</p></article>
                <article class="support-box"><h5>{{ __('home.support_office') }}</h5><p>{{ __('home.support_office_value') }}</p></article>
                <article class="support-box"><h5>{{ __('home.support_phone') }}</h5><p>+60 XXX XXX XXX</p></article>
            </div>
        </section>

    </main>

    <footer class="welcome-footer">
        <div class="footer-meta">
            <nav class="footer-links" aria-label="Footer navigation">
                <a href="#">{{ __('home.privacy_policy') }}</a>
                <a href="#">{{ __('home.terms_use') }}</a>
            </nav>
            <span>{{ __('home.system_version') }}</span>
        </div>
        <div class="footer-copy">
            &copy; {{ date('Y') }} StudentEdge. {{ __('home.copyright') }}
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.querySelector('[data-home-live]');
        if (!root) return;

        const liveUrl = root.dataset.liveUrl;
        const formatter = new Intl.NumberFormat();
        const setText = (name, value) => {
            const el = document.querySelector(`[data-home-stat="${name}"]`);
            if (el) el.textContent = value;
        };

        async function refreshHomeStats() {
            try {
                const response = await fetch(liveUrl, { headers: { Accept: 'application/json' } });
                if (!response.ok) return;

                const payload = await response.json();
                const data = payload.data || {};
                setText('students-managed', formatter.format(Number(data.students_managed || 0)));
                setText('open-actions', formatter.format(Number(data.open_actions || 0)));
                setText('digital-records', formatter.format(Number(data.digital_records || 0)));
                root.setAttribute('aria-label', `System status: ${data.system_online ? 'online' : 'offline'}, updated ${data.server_time || ''}`);
            } catch (error) {
                root.setAttribute('aria-label', 'System status: live update unavailable');
            }
        }

        refreshHomeStats();
        setInterval(refreshHomeStats, 10000);
    });
    </script>
</body>
</html>
