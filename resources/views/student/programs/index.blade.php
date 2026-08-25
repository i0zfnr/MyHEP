@extends('layouts.app')
@section('title', __('Program Activities'))
@section('header')<h2 style="margin:0;font-size:1.15rem;font-weight:800;">{{ __('Program Activities') }}</h2>@endsection



@section('content')
<main class="sp-wrap">
    @if(session('success'))
        <div class="alert alert-success" style="border-radius:14px;">{{ session('success') }}</div>
    @endif

    {{-- Active Survey Callout --}}
    @if(!empty($activeSurveys) && $activeSurveys->isNotEmpty())
        <div class="sp-survey-banner">
            <div class="sp-survey-copy">
                <div class="sp-survey-icon">
                    <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div>
                    <strong style="font-size: 1.05rem; display: block; color: var(--text);">{{ __('Soal Selidik Program Aktif') }}</strong>
                    <span style="font-size: 0.84rem; color: var(--text-muted);">{{ __('Sila lengkapkan maklum balas program Politeknik Besut yang anda sertai.') }}</span>
                </div>
            </div>
            <div style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
                @foreach($activeSurveys as $surveyProgram)
                    <a href="{{ route('student.programs.survey', $surveyProgram->program_id) }}" class="sp-survey-btn">
                        <svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.5L19 7.5V19a2 2 0 0 1-2 2z"/></svg>
                        <span>{{ __('Jawab: :title', ['title' => \Illuminate\Support\Str::limit($surveyProgram->program_title, 24)]) }}</span>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- KPI Cards --}}
    <section class="sp-kpis">
        <article class="sp-kpi-card">
            <div class="sp-kpi-info">
                <span>{{ __('Total Merit Points') }}</span>
                <strong>{{ number_format($totalPoints) }}</strong>
            </div>
            <div class="sp-kpi-icon">
                <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
        </article>
        <article class="sp-kpi-card">
            <div class="sp-kpi-info">
                <span>{{ __('Programs Joined') }}</span>
                <strong>{{ number_format($programsJoined) }}</strong>
            </div>
            <div class="sp-kpi-icon">
                <svg viewBox="0 0 24 24" width="26" height="26" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
        </article>
    </section>

    {{-- Program List --}}
    <section class="sp-section">
        <div class="sp-section-head">
            <h1>{{ __('Politeknik Besut Programs') }}</h1>
            <p>{{ __('Join open programs, view merit points, and download certificates linked to your matric number.') }}</p>
        </div>

        <div class="sp-list">
        @forelse($programs as $program)
            @php
                $hasActiveSurvey = isset($activeSurveys[$program->id]);
            @endphp
            <article class="sp-item">
                <div class="sp-item-main">
                    <div class="sp-item-title">
                        <span>{{ $program->title }}</span>
                        @if($hasActiveSurvey)
                            <span class="sp-badge" style="background:rgba(212,175,55,0.14); color:#b45309; border:1px solid rgba(212,175,55,0.3); font-size:0.72rem; padding:0.2rem 0.55rem;">
                                <svg viewBox="0 0 24 24" width="10" height="10" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg>
                                {{ __('Soal Selidik Dibuka') }}
                            </span>
                        @endif
                    </div>
                    <div class="sp-item-meta">
                        @if($program->venue)
                            <span class="sp-meta-pill">
                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                {{ $program->venue }}
                            </span>
                        @endif
                    </div>
                </div>

                <div class="sp-item-actions">
                    @if($hasActiveSurvey)
                        <a class="sp-btn sp-btn-survey" href="{{ route('student.programs.survey', $program->id) }}">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            <span>{{ __('Soal Selidik') }}</span>
                        </a>
                    @endif

                    @if($program->checked_in_at)
                        <span class="sp-badge attended">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                            {{ __('Hadir') }}
                        </span>

                        @if(!($program->certificate_enabled ?? true))
                            <span class="sp-badge points-only">
                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                {{ __('Merit only — no certificate') }}
                            </span>
                        @elseif($program->validation_status !== 'valid')
                            <span class="sp-badge points-only">{{ __('Certificate: Not eligible') }}</span>
                        @elseif(($program->certificate_status ?? null) === 'ready')
                            <a class="sp-btn sp-btn-primary" href="{{ route('student.certificates.download', $program->certificate_id) }}">
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                <span>{{ __('Download Certificate') }}</span>
                            </a>
                        @elseif(in_array(($program->certificate_status ?? null), ['pending', 'generating'], true))
                            <span class="sp-badge points-only">{{ __('Certificate: Generating') }}</span>
                        @elseif(($program->certificate_status ?? null) === 'failed')
                            <span class="sp-badge points-only">{{ __('Certificate: Failed') }}</span>
                        @else
                            <span class="sp-badge points-only">{{ __('Certificate: Pending generation') }}</span>
                        @endif
                    @elseif($program->attendance_status === 'open')
                        @if(($program->attendance_checkin_mode ?? 'qr_code') === 'portal_and_qr')
                            <a class="sp-btn sp-btn-primary" href="{{ route('student.programs.show', $program->id) }}">
                                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                                <span>{{ __('Manual Check-In') }}</span>
                            </a>
                        @else
                            <span class="sp-badge" style="background:rgba(212,175,55,0.12); color:#b45309; border:1px solid rgba(212,175,55,0.3); font-size:0.75rem; padding:0.35rem 0.75rem; display:inline-flex; align-items:center; gap:0.35rem; font-weight:750; border-radius:999px;">
                                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                <span>{{ __('Imbas QR di Dewan') }}</span>
                            </span>
                        @endif
                    @else
                        <span class="sp-badge closed">{{ __('Closed') }}</span>
                    @endif
                </div>
            </article>
        @empty
            <div class="sp-empty">
                <svg viewBox="0 0 24 24" width="44" height="44" fill="none" stroke="currentColor" stroke-width="1.5" style="margin-bottom:0.75rem;opacity:0.6;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <p>{{ __('No programs are available yet.') }}</p>
            </div>
        @endforelse
        </div>
    </section>
</main>
@endsection
