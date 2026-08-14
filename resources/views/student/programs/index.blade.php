@extends('layouts.app')
@section('title', __('Program Activities'))
@section('header')<h2 style="margin:0;font-size:1.15rem;font-weight:800;">{{ __('Program Activities') }}</h2>@endsection
@section('content')
<main style="max-width:1400px;margin:0 auto;padding:1.5rem;display:grid;gap:1rem;">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    <section style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem;">
        <article class="card" style="padding:1.25rem;"><small>{{ __('TOTAL PARTICIPATION POINTS') }}</small><strong style="display:block;font-size:2rem;margin-top:.4rem;">{{ number_format($totalPoints) }}</strong></article>
        <article class="card" style="padding:1.25rem;"><small>{{ __('PROGRAMS JOINED') }}</small><strong style="display:block;font-size:2rem;margin-top:.4rem;">{{ number_format($programsJoined) }}</strong></article>
    </section>
    <section class="card" style="padding:1.5rem;">
        <h1 style="margin:0 0 .4rem;">{{ __('Politeknik Besut Programs') }}</h1>
        <p style="color:var(--text-muted);">{{ __('Join open programs, view participation points, and download certificates linked to your matric number.') }}</p>
        <div style="display:grid;gap:.8rem;margin-top:1rem;">
        @forelse($programs as $program)
            <article style="padding:1rem;border:1px solid var(--border);border-radius:14px;display:flex;justify-content:space-between;gap:1rem;align-items:center;">
                <div><strong>{{ $program->title }}</strong><div style="font-size:.82rem;color:var(--text-muted);">{{ $program->venue }} &middot; {{ $program->participation_points }} {{ __('points') }}</div></div>
                <div style="display:flex;gap:.6rem;align-items:center;flex-wrap:wrap;justify-content:flex-end;">
                    @if($program->checked_in_at)
                        <span class="badge">{{ __(str_replace('_',' ',$program->validation_status)) }}</span>
                        @if($program->validation_status !== 'valid')<span class="badge">{{ __('Certificate: Not eligible') }}</span>
                        @elseif(($program->certificate_status ?? null) === 'ready')<a class="btn btn-primary" href="{{ route('student.certificates.download',$program->certificate_id) }}">{{ __('Download Certificate') }}</a>
                        @elseif(in_array(($program->certificate_status ?? null),['pending','generating'],true))<span class="badge">{{ __('Certificate: Generating') }}</span>
                        @elseif(($program->certificate_status ?? null) === 'failed')<span class="badge">{{ __('Certificate: Failed') }}</span>
                        @else<span class="badge">{{ __('Certificate: Pending generation') }}</span>@endif
                    @elseif($program->attendance_status === 'open')<a class="btn btn-primary" href="{{ route('student.programs.show',$program->id) }}">{{ __('Join & Answer') }}</a>
                    @else<span class="badge">{{ __('Closed') }}</span>@endif
                </div>
            </article>
        @empty
            <p>{{ __('No programs are available yet.') }}</p>
        @endforelse
        </div>
    </section>
</main>
@endsection
