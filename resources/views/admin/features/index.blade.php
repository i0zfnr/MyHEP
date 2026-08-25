@extends('layouts.app')

@section('title', __('Feature Controls'))
@section('header')<h2>{{ __('Feature Controls') }}</h2>@endsection

@section('content')
<div class="ui-shell" style="max-width:900px;margin:0 auto;">
    @if(session('success'))<div class="se-feedback se-feedback--success">{{ session('success') }}</div>@endif
    <section class="ui-card">
        <div class="ui-card-head"><strong>{{ __('Page Availability') }}</strong></div>
        <div class="ui-card-body feature-list">
            @foreach($features as $feature)
                <div class="feature-row">
                    <div class="feature-copy"><strong>{{ __($feature['label']) }}</strong><p>{{ __($feature['description']) }}</p></div>
                    <form method="POST" action="{{ route('admin.features.update', $feature['key']) }}">@csrf @method('PATCH')<input type="hidden" name="enabled" value="{{ $feature['enabled'] ? 0 : 1 }}"><button class="ui-btn {{ $feature['enabled'] ? 'btn-danger' : 'primary' }}" type="submit" aria-label="{{ $feature['enabled'] ? __('Turn off :feature', ['feature' => __($feature['label'])]) : __('Turn on :feature', ['feature' => __($feature['label'])]) }}">{{ $feature['enabled'] ? __('Turn Off') : __('Turn On') }}</button></form>
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
