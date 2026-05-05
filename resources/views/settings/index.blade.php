@extends('layouts.app')

@section('title', __('ui.settings_title'))

@push('styles')
<style>
    .wrap { max-width: 760px; margin: 0 auto; }
    .card { background:#fff; border:1px solid #ede4d9; border-radius:12px; overflow:hidden; }
    .head { padding:14px 16px; border-bottom:1px solid #ede4d9; font-weight:700; color:#2d1f14; }
    .body { padding:16px; }
    .ok { margin-bottom:12px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:8px; padding:10px; font-size:13px; }
    .err { margin-bottom:12px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:8px; padding:10px; font-size:13px; }
    label { display:block; margin-bottom:8px; font-size:13px; font-weight:600; color:#7a6555; }
    .hint { margin:0 0 10px; color:#7a6555; font-size:13px; }
    select { width:100%; border:1px solid #e5d8c8; border-radius:8px; padding:9px 10px; font-size:14px; background:#fff; }
    .actions { display:flex; gap:8px; margin-top:14px; flex-wrap:wrap; }
    .btn { display:inline-block; border:1px solid #cbb9a4; background:#fff; color:#8a7362; border-radius:8px; padding:9px 14px; text-decoration:none; font-weight:600; font-size:14px; cursor:pointer; }
    .btn-primary { background:linear-gradient(135deg,#A48D78,#CBB9A4); color:#fff; border:none; }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('ui.settings_title') }}</h2>
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
                <label for="locale">{{ __('ui.language') }}</label>
                <p class="hint">{{ __('ui.language_hint') }}</p>
                <select id="locale" name="locale" required>
                    <option value="en" {{ $currentLocale === 'en' ? 'selected' : '' }}>{{ __('ui.language_english') }}</option>
                    <option value="ms" {{ $currentLocale === 'ms' ? 'selected' : '' }}>{{ __('ui.language_malay') }}</option>
                </select>

                <div class="actions">
                    <button class="btn btn-primary" type="submit">{{ __('ui.save_changes') }}</button>
                    <a class="btn" href="{{ route($backRoute) }}">{{ __('ui.back_dashboard') }}</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
