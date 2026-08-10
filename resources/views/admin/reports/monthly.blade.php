@extends('layouts.app')

@section('title', __('Monthly Operations Report'))

@push('styles')
<style>
    .monthly-report { width:min(1320px,100%); margin:0 auto; display:grid; gap:18px; color:var(--text); }
    .report-hero { display:flex; align-items:center; justify-content:space-between; gap:24px; padding:22px 24px; border:1px solid var(--border); border-radius:20px; background:linear-gradient(135deg,var(--surface),var(--surface-soft)); box-shadow:var(--se-shadow-sm); }
    .report-hero-copy { min-width:0; }
    .report-eyebrow { display:block; margin-bottom:7px; color:var(--primary-dark); font-size:.7rem; font-weight:850; letter-spacing:.14em; text-transform:uppercase; }
    .report-hero h1 { margin:0; color:var(--text); font-size:clamp(1.65rem,3vw,2.25rem); line-height:1.08; letter-spacing:-.045em; }
    .report-hero p { max-width:680px; margin:9px 0 0; color:var(--text-muted); font-size:.88rem; line-height:1.6; }
    .report-actions { display:flex; align-items:flex-end; justify-content:flex-end; gap:10px; flex-wrap:wrap; }
    .report-actions form { display:flex; gap:8px; align-items:flex-end; }
    .report-month-field { display:grid; gap:6px; }
    .report-month-field label { color:var(--text-muted); font-size:.68rem; font-weight:800; letter-spacing:.07em; text-transform:uppercase; }
    .report-month-field input { min-width:185px; }
    .report-scope { display:flex; align-items:center; gap:8px; margin-top:13px; color:var(--text-muted); font-size:.76rem; }
    .report-scope strong { color:var(--text); }
    .report-scope span { padding:5px 10px; border:1px solid var(--border); border-radius:999px; background:var(--surface); }

    .report-module { display:grid; gap:16px; scroll-margin-top:90px; }
    .report-jump-nav { display:flex; gap:8px; flex-wrap:wrap; }
    .report-jump-nav a { display:inline-flex; align-items:center; padding:7px 11px; border:1px solid var(--border); border-radius:999px; background:var(--surface); color:var(--text-muted); text-decoration:none; font-size:.72rem; font-weight:800; }
    .report-jump-nav a:hover { border-color:var(--primary); color:var(--primary-dark); }
    .report-module-head { display:flex; justify-content:space-between; align-items:flex-end; gap:18px; }
    .report-module-head h2 { margin:3px 0 0; color:var(--text); font-size:1.3rem; letter-spacing:-.025em; }
    .report-module-head p { margin:5px 0 0; color:var(--text-muted); font-size:.8rem; }
    .report-module-badge { display:inline-flex; align-items:center; gap:7px; padding:7px 11px; border:1px solid var(--border); border-radius:999px; background:var(--surface); color:var(--text-muted); font-size:.7rem; font-weight:800; white-space:nowrap; }
    .report-module-badge::before { content:''; width:7px; height:7px; border-radius:50%; background:var(--se-success); box-shadow:0 0 0 4px var(--se-success-soft); }

    .report-kpis { display:grid; grid-template-columns:repeat(5,minmax(0,1fr)); gap:12px; perspective:1300px; }
    .report-kpi { position:relative; min-width:0; padding:18px; border:1px solid var(--border); border-top:3px solid var(--kpi-accent,var(--primary)); border-radius:16px; background:linear-gradient(145deg,var(--surface),var(--surface-soft)); box-shadow:10px 13px 24px rgba(48,34,22,.11),inset 0 1px 0 rgba(255,255,255,.82); overflow:hidden; transform:rotateX(2deg) rotateY(-1deg); transform-style:preserve-3d; }
    .report-kpi::after { content:''; position:absolute; width:90px; height:90px; top:-52px; right:-42px; border-radius:50%; background:color-mix(in srgb,var(--kpi-accent,var(--primary)) 13%,transparent); }
    .report-kpi-label { display:block; min-height:30px; color:var(--text-muted); font-size:.72rem; font-weight:750; line-height:1.35; }
    .report-kpi-value { display:block; margin-top:8px; color:var(--text); font-size:clamp(1.65rem,3vw,2.2rem); font-weight:850; line-height:1; letter-spacing:-.045em; font-variant-numeric:tabular-nums; }
    .report-kpi-note { display:block; margin-top:9px; color:var(--text-muted); font-size:.67rem; line-height:1.4; }
    .tone-slate { --kpi-accent:#64748b; }
    .tone-gold { --kpi-accent:#c48628; }
    .tone-blue { --kpi-accent:#5375c5; }
    .tone-green { --kpi-accent:#3f8f69; }
    .tone-violet { --kpi-accent:#7258bd; }
    .tone-red { --kpi-accent:#c14f5c; }

    .report-grid { display:grid; grid-template-columns:minmax(0,1.45fr) minmax(340px,.85fr); gap:16px; }
    .report-card { min-width:0; border:1px solid var(--border); border-radius:18px; background:linear-gradient(145deg,var(--surface),var(--surface-soft)); box-shadow:14px 18px 34px rgba(48,34,22,.13),inset 0 1px 0 rgba(255,255,255,.84); overflow:hidden; transform-style:preserve-3d; }
    .report-card-head { padding:19px 21px 0; }
    .report-card-kicker { color:var(--primary-dark); font-size:.67rem; font-weight:850; letter-spacing:.13em; text-transform:uppercase; }
    .report-card h3 { margin:5px 0 0; color:var(--text); font-size:1.05rem; letter-spacing:-.018em; }
    .report-card-copy { margin:5px 0 0; color:var(--text-muted); font-size:.75rem; line-height:1.5; }
    .report-card-body { padding:20px 21px 22px; }
    .report-empty { min-height:190px; display:grid; place-items:center; padding:24px; text-align:center; }
    .report-empty-mark { width:46px; height:46px; margin:0 auto 11px; display:grid; place-items:center; border-radius:14px; background:var(--surface-soft); border:1px solid var(--border); color:var(--primary-dark); font-size:1.25rem; font-weight:850; }
    .report-empty strong { display:block; color:var(--text); font-size:.92rem; }
    .report-empty span { display:block; max-width:350px; margin:5px auto 0; color:var(--text-muted); font-size:.74rem; line-height:1.5; }

    .report-bars { min-height:260px; display:flex; align-items:stretch; gap:12px; padding:12px 8px 0; perspective:900px; transform:rotateX(3deg); transform-origin:bottom; }
    .report-bar-group { flex:1; min-width:0; display:grid; grid-template-rows:1fr auto; gap:10px; }
    .report-bar-stage { min-height:205px; display:flex; align-items:flex-end; justify-content:center; gap:6px; padding:0 3px; border-bottom:1px solid var(--border); background:repeating-linear-gradient(to top,transparent 0,transparent 50px,color-mix(in srgb,var(--border) 65%,transparent) 51px); }
    .report-bar { position:relative; width:min(24px,38%); min-height:4px; height:max(4px,var(--bar-height)); border-radius:4px 4px 1px 1px; background:linear-gradient(90deg,#9a641f,#d99c45 52%,#b67826); box-shadow:8px 9px 14px rgba(91,56,20,.22); transform:translateZ(14px); transform-style:preserve-3d; }
    .report-bar::before { content:''; position:absolute; left:4px; right:-6px; top:-6px; height:6px; background:#efc47f; transform:skewX(-45deg); transform-origin:bottom; }
    .report-bar::after { content:''; position:absolute; top:-3px; right:-6px; width:6px; height:calc(100% + 3px); background:#855419; transform:skewY(-45deg); transform-origin:left; }
    .report-bar.secondary { background:linear-gradient(90deg,#285f46,#64b889 52%,#3d805f); box-shadow:8px 9px 14px rgba(35,91,64,.2); }
    .report-bar.secondary::before { background:#91d4ad; }
    .report-bar.secondary::after { background:#24563f; }
    .report-bar[data-zero="true"] { opacity:.22; }
    .report-bar-value { position:absolute; left:50%; bottom:calc(100% + 5px); transform:translateX(-50%); color:var(--text); font-size:.62rem; font-weight:850; font-variant-numeric:tabular-nums; }
    .report-bar-label { text-align:center; color:var(--text-muted); font-size:.65rem; line-height:1.25; }
    .report-bar-label strong { display:block; color:var(--text); font-size:.7rem; }
    .report-legend { display:flex; justify-content:center; gap:16px; flex-wrap:wrap; margin-top:15px; color:var(--text-muted); font-size:.68rem; }
    .report-legend span { display:inline-flex; align-items:center; gap:6px; }
    .report-legend i { width:8px; height:8px; border-radius:2px; background:#b47a2d; }
    .report-legend i.secondary { background:#3f8f69; }

    .report-donut-layout { min-height:260px; display:grid; grid-template-columns:minmax(160px,1fr) minmax(150px,.9fr); align-items:center; gap:18px; }
    .report-donut { width:170px; aspect-ratio:1; margin:auto; display:grid; place-items:center; border-radius:50%; background:var(--donut); position:relative; transform:rotateX(12deg) rotateZ(-2deg); transform-style:preserve-3d; box-shadow:0 22px 28px rgba(48,34,22,.2),inset 0 2px 0 rgba(255,255,255,.45); }
    .report-donut::before { content:''; position:absolute; inset:7px -3px -10px 7px; z-index:-1; border-radius:50%; background:color-mix(in srgb,var(--primary-dark) 45%,var(--surface-muted)); transform:translateZ(-18px); }
    .report-donut::after { content:''; position:absolute; inset:27px; border-radius:50%; background:var(--surface); box-shadow:inset 0 0 0 1px var(--border); }
    .report-donut-centre { position:relative; z-index:1; text-align:center; }
    .report-donut-centre strong { display:block; color:var(--text); font-size:1.55rem; letter-spacing:-.04em; }
    .report-donut-centre span { display:block; margin-top:2px; color:var(--text-muted); font-size:.62rem; }
    .report-status-list { display:grid; gap:12px; }
    .report-status-row { display:grid; grid-template-columns:auto 1fr auto; align-items:center; gap:8px; color:var(--text-muted); font-size:.7rem; }
    .report-status-row i { width:9px; height:9px; border-radius:50%; background:var(--status-color); }
    .report-status-row strong { color:var(--text); font-variant-numeric:tabular-nums; }

    .report-efficiency { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
    .report-efficiency-item { padding:16px; border:1px solid var(--border); border-radius:14px; background:var(--surface-soft); }
    .report-efficiency-item span { display:block; color:var(--text-muted); font-size:.68rem; line-height:1.4; }
    .report-efficiency-item strong { display:block; margin-top:8px; color:var(--text); font-size:1.45rem; letter-spacing:-.03em; }
    .report-progress { height:7px; margin-top:12px; border-radius:999px; background:var(--surface-muted); overflow:hidden; }
    .report-progress span { width:var(--progress); height:100%; border-radius:inherit; background:linear-gradient(90deg,var(--primary-dark),var(--primary)); }
    .report-footer-note { margin:0; padding:2px 2px 8px; color:var(--text-muted); font-size:.68rem; text-align:right; }

    html[data-theme="dark"] .report-kpi,
    html[data-theme="dark"] .report-card,
    body[data-theme="dark"] .report-kpi,
    body[data-theme="dark"] .report-card { background:linear-gradient(145deg,var(--se-surface),var(--se-surface-soft)); }
    html[data-theme="dark"] .report-donut::after,
    body[data-theme="dark"] .report-donut::after { background:var(--se-surface); }

    @media(max-width:1100px) {
        .report-kpis { grid-template-columns:repeat(3,minmax(0,1fr)); }
        .report-grid { grid-template-columns:1fr; }
    }
    @media(max-width:720px) {
        .monthly-report { gap:17px; }
        .report-hero { align-items:stretch; flex-direction:column; padding:18px; }
        .report-actions, .report-actions form { width:100%; }
        .report-actions form { display:grid !important; grid-template-columns:1fr auto; align-items:end !important; }
        .report-month-field input { min-width:0; }
        .report-kpis { grid-template-columns:1fr 1fr; }
        .report-module-head { align-items:flex-start; flex-direction:column; }
        .report-donut-layout { grid-template-columns:1fr; }
        .report-efficiency { grid-template-columns:1fr; }
        .report-kpi,.report-bars,.report-donut { transform:none; }
    }
    @media(max-width:430px) {
        .report-kpis { grid-template-columns:1fr; }
        .report-actions form { grid-template-columns:1fr; }
        .report-actions .ui-btn { width:100%; }
        .report-card-head, .report-card-body { padding-left:16px; padding-right:16px; }
        .report-bars { gap:6px; }
        .report-bar-stage { gap:3px; }
    }
    @media print {
        .sidebar,.topbar,.page-header,.mobile-bottom-nav,.app-footer,.report-actions { display:none !important; }
        .main-wrap,.page-body { margin:0 !important; padding:0 !important; background:#fff !important; }
        .monthly-report { width:100%; color:#111; gap:14px; }
        .report-module { break-inside:auto; }
        .report-kpi,.report-card { box-shadow:none !important; break-inside:avoid; transform:none !important; }
        .report-bars,.report-donut { transform:none !important; }
        .report-hero { border:0; padding:0; box-shadow:none; }
        .report-jump-nav { display:none; }
        .report-kpis { grid-template-columns:repeat(5,1fr); }
        .report-grid { grid-template-columns:1.35fr .85fr; }
    }
    @media(prefers-reduced-motion:reduce) { .report-kpi,.report-bars,.report-donut { transform:none; } }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ __('Monthly Report') }}</h2>
@endsection

@section('content')
@php
    $disciplineDecisionTotal = $disciplineSummary ? $disciplineSummary['fine_approved'] + $disciplineSummary['fine_rejected'] : 0;
    $disciplinePendingPct = $disciplineSummary && $disciplineSummary['fine_total'] > 0 ? round(($disciplineSummary['fine_pending'] / $disciplineSummary['fine_total']) * 100, 2) : 0;
    $disciplineApprovedPct = $disciplineSummary && $disciplineSummary['fine_total'] > 0 ? round(($disciplineSummary['fine_status_approved'] / $disciplineSummary['fine_total']) * 100, 2) : 0;
    $scholarshipPendingPct = $scholarshipSummary && $scholarshipSummary['new_records'] > 0 ? round(($scholarshipSummary['pending'] / $scholarshipSummary['new_records']) * 100, 2) : 0;
    $scholarshipConfirmedCurrent = $scholarshipSummary ? $scholarshipSummary['status_confirmed'] : 0;
    $scholarshipConfirmedPct = $scholarshipSummary && $scholarshipSummary['new_records'] > 0 ? round(($scholarshipConfirmedCurrent / $scholarshipSummary['new_records']) * 100, 2) : 0;
    $disciplineTrendTotal = $disciplineSummary ? collect($disciplineSummary['trend'])->sum(fn ($period) => $period['primary'] + $period['secondary']) : 0;
    $scholarshipTrendTotal = $scholarshipSummary ? collect($scholarshipSummary['trend'])->sum(fn ($period) => $period['primary'] + $period['secondary']) : 0;
@endphp
<div class="monthly-report">
    <header class="report-hero">
        <div class="report-hero-copy">
            <span class="report-eyebrow">{{ __('Operations Analytics') }}</span>
            <h1>{{ __('Monthly Performance Report') }}</h1>
            <p>{{ __('A structured overview of discipline and scholarship activity, workflow decisions, current backlogs, and six-month operational trends.') }}</p>
            <div class="report-scope"><strong>{{ __('Report scope:') }}</strong><span>{{ $start->format('1 M Y') }} – {{ $end->format('d M Y') }}</span></div>
        </div>
        <div class="report-actions">
            <button class="ui-btn" type="button" onclick="window.print()">{{ __('Print / Save PDF') }}</button>
            <form method="GET" action="{{ route('admin.reports.monthly') }}">
                <div class="report-month-field">
                    <label for="reportMonth">{{ __('Report month') }}</label>
                    <input id="reportMonth" type="month" name="month" value="{{ $month }}" max="{{ now()->format('Y-m') }}">
                </div>
                <button class="ui-btn primary" type="submit">{{ __('Generate Report') }}</button>
            </form>
        </div>
    </header>

    @if($hasDisciplineAccess && $hasScholarshipAccess)
        <nav class="report-jump-nav" aria-label="{{ __('Report sections') }}">
            <a href="#disciplineReport">{{ __('Discipline Operations') }}</a>
            <a href="#scholarshipReport">{{ __('Scholarship Operations') }}</a>
        </nav>
    @endif

    @if($hasDisciplineAccess && $disciplineSummary)
    <section class="report-module" id="disciplineReport" aria-labelledby="disciplineReportTitle">
        <div class="report-module-head">
            <div><span class="report-eyebrow">{{ __('Discipline Analytics') }}</span><h2 id="disciplineReportTitle">{{ __('Discipline Operations') }}</h2><p>{{ __('Offense records, fine-payment decisions, vehicle stickers, and unresolved workload.') }}</p></div>
            <span class="report-module-badge">{{ __('Live database metrics') }}</span>
        </div>

        <div class="report-kpis">
            <article class="report-kpi tone-slate"><span class="report-kpi-label">{{ __('New Offenses') }}</span><strong class="report-kpi-value">{{ number_format($disciplineSummary['new_offenses']) }}</strong><span class="report-kpi-note">{{ __('Created during this month') }}</span></article>
            <article class="report-kpi tone-green"><span class="report-kpi-label">{{ __('Paid Offenses') }}</span><strong class="report-kpi-value">{{ number_format($disciplineSummary['paid_offenses']) }}</strong><span class="report-kpi-note">{{ __('Payment completed this month') }}</span></article>
            <article class="report-kpi tone-gold"><span class="report-kpi-label">{{ __('Fine Applications Pending') }}</span><strong class="report-kpi-value">{{ number_format($disciplineSummary['fine_pending']) }}</strong><span class="report-kpi-note">{{ __('Submitted within report scope') }}</span></article>
            <article class="report-kpi tone-blue"><span class="report-kpi-label">{{ __('Fine Applications Approved') }}</span><strong class="report-kpi-value">{{ number_format($disciplineSummary['fine_approved']) }}</strong><span class="report-kpi-note">{{ __('Positive decisions this month') }}</span></article>
            <article class="report-kpi tone-violet"><span class="report-kpi-label">{{ __('Fine Approval Rate') }}</span><strong class="report-kpi-value">{{ number_format($disciplineSummary['fine_approval_rate'],1) }}%</strong><span class="report-kpi-note">{{ __('Of decided applications') }}</span></article>
        </div>

        <div class="report-grid">
            <article class="report-card">
                <div class="report-card-head"><span class="report-card-kicker">{{ __('Six Months') }}</span><h3>{{ __('Discipline Activity Trends') }}</h3><p class="report-card-copy">{{ __('Monthly offenses compared with approved fine-payment applications.') }}</p></div>
                @if($disciplineTrendTotal > 0)
                <div class="report-card-body">
                    <div class="report-bars">
                        @foreach($disciplineSummary['trend'] as $period)
                        <div class="report-bar-group"><div class="report-bar-stage"><span class="report-bar" style="--bar-height:{{ $period['primary_height'] }}%;" data-zero="{{ $period['primary'] === 0 ? 'true' : 'false' }}"><span class="report-bar-value">{{ $period['primary'] }}</span></span><span class="report-bar secondary" style="--bar-height:{{ $period['secondary_height'] }}%;" data-zero="{{ $period['secondary'] === 0 ? 'true' : 'false' }}"><span class="report-bar-value">{{ $period['secondary'] }}</span></span></div><div class="report-bar-label"><strong>{{ $period['label'] }}</strong>{{ $period['year'] }}</div></div>
                        @endforeach
                    </div>
                    <div class="report-legend"><span><i></i>{{ __('New offenses') }}</span><span><i class="secondary"></i>{{ __('Approved payments') }}</span></div>
                </div>
                @else
                <div class="report-empty"><div><div class="report-empty-mark">0</div><strong>{{ __('No discipline activity in this period') }}</strong><span>{{ __('Choose another report month or return after new offenses and payment decisions are recorded.') }}</span></div></div>
                @endif
            </article>
            <article class="report-card">
                <div class="report-card-head"><span class="report-card-kicker">{{ __('Decision Status') }}</span><h3>{{ __('Fine Application Distribution') }}</h3><p class="report-card-copy">{{ __('Current status of applications submitted this month.') }}</p></div>
                @if($disciplineSummary['fine_total'] > 0)
                <div class="report-card-body report-donut-layout">
                    <div class="report-donut" style="--donut:{{ $disciplineSummary['fine_total'] > 0 ? 'conic-gradient(#c48628 0 '.$disciplinePendingPct.'%,#3f8f69 '.$disciplinePendingPct.'% '.($disciplinePendingPct+$disciplineApprovedPct).'%,#c14f5c '.($disciplinePendingPct+$disciplineApprovedPct).'% 100%)' : 'conic-gradient(var(--surface-muted) 0 100%)' }};"><div class="report-donut-centre"><strong>{{ number_format($disciplineSummary['fine_total']) }}</strong><span>{{ __('Applications') }}</span></div></div>
                    <div class="report-status-list"><div class="report-status-row" style="--status-color:#c48628"><i></i><span>{{ __('Pending') }}</span><strong>{{ $disciplineSummary['fine_pending'] }}</strong></div><div class="report-status-row" style="--status-color:#3f8f69"><i></i><span>{{ __('Approved') }}</span><strong>{{ $disciplineSummary['fine_status_approved'] }}</strong></div><div class="report-status-row" style="--status-color:#c14f5c"><i></i><span>{{ __('Rejected') }}</span><strong>{{ $disciplineSummary['fine_status_rejected'] }}</strong></div></div>
                </div>
                @else
                <div class="report-empty"><div><div class="report-empty-mark">0</div><strong>{{ __('No fine applications this month') }}</strong><span>{{ __('The distribution will appear when students submit fine-payment applications.') }}</span></div></div>
                @endif
            </article>
        </div>

        <article class="report-card">
            <div class="report-card-head"><span class="report-card-kicker">{{ __('Operational Monitoring') }}</span><h3>{{ __('Processing Efficiency') }}</h3><p class="report-card-copy">{{ __('Decision rates and unresolved workload requiring administrator attention.') }}</p></div>
            <div class="report-card-body report-efficiency">
                <div class="report-efficiency-item"><span>{{ __('Fine decisions completed') }}</span><strong>{{ number_format($disciplineDecisionTotal) }}</strong><div class="report-progress" style="--progress:{{ $disciplineSummary['fine_approval_rate'] }}%"><span></span></div></div>
                <div class="report-efficiency-item"><span>{{ __('Vehicle sticker approval rate') }}</span><strong>{{ number_format($disciplineSummary['sticker_approval_rate'],1) }}%</strong><div class="report-progress" style="--progress:{{ $disciplineSummary['sticker_approval_rate'] }}%"><span></span></div></div>
                <div class="report-efficiency-item"><span>{{ __('Current unresolved workload') }}</span><strong>{{ number_format($disciplineSummary['current_unpaid'] + $disciplineSummary['current_fine_backlog']) }}</strong><div class="report-progress" style="--progress:{{ ($disciplineSummary['current_unpaid'] + $disciplineSummary['current_fine_backlog']) > 0 ? 100 : 0 }}%"><span></span></div></div>
            </div>
        </article>
    </section>
    @endif

    @if($hasScholarshipAccess && $scholarshipSummary)
    <section class="report-module" id="scholarshipReport" aria-labelledby="scholarshipReportTitle">
        <div class="report-module-head">
            <div><span class="report-eyebrow">{{ __('Scholarship Analytics') }}</span><h2 id="scholarshipReportTitle">{{ __('Scholarship Operations') }}</h2><p>{{ __('New aid records, decisions, financial value, announcements, and pending workload.') }}</p></div>
            <span class="report-module-badge">{{ __('Live database metrics') }}</span>
        </div>

        <div class="report-kpis">
            <article class="report-kpi tone-slate"><span class="report-kpi-label">{{ __('New Scholarship Records') }}</span><strong class="report-kpi-value">{{ number_format($scholarshipSummary['new_records']) }}</strong><span class="report-kpi-note">{{ __('Created during this month') }}</span></article>
            <article class="report-kpi tone-green"><span class="report-kpi-label">{{ __('Records Confirmed') }}</span><strong class="report-kpi-value">{{ number_format($scholarshipSummary['confirmed']) }}</strong><span class="report-kpi-note">{{ __('Confirmed during this month') }}</span></article>
            <article class="report-kpi tone-gold"><span class="report-kpi-label">{{ __('Records Pending') }}</span><strong class="report-kpi-value">{{ number_format($scholarshipSummary['pending']) }}</strong><span class="report-kpi-note">{{ __('Created and awaiting decision') }}</span></article>
            <article class="report-kpi tone-blue"><span class="report-kpi-label">{{ __('Confirmed Financial Value') }}</span><strong class="report-kpi-value">RM {{ number_format($scholarshipSummary['confirmed_amount'],0) }}</strong><span class="report-kpi-note">{{ __('Value confirmed this month') }}</span></article>
            <article class="report-kpi tone-violet"><span class="report-kpi-label">{{ __('Confirmation Rate') }}</span><strong class="report-kpi-value">{{ number_format($scholarshipSummary['confirmation_rate'],1) }}%</strong><span class="report-kpi-note">{{ __('Of decided records') }}</span></article>
        </div>

        <div class="report-grid">
            <article class="report-card">
                <div class="report-card-head"><span class="report-card-kicker">{{ __('Six Months') }}</span><h3>{{ __('Scholarship Activity Trends') }}</h3><p class="report-card-copy">{{ __('Monthly scholarship records compared with confirmed decisions.') }}</p></div>
                @if($scholarshipTrendTotal > 0)
                <div class="report-card-body">
                    <div class="report-bars">
                        @foreach($scholarshipSummary['trend'] as $period)
                        <div class="report-bar-group"><div class="report-bar-stage"><span class="report-bar" style="--bar-height:{{ $period['primary_height'] }}%;" data-zero="{{ $period['primary'] === 0 ? 'true' : 'false' }}"><span class="report-bar-value">{{ $period['primary'] }}</span></span><span class="report-bar secondary" style="--bar-height:{{ $period['secondary_height'] }}%;" data-zero="{{ $period['secondary'] === 0 ? 'true' : 'false' }}"><span class="report-bar-value">{{ $period['secondary'] }}</span></span></div><div class="report-bar-label"><strong>{{ $period['label'] }}</strong>{{ $period['year'] }}</div></div>
                        @endforeach
                    </div>
                    <div class="report-legend"><span><i></i>{{ __('New records') }}</span><span><i class="secondary"></i>{{ __('Confirmed records') }}</span></div>
                </div>
                @else
                <div class="report-empty"><div><div class="report-empty-mark">0</div><strong>{{ __('No scholarship activity in this period') }}</strong><span>{{ __('Choose another report month or return after scholarship records and decisions are added.') }}</span></div></div>
                @endif
            </article>
            <article class="report-card">
                <div class="report-card-head"><span class="report-card-kicker">{{ __('Current Status') }}</span><h3>{{ __('Scholarship Status Distribution') }}</h3><p class="report-card-copy">{{ __('Current status of records created this month.') }}</p></div>
                @if($scholarshipSummary['new_records'] > 0)
                <div class="report-card-body report-donut-layout">
                    <div class="report-donut" style="--donut:{{ $scholarshipSummary['new_records'] > 0 ? 'conic-gradient(#c48628 0 '.$scholarshipPendingPct.'%,#3f8f69 '.$scholarshipPendingPct.'% '.($scholarshipPendingPct+$scholarshipConfirmedPct).'%,#c14f5c '.($scholarshipPendingPct+$scholarshipConfirmedPct).'% 100%)' : 'conic-gradient(var(--surface-muted) 0 100%)' }};"><div class="report-donut-centre"><strong>{{ number_format($scholarshipSummary['new_records']) }}</strong><span>{{ __('Records') }}</span></div></div>
                    <div class="report-status-list"><div class="report-status-row" style="--status-color:#c48628"><i></i><span>{{ __('Pending') }}</span><strong>{{ $scholarshipSummary['pending'] }}</strong></div><div class="report-status-row" style="--status-color:#3f8f69"><i></i><span>{{ __('Confirmed') }}</span><strong>{{ $scholarshipConfirmedCurrent }}</strong></div><div class="report-status-row" style="--status-color:#c14f5c"><i></i><span>{{ __('Rejected') }}</span><strong>{{ $scholarshipSummary['rejected'] }}</strong></div></div>
                </div>
                @else
                <div class="report-empty"><div><div class="report-empty-mark">0</div><strong>{{ __('No scholarship records this month') }}</strong><span>{{ __('The distribution will appear when scholarship records are created in this report period.') }}</span></div></div>
                @endif
            </article>
        </div>

        <article class="report-card">
            <div class="report-card-head"><span class="report-card-kicker">{{ __('Operational Monitoring') }}</span><h3>{{ __('Scholarship Workflow') }}</h3><p class="report-card-copy">{{ __('Decision performance, communication activity, and current pending records.') }}</p></div>
            <div class="report-card-body report-efficiency">
                <div class="report-efficiency-item"><span>{{ __('Confirmation rate') }}</span><strong>{{ number_format($scholarshipSummary['confirmation_rate'],1) }}%</strong><div class="report-progress" style="--progress:{{ $scholarshipSummary['confirmation_rate'] }}%"><span></span></div></div>
                <div class="report-efficiency-item"><span>{{ __('Announcements published') }}</span><strong>{{ number_format($scholarshipSummary['announcements']) }}</strong><div class="report-progress" style="--progress:{{ min(100,$scholarshipSummary['announcements']*10) }}%"><span></span></div></div>
                <div class="report-efficiency-item"><span>{{ __('Current pending backlog') }}</span><strong>{{ number_format($scholarshipSummary['current_pending']) }}</strong><div class="report-progress" style="--progress:{{ $scholarshipSummary['current_pending'] > 0 ? 100 : 0 }}%"><span></span></div></div>
            </div>
        </article>
    </section>
    @endif

    <p class="report-footer-note">{{ __('Generated') }} {{ now()->format('d M Y, H:i') }} · {{ __('StudentEdge monthly operational analytics') }}</p>
</div>
@endsection
