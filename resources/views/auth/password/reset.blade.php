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
    <title>Tetapkan Kata Laluan Baharu</title>
    @include('partials.brand_icons')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root { --bg:#fdf8f3; --surface:#fff; --text:#111827; --muted:#6b7280; --border:#eadfd2; --field:#fff; --primary:#8a7362; }
        body[data-theme="dark"] { --bg:#0f0e0d; --surface:#171412; --text:#f7efe8; --muted:#c8b8a9; --border:rgba(226,209,192,.16); --field:rgba(24,21,18,.82); --primary:#d7bfa8; color-scheme:dark; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background:var(--bg); color:var(--text); margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1rem; }
        .card { width:100%; max-width:460px; background:var(--surface); border:1px solid var(--border); border-radius:14px; padding:18px; }
        h1 { margin:0 0 6px; font-size:22px; }
        p { margin:0 0 14px; color:var(--muted); font-size:14px; }
        label { display:block; font-size:13px; font-weight:600; color:var(--muted); margin-bottom:6px; }
        input { width:100%; border:1px solid var(--border); border-radius:8px; padding:9px 10px; font-size:14px; margin-bottom:10px; background:var(--field); color:var(--text); }
        .password-wrap { position:relative; margin-bottom:10px; }
        .password-wrap input { margin-bottom:0; padding-right:46px; }
        .password-toggle { position:absolute; top:50%; right:5px; transform:translateY(-50%)!important; width:36px; height:36px; display:inline-flex; align-items:center; justify-content:center; padding:0; border:0; border-radius:8px; background:transparent!important; color:var(--muted); box-shadow:none!important; transition:none!important; cursor:pointer; }
        .password-toggle:hover,.password-toggle:active { transform:translateY(-50%)!important; background:transparent!important; box-shadow:none!important; }
        .password-toggle:focus-visible { outline:2px solid var(--primary); outline-offset:1px; }
        .password-toggle svg { width:19px; height:19px; fill:none; stroke:currentColor; stroke-width:1.8; stroke-linecap:round; stroke-linejoin:round; }
        .password-toggle .eye-off { display:none; }
        .password-toggle[aria-pressed="true"] .eye { display:none; }
        .password-toggle[aria-pressed="true"] .eye-off { display:block; }
        .actions { display:flex; gap:8px; margin-top:8px; }
        .btn { display:inline-block; border:1px solid #cbb9a4; background:var(--surface); color:var(--primary); border-radius:8px; padding:9px 12px; text-decoration:none; font-size:13px; font-weight:600; cursor:pointer; }
        .btn-primary { background:linear-gradient(135deg,#A48D78,#CBB9A4); color:#fff; border:none; }
        .err { margin-bottom:10px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:8px; padding:9px; font-size:13px; }
        .app-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: .75rem;
            text-align: center;
            font-size: .76rem;
            color: var(--muted);
            pointer-events: none;
        }
    </style>
    @vite('resources/css/design-system.css')
</head>
<body data-theme="{{ session('theme', 'light') }}">
@include('partials.theme_toggle', ['themeToggleClass' => 'se-theme-toggle--standalone'])
<div class="card">
    <h1>Kata Laluan Baharu</h1>
    <p>Tetapkan kata laluan baharu untuk akaun anda.</p>

    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('password.reset.update') }}">
        @csrf
        <input type="hidden" name="ref" value="{{ $ref }}">

        <label for="password">Kata Laluan Baharu</label>
        <div class="password-wrap"><input id="password" name="password" type="password" minlength="8" required><button type="button" class="password-toggle" data-password-toggle aria-controls="password" aria-pressed="false" aria-label="Show password" title="Show password"><svg class="eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eye-off" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18"/><path d="M10.6 6.2A11.7 11.7 0 0 1 12 6c6.5 0 10 6 10 6a18 18 0 0 1-2.2 3"/><path d="M6.2 6.2C3.5 8 2 12 2 12s3.5 6 10 6c1 0 2-.2 2.8-.5"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button></div>

        <label for="password_confirmation">Sahkan Kata Laluan Baharu</label>
        <div class="password-wrap"><input id="password_confirmation" name="password_confirmation" type="password" minlength="8" required><button type="button" class="password-toggle" data-password-toggle aria-controls="password_confirmation" aria-pressed="false" aria-label="Show password" title="Show password"><svg class="eye" viewBox="0 0 24 24" aria-hidden="true"><path d="M2 12s3.5-6 10-6 10 6 10 6-3.5 6-10 6S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg><svg class="eye-off" viewBox="0 0 24 24" aria-hidden="true"><path d="m3 3 18 18"/><path d="M10.6 6.2A11.7 11.7 0 0 1 12 6c6.5 0 10 6 10 6a18 18 0 0 1-2.2 3"/><path d="M6.2 6.2C3.5 8 2 12 2 12s3.5 6 10 6c1 0 2-.2 2.8-.5"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button></div>

        <div class="actions">
            <button class="btn btn-primary" type="submit">Simpan Kata Laluan</button>
            <a class="btn" href="{{ route('login') }}">Kembali Login</a>
        </div>
    </form>
</div>
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
