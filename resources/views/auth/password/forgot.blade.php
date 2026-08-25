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
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @vite('resources/css/design-system.css')
</head>
<body data-theme="{{ session('theme', 'light') }}" class="auth-page">
@include('partials.theme_toggle', ['themeToggleClass' => 'se-theme-toggle--standalone'])
<main class="auth-recovery-shell">
<section class="auth-recovery-card">
    <div class="auth-recovery-brand">
        <img src="{{ asset('images/logo-politeknik-besut.png') }}" alt="Politeknik Besut">
        <div>
            <span>MyHEP</span>
            <small>{{ __('Student Affairs System') }}</small>
        </div>
    </div>

    <div class="auth-recovery-heading">
        <p>{{ __('Account Recovery') }}</p>
        <h1>{{ __('Lupa Kata Laluan') }}</h1>
        <span>{{ __('Masukkan maklumat akaun dan email berdaftar untuk menerima kod verifikasi.') }}</span>
    </div>

    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if(session('delivery_info'))<div class="warn">{{ session('delivery_info') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('password.forgot.send') }}" class="auth-recovery-form">
        @csrf
        <div class="auth-recovery-field">
            <label for="role">{{ __('Peranan') }}</label>
            <select id="role" name="role" required>
                <option value="student" {{ old('role', 'student') === 'student' ? 'selected' : '' }}>{{ __('Pelajar') }}</option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>{{ __('Admin') }}</option>
            </select>
        </div>

        <div class="auth-recovery-field">
            <label for="identifier">ID Akaun (No. Matrik pelajar / No. IC admin)</label>
            <input id="identifier" name="identifier" type="text" value="{{ old('identifier') }}" required>
        </div>

        <div class="auth-recovery-field">
            <label for="email">{{ __('Email berdaftar') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required>
        </div>

        <div class="auth-recovery-actions">
            <button class="btn-submit" type="submit">{{ __('Hantar Kod Verifikasi') }}</button>
            <a class="btn-home" href="{{ route('login') }}">{{ __('Kembali Login') }}</a>
        </div>
    </form>
</section>
</main>
@include('partials.app_footer')
</body>
</html>
