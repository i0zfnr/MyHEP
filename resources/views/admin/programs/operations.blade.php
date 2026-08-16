@extends('layouts.app')

@section('title', __('Program Operations & Attendance - ').$program->title)

@push('styles')
<style>
.pmr { max-width: 1300px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.25rem; }
.pmr-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    padding: 1.5rem 1.75rem;
    border-radius: 18px;
    background: var(--surface, #fff);
    border: 1px solid var(--border, #eadac8);
    box-shadow: 0 10px 28px rgba(36,26,18,0.06);
}
.pmr-hero h1 { margin: 0.2rem 0 0.25rem; font-size: 1.45rem; font-weight: 850; color: var(--text, #241d16); }
.pmr-hero p { margin: 0; font-size: 0.85rem; color: var(--text-muted, #746b62); }
.pmr-eyebrow { font-size: 0.72rem; font-weight: 850; letter-spacing: 0.08em; text-transform: uppercase; color: var(--pm-accent, #b99150); }
.pmr-actions { display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; }
.pmr-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.55rem 1rem;
    border-radius: 10px;
    border: 1px solid var(--border, #eadac8);
    background: var(--surface, #fff);
    color: var(--text, #241d16);
    font-size: 0.85rem;
    font-weight: 750;
    text-decoration: none;
    cursor: pointer;
    transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
}
.pmr-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(36,26,18,0.08); background: var(--surface-hover, #fdfbf7); }
.pmr-btn.primary {
    background: var(--pm-accent, #b99150);
    border-color: var(--pm-accent, #b99150);
    color: #fff;
}
.pmr-btn.primary:hover { background: color-mix(in srgb, var(--pm-accent, #b99150) 88%, #000); }
.pmr-btn.public-checkin {
    background: #0284c7;
    border-color: #0284c7;
    color: #fff;
}
.pmr-btn.public-checkin:hover { background: #0369a1; }
.pmr-btn svg { width: 15px; height: 15px; fill: none; stroke: currentColor; stroke-width: 2; }

.pmr-card {
    background: var(--surface, #fff);
    border: 1px solid var(--border, #eadac8);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 18px rgba(36,26,18,0.04);
}
.pmr-card h2 { margin: 0.2rem 0 0.4rem; font-size: 1.15rem; font-weight: 800; }
.pmr-card p { margin: 0 0 1rem; font-size: 0.85rem; color: var(--text-muted, #746b62); }

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
.pmr-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; }

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

/* Constrain all icons inside PMR */
.pmr svg { max-width: 100%; }

/* Participant Roster Empty State */
.pmr-roster-empty {
    text-align: center;
    padding: 2.5rem 1.5rem;
    background: color-mix(in srgb, var(--pm-accent, #b99150) 4%, var(--surface, #fff));
    border-radius: 14px;
    border: 1px dashed color-mix(in srgb, var(--pm-accent, #b99150) 24%, var(--border, #eadac8));
    margin-top: 0.75rem;
}
.pmr-roster-empty__inner {
    max-width: 480px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
}
.pmr-roster-empty__icon {
    display: grid;
    place-items: center;
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: color-mix(in srgb, var(--pm-accent, #b99150) 12%, var(--surface, #fff));
    color: var(--pm-accent, #b99150);
    margin-bottom: 1rem;
    border: 1px solid color-mix(in srgb, var(--pm-accent, #b99150) 28%, var(--border, #eadac8));
}
.pmr-roster-empty__icon svg {
    width: 26px !important;
    height: 26px !important;
    fill: none !important;
    stroke: currentColor !important;
    stroke-width: 1.8 !important;
    stroke-linecap: round;
    stroke-linejoin: round;
}
.pmr-roster-empty h3 {
    margin: 0 0 0.35rem;
    font-size: 1.1rem;
    font-weight: 800;
    color: var(--text, #241d16);
}
.pmr-roster-empty p {
    margin: 0 0 1rem;
    font-size: 0.85rem;
    color: var(--text-muted, #746b62);
    line-height: 1.45;
}
.pmr-roster-empty__toolbar {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    flex-wrap: wrap;
}
.pmr-roster-empty__status {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    min-height: 36px;
    padding: 0.35rem 0.75rem;
    border-radius: 10px;
    background: color-mix(in srgb, var(--pm-accent, #b99150) 8%, var(--surface, #fff));
    color: var(--pm-accent-strong, #8b6a34);
    font-size: 0.72rem;
    font-weight: 850;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border: 1px solid color-mix(in srgb, var(--pm-accent, #b99150) 20%, var(--border, #eadac8));
}
.pmr-roster-empty__status::before {
    content: '';
    width: 7px;
    height: 7px;
    border-radius: 50%;
    background: currentColor;
}

/* Report Sources Checklist */
.pmr-source-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.75rem;
    margin: 1rem 0;
}
.pmr-source-item {
    padding: 0.75rem 1rem;
    border-radius: 10px;
    background: color-mix(in srgb, var(--surface, #fff) 94%, var(--pm-accent, #b99150));
    border: 1px solid var(--border, #eadac8);
}
.pmr-source-item.is-ready {
    border-color: color-mix(in srgb, #21835a 30%, var(--border, #eadac8));
    background: color-mix(in srgb, #21835a 6%, var(--surface, #fff));
}
.pmr-source-item span {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    color: var(--text-muted, #746b62);
    display: block;
}
.pmr-source-item strong {
    font-size: 0.95rem;
    color: var(--text, #241d16);
    display: block;
    margin-top: 2px;
}
.pmr-source-item.is-ready strong {
    color: #187048;
}

/* Certificate Layout & Preview */
.pmr-certificate-layout {
    display: grid;
    grid-template-columns: 1.2fr 1fr;
    gap: 1.25rem;
    align-items: start;
    margin-top: 0.75rem;
}
.pmr-certificate-settings {
    display: flex;
    flex-direction: column;
    gap: 0.6rem;
}
.pmr-certificate-settings select {
    width: 100%;
    padding: 9px 12px;
    border-radius: 9px;
    border: 1px solid var(--border, #eadac8);
    font-size: 0.9rem;
    background: #fff;
}
.pmr-certificate-preview {
    border-radius: 14px;
    padding: 1.25rem;
    background: linear-gradient(135deg, #1e1b18, #2a241f);
    border: 2px solid color-mix(in srgb, var(--pm-accent, #b99150) 60%, transparent);
    box-shadow: 0 10px 24px rgba(0,0,0,0.18);
    color: #fff;
    text-align: center;
}
.pmr-certificate-preview__inner {
    border: 1px dashed color-mix(in srgb, var(--pm-accent, #b99150) 50%, #fff);
    padding: 1.25rem 1rem;
    border-radius: 10px;
}
.pmr-certificate-preview__brand {
    font-size: 0.65rem;
    font-weight: 850;
    letter-spacing: 0.12em;
    color: var(--pm-accent, #c8a96a);
    text-transform: uppercase;
}
.pmr-certificate-preview__title {
    font-size: 1.15rem;
    font-weight: 900;
    letter-spacing: 0.06em;
    margin: 0.4rem 0 0.3rem;
    color: #fff;
}
.pmr-certificate-preview__name {
    font-size: 1rem;
    font-weight: 800;
    color: var(--pm-accent, #c8a96a);
    text-decoration: underline;
    margin: 0.3rem 0;
}
.pmr-certificate-preview__meta {
    font-size: 0.72rem;
    color: rgba(255,255,255,0.7);
}
.pmr-points-only {
    padding: 1rem;
    border-radius: 10px;
    background: color-mix(in srgb, var(--pm-accent) 6%, var(--surface, #fff));
    border: 1px solid var(--border, #eadac8);
    margin-top: 0.5rem;
}

@media (max-width: 1050px) {
    .pmr-kpis { grid-template-columns: repeat(3, 1fr); }
    .pmr-grid-2 { grid-template-columns: 1fr; }
    .pmr-certificate-layout { grid-template-columns: 1fr; }
    .pmr-source-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 620px) { .pmr-source-grid { grid-template-columns: 1fr; } }
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
            <a class="pmr-btn primary" href="{{ route('admin.programs.questionnaire', $program->id) }}">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.5L19 7.5V19a2 2 0 0 1-2 2z"/></svg>
                {{ __('Questionnaire Builder') }}
            </a>
            <a class="pmr-btn public-checkin" href="{{ $publicCheckinUrl }}" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 5h5v5"/><path d="m10 14 9-9"/><path d="M19 13v5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h5"/></svg>
                {{ __('Open Public Check-in') }}
            </a>
        </div>
    </header>

    <!-- Attendance Control Card -->
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

    <!-- 2-Column Workspace Grid: Questionnaire Summary + QR Code -->
    <section class="pmr-grid-2">
        <!-- Left: Questionnaire & Participation Setup Status -->
        <article class="pmr-card" style="display:flex;flex-direction:column;">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:.75rem;flex-wrap:wrap;">
                <div>
                    <span class="pmr-eyebrow">{{ __('QUESTIONNAIRE & PARTICIPATION') }}</span>
                    <h2>{{ __('Questionnaire Setup') }}</h2>
                </div>
                @if(!$program->questionnaire_enabled)
                    <span class="pmr-live-status is-closed">{{ __('Attendance Only') }}</span>
                @elseif($survey && $survey->status === 'published')
                    <span class="pmr-live-status">{{ __('Published & Live') }}</span>
                @else
                    <span class="pmr-live-status is-closed">{{ __('Draft') }}</span>
                @endif
            </div>

            <p style="margin:.5rem 0 1.25rem;font-size:.88rem;color:var(--text-muted,#746b62);line-height:1.45;">
                @if(!$program->questionnaire_enabled)
                    {{ __('This program is configured for attendance-only check-in. Participants do not need to fill out a survey.') }}
                @elseif($survey && $survey->status === 'published')
                    {{ __('The questionnaire is published and active. Participants answer questions during check-in.') }}
                @else
                    {{ __('The questionnaire is in draft. Configure questions and publish it before opening participant check-in.') }}
                @endif
            </p>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:.75rem;margin-bottom:1.25rem;">
                <div style="padding:.75rem 1rem;background:color-mix(in srgb,var(--pm-accent) 6%,var(--surface,#fff));border-radius:10px;border:1px solid var(--border,#eadac8);">
                    <span style="font-size:.72rem;text-transform:uppercase;font-weight:800;color:var(--text-muted,#746b62);display:block;margin-bottom:2px;">{{ __('Configured Questions') }}</span>
                    <strong style="font-size:1.15rem;color:var(--text,#241d16);">{{ count($questions) }} {{ __('Questions') }}</strong>
                </div>
                <div style="padding:.75rem 1rem;background:color-mix(in srgb,var(--pm-accent) 6%,var(--surface,#fff));border-radius:10px;border:1px solid var(--border,#eadac8);">
                    <span style="font-size:.72rem;text-transform:uppercase;font-weight:800;color:var(--text-muted,#746b62);display:block;margin-bottom:2px;">{{ __('Survey Responses') }}</span>
                    <strong style="font-size:1.15rem;color:var(--pm-accent,#b99150);">{{ number_format($surveyResponsesCount) }}</strong>
                </div>
            </div>

            <div class="pmr-actions" style="margin-top:auto;">
                <a class="pmr-btn primary" href="{{ route('admin.programs.questionnaire', $program->id) }}" style="width:100%;justify-content:center;min-height:42px;">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    {{ __('Open Questionnaire Builder') }}
                </a>
            </div>
        </article>

        <!-- Right: Public QR Code & External Guest Check-in -->
        <article class="pmr-card">
            <span class="pmr-eyebrow">{{ __('ATTENDANCE & PUBLIC QR CODE') }}</span>
            <h2>{{ __('QR Check-in & Public Link') }}</h2>
            <p>{{ __('Display or print this QR code for external guests and internal students to check in and complete the questionnaire.') }}</p>

            <div class="pmr-qr-box">
                <div class="pmr-qr-image">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($publicCheckinUrl) }}" alt="{{ __('Program Attendance QR Code') }}" style="width: 180px; height: 180px;">
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

    <!-- Bottom: Certificates & Real-Time Attendee Roster -->
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
                    <div class="pmr-roster-empty__icon" aria-hidden="true"><svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 20v-1.5a3.5 3.5 0 0 0-3.5-3.5h-5A3.5 3.5 0 0 0 4 18.5V20"/><circle cx="10" cy="8" r="4"/><path d="M17 8v6m-3-3h6"/></svg></div>
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
@endsection
