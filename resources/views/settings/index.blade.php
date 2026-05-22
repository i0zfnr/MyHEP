@extends('layouts.app')

@section('title', __('ui.settings_title'))

@push('styles')
<style>
    .wrap { max-width: 760px; margin: 0 auto; }
    .card { background:var(--surface); border:1px solid var(--border); border-radius:12px; overflow:hidden; }
    .head { padding:14px 16px; border-bottom:1px solid var(--border); font-weight:700; color:var(--text); }
    .body { padding:16px; }
    .ok { margin-bottom:12px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:8px; padding:10px; font-size:13px; }
    .err { margin-bottom:12px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:8px; padding:10px; font-size:13px; }
    .field { margin-bottom:16px; }
    label { display:block; margin-bottom:8px; font-size:13px; font-weight:600; color:var(--text-muted); }
    .hint { margin:0 0 10px; color:var(--text-muted); font-size:13px; }
    select { width:100%; border:1px solid var(--border); border-radius:8px; padding:9px 10px; font-size:14px; background:var(--surface); color:var(--text); }
    .actions { display:flex; gap:8px; margin-top:18px; flex-wrap:wrap; }
    .btn { display:inline-block; border:1px solid var(--primary-light); background:var(--surface); color:var(--primary-dark); border-radius:8px; padding:9px 14px; text-decoration:none; font-weight:600; font-size:14px; cursor:pointer; }
    .btn-primary { background:linear-gradient(135deg,#A48D78,#CBB9A4); color:#fff; border:none; }
    body[data-theme="dark"] .wrap .card {
        background: #171412;
        border-color: rgba(226, 209, 192, .18);
        box-shadow: 0 18px 42px rgba(0, 0, 0, .34);
    }
    body[data-theme="dark"] .wrap .head {
        background: rgba(255, 255, 255, .03);
        border-color: rgba(226, 209, 192, .12);
        color: #f7efe8;
    }
    body[data-theme="dark"] .wrap label,
    body[data-theme="dark"] .wrap .hint {
        color: #c8b8a9;
    }
    body[data-theme="dark"] .wrap select {
        background: #201b17;
        border-color: rgba(226, 209, 192, .22);
        color: #f7efe8;
    }
    body[data-theme="dark"] .wrap .btn {
        background: #201b17;
        border-color: rgba(215, 191, 168, .38);
        color: #f2dfca;
    }
    body[data-theme="dark"] .wrap .btn-primary {
        background: linear-gradient(135deg, #8a7362, #d7bfa8);
        color: #14110f;
    }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:var(--text);">{{ __('ui.settings_title') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('settings.update') }}">
        @csrf
        <div class="card">
            <div class="head">{{ __('ui.settings') }}</div>
            <div class="body">
                <div class="field">
                    <label for="locale">{{ __('ui.language') }}</label>
                    <p class="hint">{{ __('ui.language_hint') }}</p>
                    <select id="locale" name="locale" required>
                        <option value="en" {{ $currentLocale === 'en' ? 'selected' : '' }}>{{ __('ui.language_english') }}</option>
                        <option value="ms" {{ $currentLocale === 'ms' ? 'selected' : '' }}>{{ __('ui.language_malay') }}</option>
                    </select>
                </div>

                <div class="field">
                    <label for="theme">{{ __('Theme') }}</label>
                    <p class="hint">{{ __('Choose how the system interface appears.') }}</p>
                    <select id="theme" name="theme" required>
                        <option value="light" {{ $currentTheme === 'light' ? 'selected' : '' }}>{{ __('Light') }}</option>
                        <option value="dark" {{ $currentTheme === 'dark' ? 'selected' : '' }}>{{ __('Dark') }}</option>
                    </select>
                </div>

                <div class="actions">
                    <button class="btn btn-primary" type="submit">{{ __('ui.save_changes') }}</button>
                    <a class="btn" href="{{ route($backRoute) }}">{{ __('ui.back_dashboard') }}</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
