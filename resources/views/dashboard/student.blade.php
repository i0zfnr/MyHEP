@extends('layouts.app')

@section('title', __('Dashboard Pelajar'))

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;400;500;600&display=swap" rel="stylesheet">

<style>
/* â”€â”€ TOKENS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
:root {
    --sand-50:  #fdf9f6;
    --sand-100: #f5ede4;
    --sand-200: #ede0d2;
    --sand-300: #d9c5b0;
    --sand-400: #c2a48a;
    --sand-500: #a8876a;
    --sand-600: #8a6d52;
    --sand-700: #6e5440;
    --sand-800: #3d2e22;
    --sand-900: #1e1610;
    --gold:     #c9a84c;
    --gold-dim: #e8d49a;
    --danger:   #c0392b;
    --danger-bg:#fff1f0;
    --warn:     #b45309;
    --warn-bg:  #fffbeb;
    --success:  #166534;
    --success-bg:#f0fdf4;
    --radius-sm: 8px;
    --radius-md: 14px;
    --radius-lg: 20px;
    --shadow-card: 0 1px 3px rgba(61,46,34,.07), 0 4px 16px rgba(61,46,34,.05);
    --shadow-lift: 0 4px 24px rgba(61,46,34,.12);
    --font-display: 'DM Serif Display', Georgia, serif;
    --font-body:    'DM Sans', system-ui, sans-serif;
    --transition: 200ms cubic-bezier(.4,0,.2,1);
}

/* â”€â”€ BASE â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.sdash {
    max-width: 1200px;
    margin: 0 auto;
    padding: 1.5rem 1rem 3rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    font-family: var(--font-body);
}

/* â”€â”€ ALERTS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.alert {
    display: flex;
    align-items: flex-start;
    gap: .875rem;
    padding: 1rem 1.25rem;
    border-radius: var(--radius-md);
    border: 1px solid transparent;
    animation: slideDown .3s ease both;
}
.alert-icon {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.alert-body { flex: 1; }
.alert-body strong { display: block; font-size: .9rem; font-weight: 600; margin-bottom: 2px; }
.alert-body p { margin: 0; font-size: .83rem; line-height: 1.5; }
.alert-success { background: var(--success-bg); border-color: #bbf7d0; }
.alert-success .alert-icon { background: #dcfce7; color: var(--success); }
.alert-success strong, .alert-success p { color: var(--success); }
.alert-danger { background: var(--danger-bg); border-color: #fecaca; }
.alert-danger .alert-icon { background: #fee2e2; color: var(--danger); }
.alert-danger strong, .alert-danger p { color: var(--danger); }
.alert-warn { background: var(--warn-bg); border-color: #fde68a; }
.alert-warn .alert-icon { background: #fef3c7; color: var(--warn); }
.alert-warn strong, .alert-warn p { color: var(--warn); }

/* â”€â”€ HERO â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.hero {
    position: relative;
    border-radius: var(--radius-lg);
    background: var(--sand-800);
    padding: 2rem 2rem 1.75rem;
    overflow: hidden;
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    min-height: 148px;
}
.hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background:
        radial-gradient(ellipse 60% 80% at 85% 20%, rgba(201,168,76,.22) 0%, transparent 70%),
        radial-gradient(ellipse 40% 60% at 10% 90%, rgba(138,109,82,.35) 0%, transparent 60%);
    pointer-events: none;
}
.hero-text { position: relative; z-index: 1; }
.hero-eyebrow {
    font-family: var(--font-body);
    font-size: .72rem;
    font-weight: 500;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--gold-dim);
    margin: 0 0 .4rem;
}
.hero-name {
    font-family: var(--font-display);
    font-size: clamp(1.5rem, 4vw, 2.1rem);
    color: #fff;
    margin: 0 0 .3rem;
    line-height: 1.15;
}
.hero-sub {
    font-size: .84rem;
    color: rgba(255,255,255,.55);
    margin: 0;
    font-weight: 300;
}
.hero-badge {
    position: relative;
    z-index: 1;
    background: rgba(255,255,255,.07);
    border: 1px solid rgba(255,255,255,.14);
    border-radius: var(--radius-sm);
    padding: .6rem 1rem;
    text-align: right;
    flex-shrink: 0;
}
.hero-badge-label {
    font-size: .68rem;
    color: rgba(255,255,255,.45);
    text-transform: uppercase;
    letter-spacing: .08em;
    display: block;
    margin-bottom: 2px;
}
.hero-badge-value {
    font-family: var(--font-display);
    font-size: 1.1rem;
    color: var(--gold-dim);
}
.hero-right {
    position: relative;
    z-index: 1;
    display: flex;
    align-items: flex-end;
    gap: .7rem;
}
.hero-meta-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: .45rem;
    min-width: 280px;
}
.hero-meta-item {
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.14);
    border-radius: 10px;
    padding: .42rem .52rem;
    min-height: 56px;
}
.hero-meta-label {
    display: block;
    font-size: .58rem;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: rgba(255,255,255,.54);
    margin-bottom: 2px;
}
.hero-meta-value {
    display: block;
    font-size: .78rem;
    font-weight: 600;
    color: #fff;
    line-height: 1.25;
    word-break: break-word;
}
@media (max-width: 980px) {
    .hero {
        align-items: flex-start;
        flex-direction: column;
        gap: .8rem;
    }
    .hero-right {
        width: 100%;
        align-items: stretch;
        flex-direction: column;
    }
    .hero-badge {
        width: 100%;
        text-align: left;
    }
    .hero-meta-grid {
        width: 100%;
        min-width: 0;
    }
}
@media (max-width: 620px) {
    .hero-meta-grid {
        grid-template-columns: 1fr;
    }
}

/* â”€â”€ SECTION LABEL â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.section-label {
    font-size: .7rem;
    font-weight: 600;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--sand-400);
    margin-bottom: .6rem;
    padding-left: 2px;
}

/* â”€â”€ STATS GRID â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: .75rem;
}
@media (min-width: 560px) { .stats-grid { grid-template-columns: repeat(3, 1fr); } }
@media (min-width: 900px) { .stats-grid { grid-template-columns: repeat(5, 1fr); } }

.stat-card {
    background: #fff;
    border: 1px solid var(--sand-200);
    border-radius: var(--radius-md);
    padding: 1rem 1.1rem;
    box-shadow: var(--shadow-card);
    transition: transform var(--transition), box-shadow var(--transition);
    cursor: default;
}
.stat-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-lift);
}
.stat-icon {
    width: 32px;
    height: 32px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: .65rem;
}
.stat-icon svg { width: 16px; height: 16px; }
.stat-icon.sand { background: var(--sand-100); color: var(--sand-600); }
.stat-icon.red  { background: #fee2e2; color: #b91c1c; }
.stat-icon.gold { background: #fef9e7; color: #92670a; }
.stat-icon.amber{ background: #fff8e1; color: #b45309; }
.stat-icon.teal { background: #e6f7f4; color: #0f766e; }
.stat-label { font-size: .72rem; color: var(--sand-500); font-weight: 500; margin-bottom: .2rem; line-height: 1.3; }
.stat-value { font-size: 1.8rem; font-weight: 600; color: var(--sand-800); line-height: 1.1; font-variant-numeric: tabular-nums; }
.stat-value.sm { font-size: 1rem; font-weight: 600; text-transform: uppercase; letter-spacing: .03em; padding-top: .15rem; }
.stat-value.red  { color: #b91c1c; }
.stat-value.teal { color: #0f766e; }

/* â”€â”€ QUICK ACTIONS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.actions-row {
    display: flex;
    gap: .625rem;
    flex-wrap: wrap;
}
.action-btn {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    padding: .55rem 1rem;
    border-radius: var(--radius-sm);
    border: 1px solid var(--sand-300);
    background: #fff;
    color: var(--sand-700);
    font-size: .82rem;
    font-weight: 500;
    font-family: var(--font-body);
    text-decoration: none;
    cursor: pointer;
    transition: background var(--transition), border-color var(--transition), transform var(--transition), box-shadow var(--transition);
    box-shadow: var(--shadow-card);
}
.action-btn svg { width: 14px; height: 14px; flex-shrink: 0; }
.action-btn:hover {
    background: var(--sand-100);
    border-color: var(--sand-400);
    transform: translateY(-1px);
    box-shadow: var(--shadow-lift);
    color: var(--sand-800);
}
.action-btn:active { transform: scale(.98); }
.action-btn.primary {
    background: var(--sand-800);
    border-color: var(--sand-800);
    color: #fff;
}
.action-btn.primary:hover {
    background: var(--sand-700);
    border-color: var(--sand-700);
    color: #fff;
}

/* â”€â”€ PORTAL CARDS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.portal-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1rem;
}
@media (min-width: 700px) { .portal-grid { grid-template-columns: repeat(3, 1fr); } }

.portal-card {
    position: relative;
    background: #fff;
    border: 1px solid var(--sand-200);
    border-radius: var(--radius-md);
    padding: 1.4rem 1.25rem 1.1rem;
    box-shadow: var(--shadow-card);
    text-decoration: none;
    display: flex;
    flex-direction: column;
    gap: .5rem;
    transition: transform var(--transition), box-shadow var(--transition), border-color var(--transition);
    overflow: hidden;
}
.portal-card::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: var(--sand-300);
    transform: scaleX(0);
    transform-origin: left;
    transition: transform .25s ease;
}
.portal-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-lift);
    border-color: var(--sand-300);
}
.portal-card:hover::after { transform: scaleX(1); }
.portal-card.scholarship::after { background: var(--gold); }
.portal-card.offense::after    { background: #e57373; }
.portal-card.profile::after    { background: var(--sand-500); }

.portal-card-icon {
    width: 40px;
    height: 40px;
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: .25rem;
}
.portal-card-icon svg { width: 20px; height: 20px; }
.portal-card-icon.gold  { background: #fef9e7; color: #92670a; }
.portal-card-icon.red   { background: #fee2e2; color: #b91c1c; }
.portal-card-icon.sand  { background: var(--sand-100); color: var(--sand-600); }

.portal-card h4 {
    margin: 0;
    font-size: .95rem;
    font-weight: 600;
    color: var(--sand-800);
    font-family: var(--font-body);
}
.portal-card p {
    margin: 0;
    font-size: .81rem;
    color: var(--sand-500);
    line-height: 1.5;
    flex: 1;
}
.portal-card-cta {
    margin-top: .25rem;
    font-size: .78rem;
    font-weight: 600;
    color: var(--sand-600);
    display: flex;
    align-items: center;
    gap: .3rem;
}
.portal-card-cta svg { width: 13px; height: 13px; transition: transform var(--transition); }
.portal-card:hover .portal-card-cta svg { transform: translateX(3px); }

/* â”€â”€ DIVIDER WITH LABEL â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
.divider-row {
    display: flex;
    align-items: center;
    gap: .75rem;
}
.divider-row::before,
.divider-row::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--sand-200);
}

/* â”€â”€ ANIMATIONS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
@keyframes slideDown {
    from { opacity: 0; transform: translateY(-8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.sdash > * {
    animation: slideDown .35s ease both;
}
.sdash > *:nth-child(1) { animation-delay: .04s; }
.sdash > *:nth-child(2) { animation-delay: .08s; }
.sdash > *:nth-child(3) { animation-delay: .12s; }
.sdash > *:nth-child(4) { animation-delay: .16s; }
.sdash > *:nth-child(5) { animation-delay: .20s; }
.sdash > *:nth-child(6) { animation-delay: .24s; }
.sdash > *:nth-child(7) { animation-delay: .28s; }

@media (prefers-reduced-motion: reduce) {
    .sdash > *, .stat-card, .portal-card, .action-btn { animation: none; transition: none; }
}
    /* Student UX Identity v2 */
    :root {
        --stu-ink: #1f1d1a;
        --stu-muted: #6f675f;
        --stu-line: #e9ded1;
        --stu-soft: #fbf7f1;
        --stu-accent: #7b5b43;
        --stu-accent-2: #b69172;
        --stu-glow: rgba(123, 91, 67, 0.18);
    }
    body {
        background:
            radial-gradient(1200px 520px at -8% -18%, #efe2d4 0%, transparent 56%),
            radial-gradient(900px 350px at 108% -14%, #f3e9de 0%, transparent 54%),
            linear-gradient(180deg, #faf7f2 0%, #f5efe7 100%);
    }
    .wrap,
    .student-dashboard,
    .dash-student,
    .sdash,
    .adash {
        width: min(1160px, 100%);
    }
    .card,
    .panel,
    .box,
    .stat,
    .summary,
    .tile {
        border: 1px solid var(--stu-line) !important;
        border-radius: 16px;
        background: linear-gradient(180deg, #fff 0%, #fffdfa 100%);
        box-shadow: 0 1px 2px rgba(31, 29, 26, 0.07), 0 10px 24px rgba(61, 46, 34, 0.06);
        transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
    }
    .card:hover,
    .panel:hover,
    .box:hover,
    .stat:hover,
    .summary:hover,
    .tile:hover {
        transform: translateY(-2px);
        border-color: #dcc7b0 !important;
        box-shadow: 0 5px 14px rgba(31, 29, 26, 0.11), 0 18px 30px rgba(61, 46, 34, 0.10);
    }
    .head,
    .card h2,
    .card h3,
    .section-head,
    .panel-head {
        position: relative;
        background: linear-gradient(180deg, #fff 0%, #fbf4ec 100%);
    }
    .head::before,
    .card h2::before,
    .card h3::before,
    .section-head::before,
    .panel-head::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, var(--stu-accent) 0%, var(--stu-accent-2) 100%);
    }
    .btn,
    .button,
    button[type='submit'],
    input[type='submit'] {
        border-radius: 10px;
        border: 1px solid #ceb79f;
        background: linear-gradient(180deg, #ffffff 0%, #f9f3ec 100%);
        color: #685141;
        font-weight: 700;
        transition: transform 170ms ease, box-shadow 170ms ease, border-color 170ms ease, color 170ms ease;
    }
    .btn:hover,
    .button:hover,
    button[type='submit']:hover,
    input[type='submit']:hover {
        transform: translateY(-1px);
        border-color: #ba9b7b;
        box-shadow: 0 8px 16px rgba(97, 73, 52, 0.13);
    }
    .btn-primary,
    .primary,
    .is-primary {
        border-color: #7f6249 !important;
        background: linear-gradient(135deg, #7b5b43 0%, #b69172 100%) !important;
        color: #fff !important;
    }
    input,
    select,
    textarea {
        border-color: #decdb8 !important;
        background: #fffdfb;
        color: var(--stu-ink);
        transition: border-color 150ms ease, box-shadow 150ms ease, background-color 150ms ease;
    }
    input:focus,
    select:focus,
    textarea:focus {
        border-color: #b89576 !important;
        box-shadow: 0 0 0 4px rgba(184, 149, 118, 0.18);
        outline: none;
        background: #fff;
    }
    .filters,
    .toolbar,
    .search-bar {
        background: linear-gradient(180deg, #fffdfb 0%, #faf3eb 100%);
        border-top: 1px solid #efe2d5;
        border-bottom: 1px solid #efe2d5;
    }
    table tbody tr {
        transition: background-color 140ms ease;
    }
    table tbody tr:hover {
        background: #fcf7f1;
    }
    .badge,
    .status,
    .pill {
        border-radius: 999px;
        font-weight: 700;
    }
    @media (max-width: 980px) {
        .head,
        .toolbar,
        .actions {
            align-items: flex-start;
        }
        .head > div,
        .head form,
        .toolbar > div,
        .toolbar form {
            width: 100%;
        }
        .stats,
        .summary-grid,
        .cards-grid {
            grid-template-columns: 1fr !important;
        }
        table th,
        table td {
            font-size: 12px !important;
            padding: 9px 10px !important;
        }
    }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:600;color:var(--sand-800);font-family:'DM Sans',system-ui,sans-serif;">
        {{ __('Dashboard Pelajar') }}
    </h2>
@endsection

@section('content')
@php
    $studentName = $studentProfile->full_name ?? ($authUser['name'] ?? __('Pelajar'));
    $studentMatric = $studentProfile->matric_no ?? ($authUser['matric_no'] ?? '-');
    $studentProgram = $studentProfile->program ?? ($authUser['program'] ?? '-');
    $studentSemester = $studentProfile->semester ?? ($authUser['semester'] ?? '-');
    $studentSession = $studentProfile->academic_session ?? '-';
    $studentIcNo = $studentProfile->ic_no ?? '-';
    $jsLocale = app()->getLocale() === 'ms' ? 'ms-MY' : 'en-GB';
@endphp
<div class="sdash">

    {{-- â”€â”€ SUCCESS FLASH â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    @if(session('success'))
    <div class="alert alert-success" role="alert">
        <div class="alert-icon">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
        </div>
        <div class="alert-body">
            <strong>{{ __('Berjaya') }}</strong>
            <p>{{ session('success') }}</p>
        </div>
    </div>
    @endif

    {{-- â”€â”€ UNPAID FINE ALERT â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    @if($showPaymentAlert ?? false)
    <div class="alert alert-danger" role="alert">
        <div class="alert-icon">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
        </div>
        <div class="alert-body">
            <strong>{{ __('Anda mempunyai denda yang belum dibayar') }}</strong>
            <p>{{ __('Sila semak rekod kesalahan dan hantar permohonan bayaran dengan segera untuk mengelakkan tindakan lanjut.') }}</p>
        </div>
        <a href="{{ route('student.offenses.index') }}" class="action-btn primary" style="flex-shrink:0;align-self:center;">
            {{ __('Bayar Sekarang') }}
        </a>
    </div>
    @endif

    {{-- â”€â”€ SCHOLARSHIP FORM ALERT â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    @if($needsScholarshipStatusSubmission ?? false)
    <div class="alert alert-warn" role="alert">
        <div class="alert-icon">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
        </div>
        <div class="alert-body">
            <strong>{{ __('Lengkapkan Borang Data Biasiswa') }}</strong>
            <p>{{ __('Sila hantar status biasiswa anda untuk tujuan pengumpulan data pelajar Politeknik Besut.') }}</p>
        </div>
        <a href="{{ route('student.scholarship-status.form') }}" class="action-btn" style="flex-shrink:0;align-self:center;">
            {{ __('Isi Borang') }}
        </a>
    </div>
    @endif

    {{-- â”€â”€ HERO â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div class="hero">
        <div class="hero-text">
            <p class="hero-eyebrow">{{ __('MyHEP POLIBESUT') }}</p>
            <h3 class="hero-name">{{ __('Selamat Datang,') }}<br>{{ $studentName }}</h3>
            <p class="hero-sub">{{ $studentMatric }} &nbsp;&middot;&nbsp; {{ $studentProgram }}</p>
        </div>
        <div class="hero-right">
            <div class="hero-badge">
                <span class="hero-badge-label">{{ __('Semester') }}</span>
                <span class="hero-badge-value">{{ $studentSemester ?: '-' }}</span>
            </div>
            <div class="hero-meta-grid">
                <div class="hero-meta-item">
                    <span class="hero-meta-label">{{ __('Sesi') }}</span>
                    <span class="hero-meta-value">{{ $studentSession ?: '-' }}</span>
                </div>
                <div class="hero-meta-item">
                    <span class="hero-meta-label">{{ __('Kelas') }}</span>
                    <span class="hero-meta-value">{{ $studentProgram ?: '-' }}</span>
                </div>
                <div class="hero-meta-item">
                    <span class="hero-meta-label">{{ __('Tarikh') }}</span>
                    <span class="hero-meta-value" id="heroTodayDate">-</span>
                </div>
                <div class="hero-meta-item">
                    <span class="hero-meta-label">{{ __('Masa') }}</span>
                    <span class="hero-meta-value" id="heroClock">-</span>
                </div>
                <div class="hero-meta-item" style="grid-column: 1 / -1;">
                    <span class="hero-meta-label">{{ __('No. IC') }}</span>
                    <span class="hero-meta-value">{{ $studentIcNo ?: '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- â”€â”€ STATS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div>
        <p class="section-label">{{ __('Ringkasan Akaun') }}</p>
        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-icon sand">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2.5a.75.75 0 000 1.5h2.5v2.5a.75.75 0 001.5 0v-2.5h2.5a.75.75 0 000-1.5h-2.5v-2.5z" clip-rule="evenodd"/></svg>
                </div>
                <div class="stat-label">{{ __('Jumlah Kesalahan') }}</div>
                <div class="stat-value">{{ $totalOffenses ?? 0 }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                </div>
                <div class="stat-label">{{ __('Denda Belum Bayar') }}</div>
                <div class="stat-value {{ ($unpaidOffenses ?? 0) > 0 ? 'red' : '' }}">{{ $unpaidOffenses ?? 0 }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon gold">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 10.818v2.614A3.13 3.13 0 0011.888 13c.482-.315.612-.648.612-.875 0-.227-.13-.56-.612-.875a3.13 3.13 0 00-1.138-.432zM8.33 8.62c.053.055.115.11.184.164.208.16.46.284.736.363V6.603a2.45 2.45 0 00-.35.13c-.14.065-.27.143-.386.233-.377.292-.514.627-.514.909 0 .184.058.39.33.615z"/><path fill-rule="evenodd" d="M9.25 3.75a.75.75 0 00-1.5 0V4.5c-1.113.259-2 1.01-2 2.136 0 .828.433 1.476 1.02 1.898.529.38 1.186.58 1.73.718v2.898a3.84 3.84 0 01-.585-.234 1.698 1.698 0 01-.346-.244.75.75 0 00-1.06 1.06c.188.188.42.35.676.483.51.264 1.12.413 1.815.43V14.25a.75.75 0 001.5 0v-.82c1.113-.258 2-1.01 2-2.136 0-.828-.433-1.476-1.02-1.898-.529-.38-1.186-.58-1.73-.718V6.08c.2.033.38.085.534.157.19.088.344.204.463.337a.75.75 0 101.103-1.017 3.246 3.246 0 00-.848-.613 4.53 4.53 0 00-1.252-.33V3.75z" clip-rule="evenodd"/></svg>
                </div>
                <div class="stat-label">{{ __('Biasiswa Aktif') }}</div>
                <div class="stat-value">{{ $activeScholarships ?? 0 }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon amber">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.988 3.012A2.25 2.25 0 0118 5.25v6.5A2.25 2.25 0 0115.75 14H13.5v-3.379a3 3 0 00-.879-2.121l-3.12-3.121a3 3 0 00-1.402-.791V2.25A2.25 2.25 0 0110.25 0h4.5a2.25 2.25 0 011.238.012zM11.5 3.25a.75.75 0 01.75-.75h1.5a.75.75 0 010 1.5h-1.5a.75.75 0 01-.75-.75zm.75 4.25a.75.75 0 000 1.5h.5a.75.75 0 000-1.5h-.5z" clip-rule="evenodd"/><path d="M3.5 6A1.5 1.5 0 002 7.5v9A1.5 1.5 0 003.5 18h7a1.5 1.5 0 001.5-1.5v-5.879a1.5 1.5 0 00-.44-1.06L8.44 6.439A1.5 1.5 0 007.378 6H3.5z"/></svg>
                </div>
                <div class="stat-label">{{ __('Permohonan Bayaran') }}</div>
                <div class="stat-value">{{ $pendingFineApplications ?? 0 }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon teal">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M6.5 3c-1.051 0-2.093.04-3.125.117A1.49 1.49 0 002 4.607V10.5h9V4.606c0-.771-.59-1.43-1.375-1.489A41.568 41.568 0 006.5 3zM2 12v2.5A1.5 1.5 0 003.5 16h.041a3 3 0 015.918 0h.791a.75.75 0 00.75-.75V12H2z"/><path d="M6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM13.25 5a.75.75 0 00-.75.75v8.514a3.001 3.001 0 014.893 1.44c.37-.275.607-.714.607-1.204V7.5a2.5 2.5 0 00-2.5-2.5h-2.25zM14.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/></svg>
                </div>
                <div class="stat-label">{{ __('Status Stiker') }}</div>
                <div class="stat-value sm {{ ($stickerStatusLabel ?? 'none') === 'approved' ? 'teal' : '' }}">
                    {{ $stickerStatusLabel ?? __('Tiada') }}
                </div>
            </div>

        </div>
    </div>

    {{-- â”€â”€ QUICK ACTIONS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div>
        <p class="section-label">{{ __('Tindakan Pantas') }}</p>
        <div class="actions-row">
            <a href="{{ route('student.offenses.index') }}" class="action-btn primary">
                <svg viewBox="0 0 16 16" fill="currentColor"><path d="M8.5 4.5a.5.5 0 00-1 0v3h-3a.5.5 0 000 1h3v3a.5.5 0 001 0v-3h3a.5.5 0 000-1h-3v-3z"/></svg>
                {{ __('Mohon Bayaran Denda') }}
            </a>
            <a href="{{ route('student.vehicle-stickers.index') }}" class="action-btn">
                <svg viewBox="0 0 16 16" fill="currentColor"><path d="M3 2a1 1 0 00-1 1v1a1 1 0 001 1h1v1.5a.5.5 0 001 0V5h6v1.5a.5.5 0 001 0V5h1a1 1 0 001-1V3a1 1 0 00-1-1H3zm1 5.5a1.5 1.5 0 100 3 1.5 1.5 0 000-3zm7 0a1.5 1.5 0 100 3 1.5 1.5 0 000-3z"/></svg>
                {{ __('Mohon Stiker Kenderaan') }}
            </a>
            <a href="{{ route('student.rules.index') }}" class="action-btn">
                <svg viewBox="0 0 16 16" fill="currentColor"><path d="M14 4.5V14a2 2 0 01-2 2H4a2 2 0 01-2-2V2a2 2 0 012-2h5.5L14 4.5zm-3 0A1.5 1.5 0 019.5 3V1H4a1 1 0 00-1 1v12a1 1 0 001 1h8a1 1 0 001-1V4.5h-2z"/><path d="M5 8h6v1H5V8zm0 2h6v1H5v-1zm0 2h4v1H5v-1z"/></svg>
                {{ __('Lihat Peraturan') }}
            </a>
            <a href="{{ route('student.profile') }}" class="action-btn">
                <svg viewBox="0 0 16 16" fill="currentColor"><path d="M8 8a3 3 0 100-6 3 3 0 000 6zm2-3a2 2 0 11-4 0 2 2 0 014 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/></svg>
                {{ __('Profil Saya') }}
            </a>
        </div>
    </div>

    {{-- â”€â”€ PORTAL CARDS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div>
        <p class="section-label">{{ __('Portal Utama') }}</p>
        <div class="portal-grid">

            <a href="{{ route('student.scholarships.index') }}" class="portal-card scholarship">
                <div class="portal-card-icon gold">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 10.818v2.614A3.13 3.13 0 0011.888 13c.482-.315.612-.648.612-.875 0-.227-.13-.56-.612-.875a3.13 3.13 0 00-1.138-.432zM8.33 8.62c.053.055.115.11.184.164.208.16.46.284.736.363V6.603a2.45 2.45 0 00-.35.13c-.14.065-.27.143-.386.233-.377.292-.514.627-.514.909 0 .184.058.39.33.615z"/><path fill-rule="evenodd" d="M9.25 3.75a.75.75 0 00-1.5 0V4.5c-1.113.259-2 1.01-2 2.136 0 .828.433 1.476 1.02 1.898.529.38 1.186.58 1.73.718v2.898a3.84 3.84 0 01-.585-.234 1.698 1.698 0 01-.346-.244.75.75 0 00-1.06 1.06c.188.188.42.35.676.483.51.264 1.12.413 1.815.43V14.25a.75.75 0 001.5 0v-.82c1.113-.258 2-1.01 2-2.136 0-.828-.433-1.476-1.02-1.898-.529-.38-1.186-.58-1.73-.718V6.08c.2.033.38.085.534.157.19.088.344.204.463.337a.75.75 0 101.103-1.017 3.246 3.246 0 00-.848-.613 4.53 4.53 0 00-1.252-.33V3.75z" clip-rule="evenodd"/></svg>
                </div>
                <h4>{{ __('Scholarship & Bantuan') }}</h4>
                <p>{{ __('Rekod bantuan, status permohonan, bukti penerimaan, dan pengumuman terkini.') }}</p>
                <span class="portal-card-cta">
                    {{ __('Buka portal') }}
                    <svg viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M1 8a.5.5 0 01.5-.5h11.793l-3.147-3.146a.5.5 0 01.708-.708l4 4a.5.5 0 010 .708l-4 4a.5.5 0 01-.708-.708L13.293 8.5H1.5A.5.5 0 011 8z" clip-rule="evenodd"/></svg>
                </span>
            </a>

            <a href="{{ route('student.offenses.index') }}" class="portal-card offense">
                <div class="portal-card-icon red">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                </div>
                <h4>{{ __('Rekod Kesalahan & Denda') }}</h4>
                <p>{{ __('Semak kesalahan, status denda, sejarah, dan hantar permohonan bayaran kepada pentadbir.') }}</p>
                <span class="portal-card-cta">
                    {{ __('Buka portal') }}
                    <svg viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M1 8a.5.5 0 01.5-.5h11.793l-3.147-3.146a.5.5 0 01.708-.708l4 4a.5.5 0 010 .708l-4 4a.5.5 0 01-.708-.708L13.293 8.5H1.5A.5.5 0 011 8z" clip-rule="evenodd"/></svg>
                </span>
            </a>

            <a href="{{ route('student.profile') }}" class="portal-card profile">
                <div class="portal-card-icon sand">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z"/></svg>
                </div>
                <h4>{{ __('Profil Pelajar') }}</h4>
                <p>{{ __('Kemaskini maklumat peribadi, nombor hubungan, dan tukar kata laluan akaun.') }}</p>
                <span class="portal-card-cta">
                    {{ __('Buka portal') }}
                    <svg viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M1 8a.5.5 0 01.5-.5h11.793l-3.147-3.146a.5.5 0 01.708-.708l4 4a.5.5 0 010 .708l-4 4a.5.5 0 01-.708-.708L13.293 8.5H1.5A.5.5 0 011 8z" clip-rule="evenodd"/></svg>
                </span>
            </a>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(() => {
    const dateNode = document.getElementById('heroTodayDate');
    const timeNode = document.getElementById('heroClock');
    if (!dateNode || !timeNode) return;

    const locale = @json($jsLocale);
    const updateClock = () => {
        const now = new Date();
        dateNode.textContent = now.toLocaleDateString(locale, {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
        timeNode.textContent = now.toLocaleTimeString(locale, {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });
    };

    updateClock();
    setInterval(updateClock, 1000);
})();
</script>
@endpush

