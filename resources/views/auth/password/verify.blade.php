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
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @vite('resources/css/design-system.css')
</head>
<body data-theme="{{ session('theme', 'light') }}" class="auth-page">
@include('partials.theme_toggle', ['themeToggleClass' => 'se-theme-toggle--standalone'])
<div class="card">
    <h1>{{ __('Verifikasi Kod') }}</h1>
    <p>Masukkan kod 6 digit yang dihantar ke {{ $maskedEmail }}.</p>

    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('password.verify.check') }}">
        @csrf
        <input type="hidden" name="ref" value="{{ $ref }}">

        <label for="code">{{ __('Kod Verifikasi') }}</label>
        <input id="code" name="code" type="text" inputmode="numeric" maxlength="6" value="{{ old('code') }}" required>

        <div class="actions">
            <button class="btn btn-primary" type="submit">{{ __('Sahkan Kod') }}</button>
            <a class="btn" href="{{ route('password.forgot') }}">{{ __('Hantar Semula') }}</a>
        </div>
    </form>
</div>
@include('partials.app_footer')
</body>
</html>
