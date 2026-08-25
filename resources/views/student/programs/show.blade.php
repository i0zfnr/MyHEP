@extends('layouts.app')

@section('title', __('Manual Check-In') . ' - ' . $program->title)

@section('header')
    <h2 style="margin:0;font-size:1.15rem;font-weight:800;">{{ __('Program Attendance Check-In') }}</h2>
@endsection

@section('content')
<main style="max-width: 680px; margin: 0 auto; padding: 1.5rem 1rem;">
    @if(session('success'))
        <div class="alert alert-success" style="border-radius: 14px; margin-bottom: 1.25rem;">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" style="border-radius: 14px; margin-bottom: 1.25rem;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <section class="card" style="padding: 1.75rem; border-radius: 22px; box-shadow: 0 14px 35px rgba(0,0,0,0.06); border: 1px solid var(--border);">
        <div style="border-bottom: 1px solid var(--border); padding-bottom: 1.25rem; margin-bottom: 1.25rem;">
            <div style="display:inline-flex; align-items:center; gap:0.4rem; font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:0.08em; color:var(--primary); background:rgba(212,175,55,0.12); padding:0.3rem 0.75rem; border-radius:999px; margin-bottom:0.5rem;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                <span>{{ __('Live Attendance Check-In') }}</span>
            </div>
            <h1 style="font-size: 1.5rem; font-weight: 850; margin: 0 0 0.4rem; color: var(--text);">{{ $program->title }}</h1>
            <div style="display: flex; gap: 0.75rem 1.25rem; flex-wrap: wrap; font-size: 0.85rem; color: var(--text-muted); font-weight: 600;">
                <span>📍 {{ $program->venue ?: __('Politeknik Besut Campus') }}</span>
                <span>🕒 {{ $program->starts_at ? \Carbon\Carbon::parse($program->starts_at)->format('d M Y, g:i A') : __('Today') }}</span>
            </div>
        </div>

        {{-- Student Profile Preview --}}
        <div style="background: var(--bg-alt, #faf7f2); border: 1px solid var(--border); border-radius: 14px; padding: 1.15rem; margin-bottom: 1.5rem;">
            <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 0.25rem;">
                {{ __('Attendee Profile (Verified Account)') }}
            </span>
            <strong style="font-size: 1.05rem; color: var(--text); display: block;">{{ $student->full_name }}</strong>
            <div style="font-size: 0.84rem; color: var(--text-muted); margin-top: 2px;">
                {{ $student->matric_no }} &bull; {{ $student->program }}
            </div>
        </div>

        @if($attendance)
            <div class="alert alert-success" style="border-radius: 12px; font-weight: 700; display: flex; align-items: center; gap: 0.6rem;">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                <span>{{ __('You have already checked in to this program.') }}</span>
            </div>
            <a href="{{ route('student.programs.index') }}" class="btn btn-secondary" style="width: 100%; margin-top: 1rem; justify-content: center;">
                {{ __('Back to Programs') }}
            </a>
        @else
            <form method="post" action="{{ route('student.programs.attendance.store', $program->id) }}" id="studentProgramAttendance">
                @csrf
                <input type="hidden" name="qr_token" value="{{ $token ?? old('qr_token') }}">
                <input type="hidden" name="latitude" id="paLat" value="{{ old('latitude') }}">
                <input type="hidden" name="longitude" id="paLng" value="{{ old('longitude') }}">
                <input type="hidden" name="location_accuracy_m" id="paAccuracy" value="{{ old('location_accuracy_m') }}">
                <input type="hidden" name="location_captured_at" id="paCaptured" value="{{ old('location_captured_at') }}">

                @if($program->latitude !== null && $program->longitude !== null)
                    <div id="paStatus" class="alert alert-warning" style="border-radius: 12px; margin-bottom: 1.25rem; font-size: 0.85rem; font-weight: 700;">
                        {{ __('Acquiring GPS location for venue check-in verification...') }}
                    </div>
                    <button id="paSubmit" class="btn btn-primary" type="submit" disabled style="width: 100%; min-height: 48px; font-size: 1rem; font-weight: 800; justify-content: center;">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>{{ __('Confirm Attendance & Check In') }}</span>
                    </button>
                @else
                    <div id="paStatus" class="alert alert-success" style="border-radius: 12px; margin-bottom: 1.25rem; font-size: 0.85rem; font-weight: 700;">
                        {{ __('GPS verification is optional for this program. You can check in now.') }}
                    </div>
                    <button id="paSubmit" class="btn btn-primary" type="submit" style="width: 100%; min-height: 48px; font-size: 1rem; font-weight: 800; justify-content: center;">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        <span>{{ __('Confirm Attendance & Check In') }}</span>
                    </button>
                @endif
            </form>
        @endif
    </section>
</main>

@if($program->latitude !== null && $program->longitude !== null)
<script>
(() => {
    const status = document.getElementById('paStatus');
    const button = document.getElementById('paSubmit');
    const lat = document.getElementById('paLat');
    const lng = document.getElementById('paLng');
    const acc = document.getElementById('paAccuracy');
    const cap = document.getElementById('paCaptured');

    if (!status || !navigator.geolocation) return;

    navigator.geolocation.getCurrentPosition(p => {
        if (lat) lat.value = p.coords.latitude;
        if (lng) lng.value = p.coords.longitude;
        if (acc) acc.value = p.coords.accuracy;
        if (cap) cap.value = new Date(p.timestamp).toISOString();

        status.textContent = `📍 {{ __('Location verified successfully') }} (±${Math.round(p.coords.accuracy)}m)`;
        status.className = 'alert alert-success';
        if (button) button.disabled = false;
    }, err => {
        status.textContent = '⚠️ {{ __('Location access permission is needed to verify physical presence. Please allow location access.') }}';
        status.className = 'alert alert-danger';
        // Allow soft check-in if GPS permission fails
        if (button) button.disabled = false;
    }, { enableHighAccuracy: true, timeout: 8000, maximumAge: 0 });
})();
</script>
@endif
@endsection
