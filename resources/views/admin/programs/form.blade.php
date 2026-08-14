@extends('layouts.app')
@section('title', $program ? __('Edit Program') : __('Register Approved Program'))
@section('header')<h2 style="margin:0;font-size:1.15rem;font-weight:800;">{{ $program ? __('Edit Program') : __('Register Approved Program') }}</h2>@endsection

@push('styles')
@php
    $canUseAccent = (session('auth_user.role') === 'student' || session('auth_user.admin_role') === 'system_admin');
@endphp
<style>
.pmr {
    --pm-accent: {{ $canUseAccent ? 'var(--se-primary, #C8A96A)' : '#C8A96A' }};
    display: grid;
    gap: 1.25rem;
    color: var(--text, #241d16);
    max-width: 1100px;
    margin: 0 auto;
    padding: 1.5rem 1rem;
    font-family: inherit;
}
.pmr-hero, .pmr-card {
    background: var(--surface, #fff);
    border: 1px solid color-mix(in srgb, var(--pm-accent) 22%, var(--border, #eadac8));
    border-radius: 18px;
    box-shadow: var(--glass-shadow, 0 14px 36px rgba(0,0,0,0.06));
    backdrop-filter: blur(var(--glass-blur, 16px));
}
.pmr-hero {
    padding: 1.75rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
    background: linear-gradient(135deg, var(--surface, #fff), color-mix(in srgb, var(--pm-accent) 10%, var(--surface, #fff)));
}
.pmr-eyebrow {
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .12em;
    color: var(--pm-accent);
}
.pmr h1 {
    font-size: 2rem;
    margin: .35rem 0;
    font-weight: 800;
    color: var(--text, #241d16);
}
.pmr p {
    color: var(--text-muted, #746b62);
    margin: .25rem 0;
    font-size: 0.92rem;
}
.pmr-card { padding: 1.5rem 1.75rem; }
.pmr-card h2 { margin: 0 0 .35rem 0; font-size: 1.35rem; font-weight: 800; color: var(--text, #241d16); }

.pmr-btn {
    min-height: 44px;
    border: 1px solid var(--border, #eadac8);
    border-radius: 12px;
    padding: .7rem 1.1rem;
    background: var(--surface, #fff);
    color: var(--text, #241d16);
    font-weight: 800;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 0.88rem;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.pmr-btn:hover { background: color-mix(in srgb, var(--pm-accent) 8%, var(--surface, #fff)); }
.pmr-btn.primary {
    background: var(--pm-accent);
    color: #fff;
    border-color: var(--pm-accent);
    box-shadow: 0 4px 14px color-mix(in srgb, var(--pm-accent) 30%, transparent);
}
.pmr-btn.primary:hover {
    background: color-mix(in srgb, var(--pm-accent) 85%, #000);
    border-color: color-mix(in srgb, var(--pm-accent) 85%, #000);
    color: #fff;
}

.pmr-methods {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.85rem;
    margin-top: 1rem;
}
.pmr-method {
    border: 1px solid color-mix(in srgb, var(--pm-accent) 22%, var(--border, #eadac8));
    border-radius: 14px;
    padding: 1.1rem;
    cursor: pointer;
    background: var(--surface, #fff);
    color: var(--text, #241d16);
    transition: all 0.15s ease;
}
.pmr-method:hover { border-color: var(--pm-accent); }
.pmr-method:has(input:checked) {
    border-color: var(--pm-accent);
    background: color-mix(in srgb, var(--pm-accent) 9%, var(--surface, #fff));
    box-shadow: 0 0 0 2px color-mix(in srgb, var(--pm-accent) 25%, transparent);
}
.pmr-method strong { display: block; margin: 8px 0 4px; font-weight: 800; font-size: 0.95rem; color: var(--text, #241d16); }

.pmr-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.1rem;
    margin-top: 1rem;
}
.pmr-field { display: flex; flex-direction: column; gap: 6px; }
.pmr-field.full { grid-column: 1 / -1; }
.pmr-field label { font-weight: 800; font-size: 0.85rem; color: var(--text, #241d16); }
.pmr-field label span { color: var(--se-danger, #b42318); }

.pmr-field input, .pmr-field textarea, .pmr-field select {
    min-height: 44px;
    border: 1px solid var(--border, #eadac8);
    border-radius: 12px;
    padding: .7rem .9rem;
    background: var(--surface, #fff);
    color: var(--text, #241d16);
    font-size: 0.9rem;
    font-family: inherit;
    width: 100%;
}
.pmr-field textarea { min-height: 110px; resize: vertical; line-height: 1.45; }
.pmr-field input:focus, .pmr-field textarea:focus, .pmr-field select:focus {
    outline: none;
    border-color: var(--pm-accent);
}
.pmr-optional-location {
    grid-column: 1 / -1;
    overflow: hidden;
    border: 1px solid color-mix(in srgb, var(--pm-accent) 18%, var(--border, #eadac8));
    border-radius: 14px;
    background: color-mix(in srgb, var(--surface, #fff) 94%, var(--pm-accent-soft));
}
.pmr-optional-location summary {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    min-height: 48px;
    padding: .72rem .9rem;
    color: var(--text, #241d16);
    font-size: .85rem;
    font-weight: 800;
    cursor: pointer;
    list-style: none;
}
.pmr-optional-location summary::-webkit-details-marker { display: none; }
.pmr-optional-location summary::after { content: '+'; color: var(--pm-accent); font-size: 1.15rem; }
.pmr-optional-location[open] summary::after { content: '\2212'; }
.pmr-optional-location-fields { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; padding: 0 .9rem .9rem; }
.pmr-optional-location-note { grid-column: 1 / -1; margin: 0; color: var(--text-secondary, #746b62); font-size: .78rem; line-height: 1.5; }

.pmr-actions-row {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 0.5rem;
}

@media (max-width: 760px) {
    .pmr-hero { flex-direction: column; align-items: flex-start; }
    .pmr-methods, .pmr-grid { grid-template-columns: 1fr; }
    .pmr-field.full { grid-column: auto; }
    .pmr-optional-location { grid-column: auto; }
    .pmr-optional-location-fields { grid-template-columns: 1fr; }
    .pmr-optional-location-note { grid-column: auto; }
}
</style>
@endpush
@push('styles')
@include('admin.programs.partials.design-system')
@endpush

@section('content')
@php($method = old('paperwork_method', $program->paperwork_method ?? 'pdf'))
@php($registrationType = old('registration_type', $program->registration_type ?? 'approved_program'))

<main class="pmr">
    <header class="pmr-hero">
        <div>
            <span class="pmr-eyebrow">{{ __('PROGRAM REGISTRATION') }}</span>
            <h1>{{ $program ? __('Edit Program Record') : __('Create Program or Activity') }}</h1>
            <p>{{ __('Register an approved formal program or create a simple attendance-only activity without paperwork.') }}</p>
        </div>
        <a class="pmr-btn" href="{{ route('admin.programs.index') }}">{{ __('Back') }}</a>
    </header>

    @if($errors->any())
        <div class="alert alert-danger" style="border-radius: 12px; margin-bottom: 0;">
            <strong>{{ __('Please correct the highlighted information.') }}</strong>
            <ul style="margin: 6px 0 0 0; padding-left: 20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" enctype="multipart/form-data" action="{{ $program ? route('admin.programs.update', $program->id) : route('admin.programs.store') }}">
        @csrf
        @if($program) @method('put') @endif

        <section class="pmr-card">
            <h2>{{ __('Program type') }}</h2>
            <div class="pmr-methods">
                <label class="pmr-method"><input type="radio" name="registration_type" value="approved_program" required @checked($registrationType === 'approved_program')><strong>{{ __('Approved Formal Program') }}</strong><span>{{ __('Requires approved PDF or DOCX paperwork. Questionnaire can be enabled or disabled.') }}</span></label>
                <label class="pmr-method"><input type="radio" name="registration_type" value="attendance_only_activity" required @checked($registrationType === 'attendance_only_activity')><strong>{{ __('Simple Attendance Activity') }}</strong><span>{{ __('No approved paperwork required. Uses attendance-only mode for points collection.') }}</span></label>
            </div>
        </section>

        <section class="pmr-card" id="approvedPaperworkSection">
            <h2>{{ __('Approved paperwork') }}</h2>
            <p>{{ __('The system keeps this approved paperwork as the official reference for program operations and reporting.') }}</p>

            <div class="pmr-methods">
                <label class="pmr-method">
                    <input type="radio" name="paperwork_method" value="pdf" @checked($method === 'pdf')>
                    <strong>{{ __('Upload PDF') }} · {{ __('Recommended') }}</strong>
                    <span style="font-size: 0.8rem; color: var(--text-secondary, #746b62);">{{ __('Upload the externally approved PDF paperwork.') }}</span>
                </label>

                <label class="pmr-method">
                    <input type="radio" name="paperwork_method" value="docx" @checked($method === 'docx')>
                    <strong>{{ __('Upload DOCX') }}</strong>
                    <span style="font-size: 0.8rem; color: var(--text-secondary, #746b62);">{{ __('For an editable proposal document.') }}</span>
                </label>
            </div>

            <div class="pmr-field" id="paperworkUpload" style="margin-top: 1.2rem;">
                <label for="paperwork_file">{{ __('Paperwork file') }} @if(!$program)<span>*</span>@endif</label>
                <input id="paperwork_file" type="file" name="paperwork_file" accept=".pdf" data-new-program="{{ $program ? '0' : '1' }}">
                <span style="font-size: 0.8rem; color: var(--text-secondary, #746b62);">{{ $program ? __('Upload a file only when replacing the approved paperwork. Maximum 20 MB.') : __('Approved PDF or DOCX, maximum 20 MB.') }}</span>
            </div>
        </section>

        <section class="pmr-card">
            <h2>{{ __('Basic program information') }}</h2>
            <p>{{ __('Add the searchable details used to identify the approved program and prepare its final report.') }}</p>

            <div class="pmr-grid">
                <div class="pmr-field full">
                    <label for="title">{{ __('Program name') }} <span>*</span></label>
                    <input id="title" name="title" required maxlength="180" value="{{ old('title', $program->title ?? '') }}">
                </div>

                <div class="pmr-field full">
                    <label for="reference_no">{{ __('Reference number') }} <span id="referenceRequired">*</span></label>
                    <input id="reference_no" name="reference_no" maxlength="80" value="{{ old('reference_no', $program->reference_no ?? '') }}">
                </div>

                <div class="pmr-field full">
                    <label for="description">{{ __('Background / description') }}</label>
                    <textarea id="description" name="description">{{ old('description', $program->description ?? '') }}</textarea>
                </div>

                <div class="pmr-field full">
                    <label for="objectives">{{ __('Objectives') }}</label>
                    <textarea id="objectives" name="objectives">{{ old('objectives', $program->objectives ?? '') }}</textarea>
                </div>

            </div>
        </section>

        <section class="pmr-card">
            <h2>{{ __('Schedule and participants') }}</h2>
            <p>{{ __('These details control when the program runs and provide the attendance target used in reporting.') }}</p>
            <div class="pmr-grid">
                <div class="pmr-field">
                    <label for="starts_at">{{ __('Start date and time') }} <span>*</span></label>
                    <input id="starts_at" type="datetime-local" name="starts_at" required value="{{ old('starts_at', isset($program->starts_at) ? \Illuminate\Support\Carbon::parse($program->starts_at)->format('Y-m-d\TH:i') : '') }}">
                </div>
                <div class="pmr-field">
                    <label for="ends_at">{{ __('End date and time') }} <span>*</span></label>
                    <input id="ends_at" type="datetime-local" name="ends_at" required value="{{ old('ends_at', isset($program->ends_at) ? \Illuminate\Support\Carbon::parse($program->ends_at)->format('Y-m-d\TH:i') : '') }}">
                </div>
                <div class="pmr-field">
                    <label for="target_participants">{{ __('Target participants') }} <span>*</span></label>
                    <input id="target_participants" name="target_participants" required maxlength="255" value="{{ old('target_participants', $program->target_participants ?? '') }}">
                </div>
                <div class="pmr-field">
                    <label for="estimated_participants">{{ __('Estimated participants') }}</label>
                    <input id="estimated_participants" type="number" min="1" name="estimated_participants" value="{{ old('estimated_participants', $program->estimated_participants ?? '') }}">
                </div>
                <div class="pmr-field">
                    <label for="participation_points">{{ __('Participation points') }} <span>*</span></label>
                    <input id="participation_points" type="number" min="0" max="100" name="participation_points" required value="{{ old('participation_points', $program->participation_points ?? 0) }}">
                    <small>{{ __('Points awarded to Politeknik Besut students with valid attendance.') }}</small>
                </div>
                <div class="pmr-field">
                    <label for="certificate_enabled">{{ __('Certificate availability') }} <span>*</span></label>
                    <select id="certificate_enabled" name="certificate_enabled" required>
                        <option value="1" @selected((string) old('certificate_enabled', $program->certificate_enabled ?? 1) === '1')>{{ __('Points and certificate') }}</option>
                        <option value="0" @selected((string) old('certificate_enabled', $program->certificate_enabled ?? 1) === '0')>{{ __('Points only — no certificate') }}</option>
                    </select>
                    <small>{{ __('Students still receive participation points when certificates are disabled.') }}</small>
                </div>
                <input type="hidden" name="certificate_template" value="{{ old('certificate_template', $program->certificate_template ?? 'standard_placeholder') }}">
            </div>
        </section>

        <section class="pmr-card">
            <h2>{{ __('Attendance location settings') }}</h2>
            <p>{{ __('Set the venue and attendance distance in metres. GPS coordinates are an optional secondary setting.') }}</p>
            <div class="pmr-grid">
                <div class="pmr-field full">
                    <label for="venue">{{ __('Venue') }} <span>*</span></label>
                    <input id="venue" name="venue" required maxlength="180" value="{{ old('venue', $program->venue ?? '') }}">
                </div>
                <div class="pmr-field full">
                    <label for="geofence_radius_m">{{ __('Geofence radius (metres)') }} <span>*</span></label>
                    <input id="geofence_radius_m" type="number" min="20" max="1000" name="geofence_radius_m" required value="{{ old('geofence_radius_m', $program->geofence_radius_m ?? 50) }}">
                </div>
                <details class="pmr-optional-location" @if(old('latitude', $program->latitude ?? null) !== null || old('longitude', $program->longitude ?? null) !== null || $errors->has('latitude') || $errors->has('longitude')) open @endif>
                    <summary>{{ __('Optional GPS coordinates') }}</summary>
                    <div class="pmr-optional-location-fields">
                        <div class="pmr-field">
                            <label for="latitude">{{ __('Venue latitude') }}</label>
                            <input id="latitude" type="number" step="0.0000001" name="latitude" value="{{ old('latitude', $program->latitude ?? '') }}">
                        </div>
                        <div class="pmr-field">
                            <label for="longitude">{{ __('Venue longitude') }}</label>
                            <input id="longitude" type="number" step="0.0000001" name="longitude" value="{{ old('longitude', $program->longitude ?? '') }}">
                        </div>
                        <p class="pmr-optional-location-note">{{ __('Leave both fields empty when registering the program. Coordinates can be added later before GPS attendance is opened.') }}</p>
                    </div>
                </details>
            </div>
        </section>

        <div class="pmr-actions-row">
            <a class="pmr-btn" href="{{ route('admin.programs.index') }}">{{ __('Cancel') }}</a>
            <button class="pmr-btn primary" type="submit">{{ $program ? __('Save Changes') : __('Register Program & Open Operations') }}</button>
        </div>
    </form>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const box = document.getElementById('paperworkUpload');
    const input = document.getElementById('paperwork_file');
    const paperworkSection = document.getElementById('approvedPaperworkSection');
    const reference = document.getElementById('reference_no');
    const referenceRequired = document.getElementById('referenceRequired');

    const sync = () => {
        const method = document.querySelector('[name="paperwork_method"]:checked')?.value || 'pdf';
        if (box) box.hidden = false;
        if (input) input.accept = method === 'docx' ? '.docx' : '.pdf';
        const formal = document.querySelector('[name="registration_type"]:checked')?.value === 'approved_program';
        if (paperworkSection) paperworkSection.hidden = !formal;
        if (input) input.required = formal && input.dataset.newProgram === '1';
        if (reference) reference.required = formal;
        if (referenceRequired) referenceRequired.hidden = !formal;
    };

    document.querySelectorAll('[name="paperwork_method"]').forEach(el => el.addEventListener('change', sync));
    document.querySelectorAll('[name="registration_type"]').forEach(el => el.addEventListener('change', sync));
    sync();
});
</script>
@endsection
