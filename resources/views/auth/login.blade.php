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
    <link rel="icon" type="image/png" href="{{ asset('images/newlogo.png') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/pwa/icon-180.png') }}">
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
        body[data-theme="dark"] {
            --primary: #d7bfa8;
            --primary-dark: #f2dfca;
            --primary-light: #b99b82;
            --accent: #f2c999;
            --surface: #17130f;
            --bg: #080807;
            --text: #f7efe8;
            --text-muted: #c8b8a9;
            --border: rgba(226, 209, 192, .18);
            --glass-bg: rgba(23,19,15,.88);
            --glass-border: rgba(226,209,192,.18);
            --glass-line: rgba(226,209,192,.16);
            --shadow-lg: 0 28px 64px rgba(0,0,0,.48), inset 0 1px 0 rgba(255,255,255,.04);
            color-scheme: dark;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
            overflow-y: auto;
            color: var(--text);
        }

        .login-shell {
            width: 100%;
            max-width: 900px;
            min-height: calc(100vh - 3rem);
            min-height: calc(100dvh - 3rem);
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            z-index: 1;
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
        body[data-theme="dark"]::before {
            background: radial-gradient(circle, rgba(215,191,168,.10) 0%, transparent 70%);
        }
        body[data-theme="dark"]::after {
            background: radial-gradient(circle, rgba(242,201,153,.07) 0%, transparent 70%);
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
            margin: 3.5rem 0 1rem;
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
            color: var(--text-muted);
            width: 28px; height: 28px; padding: 0;
        }
        .pwd-toggle:hover { transform: translateY(-50%); }
        .pwd-toggle svg {
            position: absolute; top: 5px; left: 5px;
        }

        .form-row {
            display: flex; align-items: center; justify-content: space-between;
            gap: .75rem;
            flex-wrap: wrap;
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
            body {
                display: block;
                padding: max(1rem, env(safe-area-inset-top)) 1rem max(1rem, env(safe-area-inset-bottom));
            }
            .login-shell {
                min-height: calc(100vh - max(2rem, env(safe-area-inset-top) + env(safe-area-inset-bottom)));
                min-height: calc(100dvh - max(2rem, env(safe-area-inset-top) + env(safe-area-inset-bottom)));
                max-width: 420px;
                margin: 0 auto;
            }
            .page-wrapper {
                flex-direction: column;
                max-width: 100%;
                min-height: auto;
                margin: 4.25rem 0 1rem;
                border-radius: 18px;
            }
            .brand-panel { padding: 1.9rem 1.35rem 1.65rem; }
            .brand-logo { width: 64px; height: 64px; }
            .brand-title { font-size: 1.2rem; }
            .brand-badge { margin-top: 1.1rem; }
            .form-panel {
                flex: none;
                padding: 1.7rem 1.35rem 1.35rem;
                justify-content: flex-start;
            }
            .form-subheading { margin-bottom: 1.25rem; }
            .role-toggle { margin-bottom: 1.1rem; }
            .form-row {
                align-items: flex-start;
                margin-bottom: 1.15rem;
            }
            .remember-label,
            .link-inline {
                font-size: .82rem;
            }
            .lang-switch {
                top: max(.75rem, env(safe-area-inset-top));
                right: 1rem;
                padding: .24rem .5rem;
            }
            .app-footer {
                padding-bottom: calc(1rem + env(safe-area-inset-bottom));
            }
        }

        @media (max-width: 420px) {
            body {
                padding-left: .75rem;
                padding-right: .75rem;
            }
            .page-wrapper {
                margin-top: 4rem;
            }
            .brand-panel,
            .form-panel {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            .form-heading {
                font-size: 1.25rem;
            }
            .field input[type="text"],
            .field input[type="password"] {
                font-size: .92rem;
            }
            .btn-submit,
            .btn-home {
                padding: .78rem;
            }
            .app-footer {
                font-size: .72rem;
            }
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
            position: relative;
            text-align: center;
            font-size: .76rem;
            color: #7a6555;
            z-index: 5;
            padding: 0 1rem 1.25rem;
        }
        body[data-theme="dark"] .page-wrapper {
            background: #12100e;
            border-color: rgba(226, 209, 192, .16);
            box-shadow: 0 30px 70px rgba(0,0,0,.55);
        }
        body[data-theme="dark"] .brand-panel {
            background:
                linear-gradient(145deg, #3b3129 0%, #7b6757 58%, #a88f79 100%),
                radial-gradient(circle at 18% 18%, rgba(255,255,255,.16), transparent 38%);
        }
        body[data-theme="dark"] .brand-panel::before {
            opacity: .55;
        }
        body[data-theme="dark"] .brand-title,
        body[data-theme="dark"] .brand-subtitle,
        body[data-theme="dark"] .brand-badge {
            text-shadow: 0 1px 8px rgba(0,0,0,.28);
        }
        body[data-theme="dark"] .form-panel {
            background:
                linear-gradient(145deg, rgba(18,16,14,.98), rgba(13,12,10,.98)),
                radial-gradient(circle at 100% 0%, rgba(215,191,168,.08), transparent 38%);
            border-left-color: rgba(226, 209, 192, .14);
        }
        body[data-theme="dark"] .form-heading {
            color: #fff8f1;
        }
        body[data-theme="dark"] .form-subheading,
        body[data-theme="dark"] .field label,
        body[data-theme="dark"] .remember-label {
            color: #cbbdaf;
        }
        body[data-theme="dark"] .role-toggle {
            background: #0d0c0b;
            border-color: rgba(226, 209, 192, .18);
            box-shadow: inset 0 1px 0 rgba(255,255,255,.03);
        }
        body[data-theme="dark"] .role-toggle label {
            color: #b8a899;
        }
        body[data-theme="dark"] .role-toggle input[type="radio"]:checked + label {
            background: #d7bfa8;
            color: #14110f;
            box-shadow: 0 10px 22px rgba(215,191,168,.16);
        }
        body[data-theme="dark"] .field input[type="text"],
        body[data-theme="dark"] .field input[type="password"] {
            background: #0d0c0b;
            border-color: rgba(226, 209, 192, .20);
            color: #fff8f1;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.03);
        }
        body[data-theme="dark"] .field input[type="text"]::placeholder,
        body[data-theme="dark"] .field input[type="password"]::placeholder {
            color: #7f7165;
        }
        body[data-theme="dark"] .field input:focus {
            border-color: #d7bfa8;
            box-shadow: 0 0 0 3px rgba(215,191,168,.16);
        }
        body[data-theme="dark"] .input-icon,
        body[data-theme="dark"] .pwd-toggle {
            color: #8f8173;
        }
        body[data-theme="dark"] .link-inline {
            color: #e8cdb5;
        }
        body[data-theme="dark"] .btn-submit {
            background: linear-gradient(135deg, #d7bfa8, #f2dfca);
            color: #17130f;
            box-shadow: 0 12px 26px rgba(215,191,168,.18);
        }
        body[data-theme="dark"] .btn-home {
            background: rgba(255,255,255,.08);
            border-color: rgba(226,209,192,.18);
            color: #f2dfca;
            box-shadow: none;
        }
        body[data-theme="dark"] .btn-home:hover {
            background: rgba(215,191,168,.14);
            border-color: rgba(215,191,168,.45);
        }
        body[data-theme="dark"] .lang-switch {
            background: #17130f;
            border-color: rgba(226,209,192,.20);
            box-shadow: 0 12px 24px rgba(0,0,0,.28);
        }
        body[data-theme="dark"] .lang-switch select {
            color: #f7efe8;
            background: transparent;
        }
        body[data-theme="dark"] .app-footer {
            color: #7f7165;
        }
    </style>
    @vite('resources/css/design-system.css')
</head>
<body data-theme="{{ session('theme', 'light') }}">
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
            {{-- <img src="{{ asset('images/politeknik-logo.png') }}" alt="Logo StudentEdge" class="brand-logo"> --}}
            <div class="brand-title">StudentEdge</div>
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
                        <svg id="eyeOpenIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <svg id="eyeClosedIcon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="display:none">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
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
    const eyeOpenIcon = document.getElementById('eyeOpenIcon');
    const eyeClosedIcon = document.getElementById('eyeClosedIcon');

    pwdToggle.addEventListener('click', () => {
        const isPassword = pwdInput.type === 'password';
        pwdInput.type = isPassword ? 'text' : 'password';
        eyeOpenIcon.style.display = isPassword ? 'none' : '';
        eyeClosedIcon.style.display = isPassword ? '' : 'none';
    });
</script>
</body>
</html>
