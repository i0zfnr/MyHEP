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
    <title>{{ __('Lupa Kata Laluan') }}</title>
    @include('partials.brand_icons')
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/password-reset.js'])
    @vite('resources/css/design-system.css')
</head>
<body data-theme="{{ session('theme', 'light') }}" class="auth-page">
@include('partials.theme_toggle', ['themeToggleClass' => 'se-theme-toggle--standalone'])
<main class="auth-recovery-shell">
<section class="auth-recovery-card">
    <div class="auth-recovery-brand">
        <img src="{{ asset('images/logo-politeknik-besut.png') }}" alt="Politeknik Besut">
        <div><span>MyHEP</span><small>{{ __('Student Affairs System') }}</small></div>
    </div>

    <div class="auth-recovery-heading">
        <p>{{ __('Account Recovery') }}</p>
        <h1>{{ __('Lupa Kata Laluan') }}</h1>
        <span>{{ __('Password reset codes are sent to the registered email address on each account.') }}</span>
    </div>

    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="auth-recovery-form">
        <div class="auth-recovery-field">
            <label for="recovery-role">{{ __('Peranan') }}</label>
            <select id="recovery-role" required>
                <option value="student" {{ old('role', 'student') === 'student' ? 'selected' : '' }}>{{ __('Pelajar') }}</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>{{ __('Admin') }}</option>
            </select>
        </div>

        <form id="student-email-reset" method="POST" action="{{ route('password.forgot.send') }}" {{ old('role', 'student') === 'admin' ? 'hidden' : '' }}>
            @csrf
            <input type="hidden" name="role" value="student">
            <div class="auth-recovery-field">
                <label for="student-identifier">{{ __('No. Matrik Pelajar') }}</label>
                <input id="student-identifier" name="identifier" type="text" maxlength="100" autocomplete="username" required value="{{ old('identifier') }}">
            </div>
            <div class="auth-recovery-actions">
                <button class="btn-submit" type="submit">{{ __('Hantar Kod Verifikasi') }}</button>
                <a class="btn-home" href="{{ route('login') }}">{{ __('Kembali Login') }}</a>
            </div>
        </form>

        <form id="admin-email-reset" method="POST" action="{{ route('password.forgot.send') }}" {{ old('role') === 'admin' ? '' : 'hidden' }}>
            @csrf
            <input type="hidden" name="role" value="admin">
            <div class="auth-recovery-field">
                <label for="admin-identifier">{{ __('No. IC admin') }}</label>
                <input id="admin-identifier" name="identifier" type="text" maxlength="150" required value="{{ old('identifier') }}">
            </div>
            <div class="auth-recovery-field">
                <label for="admin-email">{{ __('Email berdaftar') }}</label>
                <input id="admin-email" name="email" type="email" maxlength="150" required value="{{ old('email') }}">
            </div>
            <div class="auth-recovery-actions">
                <button class="btn-submit" type="submit">{{ __('Hantar Kod Verifikasi') }}</button>
                <a class="btn-home" href="{{ route('login') }}">{{ __('Kembali Login') }}</a>
            </div>
        </form>
    </div>
</section>
</main>
@include('partials.app_footer')
</body>
</html>
