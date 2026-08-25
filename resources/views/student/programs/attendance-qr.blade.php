@extends('layouts.app')

@section('title', __('Attendance QR Access'))

@section('header')
<h2 style="margin:0;font-size:1.15rem;font-weight:800;">{{ __('Attendance QR Access') }}</h2>
@endsection

@section('content')
<main class="sp-wrap">
    <section class="sp-section">
        <div class="sp-section-head">
            <h1>{{ __('Dynamic Attendance QR') }}</h1>
            <p>{{ __('Only programs assigned to you by KJ HEP or System Admin appear here. Open the QR screen when you are responsible for displaying attendance at the venue.') }}</p>
        </div>

        <div class="sp-list">
            @forelse($programs as $program)
                <article class="sp-item">
                    <div class="sp-item-main">
                        <div class="sp-item-title">
                            <span>{{ $program->title }}</span>
                            <span class="sp-badge" style="background:rgba(16,185,129,.12);color:#047857;border:1px solid rgba(16,185,129,.25);">
                                {{ __('QR presenter access') }}
                            </span>
                        </div>
                        <div class="sp-item-meta">
                            @if($program->venue)
                                <span class="sp-meta-pill">{{ $program->venue }}</span>
                            @endif
                            @if($program->reference_no)
                                <span class="sp-meta-pill">{{ $program->reference_no }}</span>
                            @endif
                            @if($program->expires_at)
                                <span class="sp-meta-pill">{{ __('Access until :time', ['time' => \Carbon\Carbon::parse($program->expires_at)->format('d M Y, h:i A')]) }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="sp-item-actions">
                        <a class="sp-btn sp-btn-primary" href="{{ route('student.programs.attendance-qr.presenter', $program->id) }}" target="_blank" rel="noopener">
                            <span>{{ __('Open Dynamic QR') }}</span>
                        </a>
                    </div>
                </article>
            @empty
                <div class="sp-empty">
                    <p>{{ __('No dynamic attendance QR access is currently assigned to you.') }}</p>
                </div>
            @endforelse
        </div>
    </section>
</main>
@endsection
