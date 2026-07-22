@extends('layouts.app')

@section('title', __('Movement Settings'))

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ __('Movement Settings') }}</h2>
@endsection

@push('styles')
<style>
    .mv-settings { display:flex; flex-direction:column; gap:1rem; }
    .mv-settings-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; }
    .mv-settings-grid label,
    .mv-check-grid label { display:flex; flex-direction:column; gap:.35rem; }
    .mv-check-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:.75rem; }
    .mv-check-item { display:flex; align-items:center; gap:.55rem; border:1px solid var(--border); border-radius:12px; padding:.85rem; background:rgba(255,255,255,.55); }
    body[data-theme="dark"] .mv-check-item { background:var(--surface); }
</style>
@endpush

@section('content')
<div class="ui-shell mv-settings">
    @if(session('success'))
        <div class="ui-card"><div class="ui-card-body" style="color:#1f5559;">{{ session('success') }}</div></div>
    @endif
    @if($errors->any())
        <div class="ui-card"><div class="ui-card-body" style="color:#991b1b;">{{ $errors->first() }}</div></div>
    @endif

    <div class="ui-hero">
        <h3>{{ __('Movement Rules') }}</h3>
        <p>{{ __('Control curfew cut-off time, QR validity, checkpoint location, and which movement types are available to students.') }}</p>
    </div>

    <form method="POST" action="{{ route('admin.movements.settings.update') }}">
        @csrf
        <div class="ui-card">
            <div class="ui-card-head">
                <strong>{{ __('Rule Settings') }}</strong>
                <a class="ui-btn" href="{{ route('admin.movements.qr') }}">{{ __('QR Code') }}</a>
            </div>
            <div class="ui-card-body mv-settings-grid">
                <label>
                    <span class="muted">{{ __('Sunday - Thursday Curfew') }}</span>
                    <input type="time" name="curfew_weekday" value="{{ $settings['curfew_weekday'] ?? '19:00' }}" required>
                </label>
                <label>
                    <span class="muted">{{ __('Friday - Saturday Curfew') }}</span>
                    <input type="time" name="curfew_weekend" value="{{ $settings['curfew_weekend'] ?? '23:00' }}" required>
                </label>
                <label style="display:flex;align-items:center;gap:.5rem;margin-top:1.25rem;">
                    <input type="checkbox" name="gps_validation_enabled" value="1" @checked(($settings['gps_validation_enabled'] ?? '0') === '1')>
                    <span>{{ __('Enable GPS Radius Validation') }}</span>
                </label>
            </div>
        </div>

        <div class="ui-card" style="margin-top:1rem;">
            <div class="ui-card-head"><strong>{{ __('Checkpoint Settings') }}</strong></div>
            <div class="ui-card-body mv-settings-grid">
                <label>
                    <span class="muted">{{ __('Checkpoint Name') }}</span>
                    <input type="text" name="checkpoint_name" value="{{ old('checkpoint_name', $checkpoint->name ?? 'Guard House Main') }}" required>
                </label>
                <label>
                    <span class="muted">{{ __('Latitude') }}</span>
                    <input type="number" step="0.0000001" name="latitude" value="{{ old('latitude', $checkpoint->latitude ?? '') }}">
                </label>
                <label>
                    <span class="muted">{{ __('Longitude') }}</span>
                    <input type="number" step="0.0000001" name="longitude" value="{{ old('longitude', $checkpoint->longitude ?? '') }}">
                </label>
                <label>
                    <span class="muted">{{ __('GPS Radius Meters') }}</span>
                    <input type="number" name="gps_radius_meters" min="10" max="5000" value="{{ old('gps_radius_meters', $checkpoint->gps_radius_meters ?? '') }}">
                </label>
            </div>
        </div>

        <div class="ui-card" style="margin-top:1rem;">
            <div class="ui-card-head"><strong>{{ __('Active Movement Types') }}</strong></div>
            <div class="ui-card-body mv-check-grid">
                @foreach($movementTypes as $type)
                    <label class="mv-check-item">
                        <input type="checkbox" name="movement_types[]" value="{{ $type->id }}" @checked($type->is_active)>
                        <span>{{ __($type->name) }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="ui-actions" style="margin-top:1rem;">
            <button class="ui-btn primary" type="submit">{{ __('Save Settings') }}</button>
            <a class="ui-btn" href="{{ route('admin.movements.index') }}">{{ __('Cancel') }}</a>
        </div>
    </form>
</div>
@endsection
