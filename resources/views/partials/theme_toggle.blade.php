@php($themeToggleClass = $themeToggleClass ?? '')
<button
    type="button"
    class="se-theme-toggle {{ $themeToggleClass }}"
    data-theme-toggle
    data-light-label="{{ __('ui.light_mode') }}"
    data-dark-label="{{ __('ui.dark_mode') }}"
    data-switch-light="{{ __('ui.switch_light') }}"
    data-switch-dark="{{ __('ui.switch_dark') }}"
    aria-label="{{ __('ui.switch_dark') }}"
    aria-pressed="false"
>
    <svg class="se-theme-icon-moon" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M20.4 15.6A8.5 8.5 0 0 1 8.4 3.6 8.5 8.5 0 1 0 20.4 15.6Z"/>
    </svg>
    <svg class="se-theme-icon-sun" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
        <circle cx="12" cy="12" r="4"/>
        <path stroke-linecap="round" d="M12 2v2M12 20v2M4.93 4.93l1.42 1.42M17.65 17.65l1.42 1.42M2 12h2M20 12h2M4.93 19.07l1.42-1.42M17.65 6.35l1.42-1.42"/>
    </svg>
    <span class="se-theme-toggle-label" data-theme-label>{{ __('ui.dark_mode') }}</span>
</button>
