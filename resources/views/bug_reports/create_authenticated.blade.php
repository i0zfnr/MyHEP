@extends('layouts.app')

@section('title', __('bug_reports.public_title'))

@section('header')
    <h2 style="margin:0;font-size:1.15rem;font-weight:800;color:var(--text);">{{ __('Report a Problem') }}</h2>
@endsection

@section('content')
<div class="report-auth-shell">
    @if(session('success'))
        <div class="report-alert ok">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="report-alert err">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <div class="report-auth-grid">
        <section class="card report-auth-card">
            <div class="report-auth-head">
                <div class="report-auth-head-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M4.5 19.5h15l-7.5-15-7.5 15z"/></svg>
                </div>
                <div>
                    <h2>{{ __('bug_reports.public_heading') }}</h2>
                    <p class="report-auth-sub">{{ __('Send a clear report directly to the MyHEP system administrators. Your account details are attached automatically.') }}</p>
                </div>
            </div>

            <form class="report-auth-form" method="POST" action="{{ route('bug-reports.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="report-auth-fields">
                    <div class="report-auth-row-2">
                        <div class="report-auth-field">
                            <label for="reporter_name">{{ __('bug_reports.form_name') }}</label>
                            <input id="reporter_name" name="reporter_name" value="{{ old('reporter_name', $authenticatedReporter?->full_name) }}" required readonly>
                        </div>
                        <div class="report-auth-field">
                            <label for="reporter_email">{{ __('bug_reports.form_email') }}</label>
                            <input id="reporter_email" name="reporter_email" type="email" value="{{ old('reporter_email', $authenticatedReporter?->email) }}" required @if($authenticatedReporter?->email) readonly @endif>
                        </div>
                    </div>

                    <div class="report-auth-field">
                        <label for="category">{{ __('bug_reports.form_category') }}</label>
                        <select id="category" name="category" required>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" @selected(old('category', 'bug') === $category)>
                                    {{ __('bug_reports.category_' . $category) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="report-auth-field">
                        <label for="subject">{{ __('bug_reports.form_subject') }}</label>
                        <input id="subject" name="subject" value="{{ old('subject') }}" required placeholder="{{ __('Briefly describe the issue') }}">
                    </div>

                    <div class="report-auth-field">
                        <label for="description">{{ __('bug_reports.form_description') }}</label>
                        <textarea id="description" name="description" rows="5" required placeholder="{{ __('What happened? What did you expect to happen?') }}">{{ old('description') }}</textarea>
                        <span class="report-auth-hint">{{ __('bug_reports.form_description_hint') }}</span>
                    </div>

                    <div class="report-auth-field">
                        <label for="screenshot">{{ __('bug_reports.form_screenshot') }}</label>
                        <input id="screenshot" name="screenshot" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                        <span class="report-auth-hint">{{ __('bug_reports.form_screenshot_hint') }}</span>
                    </div>
                </div>

                <div class="report-auth-actions">
                    <a class="btn btn-outline" href="{{ ($authenticatedReporter && session('auth_user.role') === 'admin') ? route('admin.dashboard') : route('student.dashboard') }}">
                        {{ __('bug_reports.form_cancel') }}
                    </a>
                    <button class="btn btn-primary" type="submit">
                        {{ __('bug_reports.form_submit') }}
                    </button>
                </div>
            </form>
        </section>

        <aside class="report-auth-aside">
            <div class="card report-auth-tip">
                <div class="report-auth-tip-head">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                    <h3>{{ __('bug_reports.help_title') }}</h3>
                </div>
                <ul>
                    <li>{{ __('bug_reports.help_point_1') }}</li>
                    <li>{{ __('bug_reports.help_point_2') }}</li>
                    <li>{{ __('bug_reports.help_point_3') }}</li>
                </ul>
            </div>

            <div class="card report-auth-tip">
                <div class="report-auth-tip-head">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    <h3>{{ __('Privacy') }}</h3>
                </div>
                <p>{{ __('Do not include passwords, bank details, or other sensitive information in your report or screenshot.') }}</p>
            </div>
        </aside>
    </div>
</div>
@endsection
