@extends('layouts.app')

@section('title', __('bug_reports.public_title'))

@push('styles')
<style>
    .report-page { max-width:1040px; margin:0 auto; }
    .report-hero { position:relative; overflow:hidden; padding:clamp(1.35rem,3vw,2.2rem); border:1px solid var(--glass-line); border-radius:28px; background:linear-gradient(125deg,color-mix(in srgb,var(--primary) 20%,var(--surface)),var(--glass-bg-strong)); box-shadow:var(--glass-shadow); }
    .report-hero::after { content:''; position:absolute; width:260px; height:260px; right:-100px; top:-130px; border-radius:50%; background:color-mix(in srgb,var(--primary) 28%,transparent); filter:blur(6px); }
    .report-hero > * { position:relative; z-index:1; }
    .report-kicker { display:inline-flex; padding:.36rem .7rem; border-radius:999px; background:color-mix(in srgb,var(--primary) 16%,transparent); color:var(--primary); font-size:.72rem; font-weight:900; letter-spacing:.08em; text-transform:uppercase; }
    .report-hero h1 { max-width:650px; margin:.75rem 0 .45rem; color:var(--text); font-size:clamp(1.8rem,4vw,2.7rem); letter-spacing:-.045em; }
    .report-hero p { max-width:650px; margin:0; color:var(--text-muted); line-height:1.7; }
    .report-grid { display:grid; grid-template-columns:minmax(0,1fr) 290px; gap:1rem; margin-top:1rem; align-items:start; }
    .report-card { border:1px solid var(--glass-line); border-radius:24px; background:var(--glass-bg-strong); box-shadow:var(--glass-shadow); overflow:hidden; }
    .report-form { display:grid; gap:1rem; padding:clamp(1rem,3vw,1.55rem); }
    .report-fields { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; }
    .report-field { display:grid; gap:.45rem; }
    .report-field.full { grid-column:1 / -1; }
    .report-field label { color:var(--text); font-size:.75rem; font-weight:900; letter-spacing:.06em; text-transform:uppercase; }
    .report-field input,.report-field select,.report-field textarea { width:100%; padding:.8rem .9rem; border:1px solid var(--border); border-radius:13px; background:var(--surface); color:var(--text); font:inherit; }
    .report-field textarea { min-height:150px; resize:vertical; }
    .report-field input:focus,.report-field select:focus,.report-field textarea:focus { outline:0; border-color:var(--primary); box-shadow:0 0 0 3px color-mix(in srgb,var(--primary) 16%,transparent); }
    .report-field input[readonly] { color:var(--text-muted); cursor:default; }
    .report-hint { color:var(--text-muted); font-size:.8rem; line-height:1.5; }
    .report-alert { padding:.85rem 1rem; border-radius:14px; font-size:.9rem; line-height:1.5; }
    .report-alert.ok { color:#166534; background:#dcfce7; } .report-alert.err { color:#991b1b; background:#fee2e2; }
    .report-actions { display:flex; justify-content:flex-end; gap:.75rem; padding-top:.2rem; }
    .report-button { display:inline-flex; justify-content:center; align-items:center; min-height:42px; padding:.7rem 1rem; border:1px solid var(--border); border-radius:12px; background:var(--surface); color:var(--text); text-decoration:none; font:inherit; font-weight:800; cursor:pointer; }
    .report-button.primary { border-color:var(--primary); background:var(--primary); color:var(--primary-contrast,#fff); }
    .report-aside { display:grid; gap:1rem; }
    .report-tip { padding:1.1rem; border:1px solid var(--glass-line); border-radius:20px; background:var(--glass-bg-strong); }
    .report-tip h2 { margin:0 0 .55rem; color:var(--text); font-size:1rem; }
    .report-tip p,.report-tip ul { margin:0; color:var(--text-muted); font-size:.88rem; line-height:1.65; }
    .report-tip ul { padding-left:1.1rem; }
    @media (max-width:800px) { .report-grid { grid-template-columns:1fr; } .report-aside { grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media (max-width:560px) { .report-fields,.report-aside { grid-template-columns:1fr; } .report-actions { flex-direction:column-reverse; } .report-button { width:100%; } }
</style>
@endpush

@section('content')
<div class="report-page">
    <header class="report-hero">
        <span class="report-kicker">Support</span>
        <h1>{{ __('bug_reports.public_heading') }}</h1>
        <p>Send a clear report directly to the StudentEdge system administrators. Your account details are attached automatically.</p>
    </header>

    <div class="report-grid">
        <section class="report-card">
            <form class="report-form" method="POST" action="{{ route('bug-reports.store') }}" enctype="multipart/form-data">
                @csrf
                @if(session('success'))<div class="report-alert ok">{{ session('success') }}</div>@endif
                @if($errors->any())<div class="report-alert err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
                <div class="report-fields">
                    <div class="report-field"><label for="reporter_name">{{ __('bug_reports.form_name') }}</label><input id="reporter_name" name="reporter_name" value="{{ old('reporter_name', $authenticatedReporter?->full_name) }}" required readonly></div>
                    <div class="report-field"><label for="reporter_email">{{ __('bug_reports.form_email') }}</label><input id="reporter_email" name="reporter_email" type="email" value="{{ old('reporter_email', $authenticatedReporter?->email) }}" required @if($authenticatedReporter?->email) readonly @endif></div>
                    <div class="report-field"><label for="category">{{ __('bug_reports.form_category') }}</label><select id="category" name="category" required>@foreach($categories as $category)<option value="{{ $category }}" @selected(old('category', 'bug') === $category)>{{ __('bug_reports.category_' . $category) }}</option>@endforeach</select></div>
                    <div class="report-field full"><label for="subject">{{ __('bug_reports.form_subject') }}</label><input id="subject" name="subject" value="{{ old('subject') }}" required placeholder="Briefly describe the issue"></div>
                    <div class="report-field full"><label for="description">{{ __('bug_reports.form_description') }}</label><textarea id="description" name="description" required placeholder="What happened? What did you expect to happen?">{{ old('description') }}</textarea><span class="report-hint">{{ __('bug_reports.form_description_hint') }}</span></div>
                    <div class="report-field full"><label for="screenshot">{{ __('bug_reports.form_screenshot') }}</label><input id="screenshot" name="screenshot" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"><span class="report-hint">{{ __('bug_reports.form_screenshot_hint') }}</span></div>
                </div>
                <div class="report-actions"><a class="report-button" href="{{ ($authenticatedReporter && session('auth_user.role') === 'admin') ? route('admin.dashboard') : route('student.dashboard') }}">{{ __('bug_reports.form_cancel') }}</a><button class="report-button primary" type="submit">{{ __('bug_reports.form_submit') }}</button></div>
            </form>
        </section>
        <aside class="report-aside">
            <div class="report-tip"><h2>{{ __('bug_reports.help_title') }}</h2><ul><li>{{ __('bug_reports.help_point_1') }}</li><li>{{ __('bug_reports.help_point_2') }}</li><li>{{ __('bug_reports.help_point_3') }}</li></ul></div>
            <div class="report-tip"><h2>Privacy</h2><p>Do not include passwords, bank details, or other sensitive information in your report or screenshot.</p></div>
        </aside>
    </div>
</div>
@endsection
