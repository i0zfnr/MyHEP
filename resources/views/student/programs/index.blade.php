@extends('layouts.app')
@section('title', __('Program Activities'))
@section('header')<h2 style="margin:0;font-size:1.15rem;font-weight:800;">{{ __('Program Activities') }}</h2>@endsection
@section('content')
<main style="max-width:1400px;margin:0 auto;padding:1.5rem;display:grid;gap:1rem;">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    @if(!empty($activeSurveys) && $activeSurveys->isNotEmpty())
        <div style="background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(180,83,9,0.1)); border: 1px solid rgba(212,175,55,0.35); border-radius: 18px; padding: 1.25rem 1.5rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 0.9rem;">
                <div style="width: 44px; height: 44px; border-radius: 12px; background: #d4af37; color: #1c1917; display: grid; place-items: center;">
                    <svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                </div>
                <div>
                    <strong style="font-size: 1.05rem; display: block; color: var(--text);">{{ __('Soal Selidik Program Aktif') }}</strong>
                    <span style="font-size: 0.84rem; color: var(--text-muted);">{{ __('Sila lengkapkan maklum balas program Politeknik Besut yang anda sertai.') }}</span>
                </div>
            </div>
            <div style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
                @foreach($activeSurveys as $surveyProgram)
                    <a href="{{ route('student.programs.survey', $surveyProgram->program_id) }}" class="btn btn-primary" style="font-weight: 800; font-size: 0.84rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.5L19 7.5V19a2 2 0 0 1-2 2z"/></svg>
                        {{ __('Jawab: :title', ['title' => \Illuminate\Support\Str::limit($surveyProgram->program_title, 25)]) }}
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    <section style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem;">
        <article class="card" style="padding:1.25rem;"><small>{{ __('TOTAL PARTICIPATION POINTS') }}</small><strong style="display:block;font-size:2rem;margin-top:.4rem;">{{ number_format($totalPoints) }}</strong></article>
        <article class="card" style="padding:1.25rem;"><small>{{ __('PROGRAMS JOINED') }}</small><strong style="display:block;font-size:2rem;margin-top:.4rem;">{{ number_format($programsJoined) }}</strong></article>
    </section>

    <section class="card" style="padding:1.5rem;">
        <h1 style="margin:0 0 .4rem;">{{ __('Politeknik Besut Programs') }}</h1>
        <p style="color:var(--text-muted);">{{ __('Join open programs, view participation points, and download certificates linked to your matric number.') }}</p>
        <div style="display:grid;gap:.8rem;margin-top:1rem;">
        @forelse($programs as $program)
            @php
                $hasActiveSurvey = isset($activeSurveys[$program->id]);
            @endphp
            <article style="padding:1rem;border:1px solid var(--border);border-radius:14px;display:flex;justify-content:space-between;gap:1rem;align-items:center;">
                <div>
                    <strong>{{ $program->title }}</strong>
                    <div style="font-size:.82rem;color:var(--text-muted);">
                        {{ $program->venue }} &middot; {{ $program->participation_points }} {{ __('points') }}
                        @if($hasActiveSurvey)
                            &middot; <span style="color: #d4af37; font-weight: 800; display: inline-flex; align-items: center; gap: 3px;"><svg viewBox="0 0 24 24" width="11" height="11" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg> {{ __('Soal Selidik Dibuka') }}</span>
                        @endif
                    </div>
                </div>
                <div style="display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;justify-content:flex-end;">
                    @if($hasActiveSurvey)
                        <a class="btn btn-secondary" style="font-size: 0.82rem; display: inline-flex; align-items: center; gap: 0.35rem;" href="{{ route('student.programs.survey', $program->id) }}">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            {{ __('Soal Selidik') }}
                        </a>
                    @endif

                    @if($program->checked_in_at)
                        <span class="badge" style="background: rgba(16, 185, 129, 0.12); color: #059669; border: 1px solid rgba(16, 185, 129, 0.25);">
                            ✓ {{ __('Hadir') }}
                        </span>
                        @if(!($program->certificate_enabled ?? true))<span class="badge">{{ __('Points only — no certificate') }}</span>
                        @elseif($program->validation_status !== 'valid')<span class="badge">{{ __('Certificate: Not eligible') }}</span>
                        @elseif(($program->certificate_status ?? null) === 'ready')<a class="btn btn-primary" href="{{ route('student.certificates.download',$program->certificate_id) }}">{{ __('Download Certificate') }}</a>
                        @elseif(in_array(($program->certificate_status ?? null),['pending','generating'],true))<span class="badge">{{ __('Certificate: Generating') }}</span>
                        @elseif(($program->certificate_status ?? null) === 'failed')<span class="badge">{{ __('Certificate: Failed') }}</span>
                        @else<span class="badge">{{ __('Certificate: Pending generation') }}</span>@endif
                    @elseif($program->attendance_status === 'open')
                        <a class="btn btn-primary" href="{{ route('student.programs.show',$program->id) }}">{{ __('Manual Check-In') }}</a>
                    @else
                        <span class="badge">{{ __('Closed') }}</span>
                    @endif
                </div>
            </article>
        @empty
            <p>{{ __('No programs are available yet.') }}</p>
        @endforelse
        </div>
    </section>
</main>
@endsection
