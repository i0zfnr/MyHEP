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
    <title>{{ __('login.page_title') }}</title>
    @include('partials.brand_icons')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite('resources/css/design-system.css')
</head>
<body data-theme="{{ session('theme', 'light') }}" class="auth-page">
@include('partials.theme_toggle', ['themeToggleClass' => 'se-theme-toggle--standalone'])
<form method="POST" action="{{ route('locale.update') }}" class="lang-switch">
    @csrf
    <select name="locale" onchange="this.form.submit()">
        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>EN</option>
        <option value="ms" {{ app()->getLocale() === 'ms' ? 'selected' : '' }}>BM</option>
    </select>
</form>
<div class="login-shell">
<div class="page-wrapper">

    <div class="brand-panel">
        <div class="brand-panel-inner">
            <img src="{{ asset('images/logo-politeknik-besut.png') }}" alt="Politeknik Besut" class="brand-logo">
            <div class="brand-title">MyHEP</div>
            <div class="brand-subtitle">{{ __('login.brand_subtitle') }}</div>
            <div class="brand-badge">{{ __('login.system_active') }}</div>
        </div>
    </div>

    <div class="form-panel">
        <div class="form-heading">{{ __('login.welcome_back') }}</div>
        <div class="form-subheading">{{ __('login.login_continue') }}</div>

        @if ($errors->any())
            <div class="alert-error">
                <span></span>
                <div>
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf

            <div class="role-toggle">
                <input type="radio" name="role" id="role_student" value="student" {{ old('role', 'student') == 'student' ? 'checked' : '' }}>
                <label for="role_student">{{ __('login.role_student') }}</label>
                <input type="radio" name="role" id="role_admin" value="admin" {{ old('role') == 'admin' ? 'checked' : '' }}>
                <label for="role_admin">{{ __('login.role_admin') }}</label>
            </div>

            <div class="field">
                <label for="username" id="usernameLabel">{{ __('login.username_label_student') }}</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    </span>
                    <input id="username" type="text" name="username" autocomplete="username"
                           value="{{ old('username') }}" required autofocus
                           class="{{ $errors->has('username') ? 'is-error' : '' }}"
                           placeholder="{{ __('login.username_placeholder_student') }}">
                </div>
                @error('username')
                    <div class="field-error">{{ __('login.error_prefix') }} {{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="password" id="passwordLabel">{{ __('login.password_label_student') }}</label>
                <div class="input-wrap">
                    <span class="input-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                    </span>
                    <input id="password" type="password" name="password" required
                           class="{{ $errors->has('password') ? 'is-error' : '' }}"
                           placeholder="{{ __('login.password_placeholder_student') }}">
                    <button type="button" class="pwd-toggle" id="pwdToggle" aria-controls="password" aria-pressed="false" aria-label="{{ __('login.show_password') }}" title="{{ __('login.show_password') }}">
                        <svg class="password-eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="password-eye-off" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18"/><path d="M10.6 6.2A11.7 11.7 0 0 1 12 6c6.5 0 10 6 10 6a18 18 0 0 1-2.2 3"/><path d="M6.2 6.2C3.5 8 2 12 2 12s3.5 6 10 6c1 0 2-.2 2.8-.5"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                    </button>
                </div>
                @error('password')
                    <div class="field-error">{{ __('login.error_prefix') }} {{ $message }}</div>
                @enderror
            </div>

            <div class="form-row">
                <label class="remember-label">
                    <input type="checkbox" name="remember" id="remember_me" {{ old('remember') ? 'checked' : '' }}>
                    {{ __('login.remember_me') }}
                </label>
                <a class="link-inline" href="{{ route('password.forgot') }}">{{ __('login.forgot_password') }}</a>
            </div>

            <button type="submit" class="btn-submit">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/>
                </svg>
                {{ __('login.login_button') }}
            </button>

            <a href="{{ route('home') }}" class="btn-home">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.125 1.125 0 011.591 0L21.75 12M4.5 9.75V19.5A2.25 2.25 0 006.75 21.75h10.5a2.25 2.25 0 002.25-2.25V9.75"/>
                </svg>
                {{ __('login.home_button') }}
            </a>
        </form>
    </div>
</div>
@include('partials.app_footer')
</div>

<script>
    const roleStudent = document.getElementById('role_student');
    const roleAdmin = document.getElementById('role_admin');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const usernameLabel = document.getElementById('usernameLabel');
    const passwordLabel = document.getElementById('passwordLabel');
    const loginText = {
        usernameLabelAdmin: @json(__('login.username_label_admin')),
        usernamePlaceholderAdmin: @json(__('login.username_placeholder_admin')),
        passwordLabelAdmin: @json(__('login.password_label_admin')),
        passwordPlaceholderAdmin: @json(__('login.password_placeholder_admin')),
        usernameLabelStudent: @json(__('login.username_label_student')),
        usernamePlaceholderStudent: @json(__('login.username_placeholder_student')),
        passwordLabelStudent: @json(__('login.password_label_student')),
        passwordPlaceholderStudent: @json(__('login.password_placeholder_student'))
    };

    function syncLoginHintByRole() {
        if (roleAdmin.checked) {
            usernameLabel.textContent = loginText.usernameLabelAdmin;
            usernameInput.placeholder = loginText.usernamePlaceholderAdmin;
            usernameInput.inputMode = 'email';
            passwordLabel.textContent = loginText.passwordLabelAdmin;
            passwordInput.placeholder = loginText.passwordPlaceholderAdmin;
        } else {
            usernameLabel.textContent = loginText.usernameLabelStudent;
            usernameInput.placeholder = loginText.usernamePlaceholderStudent;
            usernameInput.inputMode = 'text';
            passwordLabel.textContent = loginText.passwordLabelStudent;
            passwordInput.placeholder = loginText.passwordPlaceholderStudent;
        }
    }

    roleStudent.addEventListener('change', syncLoginHintByRole);
    roleAdmin.addEventListener('change', syncLoginHintByRole);
    syncLoginHintByRole();

    const pwdToggle = document.getElementById('pwdToggle');
    const pwdInput = document.getElementById('password');
    pwdToggle.addEventListener('click', () => {
        const isPassword = pwdInput.type === 'password';
        pwdInput.type = isPassword ? 'text' : 'password';
        pwdToggle.setAttribute('aria-pressed', isPassword ? 'true' : 'false');
        pwdToggle.setAttribute('aria-label', isPassword ? 'Hide password' : @json(__('login.show_password')));
        pwdToggle.setAttribute('title', isPassword ? 'Hide password' : @json(__('login.show_password')));
    });
</script>
</body>
</html>
