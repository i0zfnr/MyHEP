<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.theme_bootstrap')
    <meta name="theme-color" content="#171412">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>{{ __('Verifikasi Kod Reset') }}</title>
    @include('partials.brand_icons')
    @vite(['resources/css/app.css', 'resources/css/design-system.css', 'resources/css/auth.css', 'resources/js/app.js'])
</head>
<body data-theme="{{ session('theme', 'light') }}" class="auth-page">
@include('partials.theme_toggle', ['themeToggleClass' => 'se-theme-toggle--standalone'])
<main class="auth-recovery-shell">
<section class="auth-recovery-card" aria-labelledby="recovery-title">
    <div class="auth-recovery-brand">
        <img src="{{ asset('images/logo-politeknik-besut.png') }}" alt="Politeknik Besut">
        <div><span>MyHEP</span><small>{{ __('Student Affairs System') }}</small></div>
    </div>
    <div class="auth-recovery-heading">
        <p>{{ __('Account Recovery') }}</p>
        <h1 id="recovery-title">{{ __('Verifikasi Kod') }}</h1>
        <span>{{ __('Masukkan kod 6 digit yang dihantar ke') }} {{ $maskedEmail }}.</span>
    </div>

    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <form class="auth-recovery-form" method="POST" action="{{ route('password.verify.check') }}">
        @csrf
        <input type="hidden" name="ref" value="{{ $ref }}">

        <div class="auth-recovery-field">
            <label for="code">{{ __('Kod Verifikasi') }}</label>
            <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" value="{{ old('code') }}" required>
        </div>

        <div class="auth-recovery-actions">
            <button class="btn-submit" type="submit">{{ __('Sahkan Kod') }}</button>
            <a class="btn-home" href="{{ route('password.forgot') }}">{{ __('Hantar Semula') }}</a>
        </div>
    </form>
</section>
</main>
@include('partials.app_footer')
</body>
</html>
