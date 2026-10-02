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
    <title>{{ __('Tetapkan Kata Laluan Baharu') }}</title>
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
        <h1 id="recovery-title">{{ __('Kata Laluan Baharu') }}</h1>
        <span>{{ __('Tetapkan kata laluan baharu untuk akaun anda.') }}</span>
    </div>

    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <form class="auth-recovery-form" method="POST" action="{{ route('password.reset.update') }}">
        @csrf
        <input type="hidden" name="ref" value="{{ $ref }}">

        <div class="auth-recovery-field">
            <label for="password">{{ __('Kata Laluan Baharu') }}</label>
            <div class="auth-recovery-password"><input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required><button type="button" class="password-toggle" data-password-toggle aria-controls="password" aria-pressed="false" aria-label="{{ __('Show password') }}" title="{{ __('Show password') }}"><svg class="eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eye-off" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18"/><path d="M10.6 6.2A11.7 11.7 0 0 1 12 6c6.5 0 10 6 10 6a18 18 0 0 1-2.2 3"/><path d="M6.2 6.2C3.5 8 2 12 2 12s3.5 6 10 6c1 0 2-.2 2.8-.5"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button></div>
        </div>

        <div class="auth-recovery-field">
            <label for="password_confirmation">{{ __('Sahkan Kata Laluan Baharu') }}</label>
            <div class="auth-recovery-password"><input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" required><button type="button" class="password-toggle" data-password-toggle aria-controls="password_confirmation" aria-pressed="false" aria-label="{{ __('Show password') }}" title="{{ __('Show password') }}"><svg class="eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eye-off" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18"/><path d="M10.6 6.2A11.7 11.7 0 0 1 12 6c6.5 0 10 6 10 6a18 18 0 0 1-2.2 3"/><path d="M6.2 6.2C3.5 8 2 12 2 12s3.5 6 10 6c1 0 2-.2 2.8-.5"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button></div>
        </div>

        <div class="auth-recovery-actions">
            <button class="btn-submit" type="submit">{{ __('Simpan Kata Laluan') }}</button>
            <a class="btn-home" href="{{ route('login') }}">{{ __('Kembali Login') }}</a>
        </div>
    </form>
</section>
</main>
@include('partials.app_footer')
<script>
document.addEventListener('click', function (event) {
    const button = event.target.closest('[data-password-toggle]');
    if (!button) return;
    const input = document.getElementById(button.getAttribute('aria-controls'));
    if (!input) return;
    const reveal = input.type === 'password';
    input.type = reveal ? 'text' : 'password';
    button.setAttribute('aria-pressed', reveal ? 'true' : 'false');
    button.setAttribute('aria-label', reveal ? 'Hide password' : 'Show password');
    button.setAttribute('title', reveal ? 'Hide password' : 'Show password');
});
</script>
</body>
</html>
