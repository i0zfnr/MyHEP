@extends('layouts.app')

@section('title', __('ui.settings_title'))

@push('styles')
<style>
    .session-list { display:grid; gap:12px; margin-top:18px; }
    .session-item { display:flex; align-items:center; justify-content:space-between; gap:16px; padding:16px; border:1px solid var(--border); border-radius:16px; background:var(--surface-soft); }
    .session-main { min-width:0; }
    .session-title { display:flex; align-items:center; flex-wrap:wrap; gap:8px; margin-bottom:5px; }
    .session-title strong { color:var(--text); }
    .session-current { padding:3px 8px; border-radius:999px; background:rgba(22,163,74,.12); color:#15803d; font-size:.72rem; font-weight:800; }
    .session-meta { color:var(--text-muted); font-size:.82rem; line-height:1.55; overflow-wrap:anywhere; }
    .settings-autosave { color:var(--text-muted); font-size:.78rem; font-weight:700; }
    .settings-autosave[data-state="saving"] { color:var(--primary-dark); }
    .settings-autosave[data-state="saved"] { color:#15803d; }
    .settings-autosave[data-state="error"] { color:var(--danger); }
    body[data-theme="dark"] .session-current { background:rgba(119,215,166,.14); color:#9be6bd; }
    @media (max-width:640px) { .session-item { align-items:flex-start; flex-direction:column; } }
</style>
@endpush

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

            @if($canAdjustAccentTheme)
                <section class="settings-section settings-section--beta" role="group" aria-labelledby="settingsAccentTitle">
                    <div class="settings-beta-label">{{ __('Beta') }}</div>
                    <h3 class="settings-section-title" id="settingsAccentTitle">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="8"/><path stroke-linecap="round" d="M12 4v16M4 12h16"/></svg>
                        {{ __('Accent color') }}
                    </h3>
                    <p class="settings-section-copy">{{ __('Choose a curated color mood for MyHEP. Status and safety colors stay unchanged.') }}</p>
                    <div class="settings-options settings-options--accent">
                        @foreach(['gold' => ['MyHEP Gold', 'accent-preview--gold'], 'candy_blue' => ['Candy Blue', 'accent-preview--candy-blue'], 'lavender' => ['Lavender', 'accent-preview--lavender'], 'orchid' => ['Orchid', 'accent-preview--orchid'], 'violet' => ['Violet', 'accent-preview--violet']] as $value => [$label, $previewClass])
                            <label class="settings-option settings-option--accent">
                                <input type="radio" name="accent_theme" value="{{ $value }}" @checked($currentAccentTheme === $value)>
                                <span class="accent-preview {{ $previewClass }}" aria-hidden="true"><span></span><span></span><span></span></span>
                                <span class="settings-option-line"><span class="settings-option-check" aria-hidden="true"></span><span><strong>{{ $label }}</strong></span></span>
                            </label>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($canAdjustGlass)
                <section class="settings-section glass-control-section" role="group" aria-labelledby="settingsGlassTitle">
                    <h3 class="settings-section-title" id="settingsGlassTitle">
                        <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 3h10l4 7-9 11L3 10l4-7Z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="m3 10 9 3 9-3M12 13v8"/>
                        </svg>
                        {{ __('ui.glass_transparency') }}
                    </h3>
                    <p class="settings-section-copy">{{ __('ui.glass_transparency_hint') }}</p>

                    <div class="glass-control" data-glass-control>
                        <div class="glass-control-preview" aria-hidden="true">
                            <span>{{ __('ui.glass_preview') }}</span>
                            <strong data-glass-output>{{ $currentGlassTransparency }}%</strong>
                        </div>
                        <div class="glass-slider" style="--glass-range-progress: {{ (($currentGlassTransparency - 10) / 70) * 100 }}%;">
                            <span class="glass-slider-icon" title="{{ __('ui.glass_more_solid') }}" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3.5" y="5" width="14" height="11" rx="3"/><rect x="7" y="8" width="13.5" height="10.5" rx="3"/></svg>
                            </span>
                            <input
                                id="glassTransparency"
                                class="glass-range"
                                type="range"
                                name="glass_transparency"
                                min="10"
                                max="80"
                                step="1"
                                value="{{ $currentGlassTransparency }}"
                                aria-label="{{ __('ui.glass_transparency') }}"
                                aria-describedby="settingsGlassTitle"
                            >
                            <span class="glass-slider-icon glass-slider-icon--clear" title="{{ __('ui.glass_more_clear') }}" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75"><rect x="3.5" y="5" width="14" height="11" rx="3"/><rect x="7" y="8" width="13.5" height="10.5" rx="3" fill="currentColor" fill-opacity=".17"/></svg>
                            </span>
                        </div>
                        <div class="glass-range-label" aria-hidden="true">
                            <span>{{ __('ui.glass_more_solid') }}</span>
                            <span>{{ __('ui.glass_more_clear') }}</span>
                        </div>
                    </div>
                </section>
            @endif

            <div class="settings-actions">
                <a class="btn" href="{{ route($backRoute) }}">{{ __('ui.back_dashboard') }}</a>
                <span class="settings-autosave" data-settings-autosave aria-live="polite">{{ __('Changes save automatically') }}</span>
            </div>
        </div>
    </form>

    <section class="settings-panel" aria-labelledby="activeSessionsTitle">
        <section class="settings-section">
            <h3 class="settings-section-title" id="activeSessionsTitle">
                <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="4" width="18" height="13" rx="2"/>
                    <path stroke-linecap="round" d="M8 21h8M12 17v4"/>
                </svg>
                {{ __('ui.active_sessions') }}
            </h3>
            <p class="settings-section-copy">{{ __('ui.active_sessions_hint') }}</p>

            <div class="session-list">
                @foreach($activeSessions as $accountSession)
                    <article class="session-item">
                        <div class="session-main">
                            <div class="session-title">
                                <strong>{{ $accountSession->device_label }}</strong>
                                @if($accountSession->is_current)
                                    <span class="session-current">{{ __('ui.current_device') }}</span>
                                @endif
                            </div>
                            <div class="session-meta">
                                {{ $accountSession->ip_address ?: '-' }}<br>
                                {{ __('ui.last_active', ['time' => \Illuminate\Support\Carbon::parse($accountSession->last_seen_at)->diffForHumans()]) }} ·
                                {{ __('ui.signed_in_at', ['time' => \Illuminate\Support\Carbon::parse($accountSession->authenticated_at)->diffForHumans()]) }}
                            </div>
                        </div>
                        @unless($accountSession->is_current)
                            <form method="POST" action="{{ route('settings.sessions.destroy', $accountSession->public_id) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger" type="submit">{{ __('ui.revoke_session') }}</button>
                            </form>
                        @endunless
                    </article>
                @endforeach
            </div>

            @if($activeSessions->where('is_current', false)->isNotEmpty())
                <form method="POST" action="{{ route('settings.sessions.destroy-others') }}" class="settings-actions settings-actions--inline">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-danger" type="submit">{{ __('ui.revoke_other_sessions') }}</button>
                </form>
            @endif
        </section>
    </section>

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

                <div class="settings-actions settings-actions--inline">
                    @if($roleMode['student_available'])
                        <form method="POST" action="{{ route('settings.role-mode.update') }}">
                            @csrf
                            <input type="hidden" name="mode" value="student">
                            <input type="hidden" name="override" value="0">
                            <button class="btn {{ $roleMode['is_student_mode'] && !$roleMode['override_enabled'] ? 'btn-primary' : '' }}" type="submit">{{ __('ui.student_mode') }}</button>
                        </form>
                        <form method="POST" action="{{ route('settings.role-mode.update') }}">
                            @csrf
                            <input type="hidden" name="mode" value="student">
                            <input type="hidden" name="override" value="1">
                            <button class="btn {{ $roleMode['is_student_mode'] && $roleMode['override_enabled'] ? 'btn-primary' : '' }}" type="submit">{{ __('ui.enable_override') }}</button>
                        </form>
                    @endif

                    @if($roleMode['general_staff_available'])
                        <form method="POST" action="{{ route('settings.role-mode.update') }}">
                            @csrf
                            <input type="hidden" name="mode" value="general_staff">
                            <button class="btn {{ $roleMode['is_general_staff_mode'] ? 'btn-primary' : '' }}" type="submit">{{ __('PBT Staff') }}</button>
                        </form>
                    @endif

                    @if($roleMode['is_student_mode'] || $roleMode['is_general_staff_mode'])
                        <form method="POST" action="{{ route('settings.role-mode.update') }}">
                            @csrf
                            <input type="hidden" name="mode" value="admin">
                            <button class="btn" type="submit">{{ __('ui.admin_mode') }}</button>
                        </form>
                    @endif
                </div>
            </section>
        </section>
    @endif
</div>
@endsection
