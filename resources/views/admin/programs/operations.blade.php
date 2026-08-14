@extends('layouts.app')
@section('title', __('Operations & Attendance Workspace - ').$program->title)
@section('header')<h2 style="margin:0;font-size:1.15rem;font-weight:800;">{{ __('Program Operations & Attendance Workspace') }}</h2>@endsection

@push('styles')
@php
    $canUseAccent = (session('auth_user.role') === 'student' || session('auth_user.admin_role') === 'system_admin');
@endphp
<style>
.pmr {
    --pm-accent: {{ $canUseAccent ? 'var(--se-primary, #C8A96A)' : '#C8A96A' }};
    display: grid;
    gap: 1.25rem;
    color: var(--text, #241d16);
    max-width: 1360px;
    margin: 0 auto;
    padding: 1.5rem 1rem;
    font-family: inherit;
}
.pmr-hero, .pmr-card, .pmr-kpi {
    background: var(--surface, #fff);
    border: 1px solid color-mix(in srgb, var(--pm-accent) 22%, var(--border, #eadac8));
    border-radius: 18px;
    box-shadow: var(--glass-shadow, 0 14px 36px rgba(0,0,0,0.06));
    backdrop-filter: blur(var(--glass-blur, 16px));
}
.pmr-hero {
    padding: 1.75rem;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 1.5rem;
    background: linear-gradient(135deg, var(--surface, #fff), color-mix(in srgb, var(--pm-accent) 10%, var(--surface, #fff)));
}
.pmr-eyebrow {
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .12em;
    color: var(--pm-accent);
}
.pmr h1 { font-size: 2rem; margin: .35rem 0; font-weight: 800; color: var(--text, #241d16); }
.pmr p { color: var(--text-muted, #746b62); margin: .25rem 0; font-size: 0.92rem; }
.pmr-card { padding: 1.5rem 1.75rem; }
.pmr-card h2, .pmr-card h3 { margin: 0 0 .85rem 0; font-weight: 800; color: var(--text, #241d16); }

.pmr-actions { display:flex; align-items:stretch; gap:.35rem; flex-wrap:wrap; padding:.4rem; border:1px solid color-mix(in srgb,var(--pm-accent) 20%,var(--border,#eadac8)); border-radius:14px; background:color-mix(in srgb,var(--surface,#fff) 92%,var(--pm-accent) 8%); }
.pmr-btn {
    min-height: 42px;
    border: 1px solid var(--border, #eadac8);
    border-radius: 9px;
    padding: .7rem 1.1rem;
    background: var(--surface, #fff);
    color: var(--text, #241d16);
    font-weight: 800;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.88rem;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.pmr-btn:hover { background: color-mix(in srgb, var(--pm-accent) 8%, var(--surface, #fff)); }
.pmr-btn.primary { background: var(--pm-accent); color: #fff; border-color: var(--pm-accent); box-shadow: 0 4px 14px color-mix(in srgb, var(--pm-accent) 30%, transparent); }
.pmr-btn.primary:hover { background: color-mix(in srgb, var(--pm-accent) 85%, #000); color: #fff; }
.pmr-btn svg { width:16px; height:16px; flex:0 0 16px; fill:none; stroke:currentColor; stroke-width:1.9; stroke-linecap:round; stroke-linejoin:round; }
.pmr-btn.public-checkin { border-color:color-mix(in srgb,var(--pm-accent) 48%,var(--border,#eadac8)); color:var(--pm-accent-strong,#8b6a34); background:color-mix(in srgb,var(--pm-accent) 5%,var(--surface,#fff)); }
.pmr-btn.public-checkin:hover { border-color:var(--pm-accent); background:color-mix(in srgb,var(--pm-accent) 13%,var(--surface,#fff)); color:var(--pm-accent-strong,#8b6a34); }

.pmr-mode-panel { margin-top:1.1rem; padding:1rem; border:1px solid var(--border, #eadac8); border-radius:14px; background:color-mix(in srgb, var(--pm-accent) 4%, var(--surface, #fff)); }
.pmr-mode-panel label { display:block; margin-bottom:.55rem; color:var(--text, #241d16); font-size:.9rem; font-weight:800; }
.pmr-mode-panel select { width:100%; min-height:46px; padding:.7rem 2.6rem .7rem .9rem; border:1px solid var(--border, #dcc7ad); border-radius:10px; background:var(--surface, #fff); color:var(--text, #241d16); font:inherit; transition:border-color .15s ease, box-shadow .15s ease; }
.pmr-mode-panel select:focus { outline:none; border-color:var(--pm-accent); box-shadow:0 0 0 3px color-mix(in srgb, var(--pm-accent) 18%, transparent); }
.pmr-source-grid { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.7rem; margin:1rem 0; }
.pmr-source-item { padding:.85rem; border:1px solid var(--border,#eadac8); border-radius:12px; background:var(--surface,#fff); }
.pmr-source-item span { display:block; color:var(--text-muted,#746b62); font-size:.72rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; }
.pmr-source-item strong { display:block; margin-top:.3rem; color:var(--text,#241d16); font-size:.9rem; }
.pmr-source-item.is-ready { border-color:color-mix(in srgb,#21835a 35%,var(--border,#eadac8)); background:color-mix(in srgb,#21835a 5%,var(--surface,#fff)); }
.pmr-report-lock { margin-top:1rem; padding:.75rem .9rem; border-radius:10px; background:color-mix(in srgb,var(--pm-accent) 7%,var(--surface,#fff)); color:var(--text-muted,#746b62); font-size:.85rem; }
.pmr-certificate-layout { display:grid; grid-template-columns:minmax(0,.85fr) minmax(320px,1.15fr); gap:1rem; margin-top:1rem; }
.pmr-certificate-settings { display:grid; align-content:start; gap:.75rem; padding:1rem; border:1px solid color-mix(in srgb,var(--pm-accent) 22%,var(--border,#eadac8)); border-radius:14px; background:color-mix(in srgb,var(--surface,#fff) 94%,var(--pm-accent) 6%); }
.pmr-certificate-settings label { color:var(--text,#241d16); font-size:.78rem; font-weight:850; }
.pmr-certificate-settings select { width:100%; min-height:44px; padding:.65rem .8rem; border:1px solid var(--border,#eadac8); border-radius:10px; background:var(--surface,#fff); color:var(--text,#241d16); font:inherit; }
.pmr-certificate-preview { position:relative; aspect-ratio:1.414/1; display:grid; place-items:center; overflow:hidden; padding:1rem; border:1px solid color-mix(in srgb,var(--pm-accent) 32%,var(--border,#eadac8)); border-radius:14px; background:#fffdf8; color:#342619; box-shadow:0 12px 28px color-mix(in srgb,var(--pm-accent) 14%,transparent); text-align:center; }
.pmr-certificate-preview::before { content:''; position:absolute; inset:10px; border:4px solid color-mix(in srgb,var(--pm-accent) 72%,#b99150); pointer-events:none; }
.pmr-certificate-preview__inner { position:relative; z-index:1; width:80%; }
.pmr-certificate-preview__brand { color:#8b6934; font-size:.58rem; font-weight:850; letter-spacing:.16em; }
.pmr-certificate-preview__title { margin:.6rem 0 .35rem; font-family:Georgia,serif; font-size:clamp(1.05rem,2.4vw,1.7rem); font-weight:800; }
.pmr-certificate-preview__name { margin:.65rem 0 .35rem; padding-bottom:.25rem; border-bottom:1px solid #d9bf8d; color:#8b6934; font-family:Georgia,serif; font-size:clamp(.9rem,1.8vw,1.3rem); font-weight:800; }
.pmr-certificate-preview__meta { color:#695c50; font-size:.65rem; line-height:1.45; }
.pmr-points-only { margin-top:1rem; padding:1rem; border:1px solid color-mix(in srgb,var(--pm-accent) 24%,var(--border,#eadac8)); border-radius:14px; background:color-mix(in srgb,var(--pm-accent) 7%,var(--surface,#fff)); }
@media (max-width:820px) { .pmr-certificate-layout { grid-template-columns:1fr; } }
.pmr-roster-empty { min-height:230px; display:grid; place-items:center; padding:2.25rem 1rem; text-align:center; background:radial-gradient(circle at 50% 0,color-mix(in srgb,var(--pm-accent) 9%,transparent),transparent 42%); }
.pmr-roster-empty__inner { max-width:520px; }
.pmr-roster-empty__icon { width:52px; height:52px; display:grid; place-items:center; margin:0 auto .9rem; border:1px solid color-mix(in srgb,var(--pm-accent) 30%,var(--border,#eadac8)); border-radius:15px; background:color-mix(in srgb,var(--pm-accent) 8%,var(--surface,#fff)); color:var(--pm-accent-strong,#8b6a34); box-shadow:0 10px 24px color-mix(in srgb,var(--pm-accent) 12%,transparent); }
.pmr-roster-empty__icon svg { width:27px; height:27px; fill:none; stroke:currentColor; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; }
.pmr-roster-empty h3 { margin:0 0 .35rem; font-size:1rem; }
.pmr-roster-empty p { max-width:480px; margin:0 auto; font-size:.84rem; line-height:1.5; }
.pmr-roster-empty__toolbar { width:fit-content; max-width:100%; display:flex; align-items:center; justify-content:center; gap:.5rem; margin:1rem auto 0; padding:.4rem; border:1px solid color-mix(in srgb,var(--pm-accent) 22%,var(--border,#eadac8)); border-radius:14px; background:color-mix(in srgb,var(--surface,#fff) 94%,var(--pm-accent) 6%); box-shadow:0 10px 24px color-mix(in srgb,var(--pm-accent) 8%,transparent); }
.pmr-roster-empty__status { display:inline-flex; align-items:center; gap:.4rem; min-height:40px; padding:.35rem .7rem; border-radius:10px; background:color-mix(in srgb,var(--pm-accent) 8%,var(--surface,#fff)); color:var(--pm-accent-strong,#8b6a34); font-size:.7rem; font-weight:850; text-transform:uppercase; letter-spacing:.04em; }
.pmr-roster-empty__status::before { content:''; width:7px; height:7px; border-radius:50%; background:currentColor; }
.pmr-roster-empty .pmr-actions { display:inline-flex; justify-content:center; flex-wrap:wrap; padding:0; border:0; background:transparent; }
.pmr-roster-empty .pmr-btn { min-height:40px; }
@media (max-width:620px) { .pmr-roster-empty__toolbar { width:100%; align-items:stretch; flex-direction:column; box-sizing:border-box; } .pmr-roster-empty__status { justify-content:center; } .pmr-roster-empty .pmr-actions { display:grid; grid-template-columns:1fr; } }
.pmr-attendance-control { padding:0; overflow:hidden; }
.pmr-attendance-control__header { padding:1rem 1.35rem; border-bottom:1px solid color-mix(in srgb,var(--pm-accent) 18%,var(--border,#eadac8)); }
.pmr-attendance-control__body { display:flex; align-items:center; justify-content:space-between; gap:1.5rem; padding:1.25rem 1.35rem; }
.pmr-attendance-control__copy { display:flex; align-items:flex-start; gap:.9rem; min-width:0; }
.pmr-attendance-control__icon { display:grid; place-items:center; flex:0 0 42px; width:42px; height:42px; border-radius:12px; background:color-mix(in srgb,var(--pm-accent) 10%,var(--surface,#fff)); color:var(--pm-accent-strong,#8b6a34); border:1px solid color-mix(in srgb,var(--pm-accent) 30%,var(--border,#eadac8)); }
.pmr-attendance-control__icon svg { width:21px; height:21px; fill:none; stroke:currentColor; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; }
.pmr-attendance-control__body h2 { margin:0 0 .25rem; font-size:1rem; }
.pmr-attendance-control__body p { margin:0; max-width:720px; font-size:.84rem; line-height:1.45; }
.pmr-attendance-control__actions { display:flex; align-items:center; justify-content:flex-end; gap:.7rem; flex:0 0 auto; }
.pmr-live-status { display:inline-flex; align-items:center; gap:.45rem; min-height:34px; padding:.4rem .7rem; border-radius:999px; background:color-mix(in srgb,#21835a 10%,var(--surface,#fff)); color:#187048; border:1px solid color-mix(in srgb,#21835a 28%,var(--border,#eadac8)); font-size:.72rem; font-weight:850; text-transform:uppercase; letter-spacing:.04em; }
.pmr-live-status::before { content:''; width:8px; height:8px; border-radius:50%; background:currentColor; box-shadow:0 0 0 3px color-mix(in srgb,currentColor 12%,transparent); }
.pmr-live-status.is-closed { background:color-mix(in srgb,var(--text-muted,#746b62) 8%,var(--surface,#fff)); color:var(--text-muted,#746b62); border-color:var(--border,#eadac8); }
.pmr-attendance-warning { display:flex; align-items:flex-start; gap:.55rem; margin:0 1.35rem 1.15rem !important; padding:.65rem .75rem; border-radius:10px; background:#fff8e9; color:#8a5a13 !important; font-size:.8rem !important; }
.pmr-attendance-warning::before { content:'!'; display:grid; place-items:center; flex:0 0 20px; width:20px; height:20px; border-radius:50%; background:#f3d79f; font-weight:900; }
@media (max-width:720px) { .pmr-attendance-control__body { align-items:stretch; flex-direction:column; } .pmr-attendance-control__actions { justify-content:flex-start; } }
.pmr-mode-actions { display:flex; align-items:center; gap:.85rem; margin-top:.8rem; flex-wrap:wrap; }
.pmr-mode-status { display:inline-flex; align-items:flex-start; gap:.5rem; margin:0 !important; font-size:.84rem !important; }
.pmr-mode-status::before { content:'!'; display:grid; place-items:center; flex:0 0 20px; width:20px; height:20px; border-radius:50%; background:#fff1d6; color:#8a5a13; font-size:.72rem; font-weight:900; }
.pmr-attendance-mode {
    display:flex;
    align-items:center;
    gap:.55rem;
    margin-top:.75rem;
    padding:.55rem .7rem;
    border:1px solid color-mix(in srgb, var(--pm-accent) 28%, var(--border, #eadac8));
    border-radius:9px;
    background:color-mix(in srgb, var(--pm-accent) 7%, var(--surface, #fff));
}
.pmr-attendance-mode__icon {
    display:grid;
    place-items:center;
    flex:0 0 24px;
    width:24px;
    height:24px;
    border-radius:7px;
    background:var(--pm-accent);
    color:#fff;
    font-size:.75rem;
    font-weight:900;
}
.pmr-attendance-mode strong { color:var(--text, #241d16); font-size:.84rem; }
.pmr-attendance-mode p { display:inline; margin:0 0 0 .35rem; font-size:.8rem; line-height:1.35; }

/* KPI Grid */
.pmr-kpis {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 0.8rem;
}
.pmr-kpi {
    padding: 1.1rem 1.25rem;
    position: relative;
    overflow: hidden;
}
.pmr-kpi:after {
    content: '';
    position: absolute;
    width: 58px;
    height: 58px;
    border-radius: 50%;
    right: -18px;
    top: -18px;
    background: color-mix(in srgb, var(--pm-accent) 14%, transparent);
    pointer-events: none;
}
.pmr-kpi span {
    font-size: .72rem;
    color: var(--text-muted, #746b62);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.pmr-kpi strong { display: block; font-size: 1.8rem; margin-top: .45rem; font-weight: 800; color: var(--text, #241d16); }

/* 2-Column Grid */
.pmr-grid-2 { display: grid; grid-template-columns: 1.2fr 1fr; gap: 1.25rem; }

.pmr-q-item {
    border: 1px solid color-mix(in srgb, var(--pm-accent) 20%, var(--border, #eadac8));
    border-radius: 12px;
    padding: 1rem;
    margin-bottom: 0.85rem;
    background: var(--surface, #fff);
    color: var(--text, #241d16);
}
.pmr-q-head { display:flex; justify-content:space-between; align-items:center; gap:.75rem; margin-bottom:.75rem; }
.pmr-q-title { display:flex; align-items:center; gap:.6rem; font-size:.9rem; }
.pmr-q-number { display:grid; place-items:center; width:27px; height:27px; border-radius:8px; background:color-mix(in srgb,var(--pm-accent) 12%,var(--surface,#fff)); color:var(--pm-accent-strong); font-size:.75rem; font-weight:900; }
.pmr-q-field { display:grid; gap:.35rem; margin-top:.7rem; }
.pmr-q-field > span { color:var(--text-muted,#746b62); font-size:.68rem; font-weight:800; letter-spacing:.045em; text-transform:uppercase; }
.pmr-q-required { display:flex; align-items:center; gap:.6rem; width:fit-content; margin-top:.8rem; font-size:.82rem; font-weight:750; cursor:pointer; }
.pmr-q-required input[type="checkbox"] { appearance:none; width:34px !important; height:20px; margin:0 !important; border:1px solid var(--border,#eadac8); border-radius:999px !important; background:var(--border,#eadac8); position:relative; cursor:pointer; transition:.15s ease; }
.pmr-q-required input[type="checkbox"]::after { content:''; position:absolute; width:14px; height:14px; left:2px; top:2px; border-radius:50%; background:#fff; box-shadow:0 1px 3px rgba(0,0,0,.18); transition:transform .15s ease; }
.pmr-q-required input[type="checkbox"]:checked { border-color:var(--pm-accent); background:var(--pm-accent); }
.pmr-q-required input[type="checkbox"]:checked::after { transform:translateX(14px); }
.pmr-remove { display:inline-flex; align-items:center; gap:.35rem; min-height:34px; padding:.35rem .55rem; border:0; border-radius:8px; background:transparent; color:var(--se-danger,#b42318); font:inherit; font-size:.76rem; font-weight:800; cursor:pointer; }
.pmr-remove:hover { background:color-mix(in srgb,var(--se-danger,#b42318) 8%,transparent); }
.pmr-remove svg { width:14px; height:14px; fill:none; stroke:currentColor; stroke-width:2; }
.pmr-q-item input[type="text"], .pmr-q-item select {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid var(--border, #eadac8);
    border-radius: 8px;
    font-size: 0.9rem;
    margin-top: 0;
    background: var(--surface, #fff);
    color: var(--text, #241d16);
}

.pmr-qr-box {
    text-align: center;
    padding: 1.5rem;
    background: color-mix(in srgb, var(--pm-accent) 6%, var(--surface, #fff));
    border-radius: 16px;
    border: 1px dashed var(--pm-accent);
}

.pmr-table { width: 100%; border-collapse: collapse; }
.pmr-table th, .pmr-table td {
    padding: 0.9rem 1rem;
    text-align: left;
    border-bottom: 1px solid var(--border, #eadac8);
    font-size: 0.88rem;
    color: var(--text, #241d16);
}
.pmr-table th { font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted, #746b62); font-weight: 800; background: color-mix(in srgb, var(--pm-accent) 5%, var(--surface, #fff)); }

@media (max-width: 1050px) {
    .pmr-kpis { grid-template-columns: repeat(3, 1fr); }
    .pmr-grid-2 { grid-template-columns: 1fr; }
    .pmr-source-grid { grid-template-columns:repeat(2,minmax(0,1fr)); }
}
@media (max-width: 620px) { .pmr-source-grid { grid-template-columns:1fr; } }
</style>
@endpush
@push('styles')
@include('admin.programs.partials.design-system')
@endpush

@section('content')
<main class="pmr">
    @if(session('success'))
        <div class="alert alert-success" style="border-radius: 12px; margin-bottom: 0;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger" style="border-radius: 12px; margin-bottom: 0;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <!-- Hero Header -->
    <header class="pmr-hero">
        <div>
            <span class="pmr-eyebrow">{{ __('PROGRAM OPERATIONS & ATTENDANCE WORKSPACE') }}</span>
            <h1>{{ $program->title }}</h1>
            <p>{{ $program->reference_no ?: __('No reference number') }} &middot; {{ __('Venue:') }} <strong>{{ $program->venue ?: __('Not set') }}</strong></p>
        </div>
        <div class="pmr-actions">
            <a class="pmr-btn" href="{{ route('admin.programs.show', $program->id) }}">{{ __('Back to Details') }}</a>
            <button class="pmr-btn primary" type="button" onclick="document.getElementById('aiQuestionnaireModal').scrollIntoView({behavior: 'smooth'})">
                {{ __('Questionnaire Builder') }}
            </button>
            <a class="pmr-btn public-checkin" href="{{ $publicCheckinUrl }}" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 5h5v5"/><path d="m10 14 9-9"/><path d="M19 13v5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h5"/></svg>
                {{ __('Open Public Check-in') }}
            </a>
        </div>
    </header>

    <section class="pmr-card pmr-attendance-control">
        <div class="pmr-attendance-control__header">
            <span class="pmr-eyebrow">{{ __('ATTENDANCE CONTROL') }}</span>
        </div>
        <div class="pmr-attendance-control__body">
            <div class="pmr-attendance-control__copy">
                <span class="pmr-attendance-control__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></span>
                <div>
                    <h2>{{ $program->attendance_status === 'open' ? __('Participant check-in is live') : __('Participant check-in is closed') }}</h2>
                    <p>{{ $program->attendance_status === 'open' ? __('Students can now record attendance using their account or the public QR check-in link.') : __('Open attendance when the venue and participation setup are ready.') }}</p>
                </div>
            </div>
            <div class="pmr-attendance-control__actions">
                <span class="pmr-live-status {{ $program->attendance_status === 'open' ? '' : 'is-closed' }}">
                    {{ $program->attendance_status === 'open' ? __('Open') : __('Closed') }}
                </span>
            @if($canManageAttendance && $program->attendance_status !== 'open')
                <form method="post" action="{{ route('admin.programs.attendance.open', $program->id) }}">
                    @csrf
                    <button class="pmr-btn primary" type="submit" @disabled(!$attendanceReady)>{{ __('Open Attendance') }}</button>
                </form>
            @elseif($canManageAttendance)
                <form method="post" action="{{ route('admin.programs.attendance.close', $program->id) }}">
                    @csrf
                    <button class="pmr-btn" type="submit">{{ __('Close Attendance') }}</button>
                </form>
            @endif
            </div>
        </div>
        @if(!$attendanceReady)
            <p class="pmr-attendance-warning">
                <span>
                {{ __('Required before opening:') }}
                @if(!$attendanceSetup['venue']) {{ __('venue') }}; @endif
                @if(!$attendanceSetup['questionnaire']) {{ __('published questionnaire') }}. @endif
                </span>
            </p>
        @endif
    </section>

    <!-- Real-Time Joined Students & Analytics Bar -->
    <section class="pmr-kpis">
        <article class="pmr-kpi">
            <span>{{ __('Total Joined Students') }}</span>
            <strong style="color: var(--pm-accent);">{{ number_format($totalJoined) }}</strong>
        </article>
        <article class="pmr-kpi">
            <span>{{ __('Internal Students') }}</span>
            <strong class="pmr-tone-success">{{ number_format($internalCount) }}</strong>
        </article>
        <article class="pmr-kpi">
            <span>{{ __('External Guests') }}</span>
            <strong>{{ number_format($externalCount) }}</strong>
        </article>
        <article class="pmr-kpi">
            <span>{{ __('Attendance Rate') }}</span>
            <strong class="pmr-tone-accent">{{ $attendanceRate }}%</strong>
        </article>
        <article class="pmr-kpi">
            <span>{{ __('Survey Rating') }}</span>
            <strong class="pmr-tone-accent">{{ $averageRating }} / 5.0</strong>
        </article>
    </section>

    <!-- 2-Column Workspace Grid -->
    <section class="pmr-grid-2">
        <!-- Left: Program Owner Questionnaire Workspace -->
        <article class="pmr-card" id="aiQuestionnaireModal">
            <span class="pmr-eyebrow">{{ __('QUESTIONNAIRE BUILDER') }}</span>
            <h2>{{ __('Program Learning & Feedback') }}</h2>
            <p>{{ __('Create the questionnaire for your program. Written answers are recommended, and AI suggestions are optional.') }}</p>

            <form method="post" action="{{ route('admin.programs.questionnaire-setting.update', $program->id) }}" class="pmr-mode-panel">
                @csrf @method('put')
                <label for="participationMode">{{ __('Participation mode') }}</label>
                <select id="participationMode" name="questionnaire_enabled" data-saved-mode="{{ $program->questionnaire_enabled ? '1' : '0' }}" @disabled($program->attendance_status === 'open')>
                    <option value="1" @selected($program->questionnaire_enabled)>{{ __('Attendance + Questionnaire') }}</option>
                    <option value="0" @selected(!$program->questionnaire_enabled)>{{ __('Attendance Only') }}</option>
                </select>
                <div class="pmr-mode-actions">
                    <button class="pmr-btn primary" type="submit" @disabled($program->attendance_status === 'open')>{{ __('Save Participation Mode') }}</button>
                    @if($program->attendance_status === 'open')<p class="pmr-mode-status">{{ __('Close attendance before changing this setting.') }}</p>@endif
                    <p id="participationModeNotice" class="pmr-mode-status" style="display:none;color:#8a5a13;">{{ __('Save this selection to continue with the chosen setup.') }}</p>
                </div>
            </form>

            <div id="questionnaireBuilderContent" @if(!$program->questionnaire_enabled) hidden @endif>

            <div class="pmr-control-panel">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div>
                        <label style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase;">{{ __('Feedback Focus') }}</label>
                        <select id="aiFocus" style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid var(--border); margin-top: 4px;">
                            <option value="satisfaction">{{ __('Overall Satisfaction') }}</option>
                            <option value="logistics">{{ __('Event Logistics & Venue') }}</option>
                            <option value="effectiveness">{{ __('Program Effectiveness') }}</option>
                            <option value="general">{{ __('General Feedback') }}</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase;">{{ __('Question Count') }}</label>
                        <select id="aiCount" style="width: 100%; padding: 8px; border-radius: 8px; border: 1px solid var(--border); margin-top: 4px;">
                            <option value="3">3 {{ __('Questions') }}</option>
                            <option value="5" selected>5 {{ __('Questions') }}</option>
                            <option value="8">8 {{ __('Questions') }}</option>
                        </select>
                    </div>
                </div>
                <button type="button" class="pmr-btn primary" id="btnGenerateAi" style="width: 100%; margin-top: 12px;">
                    {{ __('Suggest Questions with AI') }}
                </button>
            </div>

            <!-- Survey Form -->
            <form method="post" action="{{ route('admin.programs.survey.save', $program->id) }}">
                @csrf
                <div style="margin-bottom: 1rem;">
                    <label style="font-weight: 800; font-size: 0.85rem;">{{ __('Questionnaire Title') }}</label>
                    <input name="title" required value="{{ old('title', $survey->title ?? __('Feedback Survey - ').$program->title) }}" style="width: 100%; padding: 10px; border-radius: 10px; border: 1px solid var(--border); margin-top: 4px;">
                </div>

                <div id="questionsContainer">
                    @forelse($questions as $index => $q)
                        <div class="pmr-q-item">
                            <div class="pmr-q-head">
                                <strong class="pmr-q-title"><span class="pmr-q-number">{{ $index + 1 }}</span>{{ __('Question') }}</strong>
                                <button class="pmr-remove" type="button" onclick="this.closest('.pmr-q-item').remove()"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5m4-5v5"/></svg>{{ __('Remove') }}</button>
                            </div>
                            <label class="pmr-q-field"><span>{{ __('Question text') }}</span><input name="questions[{{ $index }}][question_text]" value="{{ $q->question_text }}" required placeholder="{{ __('Enter question text') }}"></label>
                            <label class="pmr-q-field"><span>{{ __('Answer type') }}</span><select name="questions[{{ $index }}][question_type]">
                                <option value="text" @selected($q->question_type === 'text')>{{ __('Long Written Answer') }}</option>
                                <option value="rating_5" @selected($q->question_type === 'rating_5')>{{ __('Rating 1-5 Stars') }}</option>
                            </select></label>
                            <label class="pmr-q-required">
                                <input type="hidden" name="questions[{{ $index }}][is_required]" value="0">
                                <input type="checkbox" name="questions[{{ $index }}][is_required]" value="1" @checked($q->is_required) style="width:auto;margin:0;">
                                {{ __('Required question') }}
                            </label>
                        </div>
                    @empty
                        <div class="pmr-q-item">
                            <div class="pmr-q-head"><strong class="pmr-q-title"><span class="pmr-q-number">1</span>{{ __('Question') }}</strong></div>
                            <label class="pmr-q-field"><span>{{ __('Question text') }}</span><input type="text" name="questions[0][question_text]" value="{{ __('What did you learn from this program?') }}" required></label>
                            <label class="pmr-q-field"><span>{{ __('Answer type') }}</span><select name="questions[0][question_type]">
                                <option value="text">{{ __('Long Written Answer') }}</option>
                                <option value="rating_5">{{ __('Rating 1-5 Stars') }}</option>
                            </select></label>
                            <label class="pmr-q-required">
                                <input type="hidden" name="questions[0][is_required]" value="0">
                                <input type="checkbox" name="questions[0][is_required]" value="1" checked style="width:auto;margin:0;">
                                {{ __('Required question') }}
                            </label>
                        </div>
                    @endforelse
                </div>

                <div style="display: flex; gap: 10px; margin-top: 1rem;">
                    <button type="button" class="pmr-btn" onclick="addQuestionRow()">+ {{ __('Add Question') }}</button>
                    <button type="submit" class="pmr-btn primary">{{ __('Save Questionnaire Draft') }}</button>
                </div>
            </form>

            @if($survey && $survey->status !== 'published')
                <form method="post" action="{{ route('admin.programs.survey.publish', $program->id) }}" style="margin-top: 10px;">
                    @csrf
                    <button type="submit" class="pmr-btn primary" style="width: 100%;">
                        {{ __('Post / Publish Questionnaire to Students') }}
                    </button>
                </form>
            @elseif($survey && $survey->status === 'published')
                <div class="pmr-published">
                    {{ __('Questionnaire is live & published to participants.') }}
                </div>
            @endif
            </div>
            <div id="attendanceOnlyMessage" class="pmr-attendance-mode" @if($program->questionnaire_enabled) hidden @endif>
                <span class="pmr-attendance-mode__icon" aria-hidden="true">&#10003;</span>
                <div>
                    <strong>{{ __('Attendance-only mode saved') }}</strong>
                    <p>{{ __('Students can check in using their account and GPS. No questionnaire is required.') }}</p>
                </div>
            </div>
        </article>

        <!-- Right: Public QR Code & External Guest Check-in -->
        <article class="pmr-card">
            <span class="pmr-eyebrow">{{ __('ATTENDANCE & PUBLIC QR CODE') }}</span>
            <h2>{{ __('QR Check-in & Public Link') }}</h2>
            <p>{{ __('Display or print this QR code for external guests and internal students to check in and complete the questionnaire.') }}</p>

            <div class="pmr-qr-box">
                <div class="pmr-qr-image">
                    <!-- Generated QR Code via Google Chart API SVG -->
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($publicCheckinUrl) }}" alt="{{ __('Program Attendance QR Code') }}" style="width: 200px; height: 200px;">
                </div>
                <div style="margin-top: 12px; font-weight: 800; font-size: 0.95rem; color: var(--pm-accent);">
                    {{ $program->title }}
                </div>
                <div style="font-size: 0.8rem; color: var(--text-secondary, #746b62); margin-top: 4px;">
                    {{ __('Scan to Check In & Submit Feedback') }}
                </div>
            </div>

            <div style="margin-top: 1.25rem;">
                <label style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase;">{{ __('Public Check-in URL') }}</label>
                <div style="display: flex; gap: 8px; margin-top: 4px;">
                    <input id="publicUrlInput" value="{{ $publicCheckinUrl }}" readonly style="flex: 1; padding: 9px 12px; border-radius: 10px; border: 1px solid var(--border); font-size: 0.85rem;">
                    <button type="button" class="pmr-btn" onclick="navigator.clipboard.writeText(document.getElementById('publicUrlInput').value); alert('{{ __('Link copied to clipboard!') }}')">{{ __('Copy Link') }}</button>
                </div>
            </div>
        </article>
    </section>

    <!-- Post-program report and review workflow -->
    <section class="pmr-card" id="programReport" style="margin-bottom: 1.25rem;">
        <span class="pmr-eyebrow">{{ __('POST-PROGRAM REPORT') }}</span>
        <h2>{{ __('AI Report & Management Review') }}</h2>
        <p>{{ __('The Program Director uploads the finalized report, then the system routes it through :branch, the Polytechnic Director, and KJ HEP for final retention.', ['branch' => $reportBranchLabel]) }}</p>

        @php
            $operationReportStatus = $report?->status ?? 'not_generated';
            $operationStages = [
                'draft' => __('Program Director draft'),
                'pending_tpsa' => __(':branch review', ['branch' => $reportBranchLabel]),
                'pending_director' => __('Polytechnic Director review'),
                'pending_kj_hep' => __('KJ HEP acceptance'),
                'archived' => __('Archived under KJ HEP'),
            ];
            $operationStageKeys = array_keys($operationStages);
            $operationCurrentKey = $operationReportStatus === 'rejected' ? 'draft' : $operationReportStatus;
            $operationCurrentIndex = array_search($operationCurrentKey, $operationStageKeys, true);
        @endphp
        <div class="pmr-report-flow" aria-label="{{ __('Post-program report workflow') }}">
            @foreach($operationStages as $stageKey => $stageLabel)
                @php
                    $stageIndex = array_search($stageKey, $operationStageKeys, true);
                    $stageState = $operationReportStatus === 'not_generated' ? 'waiting'
                        : ($stageIndex < $operationCurrentIndex ? 'complete' : ($stageIndex === $operationCurrentIndex ? 'current' : 'waiting'));
                    $stageText = $stageState === 'complete' ? __('Completed')
                        : ($stageState === 'current' ? ($operationReportStatus === 'rejected' ? __('Returned for correction') : __('Current stage')) : __('Waiting'));
                @endphp
                <div class="pmr-report-step is-{{ $stageState }}">
                    <span>{{ $stageLabel }}</span>
                    <strong>{{ $stageText }}</strong>
                </div>
            @endforeach
        </div>

        @if($report)
            @php
                $sourceSummary = json_decode($report->source_summary ?? '{}', true) ?: [];
                $sourcePaperwork = (bool) ($sourceSummary['paperwork'] ?? (bool) $latestPaperwork);
                $sourceImages = (int) ($sourceSummary['activity_images'] ?? 0);
                $sourceAttendance = (int) ($sourceSummary['attendance_records'] ?? $totalJoined);
                $sourceResponses = (int) ($sourceSummary['questionnaire_responses'] ?? $surveyResponsesCount);
            @endphp
            <div class="pmr-source-grid" aria-label="{{ __('Report source checklist') }}">
                <div class="pmr-source-item {{ $sourcePaperwork ? 'is-ready' : '' }}"><span>{{ __('Paperwork') }}</span><strong>{{ $sourcePaperwork ? __('Included') : __('Not provided') }}</strong></div>
                <div class="pmr-source-item {{ $sourceImages > 0 ? 'is-ready' : '' }}"><span>{{ __('Activity photos') }}</span><strong>{{ trans_choice(':count file|:count files', $sourceImages, ['count' => $sourceImages]) }}</strong></div>
                <div class="pmr-source-item {{ $sourceAttendance > 0 ? 'is-ready' : '' }}"><span>{{ __('Attendance') }}</span><strong>{{ trans_choice(':count record|:count records', $sourceAttendance, ['count' => $sourceAttendance]) }}</strong></div>
                <div class="pmr-source-item {{ $sourceResponses > 0 ? 'is-ready' : '' }}"><span>{{ __('Questionnaire') }}</span><strong>{{ $program->questionnaire_enabled ? trans_choice(':count response|:count responses', $sourceResponses, ['count' => $sourceResponses]) : __('Not required') }}</strong></div>
            </div>
        @endif

        @if(!$report && $canManageReport)
            <form method="post" action="{{ route('admin.programs.report.generate', $program->id) }}" enctype="multipart/form-data" class="pmr-mode-panel">
                @csrf
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:.8rem;">
                    <div>
                        <label for="programReportImages">{{ __('Program activity photos') }}</label>
                        <input id="programReportImages" type="file" name="program_images[]" accept="image/jpeg,image/png,image/webp" multiple required style="width:100%;padding:.7rem;border:1px solid var(--border);border-radius:10px;background:var(--surface);">
                        <p>{{ __('Paperwork, attendance, and questionnaire responses are collected automatically. Add up to 8 activity photos.') }}</p>
                    </div>
                    <div>
                        <label for="reportOutputFormat">{{ __('Report file format') }}</label>
                        <select id="reportOutputFormat" name="output_format" required>
                            <option value="">{{ __('Choose output format') }}</option>
                            <option value="docx">DOCX</option>
                            <option value="pdf">PDF</option>
                            <option value="both">{{ __('DOCX and PDF') }}</option>
                        </select>
                        <p>{{ __('The official FORMAT LAPORAN POLIBESUT 2025 template will be used.') }}</p>
                    </div>
                </div>
                <button class="pmr-btn primary" type="submit" style="margin-top:.9rem;">{{ __('Generate Program Report') }}</button>
            </form>
        @elseif($report)
            <div style="margin-bottom: 12px;">
                <span class="pmr-badge {{ $report->status }}">{{ __(str_replace('_', ' ', $report->status)) }}</span>
                @if($report->ai_provider)
                    <span style="font-size:.8rem;color:var(--text-secondary,#746b62);margin-left:8px;">{{ __('Generated with') }} {{ strtoupper($report->ai_provider) }} / {{ $report->ai_model }}</span>
                @endif
                <span class="pmr-actions" style="display:inline-flex;margin-left:.75rem;">
                    @if($report->docx_path ?? null)<a class="pmr-btn" href="{{ route('admin.programs.report.download', [$program->id, 'docx']) }}">{{ __('Download DOCX') }}</a>@endif
                    @if($report->pdf_path ?? null)<a class="pmr-btn" href="{{ route('admin.programs.report.download', [$program->id, 'pdf']) }}">{{ __('Download PDF') }}</a>@endif
                    @if($canManageReport)
                        @php
                            $reportAiRoute = session('auth_user.admin_role') === 'lecturer' ? 'lecturer.ai-helper.index' : 'admin.ai-helper.index';
                        @endphp
                        <a class="pmr-btn" href="{{ route($reportAiRoute, ['program_report' => $program->id]) }}">{{ __('Regenerate in AI Helper') }}</a>
                    @endif
                </span>
            </div>

            @if($canManageReport)
                <div class="pmr-mode-panel" style="margin-bottom:1rem;">
                    <h3 style="margin-bottom:.35rem;">{{ __('Upload report for :branch review', ['branch' => $reportBranchLabel]) }}</h3>
                    <p>{{ __('Upload the finalized DOCX or PDF. It will be routed automatically using the Program Director organization line.') }}</p>
                    <form method="post" action="{{ route('admin.programs.report.upload-edited', $program->id) }}" enctype="multipart/form-data" style="margin-top:.8rem;">
                        @csrf
                        <label for="finalReportFile">{{ __('Final report file') }}</label>
                        <input id="finalReportFile" type="file" name="final_report" accept=".docx,.pdf,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required style="width:100%;padding:.7rem;border:1px solid var(--border);border-radius:10px;background:var(--surface);">
                        <button class="pmr-btn primary" type="submit" style="margin-top:.8rem;">{{ __('Upload Report File') }}</button>
                    </form>
                </div>
                <form method="post" action="{{ route('admin.programs.report.submit', $program->id) }}">
                    @csrf
                    <button class="pmr-btn primary" type="submit">{{ __('Send Report to :branch', ['branch' => $reportBranchLabel]) }}</button>
                </form>
            @elseif(!$canReviewReport)
                <div class="pmr-report-lock">{{ __('Report files are locked while the report is in review or archived. The assigned reviewer controls the next workflow action.') }}</div>
            @endif

            @if($canReviewReport)
                <form method="post" action="{{ route('admin.programs.report.review', $program->id) }}" style="margin-top:1rem;">
                    @csrf
                    <label for="report_review_note"><strong>{{ __('Review note') }}</strong></label>
                    <textarea id="report_review_note" name="review_note" rows="3" style="width:100%;margin-top:6px;padding:12px;border:1px solid var(--border);border-radius:12px;background:var(--surface);color:inherit;" placeholder="{{ __('Required when returning the report for correction') }}"></textarea>
                    <div class="pmr-actions" style="margin-top:10px;">
                        <button class="pmr-btn primary" name="decision" value="approve" type="submit">{{ $report->status === 'pending_kj_hep' ? __('Accept & Archive Report') : __('Approve & Forward') }}</button>
                        <button class="pmr-btn" name="decision" value="reject" type="submit">{{ __('Return for Correction') }}</button>
                    </div>
                </form>
            @endif
        @endif
    </section>

    <!-- Bottom: Real-Time Attendee Roster -->
    <section class="pmr-card" style="margin-bottom:1.25rem;">
        <span class="pmr-eyebrow">{{ __('CERTIFICATES') }}</span>
        @if($program->certificate_enabled ?? true)
            <h2>{{ __('Choose Certificate Design') }}</h2>
            <p>{{ __('Review the certificate design before generating private PDFs for eligible Politeknik Besut students.') }}</p>
            <div class="pmr-certificate-layout">
                <form class="pmr-certificate-settings" method="post" action="{{ route('admin.programs.certificates.generate',$program->id) }}">
                    @csrf
                    <label for="certificateTemplate">{{ __('Certificate template') }}</label>
                    <select id="certificateTemplate" name="certificate_template" required>
                        <option value="standard_placeholder" @selected(($program->certificate_template ?? 'standard_placeholder') === 'standard_placeholder')>{{ __('Standard certificate — temporary design') }}</option>
                    </select>
                    <p>{{ __('More official certificate templates can be added later. The selected design is saved when generation starts.') }}</p>
                    <div class="pmr-actions">
                        @if($canManageCertificates)<button class="pmr-btn" type="submit" formaction="{{ route('admin.programs.certificates.generate-test',$program->id) }}">{{ __('Generate Test Certificate') }}</button>@endif
                        @if($canManageCertificates)<button class="pmr-btn primary" type="submit">{{ __('Generate All Eligible Certificates') }}</button>@endif
                        <a class="pmr-btn" href="{{ route('admin.program-certificates.index',['program_id'=>$program->id]) }}">{{ __('Search Certificate Records') }}</a>
                    </div>
                </form>
                <div class="pmr-certificate-preview" aria-label="{{ __('Certificate design preview') }}">
                    <div class="pmr-certificate-preview__inner">
                        <div class="pmr-certificate-preview__brand">STUDENTEDGE · POLITEKNIK BESUT</div>
                        <div class="pmr-certificate-preview__title">{{ __('SIJIL PENYERTAAN') }}</div>
                        <div class="pmr-certificate-preview__meta">{{ __('Dengan ini diperakui bahawa') }}</div>
                        <div class="pmr-certificate-preview__name">{{ __('NAMA PELAJAR') }}</div>
                        <div class="pmr-certificate-preview__meta">{{ __('Preview only') }} · {{ $program->title }}</div>
                    </div>
                </div>
            </div>
        @else
            <h2>{{ __('Points-only program') }}</h2>
            <div class="pmr-points-only"><strong>{{ __('Certificates are not provided for this program.') }}</strong><p>{{ __('Students with valid attendance will still receive the participation points configured by the Program Director.') }}</p></div>
        @endif
    </section>

    <section class="pmr-card">
        <span class="pmr-eyebrow">{{ __('PARTICIPANT ROSTER') }}</span>
        <h2>{{ __('Joined Student Roster & Live Attendance') }}</h2>

        @if($attendances->isEmpty())
            <div class="pmr-roster-empty">
                <div class="pmr-roster-empty__inner">
                    <div class="pmr-roster-empty__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20"/><circle cx="10" cy="8" r="4"/><path d="M17 8v6m-3-3h6"/></svg></div>
                    <h3>{{ __('Waiting for participants') }}</h3>
                    <p>{{ $program->attendance_status === 'open' ? __('Attendance is open. Share the public check-in link or display the QR code so participants can record attendance.') : __('Attendance is currently closed. Open attendance when you are ready to receive participant check-ins.') }}</p>
                    <div class="pmr-roster-empty__toolbar">
                        <span class="pmr-roster-empty__status">{{ $program->attendance_status === 'open' ? __('Attendance Open') : __('Attendance Closed') }}</span>
                        @if($program->attendance_status === 'open')
                        <div class="pmr-actions">
                            <a class="pmr-btn public-checkin" href="{{ $publicCheckinUrl }}" target="_blank" rel="noopener"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 5h5v5"/><path d="m10 14 9-9"/><path d="M19 13v5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h5"/></svg>{{ __('Open Public Check-in') }}</a>
                            <button class="pmr-btn" type="button" onclick="navigator.clipboard.writeText(@js($publicCheckinUrl)); this.textContent='{{ __('Copied') }}'">{{ __('Copy Check-in Link') }}</button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="pmr-table">
                    <thead>
                        <tr>
                            <th>{{ __('Participant') }}</th>
                            <th>{{ __('Identifier') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Checked In') }}</th>
                            <th>{{ __('Survey Rating') }}</th>
                            <th>{{ __('Attendance Validation') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $row)
                            <tr>
                                <td>
                                    <strong>{{ $row->full_name }}</strong>
                                    @if($row->institution_or_unit)
                                        <div style="font-size: 0.8rem; color: var(--text-secondary, #746b62);">{{ $row->institution_or_unit }}</div>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ __(str_replace('_', ' ', $row->validation_status)) }}</strong>
                                    <div style="font-size:.78rem;color:var(--text-secondary,#746b62);">
                                        {{ $row->distance_m !== null ? number_format($row->distance_m, 1).'m '.__('from venue') : __('No distance') }}
                                        @if($row->location_accuracy_m !== null) &middot; {{ number_format($row->location_accuracy_m, 1) }}m {{ __('accuracy') }} @endif
                                    </div>
                                </td>
                                <td><code>{{ $row->identifier }}</code></td>
                                <td>
                                    <span class="pmr-badge" style="background: {{ $row->attendee_type === 'internal' ? '#e7f7ee' : '#e0f2fe' }}; color: {{ $row->attendee_type === 'internal' ? '#18734a' : '#0284c7' }};">
                                        {{ strtoupper($row->attendee_type) }}
                                    </span>
                                </td>
                                <td>{{ \Illuminate\Support\Carbon::parse($row->checked_in_at)->format('d M Y, g:i A') }}</td>
                                <td>
                                    @if($row->satisfaction_rating)
                                        <span style="font-weight: 800; color: #704a23;">★ {{ $row->satisfaction_rating }} / 5</span>
                                    @else
                                        <span style="color: var(--text-secondary, #746b62); font-size: 0.8rem;">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</main>

<script>
let questionCounter = {{ count($questions) ?: 1 }};

function escapeQuestionValue(value) {
    return String(value).replace(/[&<>'"]/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    })[character]);
}

function addQuestionRow(text = '', type = 'text', required = true) {
    const container = document.getElementById('questionsContainer');
    const div = document.createElement('div');
    div.className = 'pmr-q-item';
    div.innerHTML = `
        <div class="pmr-q-head">
            <strong class="pmr-q-title"><span class="pmr-q-number">${questionCounter + 1}</span>Question</strong>
            <button class="pmr-remove" type="button" onclick="this.closest('.pmr-q-item').remove()"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m-8 0 1 13h8l1-13M10 11v5m4-5v5"/></svg>Remove</button>
        </div>
        <label class="pmr-q-field"><span>Question text</span><input type="text" name="questions[${questionCounter}][question_text]" value="${escapeQuestionValue(text)}" required placeholder="Enter question text"></label>
        <label class="pmr-q-field"><span>Answer type</span><select name="questions[${questionCounter}][question_type]">
            <option value="text" ${type === 'text' ? 'selected' : ''}>Long Written Answer</option>
            <option value="rating_5" ${type === 'rating_5' ? 'selected' : ''}>Rating 1-5 Stars</option>
        </select></label>
        <label class="pmr-q-required">
            <input type="hidden" name="questions[${questionCounter}][is_required]" value="0">
            <input type="checkbox" name="questions[${questionCounter}][is_required]" value="1" ${required ? 'checked' : ''}>
            Required question
        </label>
    `;
    container.appendChild(div);
    questionCounter++;
}

document.getElementById('btnGenerateAi')?.addEventListener('click', async () => {
    const focus = document.getElementById('aiFocus').value;
    const count = document.getElementById('aiCount').value;
    const btn = document.getElementById('btnGenerateAi');

    btn.disabled = true;
    btn.innerText = 'AI is generating questions...';

    try {
        const response = await fetch('{{ route("admin.programs.ai-questionnaire", $program->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ focus: focus, question_count: count })
        });

        const data = await response.json();
        if (data.success && Array.isArray(data.questions)) {
            const container = document.getElementById('questionsContainer');
            container.innerHTML = '';
            questionCounter = 0;

            data.questions.forEach(q => {
                addQuestionRow(q.question_text, q.question_type, q.is_required !== false);
            });
        }
    } catch (e) {
        alert('Could not generate questions automatically. Please key in questions manually.');
    } finally {
        btn.disabled = false;
        btn.innerText = 'Suggest Questions with AI';
    }
});

const participationMode = document.getElementById('participationMode');
const questionnaireBuilderContent = document.getElementById('questionnaireBuilderContent');
const attendanceOnlyMessage = document.getElementById('attendanceOnlyMessage');
const participationModeNotice = document.getElementById('participationModeNotice');

function syncParticipationMode() {
    if (!participationMode) return;
    const questionnaireSelected = participationMode.value === '1';
    const selectionSaved = participationMode.value === participationMode.dataset.savedMode;
    if (questionnaireBuilderContent) questionnaireBuilderContent.hidden = !questionnaireSelected || !selectionSaved;
    if (attendanceOnlyMessage) attendanceOnlyMessage.hidden = questionnaireSelected || !selectionSaved;
    if (participationModeNotice) participationModeNotice.style.display = selectionSaved ? 'none' : 'block';
}

participationMode?.addEventListener('change', syncParticipationMode);
syncParticipationMode();
</script>
@endsection
