@extends('layouts.app')

@section('title', __('Program Operations & Attendance - ').$program->title)



@push('styles')
@include('admin.programs.partials.design-system')
<style>
    .pmr-student-autocomplete {
        position: relative;
    }

    .pmr-student-permission-form {
        overflow: visible;
    }

    .pmr-student-permission-grid {
        align-items: end;
        display: grid;
        gap: .85rem;
        grid-template-columns: minmax(260px, 1.2fr) minmax(180px, .65fr) minmax(190px, .65fr);
    }

    .pmr-student-permission-actions {
        align-items: end;
        display: grid;
        gap: .85rem;
        grid-template-columns: minmax(260px, 1fr) auto;
        margin-top: .85rem;
    }

    .pmr-student-autocomplete input[type="search"] {
        width: 100%;
        min-height: 44px;
        border: 1px solid var(--border, #eadac8);
        border-radius: 10px;
        background: var(--surface, #fff);
        color: var(--text, #241d16);
        padding: .7rem .85rem;
        font: inherit;
        outline: none;
    }

    .pmr-student-autocomplete input[type="search"]:focus {
        border-color: var(--pm-accent, #b99150);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--pm-accent, #b99150) 16%, transparent);
    }

    .pmr-student-autocomplete__results {
        position: absolute;
        z-index: 120;
        top: calc(100% + 6px);
        left: 0;
        right: 0;
        max-height: 320px;
        overflow: auto;
        border: 1px solid var(--border, #eadac8);
        border-radius: 16px;
        background: color-mix(in srgb, var(--surface, #fff) 94%, var(--pm-accent, #b99150) 6%);
        box-shadow: 0 24px 54px rgba(36, 26, 18, .18);
        padding: .35rem;
    }

    .pmr-student-autocomplete__option {
        width: 100%;
        border: 0;
        border-radius: 10px;
        background: transparent;
        color: var(--text, #241d16);
        cursor: pointer;
        display: block;
        padding: .75rem .85rem;
        text-align: left;
    }

    .pmr-student-autocomplete__option:hover,
    .pmr-student-autocomplete__option.is-active {
        background: color-mix(in srgb, var(--pm-accent, #b99150) 12%, transparent);
    }

    .pmr-student-autocomplete__option strong {
        display: block;
        font-size: .9rem;
        letter-spacing: -.01em;
    }

    .pmr-student-autocomplete__option span {
        color: var(--text-muted, #746b62);
        display: block;
        font-size: .78rem;
        margin-top: .15rem;
    }

    @media (max-width: 900px) {
        .pmr-student-permission-grid,
        .pmr-student-permission-actions {
            grid-template-columns: 1fr;
        }
    }

    .pmr-student-autocomplete__empty {
        color: var(--text-muted, #746b62);
        font-size: .85rem;
        padding: .75rem .8rem;
    }

    .pmr-permission-table {
        table-layout: fixed;
        width: 100%;
    }

    .pmr-permission-table th,
    .pmr-permission-table td {
        overflow-wrap: anywhere;
        vertical-align: middle;
    }

    .pmr-permission-table th:nth-child(1),
    .pmr-permission-table td:nth-child(1) {
        width: 28%;
    }

    .pmr-permission-table th:nth-child(2),
    .pmr-permission-table td:nth-child(2) {
        width: 18%;
    }

    .pmr-permission-table th:nth-child(3),
    .pmr-permission-table td:nth-child(3) {
        width: 13%;
    }

    .pmr-permission-table th:nth-child(4),
    .pmr-permission-table td:nth-child(4) {
        width: 13%;
    }

    .pmr-permission-table th:nth-child(5),
    .pmr-permission-table td:nth-child(5) {
        width: 18%;
    }

    .pmr-permission-table th:nth-child(6),
    .pmr-permission-table td:nth-child(6) {
        text-align: right;
        width: 10%;
    }

    .pmr-permission-table .pmr-btn {
        min-height: 38px;
        padding: .55rem .8rem;
        white-space: nowrap;
    }
</style>
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
        </div>
    </header>

    <!-- Real-Time Joined Students & Analytics Bar -->
    <section class="pmr-kpis" style="margin-bottom: 1.25rem;">
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

    <!-- Dual Operations Grid: Attendance & Live Check-in (Left) + Questionnaire & Feedback (Right) -->
    <div class="pmr-grid-2" style="margin-bottom: 1.25rem;">

        <!-- 1. Attendance & Live Check-in Control Card -->
        <section class="pmr-card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap;">
                    <div>
                        <span class="pmr-eyebrow" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            {{ __('ATTENDANCE & LIVE CHECK-IN CONTROL') }}
                        </span>
                        <h2>{{ __('Attendance Operations') }}</h2>
                    </div>
                    @if($program->attendance_status !== 'open')
                        <span class="pmr-live-status is-closed">{{ __('Attendance Closed') }}</span>
                    @elseif(($program->attendance_checkin_mode ?? 'qr_code') === 'qr_code')
                        <span class="pmr-live-status" style="background:rgba(212,175,55,0.15);color:#b99150;border:1px solid rgba(212,175,55,0.3); display: inline-flex; align-items: center; gap: 0.35rem;">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                            {{ __('Mode 1: QR Scan Only') }}
                        </span>
                    @else
                        <span class="pmr-live-status" style="background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3); display: inline-flex; align-items: center; gap: 0.35rem;">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2 3 14h8l-1 8 11-14h-8z"/></svg>
                            {{ __('Mode 2: Portal & QR') }}
                        </span>
                    @endif
                </div>

                <p style="margin: .5rem 0 1.25rem; font-size: .88rem; color: var(--text-muted,#746b62); line-height: 1.45;">
                    {{ __('Control participant check-in for this program. Choose Mode 1 (QR Scan Only) to prevent proxy check-in by requiring the dynamic screen QR code, or Mode 2 (Portal & QR) for open check-in.') }}
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: .75rem; margin-bottom: 1.25rem;">
                    <div style="padding: .75rem 1rem; background: color-mix(in srgb,var(--pm-accent) 6%,var(--surface,#fff)); border-radius: 10px; border: 1px solid var(--border,#eadac8);">
                        <span style="font-size: .72rem; text-transform: uppercase; font-weight: 800; color: var(--text-muted,#746b62); display: block; margin-bottom: 2px;">{{ __('Total Joined') }}</span>
                        <strong style="font-size: 1.15rem; color: var(--text,#241d16);">{{ number_format($totalJoined) }} {{ __('Attendees') }}</strong>
                    </div>
                    <div style="padding: .75rem 1rem; background: color-mix(in srgb,var(--pm-accent) 6%,var(--surface,#fff)); border-radius: 10px; border: 1px solid var(--border,#eadac8);">
                        <span style="font-size: .72rem; text-transform: uppercase; font-weight: 800; color: var(--text-muted,#746b62); display: block; margin-bottom: 2px;">{{ __('Attendance Rate') }}</span>
                        <strong style="font-size: 1.15rem; color: var(--pm-accent,#b99150);">{{ $attendanceRate }}%</strong>
                    </div>
                </div>

                <!-- Attendance Action Options -->
                @if($canManageAttendance)
                    <div style="background: var(--bg-alt, #faf7f2); border: 1px solid var(--border); border-radius: 12px; padding: 1.15rem; margin-bottom: 1.25rem;">
                        <span style="font-size: 0.76rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 0.65rem;">
                            {{ __('Attendance Action Options:') }}
                        </span>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.75rem;">
                            <form method="post" action="{{ route('admin.programs.attendance.open', $program->id) }}">
                                @csrf
                                <input type="hidden" name="mode" value="qr_code">
                                <button type="submit" class="pmr-btn {{ $program->attendance_status === 'open' && ($program->attendance_checkin_mode ?? 'qr_code') === 'qr_code' ? 'primary' : '' }}" style="width: 100%; min-height: 42px; justify-content: center; font-size: 0.82rem; gap: 0.45rem;" @disabled(!$attendanceReady)>
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                    {{ __('Mode 1: QR Only') }}
                                </button>
                            </form>

                            <form method="post" action="{{ route('admin.programs.attendance.open', $program->id) }}">
                                @csrf
                                <input type="hidden" name="mode" value="portal_and_qr">
                                <button type="submit" class="pmr-btn {{ $program->attendance_status === 'open' && ($program->attendance_checkin_mode ?? '') === 'portal_and_qr' ? 'primary' : '' }}" style="width: 100%; min-height: 42px; justify-content: center; font-size: 0.82rem; gap: 0.45rem;" @disabled(!$attendanceReady)>
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M13 2 3 14h8l-1 8 11-14h-8z"/></svg>
                                    {{ __('Mode 2: Portal & QR') }}
                                </button>
                            </form>

                            @if($program->attendance_status === 'open')
                                <form method="post" action="{{ route('admin.programs.attendance.close', $program->id) }}">
                                    @csrf
                                    <button type="submit" class="pmr-btn" style="width: 100%; min-height: 42px; justify-content: center; font-size: 0.82rem; color: #dc2626; border-color: rgba(220,38,38,0.3); gap: 0.4rem;">
                                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                        {{ __('Close') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

                @if(!$attendanceReady)
                    <p class="pmr-attendance-warning" style="margin: 0 0 1.15rem !important;">
                        <span>
                        {{ __('Required before opening:') }}
                        @if(!$attendanceSetup['venue']) {{ __('venue') }}; @endif
                        @if(!$attendanceSetup['questionnaire']) {{ __('published questionnaire') }}. @endif
                        </span>
                    </p>
                @endif
            </div>

            <div class="pmr-actions" style="margin-top: 0.5rem;">
                <a class="pmr-btn" href="{{ route('admin.programs.presenter', $program->id) }}" target="_blank" style="width: 100%; justify-content: center; min-height: 42px; font-weight: 800; background: linear-gradient(135deg, rgba(212,175,55,0.14), rgba(99,102,241,0.10)); border-color: var(--pm-accent);">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                    {{ __('Launch Presenter Screen (Dynamic QR)') }}
                </a>
            </div>
        </section>

        <!-- 2. Questionnaire & Feedback Control Card -->
        <section class="pmr-card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap;">
                    <div>
                        <span class="pmr-eyebrow" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.5L19 7.5V19a2 2 0 0 1-2 2z"/></svg>
                            {{ __('QUESTIONNAIRE & FEEDBACK CONTROL') }}
                        </span>
                        <h2>{{ __('Questionnaire Publishing') }}</h2>
                    </div>
                    @if(!$survey || $survey->status !== 'published' || ($program->questionnaire_publish_mode ?? '') === 'closed')
                        <span class="pmr-live-status is-closed">{{ __('Draft / Closed') }}</span>
                    @elseif(($program->questionnaire_publish_mode ?? 'internal_system') === 'internal_system')
                        <span class="pmr-live-status" style="background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3); display: inline-flex; align-items: center; gap: 0.35rem;">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2 3 14h8l-1 8 11-14h-8z"/></svg>
                            {{ __('Direct in Portal (PB)') }}
                        </span>
                    @else
                        <span class="pmr-live-status" style="background:rgba(212,175,55,0.15);color:#b99150;border:1px solid rgba(212,175,55,0.3); display: inline-flex; align-items: center; gap: 0.35rem;">
                            <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                            {{ __('QR Scan Mode') }}
                        </span>
                    @endif
                </div>

                <p style="margin: .5rem 0 1.25rem; font-size: .88rem; color: var(--text-muted,#746b62); line-height: 1.45;">
                    {{ __('The Program Director can manage questionnaire publication at any time. Choose whether to publish directly to Politeknik Besut students on the portal without QR scanning, or require a QR scan.') }}
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: .75rem; margin-bottom: 1.25rem;">
                    <div style="padding: .75rem 1rem; background: color-mix(in srgb,var(--pm-accent) 6%,var(--surface,#fff)); border-radius: 10px; border: 1px solid var(--border,#eadac8);">
                        <span style="font-size: .72rem; text-transform: uppercase; font-weight: 800; color: var(--text-muted,#746b62); display: block; margin-bottom: 2px;">{{ __('Configured Questions') }}</span>
                        <strong style="font-size: 1.15rem; color: var(--text,#241d16);">{{ count($questions) }} {{ __('Questions') }}</strong>
                    </div>
                    <div style="padding: .75rem 1rem; background: color-mix(in srgb,var(--pm-accent) 6%,var(--surface,#fff)); border-radius: 10px; border: 1px solid var(--border,#eadac8);">
                        <span style="font-size: .72rem; text-transform: uppercase; font-weight: 800; color: var(--text-muted,#746b62); display: block; margin-bottom: 2px;">{{ __('Responses Received') }}</span>
                        <strong style="font-size: 1.15rem; color: var(--pm-accent,#b99150);">{{ number_format($surveyResponsesCount) }}</strong>
                    </div>
                </div>

                <!-- Publishing Mode Options -->
                @if($canManageAttendance)
                    <div style="background: var(--bg-alt, #faf7f2); border: 1px solid var(--border); border-radius: 12px; padding: 1.15rem; margin-bottom: 1.25rem;">
                        <span style="font-size: 0.76rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 0.65rem;">
                            {{ __('Questionnaire Publishing Options:') }}
                        </span>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 0.75rem;">
                            <form method="post" action="{{ route('admin.programs.survey.publish-mode', $program->id) }}">
                                @csrf
                                <input type="hidden" name="publish_mode" value="internal_system">
                                <button type="submit" class="pmr-btn {{ ($program->questionnaire_publish_mode ?? 'internal_system') === 'internal_system' && ($survey && $survey->status === 'published') ? 'primary' : '' }}" style="width: 100%; min-height: 42px; justify-content: center; font-size: 0.82rem; gap: 0.45rem;" @disabled(count($questions) === 0)>
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M13 2 3 14h8l-1 8 11-14h-8z"/></svg>
                                    {{ __('Mode 1: Portal') }}
                                </button>
                            </form>

                            <form method="post" action="{{ route('admin.programs.survey.publish-mode', $program->id) }}">
                                @csrf
                                <input type="hidden" name="publish_mode" value="qr_code">
                                <button type="submit" class="pmr-btn {{ ($program->questionnaire_publish_mode ?? '') === 'qr_code' && ($survey && $survey->status === 'published') ? 'primary' : '' }}" style="width: 100%; min-height: 42px; justify-content: center; font-size: 0.82rem; gap: 0.45rem;" @disabled(count($questions) === 0)>
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                    {{ __('Mode 2: QR Scan') }}
                                </button>
                            </form>

                            @if($survey && $survey->status === 'published' && ($program->questionnaire_publish_mode ?? '') !== 'closed')
                                <form method="post" action="{{ route('admin.programs.survey.close', $program->id) }}">
                                    @csrf
                                    <button type="submit" class="pmr-btn" style="width: 100%; min-height: 42px; justify-content: center; font-size: 0.82rem; color: #dc2626; gap: 0.4rem;">
                                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                        {{ __('Close') }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="pmr-actions" style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.5rem;">
                <a class="pmr-btn" href="{{ route('admin.programs.questionnaire', $program->id) }}" style="width: 100%; justify-content: center; min-height: 42px; font-weight: 800;">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.5L19 7.5V19a2 2 0 0 1-2 2z"/></svg>
                    {{ __('Open Questionnaire Builder') }}
                </a>
            </div>
        </section>
    </div>

    @if($canManageStudentPagePermissions)
        <section class="pmr-card" style="margin-bottom: 1.25rem; overflow: visible;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
                <div>
                    <span class="pmr-eyebrow">{{ __('STUDENT PAGE PERMISSIONS') }}</span>
                    <h2>{{ __('Allow selected students to open protected pages') }}</h2>
                    <p style="margin:.35rem 0 0;color:var(--text-muted,#746b62);font-size:.9rem;max-width:760px;">
                        {{ __('Use this when KJ HEP or System Admin assigns a responsible student to display the live dynamic attendance QR, or to open a restricted questionnaire page, without changing access for everyone else.') }}
                    </p>
                </div>
                <span class="pmr-live-status" style="background:rgba(212,175,55,.13);color:#926b1d;border:1px solid rgba(212,175,55,.32);">
                    {{ __('KJ HEP / System Admin only') }}
                </span>
            </div>

            <form method="post" action="{{ route('admin.programs.student-page-permissions.store', $program->id) }}" class="pmr-mode-panel pmr-student-permission-form" style="margin-bottom:1rem;">
                @csrf
                @php
                    $oldStudentId = old('student_id');
                    $oldStudent = $studentPermissionCandidates->firstWhere('id', (int) $oldStudentId);
                    $oldStudentLabel = $oldStudent
                        ? trim($oldStudent->full_name.' '.($oldStudent->matric_no ? '· '.$oldStudent->matric_no : '').($oldStudent->program ? ' · '. $oldStudent->program : ''))
                        : '';
                @endphp
                <div class="pmr-student-permission-grid">
                    <div>
                        <label for="studentPagePermissionSearch">{{ __('Student') }}</label>
                        <div class="pmr-student-autocomplete" data-student-autocomplete>
                            <input id="studentPagePermissionSearch" type="search" autocomplete="off" placeholder="{{ __('Search name, matric no, or program') }}" value="{{ $oldStudentLabel }}" data-student-search>
                            <input type="hidden" name="student_id" value="{{ old('student_id') }}" data-student-id required>
                            <div class="pmr-student-autocomplete__results" data-student-results hidden></div>
                        </div>
                    </div>
                    <div>
                        <label for="studentPagePermissionAccess">{{ __('Permission') }}</label>
                        <select id="studentPagePermissionAccess" name="access_type" required>
                            <option value="qr_presenter" @selected(old('access_type') === 'qr_presenter')>{{ __('Dynamic QR Presenter') }}</option>
                            <option value="questionnaire" @selected(old('access_type') === 'questionnaire')>{{ __('Questionnaire') }}</option>
                            <option value="all" @selected(old('access_type') === 'all')>{{ __('QR Presenter & Questionnaire') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="studentPagePermissionExpires">{{ __('Expires at (optional)') }}</label>
                        <input id="studentPagePermissionExpires" type="datetime-local" name="expires_at" value="{{ old('expires_at') }}">
                    </div>
                </div>
                <div class="pmr-student-permission-actions">
                    <div>
                        <label for="studentPagePermissionNote">{{ __('Admin note (optional)') }}</label>
                        <input id="studentPagePermissionNote" type="text" name="note" value="{{ old('note') }}" maxlength="500" placeholder="{{ __('Example: responsible class representative for opening the event QR screen') }}">
                    </div>
                    <button class="pmr-btn primary" type="submit">{{ __('Grant Permission') }}</button>
                </div>
            </form>

            @if($studentPagePermissions->isEmpty())
                <p style="margin:0;color:var(--text-muted,#746b62);font-size:.9rem;">{{ __('No student page permissions have been granted for this program.') }}</p>
            @else
                <div class="pmr-table-wrap">
                    <table class="pmr-table pmr-permission-table">
                        <thead>
                            <tr>
                                <th>{{ __('Student') }}</th>
                                <th>{{ __('Permission') }}</th>
                                <th>{{ __('Expires') }}</th>
                                <th>{{ __('Granted by') }}</th>
                                <th>{{ __('Note') }}</th>
                                <th>{{ __('Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($studentPagePermissions as $permission)
                                <tr>
                                    <td>
                                        <strong>{{ $permission->student_name }}</strong><br>
                                        <span style="color:var(--text-muted,#746b62);font-size:.84rem;">{{ $permission->matric_no ?: __('No matric no') }} @if($permission->student_program) · {{ $permission->student_program }} @endif</span>
                                    </td>
                                    <td>{{ __(match($permission->access_type) {
                                        'qr_presenter' => 'Dynamic QR Presenter',
                                        'questionnaire' => 'Questionnaire',
                                        default => 'QR Presenter & Questionnaire',
                                    }) }}</td>
                                    <td>{{ $permission->expires_at ? \Carbon\Carbon::parse($permission->expires_at)->format('d M Y, h:i A') : __('No expiry') }}</td>
                                    <td>{{ $permission->granted_by_name ?: __('Unknown admin') }}</td>
                                    <td>{{ $permission->note ?: '—' }}</td>
                                    <td>
                                        <form method="post" action="{{ route('admin.programs.student-page-permissions.destroy', [$program->id, $permission->id]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button class="pmr-btn" type="submit" style="color:#dc2626;border-color:rgba(220,38,38,.25);">{{ __('Revoke') }}</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    @endif

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
                        <input type="file" name="final_report" accept="application/pdf,.docx" required style="padding:.7rem;border:1px solid var(--border);border-radius:10px;background:var(--surface);">
                        <button class="pmr-btn primary" type="submit">{{ __('Upload Final Report') }}</button>
                    </form>
                </div>
            @endif

            @if($canManageReport && in_array($report->status, ['draft', 'rejected'], true))
                <form method="post" action="{{ route('admin.programs.report.submit', $program->id) }}">
                    @csrf
                    <button class="pmr-btn primary" type="submit">{{ __('Submit Report for :branch Review', ['branch' => $reportBranchLabel]) }}</button>
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

    <!-- Certificate Generation Section -->
    <section class="pmr-card" style="margin-bottom: 1.25rem;">
        <span class="pmr-eyebrow">{{ __('CERTIFICATE ISSUANCE') }}</span>
        @if((bool) ($program->certificate_enabled ?? true))
            <h2>{{ __('Generate Program Certificates') }}</h2>
            <p>{{ __('Choose the prepared design and generate certificates for all eligible students.') }}</p>
            @php
                $activeTemplateId = (int) ($program->certificate_template_id ?: ($certificateTemplates->first()->id ?? 0));
            @endphp
            <form method="post" action="{{ route('admin.programs.certificates.generate', $program->id) }}" class="pmr-mode-panel pmr-certificate-generator">
                @csrf
                <input type="hidden" name="certificate_template" value="standard_placeholder">
                @if($certificateTemplates->isNotEmpty())
                    <label for="uploadedCertTemplate">{{ __('Certificate design') }}</label>
                    <select id="uploadedCertTemplate" name="certificate_template_id" style="width:100%;margin-bottom:.75rem;">
                        @foreach($certificateTemplates as $template)
                            <option value="{{ $template->id }}" @selected($activeTemplateId === (int) $template->id)>{{ $template->name }}</option>
                        @endforeach
                    </select>
                @else
                    <div class="pmr-certificate-empty">
                        <strong>{{ __('No certificate design yet') }}</strong>
                        <span>{{ __('Upload one PDF and AI will prepare it for you.') }}</span>
                    </div>
                @endif

                <div class="pmr-actions pmr-certificate-main-actions">
                    @if($canManageCertificates && $certificateTemplates->isNotEmpty())
                        <button class="pmr-btn primary" type="submit">{{ __('Generate Certificates') }}</button>
                        <button class="pmr-btn" type="submit" formaction="{{ route('admin.programs.certificates.generate-test',$program->id) }}">{{ __('Test & Preview') }}</button>
                    @endif
                    <a class="pmr-btn" href="{{ route('admin.program-certificate-templates.index', ['program_id' => $program->id]) }}">{{ $certificateTemplates->isEmpty() ? __('Create Design') : __('New Design') }}</a>
                </div>

                <details class="pmr-certificate-more">
                    <summary>{{ __('More options') }}</summary>
                    <div class="pmr-actions">
                        <a class="pmr-btn" href="{{ route('admin.program-certificates.index',['program_id'=>$program->id]) }}">{{ __('Certificate Records') }}</a>
                        @if($canManageCertificates)
                            <button class="pmr-btn danger" type="submit" form="deleteProgramCertificatesForm" onclick="return confirm('{{ __('Delete all generated certificates for this program? This cannot be undone.') }}')">{{ __('Delete Generated Certificates') }}</button>
                        @endif
                    </div>
                </details>
            </form>
            @if($canManageCertificates)
                <form id="deleteProgramCertificatesForm" method="post" action="{{ route('admin.programs.certificates.destroy-all', $program->id) }}" style="display:none;">
                    @csrf
                    @method('DELETE')
                </form>
            @endif
        @else
            <h2>{{ __('Points-only program') }}</h2>
            <div class="pmr-points-only"><strong>{{ __('Certificates are not provided for this program.') }}</strong><p>{{ __('Students with valid attendance will still receive the participation points configured by the Program Director.') }}</p></div>
        @endif
    </section>

    <!-- Real-Time Joined Student Roster -->
    <section class="pmr-card">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;margin-bottom:1rem;">
            <div>
                <span class="pmr-eyebrow">{{ __('PARTICIPANT ROSTER') }}</span>
                <h2>{{ __('Joined Student Roster & Live Attendance') }}</h2>
            </div>
            <div style="display:flex;align-items:center;gap:0.6rem;">
                <span class="pmr-badge" style="background: rgba(16,185,129,0.12); color: #059669; font-weight: 800;">
                    {{ count($attendances) }} {{ __('Telah Mendaftar') }}
                </span>
            </div>
        </div>

        @if($attendances->isEmpty())
            <div class="pmr-roster-empty">
                <div class="pmr-roster-empty__inner">
                    <div class="pmr-roster-empty__icon-wrap">
                        <div class="pmr-roster-empty__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                    </div>
                    <h3>{{ __('Waiting for Participant Check-ins') }}</h3>
                    <p>{{ $program->attendance_status === 'open' ? __('Attendance is currently open. Students can check in by scanning the dynamic event QR code on their mobile devices or through public check-in.') : __('Attendance is currently closed. Open attendance to begin receiving live student check-ins and GPS verification.') }}</p>
                    
                    <div class="pmr-roster-guidelines">
                        <div class="pmr-roster-guide">
                            <strong>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4z"/><path d="M14 14h2m2 0h2m-6 4h6"/></svg>
                                {{ __('Dynamic QR Scan') }}
                            </strong>
                            <span>{{ __('Students scan the rotating dynamic QR code via the MyHEP mobile app or browser.') }}</span>
                        </div>
                        <div class="pmr-roster-guide">
                            <strong>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="m16 10-4 4-2-2"/></svg>
                                {{ __('GPS Geofence') }}
                            </strong>
                            <span>{{ __('Location and distance are verified automatically against the event venue coordinates.') }}</span>
                        </div>
                        <div class="pmr-roster-guide">
                            <strong>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                {{ __('Certificates & Points') }}
                            </strong>
                            <span>{{ __('Verified attendees automatically receive merit points and digital certificates.') }}</span>
                        </div>
                    </div>

                    <div class="pmr-roster-empty__toolbar">
                        <span class="pmr-roster-empty__status {{ $program->attendance_status === 'open' ? 'is-open' : 'is-closed' }}">
                            {{ $program->attendance_status === 'open' ? __('Live Check-in Active') : __('Attendance Closed') }}
                        </span>
                        @if($program->attendance_status === 'open')
                            <div class="pmr-actions">
                                <button class="pmr-btn" type="button" onclick="navigator.clipboard.writeText(@js($publicCheckinUrl)); this.textContent='{{ __('Copied') }}'">{{ __('Copy Check-in Link') }}</button>
                            </div>
                        @elseif($canManageAttendance)
                            <form method="post" action="{{ route('admin.programs.attendance.open', $program->id) }}" style="display:inline;">
                                @csrf
                                <button class="pmr-btn primary" type="submit">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                                    {{ __('Open Attendance Now') }}
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div style="overflow-x: auto;">
                <table class="pmr-table">
                    <thead>
                        <tr>
                            <th>{{ __('Student / Attendee') }}</th>
                            <th>{{ __('Matric No. / Identifier') }}</th>
                            <th>{{ __('Category') }}</th>
                            <th>{{ __('Location Verification') }}</th>
                            <th>{{ __('Certificate Status') }}</th>
                            <th>{{ __('Certificate') }}</th>
                            <th>{{ __('Scan Time') }}</th>
                            <th>{{ __('Questionnaire') }}</th>
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
                                <td><code>{{ $row->identifier }}</code></td>
                                <td>
                                    <span class="pmr-badge" style="background: {{ $row->attendee_type === 'internal' ? 'rgba(16, 185, 129, 0.12)' : 'rgba(2, 132, 199, 0.12)' }}; color: {{ $row->attendee_type === 'internal' ? '#059669' : '#0284c7' }}; border: 1px solid {{ $row->attendee_type === 'internal' ? 'rgba(16, 185, 129, 0.25)' : 'rgba(2, 132, 199, 0.25)' }}; font-weight: 800;">
                                        {{ $row->attendee_type === 'internal' ? __('PB Student') : __('External Guest') }}
                                    </span>
                                </td>
                                <td>
                                    <strong>{{ __(str_replace('_', ' ', $row->validation_status)) }}</strong>
                                    <div style="font-size:.78rem;color:var(--text-secondary,#746b62);">
                                        {{ $row->distance_m !== null ? number_format($row->distance_m, 1).'m '.__('from venue') : __('No distance recorded') }}
                                        @if($row->location_accuracy_m !== null) &middot; {{ number_format($row->location_accuracy_m, 1) }}m {{ __('accuracy') }} @endif
                                    </div>
                                </td>
                                <td>
                                    @if($row->attendee_type !== 'internal')
                                        <span class="pmr-badge certificate muted">{{ __('Not applicable') }}</span>
                                    @elseif($row->certificate_status === 'ready')
                                        <span class="pmr-badge certificate ready">{{ __('Certificate Ready') }}</span>
                                        @if($row->certificate_generated_at)
                                            <div class="pmr-cert-meta">{{ \Illuminate\Support\Carbon::parse($row->certificate_generated_at)->format('d M Y, g:i A') }}</div>
                                        @endif
                                    @elseif(in_array($row->certificate_status, ['pending', 'generating'], true))
                                        <span class="pmr-badge certificate pending">{{ __(ucfirst($row->certificate_status)) }}</span>
                                    @elseif($row->certificate_status === 'failed')
                                        <span class="pmr-badge certificate failed">{{ __('Failed') }}</span>
                                        @if($row->certificate_failure_reason)
                                            <div class="pmr-cert-meta failed" title="{{ $row->certificate_failure_reason }}">{{ \Illuminate\Support\Str::limit($row->certificate_failure_reason, 72) }}</div>
                                        @endif
                                    @else
                                        <span class="pmr-badge certificate missing">{{ __('No Certificate Yet') }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($row->certificate_status === 'ready' && $row->certificate_id)
                                        <a class="pmr-mini-action" href="{{ route('admin.program-certificates.download', $row->certificate_id) }}" target="_blank" rel="noopener">
                                            {{ __('View Certificate') }}
                                        </a>
                                    @elseif($row->certificate_status === 'failed')
                                        <span class="pmr-cert-meta">{{ __('Regenerate required') }}</span>
                                    @elseif(in_array($row->certificate_status, ['pending', 'generating'], true))
                                        <span class="pmr-cert-meta">{{ __('Processing') }}</span>
                                    @else
                                        <span class="pmr-cert-meta">—</span>
                                    @endif
                                </td>
                                <td>{{ \Illuminate\Support\Carbon::parse($row->checked_in_at)->format('d M Y, g:i A') }}</td>
                                <td>
                                    @if($row->satisfaction_rating)
                                        <span style="font-weight: 800; color: #b99150;">★ {{ $row->satisfaction_rating }} / 5</span>
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

@push('scripts')
@php
    $studentAutocompleteOptions = $studentPermissionCandidates->map(function ($student) {
        $label = trim($student->full_name.' '.($student->matric_no ? '· '.$student->matric_no : '').($student->program ? ' · '.$student->program : ''));

        return [
            'id' => $student->id,
            'name' => $student->full_name,
            'matric' => $student->matric_no,
            'program' => $student->program,
            'label' => $label,
            'search' => strtolower(trim($student->full_name.' '.$student->matric_no.' '.$student->program)),
        ];
    })->values();
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const students = @json($studentAutocompleteOptions);

    document.querySelectorAll('[data-student-autocomplete]').forEach(function (root) {
        const input = root.querySelector('[data-student-search]');
        const hidden = root.querySelector('[data-student-id]');
        const results = root.querySelector('[data-student-results]');
        let activeIndex = -1;
        let currentMatches = [];

        if (!input || !hidden || !results) return;

        const closeResults = function () {
            results.hidden = true;
            results.innerHTML = '';
            activeIndex = -1;
        };

        const selectStudent = function (student) {
            input.value = student.label;
            hidden.value = student.id;
            closeResults();
        };

        const renderResults = function () {
            results.innerHTML = '';

            if (currentMatches.length === 0) {
                const empty = document.createElement('div');
                empty.className = 'pmr-student-autocomplete__empty';
                empty.textContent = '{{ __('No student found. Try name, matric no, or program.') }}';
                results.appendChild(empty);
                results.hidden = false;
                return;
            }

            currentMatches.forEach(function (student, index) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'pmr-student-autocomplete__option' + (index === activeIndex ? ' is-active' : '');
                button.innerHTML = '<strong></strong><span></span>';
                button.querySelector('strong').textContent = student.name || '{{ __('Unnamed student') }}';
                button.querySelector('span').textContent = [student.matric, student.program].filter(Boolean).join(' · ');
                button.addEventListener('mousedown', function (event) {
                    event.preventDefault();
                    selectStudent(student);
                });
                results.appendChild(button);
            });

            results.hidden = false;
        };

        const updateMatches = function () {
            const term = input.value.trim().toLowerCase();
            hidden.value = '';

            if (term.length === 0) {
                closeResults();
                return;
            }

            currentMatches = students
                .filter(function (student) {
                    return student.search.includes(term);
                })
                .slice(0, 12);
            activeIndex = currentMatches.length > 0 ? 0 : -1;
            renderResults();
        };

        input.addEventListener('input', updateMatches);
        input.addEventListener('focus', function () {
            if (input.value.trim() !== '' && hidden.value === '') {
                updateMatches();
            }
        });
        input.addEventListener('keydown', function (event) {
            if (results.hidden) return;

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                activeIndex = Math.min(activeIndex + 1, currentMatches.length - 1);
                renderResults();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                renderResults();
            } else if (event.key === 'Enter' && activeIndex >= 0 && currentMatches[activeIndex]) {
                event.preventDefault();
                selectStudent(currentMatches[activeIndex]);
            } else if (event.key === 'Escape') {
                closeResults();
            }
        });

        document.addEventListener('mousedown', function (event) {
            if (!root.contains(event.target)) {
                closeResults();
            }
        });
    });
});
</script>
@endpush
