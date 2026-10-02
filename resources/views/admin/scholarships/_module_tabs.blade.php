@php
    $submittedFormsTab = request()->routeIs('admin.scholarships.index');
@endphp

<section class="scholarship-module-nav" aria-label="{{ __('Scholarship and welfare sections') }}">
    <nav class="scholarship-module-tabs" aria-label="{{ __('Scholarship and welfare sections') }}">
        <a href="{{ route('admin.student-scholarship-status.index') }}"
            class="scholarship-module-tab {{ $submittedFormsTab ? '' : 'is-active' }}"
            @if(!$submittedFormsTab) aria-current="page" @endif>
            {{ __('Student Overview') }}
        </a>
        <a href="{{ route('admin.scholarships.index') }}"
            class="scholarship-module-tab {{ $submittedFormsTab ? 'is-active' : '' }}"
            @if($submittedFormsTab) aria-current="page" @endif>
            {{ __('Submitted Forms') }}
        </a>
    </nav>

    <p class="scholarship-module-description">
        @if($submittedFormsTab)
            {{ __('Manage scholarship award records, including provider, amount and status. The count below shows students with submitted forms; use Student Overview to review full scholarship and welfare details.') }}
        @else
            {{ __('View every student and their scholarship or welfare status, including form details, supporting documents and students who have not submitted.') }}
        @endif
    </p>
</section>

<style>
    .scholarship-module-nav {
        margin: 0 0 18px;
        padding: 14px 16px;
        border: 1px solid var(--se-border, #e8d7bc);
        border-radius: 14px;
        background: var(--se-surface, #fff);
    }
    .scholarship-module-tabs {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .scholarship-module-tab {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 40px;
        padding: 8px 15px;
        border: 1px solid var(--se-border, #e8d7bc);
        border-radius: 9px;
        color: var(--se-text, #392b20);
        background: var(--se-surface, #fff);
        font-size: 0.88rem;
        font-weight: 650;
        text-decoration: none;
        transition: background-color .16s ease, border-color .16s ease, color .16s ease;
    }
    .scholarship-module-tab:hover {
        border-color: var(--se-primary, #a77b35);
    }
    .scholarship-module-tab.is-active {
        border-color: var(--se-primary, #a77b35);
        color: #fff;
        background: var(--se-primary, #a77b35);
    }
    .scholarship-module-tab:focus-visible {
        outline: 3px solid color-mix(in srgb, var(--se-primary, #a77b35) 35%, transparent);
        outline-offset: 2px;
    }
    .scholarship-module-description {
        margin: 10px 2px 0;
        color: var(--se-text-muted, #766656);
        font-size: 0.84rem;
        line-height: 1.55;
    }
</style>
