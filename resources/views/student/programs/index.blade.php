@extends('layouts.app')
@section('title', __('Program Activities'))
@section('header')<h2 style="margin:0;font-size:1.15rem;font-weight:800;">{{ __('Program Activities') }}</h2>@endsection

@push('styles')
<style>
    .sp-wrap { max-width: 1200px; margin: 0 auto; display: flex; flex-direction: column; gap: 1.25rem; }
    
    /* Top Banner / Active Survey Alert */
    .sp-survey-banner {
        background: linear-gradient(135deg, rgba(212,175,55,0.14) 0%, rgba(180,83,9,0.08) 100%);
        border: 1px solid rgba(212,175,55,0.35);
        border-radius: 20px;
        padding: 1.25rem 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        box-shadow: 0 4px 20px rgba(212,175,55,0.08);
    }
    .sp-survey-copy { display: flex; align-items: center; gap: 1rem; }
    .sp-survey-icon {
        width: 48px; height: 48px; border-radius: 14px;
        background: #d4af37; color: #1c1917;
        display: grid; place-items: center; flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(212,175,55,0.3);
    }
    .sp-survey-btn {
        display: inline-flex; align-items: center; gap: 0.45rem;
        padding: 0.6rem 1.1rem; border-radius: 12px;
        background: #d4af37; color: #171310;
        font-size: 0.85rem; font-weight: 850;
        text-decoration: none; transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(212,175,55,0.25);
    }
    .sp-survey-btn:hover {
        background: #c29d2b; transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(212,175,55,0.35);
    }

    /* KPI Highlights */
    .sp-kpis {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1.25rem;
    }
    .sp-kpi-card {
        border: 1px solid var(--border, #eadac8);
        border-radius: 20px;
        background: var(--surface, #fff);
        padding: 1.4rem 1.6rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 4px 18px rgba(36,26,18,0.04);
        position: relative;
        overflow: hidden;
    }
    .sp-kpi-card::after {
        content: ''; position: absolute; right: -20px; top: -20px;
        width: 90px; height: 90px; border-radius: 50%;
        background: color-mix(in srgb, var(--primary, #b99150) 8%, transparent);
        pointer-events: none;
    }
    .sp-kpi-info span {
        display: block;
        font-size: 0.76rem;
        font-weight: 850;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--text-muted, #746b62);
    }
    .sp-kpi-info strong {
        display: block;
        font-size: 2.2rem;
        font-weight: 900;
        margin-top: 0.35rem;
        color: var(--text, #241d16);
        line-height: 1;
    }
    .sp-kpi-icon {
        width: 52px; height: 52px; border-radius: 16px;
        background: color-mix(in srgb, var(--primary, #b99150) 12%, var(--surface, #fff));
        color: var(--primary, #b99150);
        display: grid; place-items: center;
        border: 1px solid color-mix(in srgb, var(--primary, #b99150) 24%, transparent);
    }

    /* Program List Section */
    .sp-section {
        border: 1px solid var(--border, #eadac8);
        border-radius: 22px;
        background: var(--surface, #fff);
        padding: 1.75rem;
        box-shadow: 0 4px 20px rgba(36,26,18,0.04);
    }
    .sp-section-head { margin-bottom: 1.5rem; }
    .sp-section-head h1 { margin: 0 0 0.35rem; font-size: 1.35rem; font-weight: 850; color: var(--text, #241d16); }
    .sp-section-head p { margin: 0; font-size: 0.88rem; color: var(--text-muted, #746b62); }

    .sp-list { display: flex; flex-direction: column; gap: 0.95rem; }
    .sp-item {
        border: 1px solid var(--border, #eadac8);
        border-radius: 18px;
        background: color-mix(in srgb, var(--surface, #fff) 96%, var(--primary, #b99150) 4%);
        padding: 1.25rem 1.4rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1.25rem;
        flex-wrap: wrap;
        transition: transform 0.15s ease, box-shadow 0.15s ease, border-color 0.15s ease;
    }
    .sp-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(36,26,18,0.06);
        border-color: color-mix(in srgb, var(--primary, #b99150) 40%, var(--border, #eadac8));
    }
    .sp-item-main { min-width: 260px; flex: 1; }
    .sp-item-title {
        font-size: 1.08rem;
        font-weight: 800;
        color: var(--text, #241d16);
        margin-bottom: 0.45rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    .sp-item-meta {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 0.82rem;
        color: var(--text-muted, #746b62);
        flex-wrap: wrap;
    }
    .sp-meta-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.25rem 0.6rem;
        border-radius: 8px;
        background: color-mix(in srgb, var(--border, #eadac8) 40%, transparent);
        font-weight: 700;
        color: var(--text, #241d16);
    }
    .sp-meta-points {
        background: rgba(212,175,55,0.14);
        color: #926f1a;
        font-weight: 800;
    }
    body[data-theme="dark"] .sp-meta-points {
        background: rgba(212,175,55,0.18);
        color: #f3d49b;
    }

    /* Actions & Badges */
    .sp-item-actions {
        display: flex;
        align-items: center;
        gap: 0.65rem;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .sp-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.85rem;
        border-radius: 10px;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.02em;
    }
    .sp-badge.attended {
        background: rgba(16, 185, 129, 0.12);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.25);
    }
    .sp-badge.points-only {
        background: rgba(116, 107, 98, 0.08);
        color: var(--text-muted, #746b62);
        border: 1px solid var(--border, #eadac8);
    }
    .sp-badge.closed {
        background: rgba(116, 107, 98, 0.08);
        color: var(--text-muted, #746b62);
        border: 1px solid var(--border, #eadac8);
    }
    .sp-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.55rem 1.05rem;
        border-radius: 12px;
        font-size: 0.82rem;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 1px solid transparent;
    }
    .sp-btn-primary {
        background: var(--primary, #b99150);
        color: #fff;
        box-shadow: 0 4px 12px rgba(185, 145, 80, 0.25);
    }
    .sp-btn-primary:hover {
        background: color-mix(in srgb, var(--primary, #b99150) 88%, #000);
        transform: translateY(-1px);
    }
    .sp-btn-survey {
        background: #fdf8eb;
        color: #926f1a;
        border-color: rgba(212,175,55,0.4);
    }
    .sp-btn-survey:hover {
        background: #faefd2;
        transform: translateY(-1px);
    }
    body[data-theme="dark"] .sp-btn-survey {
        background: rgba(212,175,55,0.14);
        color: #f3d49b;
        border-color: rgba(212,175,55,0.3);
    }
    .sp-empty {
        text-align: center;
        padding: 3rem 1.5rem;
        color: var(--text-muted, #746b62);
    }
</style>
@endpush

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
                <span>{{ __('Total Participation Points') }}</span>
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
            <p>{{ __('Join open programs, view participation points, and download certificates linked to your matric number.') }}</p>
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
                        <span class="sp-meta-pill sp-meta-points">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            +{{ $program->participation_points }} {{ __('Points') }}
                        </span>
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
                                {{ __('Points only — no certificate') }}
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
                        <a class="sp-btn sp-btn-primary" href="{{ route('student.programs.show', $program->id) }}">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                            <span>{{ __('Manual Check-In') }}</span>
                        </a>
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
