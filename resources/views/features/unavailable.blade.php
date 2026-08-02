@extends('layouts.app')

@section('title', __('Feature Unavailable'))
@section('header')<h2>{{ __('Feature Unavailable') }}</h2>@endsection
@section('content')
<div class="ui-shell" style="max-width:720px;margin:0 auto;">
    <section class="ui-card"><div class="ui-card-body" style="text-align:center;padding:48px 24px;">
        <h3 style="margin-top:0;">{{ __($feature) }} {{ __('is currently unavailable') }}</h3>
        <p style="color:var(--text-muted);">{{ __('This page has been temporarily disabled by the system administrator. Please try again later.') }}</p>
        <a class="ui-btn" href="{{ session('auth_user.role') === 'admin' ? route('admin.dashboard') : route('student.dashboard') }}">{{ __('Back to Dashboard') }}</a>
    </div></section>
</div>
@endsection
