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
    <title>{{ __('bug_reports.public_title') }} - MyHEP</title>
    @include('partials.brand_icons')
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @vite('resources/css/design-system.css')
</head>
<body data-theme="{{ session('theme', 'light') }}" class="public-bug-report-page">
@include('partials.theme_toggle', ['themeToggleClass' => 'se-theme-toggle--standalone'])
    <div class="shell">
        <div class="topbar">
            <a href="{{ route('home') }}" class="brand">
                <img src="{{ asset('images/myhep-mark.png') }}?v=11" alt="MyHEP">
                <span>MyHEP</span>
            </a>
            <a href="{{ route('home') }}" class="back-link">{{ __('bug_reports.back_home') }}</a>
        </div>

        <div class="hero">
            <section class="panel">
                <div class="panel-head">
                    <span class="eyebrow">{{ __('bug_reports.public_eyebrow') }}</span>
                    <h1>{{ __('bug_reports.public_heading') }}</h1>
                    <p class="lead">{{ __('bug_reports.public_description') }}</p>
                </div>

                <div class="panel-body">
                    @if(session('success'))
                        <div class="message ok">{{ session('success') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="message err">
                            @foreach($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('bug-reports.store') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="grid">
                            <div class="field">
                                <label for="reporter_name">{{ __('bug_reports.form_name') }}</label>
                                <input id="reporter_name" name="reporter_name" type="text" value="{{ old('reporter_name', $authenticatedReporter?->full_name) }}" required @if($authenticatedReporter) readonly @endif>
                            </div>
                            <div class="field">
                                <label for="reporter_email">{{ __('bug_reports.form_email') }}</label>
                                <input id="reporter_email" name="reporter_email" type="email" value="{{ old('reporter_email', $authenticatedReporter?->email) }}" required @if($authenticatedReporter?->email) readonly @endif>
                            </div>
                            <div class="field">
                                <label for="category">{{ __('bug_reports.form_category') }}</label>
                                <select id="category" name="category" required>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" @selected(old('category', 'bug') === $category)>
                                            {{ __('bug_reports.category_' . $category) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="field full">
                                <label for="subject">{{ __('bug_reports.form_subject') }}</label>
                                <input id="subject" name="subject" type="text" value="{{ old('subject') }}" required>
                            </div>
                            <div class="field full">
                                <label for="description">{{ __('bug_reports.form_description') }}</label>
                                <textarea id="description" name="description" required>{{ old('description') }}</textarea>
                                <div class="hint">{{ __('bug_reports.form_description_hint') }}</div>
                            </div>
                            <div class="field full">
                                <label for="screenshot">{{ __('bug_reports.form_screenshot') }}</label>
                                <input id="screenshot" name="screenshot" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                                <div class="hint">{{ __('bug_reports.form_screenshot_hint') }}</div>
                            </div>
                        </div>

                        <div class="actions">
                            <button type="submit" class="btn btn-primary">{{ __('bug_reports.form_submit') }}</button>
                            <a href="{{ route('home') }}" class="btn btn-secondary">{{ __('bug_reports.form_cancel') }}</a>
                        </div>
                    </form>
                </div>
            </section>

            <aside class="info-card">
                <div class="info-block">
                    <h2>{{ __('bug_reports.help_title') }}</h2>
                    <ul>
                        <li>{{ __('bug_reports.help_point_1') }}</li>
                        <li>{{ __('bug_reports.help_point_2') }}</li>
                        <li>{{ __('bug_reports.help_point_3') }}</li>
                    </ul>
                </div>
                <div class="info-block">
                    <h2>{{ __('bug_reports.privacy_title') }}</h2>
                    <p>{{ __('bug_reports.privacy_description') }}</p>
                </div>
                <div class="info-block">
                    <h2>{{ __('bug_reports.response_title') }}</h2>
                    <p>{{ __('bug_reports.response_description') }}</p>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>
