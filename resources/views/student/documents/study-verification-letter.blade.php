@extends('layouts.app')

@section('title', __('Generate Study Verification Letter'))

@section('header')
    <h2>{{ __('Study Verification Letter') }}</h2>
@endsection

@push('styles')
    @vite('resources/css/student-verification-letter.css')
@endpush

@section('content')
<div class="svl-shell">
    <nav class="svl-breadcrumb" aria-label="{{ __('Breadcrumb') }}">
        <a href="{{ route('student.documents.index') }}">{{ __('Document Centre') }}</a>
        <span aria-hidden="true">/</span>
        <span>{{ __('Study Verification Letter') }}</span>
    </nav>

    <section class="svl-hero">
        <div>
            <span class="svl-eyebrow">{{ __('Self-service official document') }}</span>
            <h1>{{ __('Generate your study verification letter') }}</h1>
            <p>{{ __('MyHEP has filled in your verified student record. Complete the study period and sponsorship information, check every detail, then download the letter as DOCX or PDF.') }}</p>
        </div>
        <div class="svl-hero-mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 2h9l4 4v16H6z"/><path d="M14 2v5h5M9 12h6M9 16h6"/></svg>
        </div>
    </section>

    @if(isset($errors) && $errors->any())
        <div class="se-feedback se-feedback--error" role="alert">
            <strong>{{ __('Please check the information below.') }}</strong>
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('student.documents.study-verification-letter.generate') }}" class="svl-form" target="_blank">
        @csrf

        <section class="ui-card svl-section">
            <div class="svl-section-head">
                <div><span>1</span><div><h2>{{ __('Verified student information') }}</h2><p>{{ __('These fields come directly from your MyHEP student record and cannot be changed in this letter form.') }}</p></div></div>
                <span class="svl-locked">{{ __('Locked') }}</span>
            </div>
            <div class="svl-identity-grid">
                <div><span>{{ __('Student Name') }}</span><strong>{{ $identity['student_name'] ?: '-' }}</strong></div>
                <div><span>{{ __('IC Number') }}</span><strong>{{ $identity['ic_no'] ?: '-' }}</strong></div>
                <div><span>{{ __('Registration Number') }}</span><strong>{{ $identity['matric_no'] ?: '-' }}</strong></div>
                <div><span>{{ __('Programme') }}</span><strong>{{ $identity['program_name'] ?: '-' }}</strong></div>
            </div>
            @if(empty($identity['student_name']) || empty($identity['ic_no']) || empty($identity['matric_no']) || empty($identity['program_name']))
                <p class="svl-record-warning">{{ __('Your student record is incomplete. Please contact HEP to correct the locked information before using this letter.') }}</p>
            @endif
        </section>

        <section class="ui-card svl-section">
            <div class="svl-section-head">
                <div><span>2</span><div><h2>{{ __('Complete the study details') }}</h2><p>{{ __('Enter the information exactly as it should appear in the official letter.') }}</p></div></div>
                <span class="svl-required">{{ __('Required') }}</span>
            </div>
            <div class="svl-fields">
                <div class="svl-field">
                    <label for="study_start_session">{{ __('Study Start Session') }}</label>
                    <input id="study_start_session" name="study_start_session" value="{{ old('study_start_session', $defaults['study_start_session']) }}" maxlength="40" required placeholder="{{ __('Example: SESSION I: 2025/2026') }}">
                </div>
                <div class="svl-field">
                    <label for="study_end_session">{{ __('Study End Session') }}</label>
                    <input id="study_end_session" name="study_end_session" value="{{ old('study_end_session', $defaults['study_end_session']) }}" maxlength="40" required placeholder="{{ __('Example: SESSION II: 2027/2028') }}">
                </div>
                <div class="svl-field">
                    <label for="study_duration">{{ __('Study Duration') }}</label>
                    <input id="study_duration" name="study_duration" value="{{ old('study_duration', $defaults['study_duration']) }}" maxlength="50" required placeholder="{{ __('Example: 3 YEARS') }}">
                </div>
                <div class="svl-field">
                    <label for="current_study_year">{{ __('Current Study Year') }}</label>
                    <input id="current_study_year" name="current_study_year" value="{{ old('current_study_year', $defaults['current_study_year']) }}" maxlength="20" required placeholder="{{ __('Example: 2026') }}">
                </div>
                <div class="svl-field">
                    <label for="current_semester">{{ __('Current Semester') }}</label>
                    <input id="current_semester" type="number" name="current_semester" value="{{ old('current_semester', $defaults['current_semester']) }}" min="1" max="12" required inputmode="numeric">
                </div>
                <div class="svl-field svl-field-wide">
                    <label for="sponsorship_details">{{ __('Existing Sponsorship Scholarship Loan or Financing Details') }}</label>
                    <textarea id="sponsorship_details" name="sponsorship_details" maxlength="500" required rows="3" placeholder="{{ __('Enter NONE if you do not receive any sponsorship.') }}">{{ old('sponsorship_details', $defaults['sponsorship_details']) }}</textarea>
                    <small>{{ __('Enter NONE if there is no current sponsorship, scholarship, loan, or financing.') }}</small>
                </div>
            </div>
        </section>

        <section class="ui-card svl-section svl-confirmation">
            <div class="svl-section-head">
                <div><span>3</span><div><h2>{{ __('Confirm and generate') }}</h2><p>{{ __('The downloaded letter will use the information shown and entered above.') }}</p></div></div>
            </div>
            <label class="svl-check">
                <input type="checkbox" name="details_confirmed" value="1" required @checked(old('details_confirmed'))>
                <span>{{ __('I confirm that the study and sponsorship information is complete and correct.') }}</span>
            </label>
            <div class="svl-actions">
                <a class="ui-btn" href="{{ route('student.documents.index') }}">{{ __('Back to Document Centre') }}</a>
                <button class="ui-btn" type="submit" name="format" value="docx" @disabled(empty($identity['student_name']) || empty($identity['ic_no']) || empty($identity['matric_no']) || empty($identity['program_name']))>
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 2h7l3 3v13H5z"/><path d="M12 2v4h4M8 11h4M8 14h4"/></svg>
                    {{ __('Generate DOCX Letter') }}
                </button>
                <button class="ui-btn primary" type="submit" name="format" value="pdf" @disabled(empty($identity['student_name']) || empty($identity['ic_no']) || empty($identity['matric_no']) || empty($identity['program_name']))>
                    <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M5 2h7l3 3v13H5z"/><path d="M12 2v4h4M8 11h4M8 14h4"/></svg>
                    {{ __('Generate PDF Letter') }}
                </button>
            </div>
        </section>
    </form>
</div>
@endsection
