@extends('layouts.app')

@section('title', __('Feature Controls'))
@section('header')<h2>{{ __('Feature Controls') }}</h2>@endsection
@push('styles')
<style>
    .feature-list { display:grid; gap:14px; }
    .feature-row { display:flex; justify-content:space-between; align-items:center; gap:18px; padding:16px; border:1px solid var(--border); border-radius:14px; }
    .feature-copy { min-width:0; }
    .feature-copy p { margin:5px 0 0; color:var(--text-muted); }
    .feature-row form { flex:0 0 auto; }
    .feature-row--session { align-items:end; padding:18px; background:color-mix(in srgb, var(--surface) 82%, var(--primary) 18%); border-color:color-mix(in srgb, var(--border) 72%, var(--primary) 28%); box-shadow:inset 0 1px 0 color-mix(in srgb, #fff 42%, transparent); }
    .feature-row--session .feature-copy strong { color:var(--text); }
    .feature-row--session .feature-copy p { max-width:560px; line-height:1.55; }
    .session-form { display:grid; grid-template-columns:minmax(108px, 132px) auto; align-items:end; gap:10px; max-width:100%; }
    .session-form label { display:grid; gap:6px; font-size:.78rem; font-weight:800; letter-spacing:.04em; text-transform:uppercase; color:var(--text-muted); }
    .session-form input { width:100%; min-height:44px; font-size:1rem; font-weight:800; background:var(--surface); color:var(--text); border:1px solid var(--border); border-radius:10px; padding:0 12px; }
    .session-form input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px color-mix(in srgb, var(--primary) 20%, transparent); }
    .session-form .ui-btn { min-height:44px; white-space:nowrap; }
    body[data-theme="dark"] .feature-row--session { background:color-mix(in srgb, var(--surface) 92%, var(--primary) 8%); box-shadow:inset 0 1px 0 rgba(255,255,255,.04); }
    @media(max-width:640px) {
        .feature-row { align-items:stretch; flex-direction:column; }
        .feature-row .ui-btn { width:100%; }
        .session-form { align-items:stretch; flex-direction:column; }
        .session-form input { width:100%; }
    }
    @media(max-width:900px) {
        .feature-row--session { align-items:stretch; flex-direction:column; }
        .session-form { grid-template-columns:minmax(108px, 132px) auto; align-self:flex-start; }
    }
    @media(max-width:420px) {
        .session-form { grid-template-columns:1fr; width:100%; }
        .session-form .ui-btn { width:100%; }
    }
</style>
@endpush
@section('content')
<div class="ui-shell" style="max-width:900px;margin:0 auto;">
    @if(session('success'))<div class="se-feedback se-feedback--success">{{ session('success') }}</div>@endif
    <section class="ui-card">
        <div class="ui-card-head"><strong>{{ __('Page Availability') }}</strong></div>
        <div class="ui-card-body feature-list">
            @foreach($features as $feature)
                <div class="feature-row">
                    <div class="feature-copy"><strong>{{ __($feature['label']) }}</strong><p>{{ __($feature['description']) }}</p></div>
                    <form method="POST" action="{{ route('admin.features.update', $feature['key']) }}">@csrf @method('PATCH')<input type="hidden" name="enabled" value="{{ $feature['enabled'] ? 0 : 1 }}"><button class="ui-btn {{ $feature['enabled'] ? 'btn-danger' : 'primary' }}" type="submit">{{ $feature['enabled'] ? __('Turn Off') : __('Turn On') }}</button></form>
                </div>
            @endforeach
        </div>
    </section>
    <section class="ui-card" style="margin-top:16px;">
        <div class="ui-card-head"><strong>{{ __('Session Security') }}</strong></div>
        <div class="ui-card-body">
            <div class="feature-row feature-row--session">
                <div class="feature-copy">
                    <strong>{{ __('Idle session timeout') }}</strong>
                    <p>{{ __('Automatically sign out students and administrators after this period without activity. Choose between 1 and 30 days.') }}</p>
                </div>
                <form class="session-form" method="POST" action="{{ route('admin.system-settings.session-lifetime.update') }}">
                    @csrf
                    @method('PATCH')
                    <label for="session_lifetime_days">{{ __('Days') }}
                        <input id="session_lifetime_days" type="number" name="session_lifetime_days" min="1" max="30" required value="{{ old('session_lifetime_days', $sessionLifetimeDays) }}">
                    </label>
                    <button class="ui-btn primary" type="submit">{{ __('Save timeout') }}</button>
                </form>
            </div>
            @error('session_lifetime_days')<p class="se-feedback se-feedback--error" style="margin:12px 0 0;">{{ $message }}</p>@enderror
        </div>
    </section>
</div>
@endsection
