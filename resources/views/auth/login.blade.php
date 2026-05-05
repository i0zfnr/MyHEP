<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('login.page_title') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logohep.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logohep.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --primary: #A48D78;
            --primary-dark: #CBB9A4;
            --primary-light: #FFDDAB;
            --accent: #FAD7BC;
            --surface: #ffffff;
            --bg: #fdf8f3;
            --text: #111827;
            --text-muted: #6b7280;
            --border: #eadfd2;
            --error: #ef4444;
            --radius: 12px;
            --glass-bg: rgba(255,255,255,.64);
            --glass-border: rgba(255,255,255,.74);
            --glass-line: rgba(234,223,210,.70);
            --glass-blur: 18px;
            --shadow-lg: 0 22px 52px rgba(164,141,120,.18), inset 0 1px 0 rgba(255,255,255,.74);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow: hidden;
            color: var(--text);
        }

        body::before, body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            z-index: 0;
            pointer-events: none;
        }
        body::before {
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(203,185,164,.25) 0%, transparent 70%);
            top: -200px; right: -150px;
        }
        body::after {
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(250,215,188,.35) 0%, transparent 70%);
            bottom: -150px; left: -100px;
        }

        .page-wrapper {
            display: flex;
            width: 100%;
            max-width: 900px;
            min-height: 540px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            border: 1px solid var(--glass-border);
            background: rgba(255,255,255,.34);
            backdrop-filter: blur(var(--glass-blur)) saturate(132%);
            -webkit-backdrop-filter: blur(var(--glass-blur)) saturate(132%);
            position: relative;
            z-index: 1;
            animation: fadeUp .5s cubic-bezier(.22,.61,.36,1) both;
        }

        .brand-panel {
            flex: 1;
            background:
                linear-gradient(145deg, rgba(203,185,164,.82) 0%, rgba(164,141,120,.82) 60%, rgba(250,215,188,.72) 100%),
                radial-gradient(circle at 20% 20%, rgba(255,255,255,.28), transparent 36%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 2.5rem;
            position: relative;
            overflow: hidden;
        }
        .brand-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='30' cy='30' r='1.5' fill='rgba(255,255,255,.12)'/%3E%3C/svg%3E");
        }
        .brand-panel-inner { position: relative; text-align: center; }
        .brand-logo { width: 88px; height: 88px; object-fit: contain; margin-bottom: 1.5rem; filter: drop-shadow(0 4px 12px rgba(0,0,0,.25)); }
        .brand-title {
            font-size: 1.6rem; font-weight: 700; color: #fff;
            letter-spacing: -.3px; line-height: 1.2; margin-bottom: .5rem;
        }
        .brand-subtitle { font-size: .875rem; color: rgba(255,255,255,.75); font-weight: 500; }
        .brand-badge {
            display: inline-flex; align-items: center; gap: .4rem;
            margin-top: 2rem; padding: .45rem 1rem;
            background: rgba(255,255,255,.15); border-radius: 99px;
            font-size: .75rem; color: rgba(255,255,255,.9); font-weight: 600;
            letter-spacing: .04em; text-transform: uppercase;
            border: 1px solid rgba(255,255,255,.2);
            backdrop-filter: blur(8px);
        }
        .brand-badge::before { content: '*'; color: #4ade80; font-size: .6rem; }

        .form-panel {
            flex: 0 0 420px;
            background:
                linear-gradient(145deg, rgba(255,255,255,.76), rgba(253,248,243,.54)),
                radial-gradient(circle at 94% 10%, rgba(164,141,120,.10), transparent 34%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 2.5rem 2.75rem;
            border-left: 1px solid var(--glass-line);
            backdrop-filter: blur(var(--glass-blur)) saturate(136%);
            -webkit-backdrop-filter: blur(var(--glass-blur)) saturate(136%);
        }

        .form-heading { font-size: 1.4rem; font-weight: 700; color: var(--text); margin-bottom: .25rem; }
        .form-subheading { font-size: .875rem; color: var(--text-muted); margin-bottom: 2rem; }

        .role-toggle {
            display: grid; grid-template-columns: 1fr 1fr;
            background: rgba(255,255,255,.46); border-radius: var(--radius);
            border: 1px solid var(--glass-line);
            padding: 4px; gap: 4px; margin-bottom: 1.5rem;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.62);
        }
        .role-toggle input[type="radio"] { display: none; }
        .role-toggle label {
            display: flex; align-items: center; justify-content: center; gap: .5rem;
            padding: .6rem; border-radius: 9px;
            font-size: .875rem; font-weight: 600; color: var(--text-muted);
            cursor: pointer; transition: all .2s ease;
            user-select: none;
        }
        .role-toggle input[type="radio"]:checked + label {
            background: rgba(255,255,255,.76);
            color: var(--primary);
            box-shadow: 0 6px 16px rgba(164,141,120,.14), inset 0 1px 0 rgba(255,255,255,.72);
        }

        .field { margin-bottom: 1.1rem; }
        .field label {
            display: block; font-size: .8rem; font-weight: 600;
            color: var(--text-muted); margin-bottom: .4rem; letter-spacing: .02em;
            text-transform: uppercase;
        }
        .input-wrap { position: relative; }
        .input-icon {
            position: absolute; left: .9rem; top: 50%; transform: translateY(-50%);
            color: var(--text-muted); font-size: 1rem; pointer-events: none;
        }
        .field input[type="text"],
        .field input[type="password"] {
            width: 100%;
            padding: .7rem .9rem .7rem 2.5rem;
            border: 1.5px solid var(--border);
            border-radius: var(--radius);
            font-size: .9375rem;
            font-family: inherit;
            color: var(--text);
            background: rgba(255,255,255,.62);
            backdrop-filter: blur(10px) saturate(128%);
            -webkit-backdrop-filter: blur(10px) saturate(128%);
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }
        .field input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(164,141,120,.18);
        }
        .field input.is-error { border-color: var(--error); }
        .field-error { font-size: .78rem; color: var(--error); margin-top: .3rem; }

        .pwd-toggle {
            position: absolute; right: .85rem; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--text-muted); padding: .2rem;
            transition: color .15s; display: flex; align-items: center;
        }
        .pwd-toggle:hover { color: var(--primary); }

        .form-row {
            display: flex; align-items: center; justify-content: space-between;
            margin: .25rem 0 1.5rem;
        }
        .remember-label {
            display: flex; align-items: center; gap: .5rem;
            font-size: .85rem; color: var(--text-muted); cursor: pointer;
        }
        .remember-label input[type="checkbox"] {
            width: 15px; height: 15px; accent-color: var(--primary); cursor: pointer;
        }
        .link-inline { font-size: .85rem; color: var(--primary); text-decoration: none; font-weight: 600; }
        .link-inline:hover { text-decoration: underline; }

        .btn-submit {
            width: 100%; padding: .8rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: #fff; border: none; border-radius: var(--radius);
            font-size: .9375rem; font-weight: 700; font-family: inherit;
            cursor: pointer; letter-spacing: .01em;
            display: flex; align-items: center; justify-content: center; gap: .5rem;
            transition: transform .15s, box-shadow .15s, filter .15s;
            box-shadow: 0 4px 14px rgba(164,141,120,.35);
        }
        .btn-submit:hover { transform: translateY(-1px); box-shadow: 0 6px 20px rgba(164,141,120,.45); filter: brightness(1.05); }
        .btn-submit:active { transform: translateY(0); }

        .btn-home {
            width: 100%;
            margin-top: .75rem;
            padding: .75rem;
            background: rgba(255,255,255,.56);
            color: var(--primary-dark);
            border: 1.5px solid var(--glass-line);
            border-radius: var(--radius);
            font-size: .9rem;
            font-weight: 600;
            font-family: inherit;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            transition: border-color .15s, color .15s, background-color .15s;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.68);
            backdrop-filter: blur(10px) saturate(128%);
            -webkit-backdrop-filter: blur(10px) saturate(128%);
        }
        .btn-home:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
        }

        .alert-error {
            background: #fef2f2; border: 1px solid #fecaca; border-radius: var(--radius);
            padding: .7rem 1rem; margin-bottom: 1.25rem;
            font-size: .85rem; color: #b91c1c; display: flex; gap: .5rem; align-items: flex-start;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 700px) {
            .page-wrapper { flex-direction: column; max-width: 420px; min-height: auto; }
            .brand-panel { padding: 2rem 1.5rem; }
            .brand-logo { width: 64px; height: 64px; }
            .brand-title { font-size: 1.2rem; }
            .form-panel { flex: none; padding: 2rem 1.5rem; }
        }

        .lang-switch {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 10;
            background: rgba(255,255,255,.62);
            border: 1px solid var(--glass-border);
            border-radius: 999px;
            padding: .3rem .6rem;
            box-shadow: var(--shadow-lg);
            backdrop-filter: blur(14px) saturate(132%);
            -webkit-backdrop-filter: blur(14px) saturate(132%);
        }
        .lang-switch select {
            border: none;
            background: transparent;
            font-size: .85rem;
            font-weight: 600;
            color: var(--text);
            outline: none;
            cursor: pointer;
        }
        .app-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: .75rem;
            text-align: center;
            font-size: .76rem;
            color: #7a6555;
            z-index: 5;
            pointer-events: none;
        }
    </style>
</head>
<body>
<form method="POST" action="{{ route('locale.update') }}" class="lang-switch">
    @csrf
    <select name="locale" onchange="this.form.submit()">
        <option value="en" {{ app()->getLocale() === 'en' ? 'selected' : '' }}>EN</option>
        <option value="ms" {{ app()->getLocale() === 'ms' ? 'selected' : '' }}>BM</option>
    </select>
</form>
<div class="page-wrapper">

    <div class="brand-panel">
        <div class="brand-panel-inner">
            {{-- <img src="{{ asset('images/politeknik-logo.png') }}" alt="Logo MyHEP POLIBESUT" class="brand-logo"> --}}
            <div class="brand-title">MyHEP<br>POLIBESUT</div>
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
                    <input id="username" type="text" name="username"
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
                    <button type="button" class="pwd-toggle" id="pwdToggle" aria-label="{{ __('login.show_password') }}">
                        <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
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
            passwordLabel.textContent = loginText.passwordLabelAdmin;
            passwordInput.placeholder = loginText.passwordPlaceholderAdmin;
        } else {
            usernameLabel.textContent = loginText.usernameLabelStudent;
            usernameInput.placeholder = loginText.usernamePlaceholderStudent;
            passwordLabel.textContent = loginText.passwordLabelStudent;
            passwordInput.placeholder = loginText.passwordPlaceholderStudent;
        }
    }

    roleStudent.addEventListener('change', syncLoginHintByRole);
    roleAdmin.addEventListener('change', syncLoginHintByRole);
    syncLoginHintByRole();

    const pwdToggle = document.getElementById('pwdToggle');
    const pwdInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');

    const eyeOffPath = `<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>`;
    const eyeOnPath = `<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>`;

    pwdToggle.addEventListener('click', () => {
        const isPassword = pwdInput.type === 'password';
        pwdInput.type = isPassword ? 'text' : 'password';
        eyeIcon.innerHTML = isPassword ? eyeOffPath : eyeOnPath;
    });
</script>
</body>
</html>

