@extends('layouts.app')

@section('title', __('ui.settings_title'))

@section('header')
    <h2>{{ __('ui.settings_title') }}</h2>
@endsection

@section('content')
<div class="settings-shell">
    @if(session('success'))
        <div class="se-feedback se-feedback--success" role="status" aria-live="polite">
            <span class="se-feedback-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 4 4L19 6"/>
                </svg>
            </span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="se-feedback se-feedback--error" role="alert">
            <span class="se-feedback-icon" aria-hidden="true">!</span>
            <div>
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
            </div>
        </div>
    @endif

    <section class="settings-intro" aria-labelledby="settingsIntroTitle">
        <div class="settings-intro-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="3"/>
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.12 2.12-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.55V20.3h-3v-.09a1.7 1.7 0 0 0-1.03-1.55 1.7 1.7 0 0 0-1.88.34l-.06.06-2.12-2.12.06-.06A1.7 1.7 0 0 0 7 15a1.7 1.7 0 0 0-1.55-1.03H5.3v-3h.15A1.7 1.7 0 0 0 7 9.94a1.7 1.7 0 0 0-.34-1.88L6.6 8l2.12-2.12.06.06a1.7 1.7 0 0 0 1.88.34A1.7 1.7 0 0 0 11.7 4.7v-.1h3v.1a1.7 1.7 0 0 0 1.03 1.56 1.7 1.7 0 0 0 1.88-.34l.06-.06L19.8 8l-.06.06a1.7 1.7 0 0 0-.34 1.88 1.7 1.7 0 0 0 1.55 1.03h.15v3h-.15A1.7 1.7 0 0 0 19.4 15Z"/>
            </svg>
        </div>
        <div>
            <span class="settings-eyebrow">{{ __('ui.preferences') }}</span>
            <h2 id="settingsIntroTitle">{{ __('ui.settings_title') }}</h2>
            <p>{{ __('ui.settings_intro') }}</p>
        </div>
    </section>

    <form method="POST" action="{{ route('settings.update') }}" data-settings-form>
        @csrf
        <div class="settings-panel">
            <section class="settings-section" role="group" aria-labelledby="settingsLanguageTitle">
                <h3 class="settings-section-title" id="settingsLanguageTitle">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9"/>
                        <path stroke-linecap="round" d="M3 12h18M12 3a15 15 0 0 1 0 18M12 3a15 15 0 0 0 0 18"/>
                    </svg>
                    {{ __('ui.language') }}
                </h3>
                <p class="settings-section-copy">{{ __('ui.language_hint') }}</p>

                <div class="settings-options">
                    <label class="settings-option">
                        <input type="radio" name="locale" value="en" {{ $currentLocale === 'en' ? 'checked' : '' }} required>
                        <span class="settings-option-line">
                            <span class="settings-option-check" aria-hidden="true"></span>
                            <span>
                                <strong>{{ __('ui.language_english') }}</strong>
                                <small>{{ __('ui.english_hint') }}</small>
                            </span>
                        </span>
                    </label>

                    <label class="settings-option">
                        <input type="radio" name="locale" value="ms" {{ $currentLocale === 'ms' ? 'checked' : '' }} required>
                        <span class="settings-option-line">
                            <span class="settings-option-check" aria-hidden="true"></span>
                            <span>
                                <strong>{{ __('ui.language_malay') }}</strong>
                                <small>{{ __('ui.malay_hint') }}</small>
                            </span>
                        </span>
                    </label>
                </div>
            </section>

            <section class="settings-section" role="group" aria-labelledby="settingsAppearanceTitle">
                <h3 class="settings-section-title" id="settingsAppearanceTitle">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1.5M12 19.5V21M3 12h1.5M19.5 12H21M5.64 5.64l1.06 1.06M17.3 17.3l1.06 1.06M5.64 18.36l1.06-1.06M17.3 6.7l1.06-1.06"/>
                        <circle cx="12" cy="12" r="4"/>
                    </svg>
                    {{ __('ui.appearance') }}
                </h3>
                <p class="settings-section-copy">{{ __('ui.appearance_hint') }}</p>

                <div class="settings-options">
                    <label class="settings-option">
                        <input type="radio" name="theme" value="light" {{ $currentTheme === 'light' ? 'checked' : '' }} required>
                        <span class="theme-preview light" aria-hidden="true">
                            <span class="theme-preview-sidebar"></span>
                            <span class="theme-preview-content">
                                <span class="theme-preview-bar"></span>
                                <span class="theme-preview-card"></span>
                            </span>
                        </span>
                        <span class="settings-option-line">
                            <span class="settings-option-check" aria-hidden="true"></span>
                            <span>
                                <strong>{{ __('ui.light_mode') }}</strong>
                                <small>{{ __('ui.light_mode_hint') }}</small>
                            </span>
                        </span>
                    </label>

                    <label class="settings-option">
                        <input type="radio" name="theme" value="dark" {{ $currentTheme === 'dark' ? 'checked' : '' }} required>
                        <span class="theme-preview dark" aria-hidden="true">
                            <span class="theme-preview-sidebar"></span>
                            <span class="theme-preview-content">
                                <span class="theme-preview-bar"></span>
                                <span class="theme-preview-card"></span>
                            </span>
                        </span>
                        <span class="settings-option-line">
                            <span class="settings-option-check" aria-hidden="true"></span>
                            <span>
                                <strong>{{ __('ui.dark_mode') }}</strong>
                                <small>{{ __('ui.dark_mode_hint') }}</small>
                            </span>
                        </span>
                    </label>
                </div>
            </section>

            <div class="settings-actions">
                <a class="btn" href="{{ route($backRoute) }}">{{ __('ui.back_dashboard') }}</a>
                <button class="btn btn-primary" type="submit">{{ __('ui.save_changes') }}</button>
            </div>
        </div>
    </form>

    @if($roleMode['available'])
        <section class="settings-panel" aria-labelledby="accessModeTitle">
            <section class="settings-section">
                <h3 class="settings-section-title" id="accessModeTitle">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06-2.12 2.12-.06-.06a1.7 1.7 0 0 0-1.88-.34 1.7 1.7 0 0 0-1.03 1.55V20.3h-3v-.09a1.7 1.7 0 0 0-1.03-1.55 1.7 1.7 0 0 0-1.88.34l-.06.06-2.12-2.12.06-.06A1.7 1.7 0 0 0 7 15a1.7 1.7 0 0 0-1.55-1.03H5.3v-3h.15A1.7 1.7 0 0 0 7 9.94a1.7 1.7 0 0 0-.34-1.88L6.6 8l2.12-2.12.06.06a1.7 1.7 0 0 0 1.88.34A1.7 1.7 0 0 0 11.7 4.7v-.1h3v.1a1.7 1.7 0 0 0 1.03 1.56 1.7 1.7 0 0 0 1.88-.34l.06-.06L19.8 8l-.06.06a1.7 1.7 0 0 0-.34 1.88 1.7 1.7 0 0 0 1.55 1.03h.15v3h-.15A1.7 1.7 0 0 0 19.4 15Z"/>
                    </svg>
                    {{ __('ui.access_mode') }}
                </h3>
                <p class="settings-section-copy">{{ __('ui.access_mode_hint') }}</p>

                <div class="settings-actions" style="justify-content:flex-start;">
                    @if($roleMode['is_student_mode'])
                        <form method="POST" action="{{ route('settings.role-mode.update') }}">
                            @csrf
                            <input type="hidden" name="mode" value="student">
                            <input type="hidden" name="override" value="0">
                            <button class="btn" type="submit">{{ __('ui.student_mode') }}</button>
                        </form>
                        <form method="POST" action="{{ route('settings.role-mode.update') }}">
                            @csrf
                            <input type="hidden" name="mode" value="student">
                            <input type="hidden" name="override" value="1">
                            <button class="btn {{ $roleMode['override_enabled'] ? '' : 'btn-primary' }}" type="submit">{{ __('ui.enable_override') }}</button>
                        </form>
                        <form method="POST" action="{{ route('settings.role-mode.update') }}">
                            @csrf
                            <input type="hidden" name="mode" value="admin">
                            <button class="btn" type="submit">{{ __('ui.admin_mode') }}</button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('settings.role-mode.update') }}">
                            @csrf
                            <input type="hidden" name="mode" value="student">
                            <input type="hidden" name="override" value="0">
                            <button class="btn" type="submit">{{ __('ui.student_mode') }}</button>
                        </form>
                        <form method="POST" action="{{ route('settings.role-mode.update') }}">
                            @csrf
                            <input type="hidden" name="mode" value="student">
                            <input type="hidden" name="override" value="1">
                            <button class="btn btn-primary" type="submit">{{ __('ui.enable_override') }}</button>
                        </form>
                    @endif
                </div>
            </section>
        </section>
    @endif
</div>
@endsection
