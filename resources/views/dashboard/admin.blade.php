@extends('layouts.app')

@section('title', 'Dashboard Admin')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">

<style>
    :root {
        --c-bg: #F7F6F3;
        --c-surface: #FFFFFF;
        --c-surface-2: #F2F0EC;
        --c-border: #E8E5DF;
        --c-border-strong: #D4CFC6;
        --c-text-primary: #1A1714;
        --c-text-secondary: #6B6560;
        --c-text-muted: #9E9892;
        --c-accent: #2D6A4F;
        --c-accent-light: #D8EDDF;
        --c-accent-text: #1B4332;
        --c-gold: #C5960A;
        --c-gold-light: #FEF3C7;
        --c-gold-text: #78350F;
        --c-red: #B91C1C;
        --c-red-light: #FEE2E2;
        --c-red-text: #7F1D1D;
        --c-blue: #1D4ED8;
        --c-blue-light: #DBEAFE;
        --c-blue-text: #1E3A8A;
        --c-amber: #D97706;
        --c-amber-light: #FEF3C7;
        --c-amber-text: #78350F;
        --radius-sm: 8px;
        --radius-md: 12px;
        --radius-lg: 16px;
        --radius-xl: 20px;
        --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
        --shadow-md: 0 4px 12px rgba(0,0,0,0.07), 0 1px 3px rgba(0,0,0,0.04);
        --shadow-focus: 0 0 0 3px rgba(45,106,79,0.18);
    }

    * { box-sizing: border-box; }

    body {
        font-family: 'DM Sans', -apple-system, sans-serif;
        background: var(--c-bg);
        color: var(--c-text-primary);
    }
    body[data-theme="dark"] {
        --c-bg: #080807;
        --c-surface: rgba(28, 25, 22, .86);
        --c-surface-2: rgba(255,255,255,.055);
        --c-border: rgba(226, 209, 192, .14);
        --c-border-strong: rgba(226, 209, 192, .24);
        --c-text-primary: #f7efe8;
        --c-text-secondary: #c8b8a9;
        --c-text-muted: #9e8f81;
        --c-accent: #5fbe91;
        --c-accent-light: rgba(95, 190, 145, .16);
        --c-accent-text: #cdf7df;
        --c-gold-light: rgba(245, 158, 11, .16);
        --c-gold-text: #fde68a;
        --c-red-light: rgba(239, 68, 68, .16);
        --c-red-text: #fecaca;
        --c-blue-light: rgba(96, 165, 250, .16);
        --c-blue-text: #bfdbfe;
        --c-amber-light: rgba(245, 158, 11, .16);
        --c-amber-text: #fde68a;
        --shadow-sm: 0 16px 36px rgba(0,0,0,.26), inset 0 1px 0 rgba(255,255,255,.07);
        --shadow-md: 0 24px 52px rgba(0,0,0,.36), inset 0 1px 0 rgba(255,255,255,.10);
        --shadow-focus: 0 0 0 3px rgba(95,190,145,.18);
    }

    /* ── Layout ── */
    .adash {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
        padding: 0;
    }

    /* ── Hero Banner ── */
    .dash-hero {
        background: var(--c-surface);
        border: 1px solid var(--c-border);
        border-radius: var(--radius-xl);
        padding: 2rem 2.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        box-shadow: var(--shadow-sm);
        position: relative;
        overflow: hidden;
    }
    .dash-hero::before {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 320px; height: 100%;
        background: linear-gradient(135deg, transparent 0%, var(--c-surface-2) 100%);
        border-radius: 0 var(--radius-xl) var(--radius-xl) 0;
    }
    .dash-hero-text { position: relative; z-index: 1; }
    .dash-hero-label {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--c-accent);
        background: var(--c-accent-light);
        display: inline-block;
        padding: 3px 10px;
        border-radius: 99px;
        margin-bottom: 0.6rem;
    }
    .dash-hero h3 {
        font-family: 'DM Serif Display', Georgia, serif;
        font-size: 1.75rem;
        font-weight: 400;
        color: var(--c-text-primary);
        margin: 0 0 0.4rem;
        line-height: 1.2;
    }
    .dash-hero p {
        font-size: 0.875rem;
        color: var(--c-text-secondary);
        margin: 0;
        max-width: 440px;
    }
    .dash-hero-date {
        position: relative; z-index: 1;
        font-size: 0.8rem;
        color: var(--c-text-muted);
        white-space: nowrap;
    }

    /* ── Alert ── */
    .ui-alert-success {
        background: var(--c-accent-light);
        border: 1px solid #A7D7B8;
        border-radius: var(--radius-md);
        padding: 0.8rem 1.25rem;
        color: var(--c-accent-text);
        font-size: 0.875rem;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 0.6rem;
    }
    .ui-alert-success::before {
        content: '';
        display: block;
        width: 18px; height: 18px;
        background: var(--c-accent);
        border-radius: 50%;
        flex-shrink: 0;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20' fill='white'%3E%3Cpath fill-rule='evenodd' d='M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z' clip-rule='evenodd'/%3E%3C/svg%3E");
        background-size: 12px;
        background-repeat: no-repeat;
        background-position: center;
    }

    /* ── Quick Links ── */
    .portal-card {
        background: var(--c-surface);
        border: 1px solid var(--c-border);
        border-radius: var(--radius-lg);
        padding: 1.25rem 1.5rem;
        box-shadow: var(--shadow-sm);
    }
    .portal-card-head {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--c-text-muted);
        margin-bottom: 1rem;
    }
    .portal-links {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .portal-link {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.45rem 1rem;
        background: var(--c-surface-2);
        border: 1px solid var(--c-border);
        border-radius: var(--radius-sm);
        font-size: 0.8rem;
        font-weight: 500;
        color: var(--c-text-primary);
        text-decoration: none;
        cursor: pointer;
        transition: all 200ms ease;
    }
    .portal-link:hover {
        background: var(--c-accent-light);
        border-color: #A7D7B8;
        color: var(--c-accent-text);
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
    }
    .portal-link:focus-visible {
        outline: none;
        box-shadow: var(--shadow-focus);
    }
    .portal-link svg { width: 13px; height: 13px; opacity: 0.6; }

    /* ── Stats Grid ── */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
    }
    @media (min-width: 640px) {
        .stats-grid { grid-template-columns: repeat(4, 1fr); }
    }

    .stat-card {
        background: var(--c-surface);
        border: 1px solid var(--c-border);
        border-radius: var(--radius-lg);
        padding: 1.25rem 1.5rem;
        box-shadow: var(--shadow-sm);
        transition: box-shadow 200ms ease, transform 200ms ease;
        cursor: default;
    }
    .stat-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-1px);
    }
    .stat-label {
        font-size: 0.72rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: var(--c-text-muted);
        margin-bottom: 0.6rem;
    }
    .stat-value {
        font-family: 'DM Serif Display', Georgia, serif;
        font-size: 2.1rem;
        font-weight: 400;
        color: var(--c-text-primary);
        line-height: 1;
    }
    .stat-card.accent { border-left: 3px solid var(--c-accent); }
    .stat-card.gold   { border-left: 3px solid var(--c-gold); }
    .stat-card.red    { border-left: 3px solid var(--c-red); }
    .stat-card.blue   { border-left: 3px solid var(--c-blue); }

    /* ── Section Heading ── */
    .section-heading {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--c-text-muted);
        margin: 0.25rem 0 0.6rem;
        padding-left: 0.25rem;
    }

    /* ── Two Col Grid ── */
    .two-col {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    @media (min-width: 1000px) {
        .two-col { grid-template-columns: 1fr 1fr; }
    }

    /* ── Data Card ── */
    .data-card {
        background: var(--c-surface);
        border: 1px solid var(--c-border);
        border-radius: var(--radius-lg);
        overflow: hidden;
        box-shadow: var(--shadow-sm);
    }
    .data-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid var(--c-border);
        background: var(--c-surface-2);
    }
    .data-card-head strong {
        font-size: 0.875rem;
        font-weight: 600;
        color: var(--c-text-primary);
    }
    .btn-ghost {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 0.3rem 0.75rem;
        background: var(--c-surface);
        border: 1px solid var(--c-border-strong);
        border-radius: var(--radius-sm);
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--c-text-secondary);
        text-decoration: none;
        cursor: pointer;
        transition: all 180ms ease;
    }
    .btn-ghost:hover {
        background: var(--c-accent-light);
        border-color: #A7D7B8;
        color: var(--c-accent-text);
    }
    .btn-ghost:focus-visible {
        outline: none;
        box-shadow: var(--shadow-focus);
    }

    /* ── Table ── */
    .data-card table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.82rem;
    }
    .data-card thead tr {
        border-bottom: 1px solid var(--c-border);
    }
    .data-card th {
        padding: 0.65rem 1.25rem;
        text-align: left;
        font-size: 0.68rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: var(--c-text-muted);
        background: var(--c-surface-2);
    }
    .data-card td {
        padding: 0.75rem 1.25rem;
        color: var(--c-text-primary);
        border-bottom: 1px solid var(--c-border);
        vertical-align: middle;
    }
    .data-card tbody tr:last-child td { border-bottom: none; }
    .data-card tbody tr {
        transition: background 150ms ease;
    }
    .data-card tbody tr:hover {
        background: var(--c-surface-2);
    }

    /* ── Badges ── */
    .badge {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 99px;
        font-size: 0.68rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .status-unpaid   { background: var(--c-red-light);   color: var(--c-red-text); }
    .status-applied  { background: var(--c-blue-light);  color: var(--c-blue-text); }
    .status-pending  { background: var(--c-amber-light); color: var(--c-amber-text); }
    .status-active   { background: var(--c-accent-light);color: var(--c-accent-text); }
    .status-paid     { background: var(--c-accent-light);color: var(--c-accent-text); }

    /* ── Empty State ── */
    .empty-state {
        padding: 2.5rem 1.25rem;
        text-align: center;
        color: var(--c-text-muted);
        font-size: 0.82rem;
    }
    .empty-state svg {
        display: block;
        margin: 0 auto 0.75rem;
        opacity: 0.35;
    }

    /* ── No Access ── */
    .no-access {
        background: var(--c-surface);
        border: 1px solid var(--c-border);
        border-radius: var(--radius-lg);
        padding: 3rem 2rem;
        text-align: center;
        box-shadow: var(--shadow-sm);
    }
    .no-access p {
        color: var(--c-text-secondary);
        font-size: 0.875rem;
        margin: 0.5rem 0 0;
    }
    .no-access .icon-wrap {
        width: 52px; height: 52px;
        background: var(--c-surface-2);
        border: 1px solid var(--c-border);
        border-radius: var(--radius-md);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
    }

    /* ── System Monitoring ── */
    .monitor-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    @media (min-width: 920px) {
        .monitor-grid { grid-template-columns: 1.2fr .8fr; }
    }
    .monitor-card {
        background: linear-gradient(145deg, rgba(255,255,255,0.76), rgba(248,252,250,0.48));
        border: 1px solid rgba(255,255,255,0.78);
        border-radius: var(--radius-lg);
        padding: 1rem 1.1rem;
        box-shadow: 0 18px 45px rgba(31, 41, 55, 0.10), inset 0 1px 0 rgba(255,255,255,0.78);
        backdrop-filter: blur(18px) saturate(140%);
        -webkit-backdrop-filter: blur(18px) saturate(140%);
        position: relative;
        overflow: hidden;
    }
    .monitor-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            linear-gradient(120deg, rgba(255,255,255,0.58), transparent 36%),
            radial-gradient(circle at 85% 12%, rgba(45,106,79,0.10), transparent 34%);
        pointer-events: none;
    }
    .monitor-card > * {
        position: relative;
        z-index: 1;
    }
    .monitor-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .75rem;
        gap: .75rem;
    }
    .monitor-title {
        font-size: .85rem;
        font-weight: 700;
        color: var(--c-text-primary);
        letter-spacing: .02em;
    }
    .monitor-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .2rem .55rem;
        font-size: .65rem;
        font-weight: 700;
        letter-spacing: .05em;
        text-transform: uppercase;
        border: 1px solid transparent;
    }
    .monitor-pill.ok { background: var(--c-accent-light); color: var(--c-accent-text); border-color: #A7D7B8; }
    .monitor-pill.warn { background: var(--c-amber-light); color: var(--c-amber-text); border-color: #FCD34D; }
    .monitor-pill.error { background: var(--c-red-light); color: var(--c-red-text); border-color: #FCA5A5; }

    .meter-wrap { margin-bottom: .65rem; }
    .meter-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        margin-bottom: .22rem;
        gap: .5rem;
    }
    .meter-label {
        font-size: .72rem;
        color: var(--c-text-secondary);
        text-transform: uppercase;
        letter-spacing: .05em;
        font-weight: 600;
    }
    .meter-value {
        font-size: .76rem;
        color: var(--c-text-primary);
        font-weight: 700;
        white-space: nowrap;
    }
    .meter-track {
        height: 8px;
        width: 100%;
        border-radius: 999px;
        background: rgba(236,232,225,0.72);
        border: 1px solid rgba(255,255,255,0.62);
        overflow: hidden;
        box-shadow: inset 0 1px 2px rgba(31, 41, 55, 0.07);
    }
    .meter-fill {
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #2D6A4F, #48A67E);
        transition: width 300ms ease;
    }
    .meter-fill.warn { background: linear-gradient(90deg, #D97706, #F59E0B); }
    .meter-fill.error { background: linear-gradient(90deg, #B91C1C, #EF4444); }

    .monitor-list {
        display: grid;
        grid-template-columns: 1fr;
        gap: .45rem;
    }
    .monitor-item {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        gap: .75rem;
        font-size: .78rem;
        border-bottom: 1px dashed rgba(107,101,96,0.22);
        padding-bottom: .38rem;
    }
    .monitor-item:last-child { border-bottom: none; padding-bottom: 0; }
    .monitor-key { color: var(--c-text-secondary); font-weight: 600; }
    .monitor-val {
        color: var(--c-text-primary);
        font-weight: 700;
        text-align: right;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: .74rem;
        word-break: break-word;
    }
    .monitor-kpi-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: .75rem;
    }
    .monitor-kpi {
        border: 1px solid rgba(255,255,255,0.78);
        background:
            linear-gradient(145deg, rgba(255,255,255,0.78), rgba(250,253,251,0.46)),
            radial-gradient(circle at 92% 8%, rgba(45,106,79,0.11), transparent 34%);
        border-radius: 14px;
        padding: .85rem .85rem;
        box-shadow: 0 14px 35px rgba(31, 41, 55, 0.09), inset 0 1px 0 rgba(255,255,255,0.82);
        backdrop-filter: blur(16px) saturate(145%);
        -webkit-backdrop-filter: blur(16px) saturate(145%);
        position: relative;
        overflow: hidden;
        transition: transform 180ms ease, box-shadow 180ms ease, border-color 180ms ease;
    }
    body[data-theme="dark"] .monitor-kpi {
        background:
            linear-gradient(145deg, rgba(34, 30, 26, .88), rgba(14, 13, 12, .76)),
            radial-gradient(circle at 0% 0%, rgba(255,255,255,.07), transparent 34%) !important;
        border-color: rgba(226, 209, 192, .16) !important;
    }
    body[data-theme="dark"] .monitor-kpi-label,
    body[data-theme="dark"] .stat-label,
    body[data-theme="dark"] .portal-card-head,
    body[data-theme="dark"] .section-heading {
        color: #a99a8c;
    }
    body[data-theme="dark"] .monitor-kpi-value,
    body[data-theme="dark"] .stat-value,
    body[data-theme="dark"] .monitor-title,
    body[data-theme="dark"] .trend-title {
        color: #f7efe8;
    }
    body[data-theme="dark"] .monitor-kpi-sub,
    body[data-theme="dark"] .monitor-key,
    body[data-theme="dark"] .dash-hero p {
        color: #b9aa9d;
    }
    .monitor-kpi::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(120deg, rgba(255,255,255,0.54), transparent 42%);
        pointer-events: none;
    }
    .monitor-kpi:hover {
        transform: translateY(-3px);
        border-color: rgba(167,215,184,0.72);
        box-shadow: 0 20px 44px rgba(31, 41, 55, 0.13), inset 0 1px 0 rgba(255,255,255,0.88);
    }
    .monitor-kpi > * {
        position: relative;
        z-index: 1;
    }
    .monitor-kpi-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .5rem;
        margin-bottom: .45rem;
    }
    .monitor-kpi-label {
        font-size: .68rem;
        color: var(--c-text-muted);
        text-transform: uppercase;
        letter-spacing: .06em;
        font-weight: 700;
    }
    .monitor-kpi-value {
        font-size: 1.45rem;
        font-weight: 800;
        color: var(--c-text-primary);
        line-height: 1;
        font-variant-numeric: tabular-nums;
    }
    .monitor-kpi-sub {
        margin-top: .2rem;
        font-size: .72rem;
        color: var(--c-text-secondary);
    }
    .monitor-two-up {
        display: grid;
        grid-template-columns: .9fr 1.1fr;
        gap: .75rem;
    }
    .perf-circle-wrap {
        display: flex;
        justify-content: center;
        margin: .45rem 0 .85rem;
    }
    .perf-circle {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: conic-gradient(#2D6A4F var(--angle, 0deg), rgba(236,232,225,0.72) 0deg);
        box-shadow: 0 16px 34px rgba(45,106,79,0.13), inset 0 0 0 1px rgba(255,255,255,.6);
        position: relative;
    }
    .perf-circle::after {
        content: '';
        position: absolute;
        inset: 14px;
        border-radius: 50%;
        background: rgba(255,255,255,0.72);
        border: 1px solid rgba(255,255,255,0.82);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
    .perf-circle-text {
        position: relative;
        z-index: 1;
        text-align: center;
    }
    .perf-circle-text strong {
        display: block;
        font-size: 1.5rem;
        color: #1f3d33;
        line-height: 1.1;
        font-weight: 800;
    }
    .perf-circle-text span {
        font-size: .72rem;
        color: var(--c-text-secondary);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .trend-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: .45rem;
        gap: .75rem;
    }
    .trend-title {
        font-size: .88rem;
        font-weight: 700;
        color: var(--c-text-primary);
    }
    .trend-meta {
        font-size: .72rem;
        color: var(--c-text-muted);
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
    }
    .trend-chart {
        height: 180px;
        border: 1px solid rgba(255,255,255,0.68);
        border-radius: 14px;
        padding: .65rem .55rem .45rem;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: .4rem;
        background:
            linear-gradient(to top, rgba(107,101,96,.10) 1px, transparent 1px) 0 0/100% 33.33%,
            linear-gradient(145deg, rgba(255,255,255,0.38), rgba(255,255,255,0.12));
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.55);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }
    .trend-col {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: .35rem;
        width: 100%;
    }
    .trend-bar-wrap {
        width: 100%;
        flex: 1;
        display: flex;
        align-items: flex-end;
    }
    .trend-bar {
        width: 100%;
        border-radius: 8px 8px 3px 3px;
        background: linear-gradient(180deg, #67b190 0%, #2D6A4F 100%);
        min-height: 8px;
        opacity: .85;
        box-shadow: 0 8px 18px rgba(45,106,79,0.14);
    }
    .trend-col.active .trend-bar {
        background: linear-gradient(180deg, #7ca7ff 0%, #4f73ce 100%);
        opacity: 1;
    }
    .trend-day {
        font-size: .68rem;
        color: var(--c-text-secondary);
        font-weight: 700;
    }
    .trend-col.active .trend-day {
        color: #3d5faa;
    }
    body[data-theme="dark"] .perf-circle {
        background: conic-gradient(#5fbe91 var(--angle, 0deg), rgba(255,255,255,.10) 0deg);
        box-shadow: 0 18px 38px rgba(0,0,0,.28), inset 0 0 0 1px rgba(255,255,255,.10);
    }
    body[data-theme="dark"] .perf-circle::after {
        background: rgba(10, 9, 8, .72);
        border-color: rgba(255,255,255,.10);
    }
    body[data-theme="dark"] .perf-circle-text strong {
        color: #cdf7df;
    }
    body[data-theme="dark"] .trend-chart {
        background:
            linear-gradient(to top, rgba(255,255,255,.055) 1px, transparent 1px) 0 0/100% 33.33%,
            linear-gradient(145deg, rgba(255,255,255,.055), rgba(255,255,255,.018));
        border-color: rgba(226, 209, 192, .14);
        box-shadow: inset 0 1px 0 rgba(255,255,255,.06);
    }
    body[data-theme="dark"] .trend-bar {
        background: linear-gradient(180deg, #74d0a5 0%, #3f9a73 100%);
        box-shadow: 0 8px 18px rgba(95,190,145,.16);
    }
    body[data-theme="dark"] .trend-col.active .trend-bar {
        background: linear-gradient(180deg, #9ab7ff 0%, #5b7ee6 100%);
    }
    body[data-theme="dark"] .trend-col.active .trend-day {
        color: #a9c1ff;
    }
    body[data-theme="dark"] .meter-track {
        background: rgba(255,255,255,.13);
        box-shadow: inset 0 1px 2px rgba(0,0,0,.28);
    }
    body[data-theme="dark"] .monitor-item {
        border-bottom-color: rgba(226, 209, 192, .10);
    }
    body[data-theme="dark"] .monitor-pill.ok {
        background: rgba(95, 190, 145, .15);
        color: #cdf7df;
        border-color: rgba(95, 190, 145, .42);
    }

    /* Admin UX Identity v2 */
    .adash {
        position: relative;
    }
    .adash::before {
        content: '';
        position: fixed;
        inset: 0;
        pointer-events: none;
        background:
            radial-gradient(1100px 460px at -12% -18%, rgba(223, 198, 169, 0.22) 0%, transparent 55%),
            radial-gradient(900px 360px at 112% -10%, rgba(235, 220, 200, 0.22) 0%, transparent 52%);
        z-index: -1;
    }
    .portal-card,
    .data-card,
    .stat-card,
    .dash-hero {
        box-shadow: 0 2px 8px rgba(36, 26, 18, 0.07), 0 14px 30px rgba(61, 46, 34, 0.07);
        transition: transform 200ms ease, box-shadow 200ms ease, border-color 200ms ease;
    }
    .portal-card:hover,
    .data-card:hover,
    .stat-card:hover,
    .dash-hero:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(36, 26, 18, 0.12), 0 20px 36px rgba(61, 46, 34, 0.10);
        border-color: #dfccb6;
    }
    .data-card-head {
        background: linear-gradient(180deg, #fff 0%, #fbf5ee 100%);
        position: relative;
    }
    .data-card-head::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, #8f6f52 0%, #c7a98b 100%);
    }
    body[data-theme="dark"] .adash::before {
        background:
            radial-gradient(900px 420px at -12% -18%, rgba(215, 191, 168, .10) 0%, transparent 58%),
            radial-gradient(820px 360px at 112% -10%, rgba(95, 190, 145, .08) 0%, transparent 54%);
    }
    body[data-theme="dark"] .portal-card,
    body[data-theme="dark"] .data-card,
    body[data-theme="dark"] .stat-card,
    body[data-theme="dark"] .dash-hero {
        box-shadow: 0 16px 36px rgba(0, 0, 0, .26), inset 0 1px 0 rgba(255,255,255,.07);
    }
    body[data-theme="dark"] .portal-card:hover,
    body[data-theme="dark"] .data-card:hover,
    body[data-theme="dark"] .stat-card:hover,
    body[data-theme="dark"] .dash-hero:hover {
        border-color: rgba(215, 191, 168, .34);
        box-shadow: 0 24px 52px rgba(0, 0, 0, .36), inset 0 1px 0 rgba(255,255,255,.10);
    }
    body[data-theme="dark"] .data-card-head::before {
        background: linear-gradient(180deg, #5fbe91 0%, #d7bfa8 100%);
    }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            transition: none !important;
            animation: none !important;
        }
    }
    @media (max-width: 1180px) {
        .monitor-kpi-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .monitor-two-up { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .monitor-kpi-grid { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:600;color:var(--c-text-primary,#1A1714);">Dashboard Admin</h2>
@endsection

@section('content')
<div class="adash">

    @if (session('success'))
        <div class="ui-alert-success">{{ session('success') }}</div>
    @endif

    {{-- ── Hero ── --}}
    <div class="dash-hero">
        <div class="dash-hero-text">
            <span class="dash-hero-label">Overview</span>
            <h3>Dashboard Admin</h3>
            <p>
                @if($hasDisciplineAccess && $hasScholarshipAccess)
                    Gambaran keseluruhan modul disiplin dan scholarship.
                @elseif($hasDisciplineAccess)
                    Gambaran keseluruhan modul disiplin pelajar.
                @elseif($hasScholarshipAccess)
                    Gambaran keseluruhan modul scholarship pelajar.
                @else
                    Akaun ini tiada akses modul.
                @endif
            </p>
        </div>
        <div class="dash-hero-date">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="display:inline;vertical-align:-2px;margin-right:4px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            {{ now()->format('d M Y') }}
        </div>
    </div>

    {{-- ── Portal Utama ── --}}
    <div class="portal-card">
        <div class="portal-card-head">Portal Utama</div>
        <div class="portal-links">
            <a href="{{ route('admin.reports.monthly') }}" class="portal-link">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Laporan Bulanan
            </a>
            @if($hasDisciplineAccess)
                <a href="{{ route('admin.offenses.create') }}" class="portal-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    Daftar Kesalahan
                </a>
                <a href="{{ route('admin.offenses.index') }}" class="portal-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    Senarai Kesalahan
                </a>
                <a href="{{ route('admin.fine-applications.index') }}" class="portal-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    Permohonan Bayaran
                </a>
            @endif
            @if($hasScholarshipAccess)
                <a href="{{ route('admin.scholarships.index') }}" class="portal-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l-3.5-2M12 20l-9-5"/></svg>
                    Rekod Scholarship
                </a>
                <a href="{{ route('admin.scholarship-announcements.index') }}" class="portal-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    Pengumuman Scholarship
                </a>
            @endif
        </div>
    </div>

    @if(($showSystemMonitoring ?? false) && !empty($systemMonitoring))
        <p class="section-heading">System Monitoring</p>
        @php
            $cpuPercent = $systemMonitoring['cpu_percent'];
            $ramPercent = $systemMonitoring['ram_percent'];
            $diskPercent = $systemMonitoring['disk_percent'];
            $cpuState = $cpuPercent !== null && $cpuPercent >= 85 ? 'error' : ($cpuPercent !== null && $cpuPercent >= 70 ? 'warn' : '');
            $ramState = $ramPercent !== null && $ramPercent >= 85 ? 'error' : ($ramPercent !== null && $ramPercent >= 70 ? 'warn' : '');
            $diskState = $diskPercent !== null && $diskPercent >= 90 ? 'error' : ($diskPercent !== null && $diskPercent >= 75 ? 'warn' : '');
            $overallLoad = round(collect([$cpuPercent, $ramPercent, $diskPercent])->filter(fn ($v) => $v !== null)->avg() ?? 0, 1);
            $trendBase = $overallLoad > 0 ? $overallLoad : 42;
            $trend = [
                max(8, $trendBase - 18),
                max(8, $trendBase - 10),
                max(8, $trendBase - 7),
                max(8, $trendBase + 9),
                max(8, $trendBase - 3),
                max(8, $trendBase - 14),
                max(8, $trendBase - 6),
            ];
            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $activeTrendIndex = 3;
        @endphp

        <div class="monitor-grid" data-system-monitoring data-live-url="{{ route('admin.system-monitoring.live') }}" style="grid-template-columns:1fr;gap:.75rem;">
            <div class="monitor-kpi-grid">
                <div class="monitor-kpi">
                    <div class="monitor-kpi-top">
                        <span class="monitor-kpi-label">CPU Usage</span>
                        <span class="monitor-pill {{ $cpuState ?: 'ok' }}" data-monitor="cpu-pill">{{ $cpuPercent !== null ? number_format($cpuPercent, 1) . '%' : 'N/A' }}</span>
                    </div>
                    <div class="monitor-kpi-value" data-monitor="cpu-value">{{ $cpuPercent !== null ? number_format($cpuPercent, 1) . '%' : 'N/A' }}</div>
                    <div class="monitor-kpi-sub">Current processing load</div>
                </div>
                <div class="monitor-kpi">
                    <div class="monitor-kpi-top">
                        <span class="monitor-kpi-label">Memory Usage</span>
                        <span class="monitor-pill {{ $ramState ?: 'ok' }}" data-monitor="ram-pill">{{ $ramPercent !== null ? number_format($ramPercent, 1) . '%' : 'N/A' }}</span>
                    </div>
                    <div class="monitor-kpi-value" data-monitor="ram-value">{{ $systemMonitoring['ram_usage_text'] }}</div>
                    <div class="monitor-kpi-sub" data-monitor="ram-limit">Limit: {{ $systemMonitoring['ram_limit_text'] }}</div>
                </div>
                <div class="monitor-kpi">
                    <div class="monitor-kpi-top">
                        <span class="monitor-kpi-label">Disk Usage</span>
                        <span class="monitor-pill {{ $diskState ?: 'ok' }}" data-monitor="disk-pill">{{ $diskPercent !== null ? number_format($diskPercent, 1) . '%' : 'N/A' }}</span>
                    </div>
                    <div class="monitor-kpi-value" data-monitor="disk-value">{{ $systemMonitoring['disk_used_text'] }}</div>
                    <div class="monitor-kpi-sub" data-monitor="disk-total">Total: {{ $systemMonitoring['disk_total_text'] }}</div>
                </div>
                <div class="monitor-kpi">
                    <div class="monitor-kpi-top">
                        <span class="monitor-kpi-label">Database</span>
                        <span class="monitor-pill {{ $systemMonitoring['db_status'] === 'ok' ? 'ok' : 'error' }}" data-monitor="db-pill">DB {{ strtoupper($systemMonitoring['db_status']) }}</span>
                    </div>
                    <div class="monitor-kpi-value" data-monitor="db-value">{{ $systemMonitoring['maintenance'] ? 'Maintenance ON' : 'Healthy' }}</div>
                    <div class="monitor-kpi-sub" data-monitor="server-sub">Server: {{ $systemMonitoring['server_time'] }}</div>
                </div>
            </div>

            <div class="monitor-two-up">
                <div class="monitor-card">
                    <div class="monitor-head">
                        <span class="monitor-title">System Performance</span>
                        <span class="monitor-pill {{ $systemMonitoring['maintenance'] ? 'warn' : 'ok' }}" data-monitor="maintenance-pill">
                            {{ $systemMonitoring['maintenance'] ? 'Maintenance ON' : 'Maintenance OFF' }}
                        </span>
                    </div>

                    <div class="perf-circle-wrap">
                        <div class="perf-circle" data-monitor="overall-circle" style="--angle: {{ max(0, min(360, ($overallLoad / 100) * 360)) }}deg;">
                            <div class="perf-circle-text">
                                <strong data-monitor="overall-value">{{ number_format($overallLoad, 1) }}%</strong>
                                <span>Overall Load</span>
                            </div>
                        </div>
                    </div>

                    <div class="meter-wrap">
                        <div class="meter-row">
                            <span class="meter-label">CPU Usage</span>
                            <span class="meter-value" data-monitor="cpu-meter-value">{{ $cpuPercent !== null ? number_format($cpuPercent, 1) . '%' : 'N/A' }}</span>
                        </div>
                        <div class="meter-track"><div class="meter-fill {{ $cpuState }}" data-monitor="cpu-meter" style="width: {{ $cpuPercent !== null ? max(1, min(100, $cpuPercent)) : 0 }}%;"></div></div>
                    </div>
                    <div class="meter-wrap">
                        <div class="meter-row">
                            <span class="meter-label">Memory</span>
                            <span class="meter-value" data-monitor="ram-meter-value">{{ $ramPercent !== null ? number_format($ramPercent, 1) . '%' : 'N/A' }}</span>
                        </div>
                        <div class="meter-track"><div class="meter-fill {{ $ramState }}" data-monitor="ram-meter" style="width: {{ $ramPercent !== null ? max(1, min(100, $ramPercent)) : 0 }}%;"></div></div>
                    </div>
                    <div class="meter-wrap" style="margin-bottom:0;">
                        <div class="meter-row">
                            <span class="meter-label">Disk</span>
                            <span class="meter-value" data-monitor="disk-meter-value">{{ $diskPercent !== null ? number_format($diskPercent, 1) . '%' : 'N/A' }}</span>
                        </div>
                        <div class="meter-track"><div class="meter-fill {{ $diskState }}" data-monitor="disk-meter" style="width: {{ $diskPercent !== null ? max(1, min(100, $diskPercent)) : 0 }}%;"></div></div>
                    </div>
                </div>

                <div class="monitor-card">
                    <div class="trend-head">
                        <span class="trend-title">Resource Trend (Weekly)</span>
                        <span class="trend-meta">Last 7 Days</span>
                    </div>
                    <div class="trend-chart">
                        @foreach($trend as $i => $bar)
                            <div class="trend-col {{ $i === $activeTrendIndex ? 'active' : '' }}">
                                <div class="trend-bar-wrap">
                                    <div class="trend-bar" data-monitor-trend="{{ $i }}" style="height: {{ max(8, min(100, $bar)) }}%;"></div>
                                </div>
                                <span class="trend-day">{{ $days[$i] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="monitor-list" style="margin-top:.75rem;">
                        <div class="monitor-item"><span class="monitor-key">Server Time</span><span class="monitor-val" data-monitor="server-time">{{ $systemMonitoring['server_time'] }}</span></div>
                        <div class="monitor-item"><span class="monitor-key">PHP Version</span><span class="monitor-val" data-monitor="php-version">{{ $systemMonitoring['php_version'] }}</span></div>
                        <div class="monitor-item"><span class="monitor-key">Laravel Version</span><span class="monitor-val" data-monitor="laravel-version">{{ $systemMonitoring['laravel_version'] }}</span></div>
                        <div class="monitor-item"><span class="monitor-key">OS</span><span class="monitor-val" data-monitor="os">{{ $systemMonitoring['os'] }}</span></div>
                        <div class="monitor-item"><span class="monitor-key">1-min Load Avg</span><span class="monitor-val" data-monitor="load-1m">{{ $systemMonitoring['load_1m'] !== null ? number_format($systemMonitoring['load_1m'], 2) : 'N/A' }}</span></div>
                        <div class="monitor-item"><span class="monitor-key">RAM Peak</span><span class="monitor-val" data-monitor="ram-peak">{{ $systemMonitoring['ram_peak_text'] }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Discipline Module ── --}}
    @if($hasDisciplineAccess)
        <p class="section-heading">Disiplin</p>

        <div class="stats-grid">
            <div class="stat-card accent">
                <div class="stat-label">Jumlah Pelajar</div>
                <div class="stat-value">{{ $totalStudents }}</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-label">Jumlah Kesalahan</div>
                <div class="stat-value">{{ $totalOffenses }}</div>
            </div>
            <div class="stat-card red">
                <div class="stat-label">Kes Unpaid</div>
                <div class="stat-value">{{ $unpaidOffenses }}</div>
            </div>
            <div class="stat-card gold">
                <div class="stat-label">Permohonan Pending</div>
                <div class="stat-value">{{ $pendingFineApplications }}</div>
            </div>
        </div>

        <div class="two-col">
            <div class="data-card">
                <div class="data-card-head">
                    <strong>Rekod Kesalahan Terkini</strong>
                    <a class="btn-ghost" href="{{ route('admin.offenses.index') }}">
                        Lihat Semua
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                @if($recentOffenses->isEmpty())
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Tiada rekod kesalahan.
                    </div>
                @else
                    <div style="overflow-x:auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Pelajar</th>
                                    <th>No Matrik</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOffenses as $offense)
                                    <tr>
                                        <td style="font-weight:500;">{{ $offense->student_name }}</td>
                                        <td style="color:var(--c-text-secondary);font-family:monospace;font-size:0.8rem;">{{ $offense->matric_no }}</td>
                                        <td><span class="badge status-{{ $offense->status }}">{{ __($offense->status) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="data-card">
                <div class="data-card-head">
                    <strong>Permohonan Bayaran Terkini</strong>
                    <a class="btn-ghost" href="{{ route('admin.fine-applications.index') }}">
                        Semak
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                @if($recentFineApplications->isEmpty())
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.3"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        Tiada permohonan bayaran.
                    </div>
                @else
                    <div style="overflow-x:auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Pelajar</th>
                                    <th>Lokasi</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentFineApplications as $application)
                                    <tr>
                                        <td style="font-weight:500;">{{ $application->student_name }}</td>
                                        <td style="color:var(--c-text-secondary);">{{ $application->place }}</td>
                                        <td><span class="badge status-{{ $application->status }}">{{ __($application->status) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ── Scholarship Module ── --}}
    @if($hasScholarshipAccess)
        <p class="section-heading">Scholarship</p>

        <div class="stats-grid">
            <div class="stat-card accent">
                <div class="stat-label">Jumlah Rekod Scholarship</div>
                <div class="stat-value">{{ $totalScholarshipRecords }}</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-label">Scholarship Aktif</div>
                <div class="stat-value">{{ $activeScholarships }}</div>
            </div>
            <div class="stat-card gold">
                <div class="stat-label">Permohonan Pending</div>
                <div class="stat-value">{{ $pendingScholarships }}</div>
            </div>
            <div class="stat-card" style="border-left:3px solid var(--c-text-muted);">
                <div class="stat-label">Pengumuman Terkini</div>
                <div class="stat-value">{{ $recentScholarshipAnnouncements->count() }}</div>
            </div>
        </div>

        <div class="two-col">
            <div class="data-card">
                <div class="data-card-head">
                    <strong>Rekod Scholarship Terkini</strong>
                    <a class="btn-ghost" href="{{ route('admin.scholarships.index') }}">
                        Lihat Semua
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                @if($recentScholarshipRecords->isEmpty())
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6"/></svg>
                        Tiada rekod scholarship.
                    </div>
                @else
                    <div style="overflow-x:auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Pelajar</th>
                                    <th>No Matrik</th>
                                    <th>Jenis</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentScholarshipRecords as $record)
                                    <tr>
                                        <td style="font-weight:500;">{{ $record->student_name }}</td>
                                        <td style="color:var(--c-text-secondary);font-family:monospace;font-size:0.8rem;">{{ $record->matric_no }}</td>
                                        <td style="color:var(--c-text-secondary);">{{ $record->type }}</td>
                                        <td><span class="badge status-{{ $record->status }}">{{ __($record->status) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="data-card">
                <div class="data-card-head">
                    <strong>Pengumuman Scholarship Terkini</strong>
                    <a class="btn-ghost" href="{{ route('admin.scholarship-announcements.index') }}">
                        Semak
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                @if($recentScholarshipAnnouncements->isEmpty())
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.3"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        Tiada pengumuman scholarship.
                    </div>
                @else
                    <div style="overflow-x:auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tajuk</th>
                                    <th>Jenis</th>
                                    <th>Tarikh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentScholarshipAnnouncements as $news)
                                    <tr>
                                        <td style="font-weight:500;">{{ $news->title }}</td>
                                        <td style="color:var(--c-text-secondary);">{{ $news->type }}</td>
                                        <td style="color:var(--c-text-muted);font-size:0.78rem;white-space:nowrap;">{{ $news->created_at ? \Illuminate\Support\Carbon::parse($news->created_at)->format('d M Y') : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if(!$hasDisciplineAccess && !$hasScholarshipAccess)
        <div class="no-access">
            <div class="icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" style="color:#9E9892;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <strong style="font-size:0.9rem;color:var(--c-text-primary);">Tiada Akses Modul</strong>
            <p>Akses modul untuk akaun ini belum dikonfigurasi.</p>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-system-monitoring]');
    if (!root) return;

    const liveUrl = root.dataset.liveUrl;
    const $ = (name) => root.querySelector(`[data-monitor="${name}"]`);
    const formatPercent = (value) => value === null || value === undefined ? 'N/A' : `${Number(value).toFixed(1)}%`;
    const stateClass = (value, warnAt, errorAt) => {
        if (value === null || value === undefined) return 'ok';
        if (Number(value) >= errorAt) return 'error';
        if (Number(value) >= warnAt) return 'warn';
        return 'ok';
    };
    const setPill = (el, text, state) => {
        if (!el) return;
        el.textContent = text;
        el.classList.remove('ok', 'warn', 'error');
        el.classList.add(state);
    };
    const setText = (name, value) => {
        const el = $(name);
        if (el) el.textContent = value;
    };
    const setMeter = (name, value, state) => {
        const el = $(name);
        if (!el) return;
        el.style.width = value === null || value === undefined ? '0%' : `${Math.max(1, Math.min(100, Number(value)))}%`;
        el.classList.remove('warn', 'error');
        if (state !== 'ok') el.classList.add(state);
    };

    async function refreshMonitoring() {
        try {
            const response = await fetch(liveUrl, { headers: { Accept: 'application/json' } });
            if (!response.ok) return;

            const payload = await response.json();
            const data = payload.data || {};
            const cpuState = stateClass(data.cpu_percent, 70, 85);
            const ramState = stateClass(data.ram_percent, 70, 85);
            const diskState = stateClass(data.disk_percent, 75, 90);
            const dbState = data.db_status === 'ok' ? 'ok' : 'error';
            const maintenanceState = data.maintenance ? 'warn' : 'ok';

            setPill($('cpu-pill'), formatPercent(data.cpu_percent), cpuState);
            setText('cpu-value', formatPercent(data.cpu_percent));
            setText('cpu-meter-value', formatPercent(data.cpu_percent));
            setMeter('cpu-meter', data.cpu_percent, cpuState);

            setPill($('ram-pill'), formatPercent(data.ram_percent), ramState);
            setText('ram-value', data.ram_usage_text || '-');
            setText('ram-limit', `Limit: ${data.ram_limit_text || '-'}`);
            setText('ram-meter-value', formatPercent(data.ram_percent));
            setMeter('ram-meter', data.ram_percent, ramState);

            setPill($('disk-pill'), formatPercent(data.disk_percent), diskState);
            setText('disk-value', data.disk_used_text || '-');
            setText('disk-total', `Total: ${data.disk_total_text || '-'}`);
            setText('disk-meter-value', formatPercent(data.disk_percent));
            setMeter('disk-meter', data.disk_percent, diskState);

            setPill($('db-pill'), `DB ${(data.db_status || 'error').toUpperCase()}`, dbState);
            setText('db-value', data.maintenance ? 'Maintenance ON' : 'Healthy');
            setText('server-sub', `Server: ${data.server_time || '-'}`);
            setPill($('maintenance-pill'), data.maintenance ? 'Maintenance ON' : 'Maintenance OFF', maintenanceState);

            setText('overall-value', formatPercent(data.overall_load));
            const circle = $('overall-circle');
            if (circle) circle.style.setProperty('--angle', `${Math.max(0, Math.min(360, (Number(data.overall_load || 0) / 100) * 360))}deg`);

            setText('server-time', data.server_time || '-');
            setText('php-version', data.php_version || '-');
            setText('laravel-version', data.laravel_version || '-');
            setText('os', data.os || '-');
            setText('load-1m', data.load_1m === null || data.load_1m === undefined ? 'N/A' : Number(data.load_1m).toFixed(2));
            setText('ram-peak', data.ram_peak_text || '-');

            (data.trend || []).forEach((value, index) => {
                const bar = root.querySelector(`[data-monitor-trend="${index}"]`);
                if (bar) bar.style.height = `${Math.max(8, Math.min(100, Number(value)))}%`;
            });
        } catch (error) {
            // Keep the last rendered values if the live endpoint is temporarily unavailable.
        }
    }

    refreshMonitoring();
    setInterval(refreshMonitoring, 5000);
});
</script>
@endpush
