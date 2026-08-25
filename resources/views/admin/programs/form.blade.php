@extends('layouts.app')
@section('title', $program ? __('Edit Program') : __('Register Approved Program'))
@section('header')<h2 style="margin:0;font-size:1.15rem;font-weight:800;">{{ $program ? __('Edit Program') : __('Register Approved Program') }}</h2>@endsection

@push('styles')
@php
    $canUseAccent = (session('auth_user.role') === 'student' || session('auth_user.admin_role') === 'system_admin');
@endphp

@endpush
@push('styles')
@include('admin.programs.partials.design-system')
@endpush

@section('content')
@php
    $method = old('paperwork_method', $program->paperwork_method ?? 'pdf');
    $registrationType = old('registration_type', $program->registration_type ?? 'approved_program');
@endphp

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
                <label class="pmr-method"><input type="radio" name="registration_type" value="approved_program" required @checked($registrationType === 'approved_program')><strong>{{ __('Approved Formal Program') }}</strong><span>{{ __('Standard formal program with questionnaire, attendance, and reporting. Paperwork upload is optional.') }}</span></label>
                <label class="pmr-method"><input type="radio" name="registration_type" value="attendance_only_activity" required @checked($registrationType === 'attendance_only_activity')><strong>{{ __('Simple Attendance Activity') }}</strong><span>{{ __('Uses attendance-only mode for points collection.') }}</span></label>
            </div>
        </section>

        <section class="pmr-card" id="approvedPaperworkSection">
            <h2>{{ __('Approved paperwork (Optional)') }}</h2>
            <p>{{ __('You can attach an approved paperwork file now, or skip this step to open attendance & questionnaire immediately.') }}</p>

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
                <label for="paperwork_file">{{ __('Paperwork file (Optional)') }}</label>
                <input id="paperwork_file" type="file" name="paperwork_file" accept=".pdf" data-new-program="{{ $program ? '0' : '1' }}">
                <span style="font-size: 0.8rem; color: var(--text-secondary, #746b62);">{{ __('Optional approved PDF or DOCX, maximum 20 MB.') }}</span>
            </div>
        </section>

        <!-- Reporting Route: Choice of TP / TPA, TPSA, or TPSP -->
        <section class="pmr-card">
            <span class="pmr-eyebrow" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                {{ __('LALUAN SEMAKAN LAPORAN PROGRAM') }}
            </span>
            <h2>{{ __('Pilihan Timbalan Pengarah Untuk Semakan Laporan') }}</h2>
            <p>{{ __('Tentukan Timbalan Pengarah yang akan menerima draf laporan akhir program ini untuk semakan dan sokongan sebelum dimajukan ke Pengarah Politeknik.') }}</p>

            @php
                $selectedBranch = old('approval_branch', $program->approval_branch ?? ($defaultBranch ?? 'tpsa'));
            @endphp

            <div class="pmr-methods" style="grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));">
                <label class="pmr-method" style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <input type="radio" name="approval_branch" value="tpa" required @checked($selectedBranch === 'tpa')>
                        <span style="font-size: 0.72rem; font-weight: 800; background: rgba(2,132,199,0.12); color: #0284c7; padding: 0.2rem 0.55rem; border-radius: 6px;">TPA</span>
                    </div>
                    <strong style="font-size: 0.92rem; color: var(--text); margin-top: 6px;">{{ __('TIMBALAN PENGARAH (AKADEMIK)') }}</strong>
                    <span style="font-size: 0.84rem; font-weight: 800; color: var(--pm-accent); display: block;">SAIFUDDIN BIN SEMAIL</span>
                    <span style="font-size: 0.78rem; color: var(--text-secondary, #746b62); display: block; margin-top: 4px; line-height: 1.4;">
                        {{ __('Program di bawah Jabatan Akademik (JTMK, JPA, JRKV, JMSK).') }}
                    </span>
                </label>

                <label class="pmr-method" style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <input type="radio" name="approval_branch" value="tpsa" required @checked($selectedBranch === 'tpsa')>
                        <span style="font-size: 0.72rem; font-weight: 800; background: rgba(16,185,129,0.12); color: #059669; padding: 0.2rem 0.55rem; border-radius: 6px;">TPSA</span>
                    </div>
                    <strong style="font-size: 0.92rem; color: var(--text); margin-top: 6px;">{{ __('TIMBALAN PENGARAH (SOKONGAN AKADEMIK)') }}</strong>
                    <span style="font-size: 0.84rem; font-weight: 800; color: var(--pm-accent); display: block;">SITI ZUHRA BINTI ABU BAKAR</span>
                    <span style="font-size: 0.78rem; color: var(--text-secondary, #746b62); display: block; margin-top: 4px; line-height: 1.4;">
                        {{ __('Program pembangunan pelajar, hal ehwal pelajar (JHEP), kepimpinan, sukan, USTM & aset.') }}
                    </span>
                </label>

                <label class="pmr-method" style="display: flex; flex-direction: column; gap: 0.25rem;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <input type="radio" name="approval_branch" value="tpsp" required @checked($selectedBranch === 'tpsp')>
                        <span style="font-size: 0.72rem; font-weight: 800; background: rgba(212,175,55,0.15); color: #b99150; padding: 0.2rem 0.55rem; border-radius: 6px;">TPGS / TPSP</span>
                    </div>
                    <strong style="font-size: 0.92rem; color: var(--text); margin-top: 6px;">{{ __('TIMBALAN PENGARAH (GOVERNAN & STRATEGIK)') }}</strong>
                    <span style="font-size: 0.84rem; font-weight: 800; color: var(--pm-accent); display: block;">TS. ELISNORAZMALIZA BINTI AB HAMID</span>
                    <span style="font-size: 0.78rem; color: var(--text-secondary, #746b62); display: block; margin-top: 4px; line-height: 1.4;">
                        {{ __('Latihan staf, inovasi & penyelidikan, ULPL, UPLI, CISEC & pengurusan kualiti.') }}
                    </span>
                </label>
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
                    <label for="reference_no">{{ __('Reference number (Optional)') }}</label>
                    <input id="reference_no" name="reference_no" maxlength="80" value="{{ old('reference_no', $program->reference_no ?? '') }}" placeholder="{{ __('e.g., PB/01/2025 (Optional)') }}">
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
                    <label for="participation_points">{{ __('Merit points') }} <span>*</span></label>
                    <select id="participation_points" name="participation_points" required>
                        @php $selectedMerit = (int) old('participation_points', $program->participation_points ?? 3); @endphp
                        <option value="1" @selected($selectedMerit === 1)>1 {{ __('Merit') }}</option>
                        <option value="2" @selected($selectedMerit === 2)>2 {{ __('Merit') }}</option>
                        <option value="3" @selected($selectedMerit === 3)>3 {{ __('Merit (Standard / Default)') }}</option>
                        <option value="4" @selected($selectedMerit === 4)>4 {{ __('Merit') }}</option>
                        <option value="5" @selected($selectedMerit === 5)>5 {{ __('Merit (High Impact)') }}</option>
                    </select>
                    <small>{{ __('Choose merit points awarded to Politeknik Besut students with valid attendance (Default: 3 Merit).') }}</small>
                </div>
                <div class="pmr-field">
                    <label for="certificate_enabled">{{ __('Certificate availability') }} <span>*</span></label>
                    <select id="certificate_enabled" name="certificate_enabled" required>
                        <option value="1" @selected((string) old('certificate_enabled', $program->certificate_enabled ?? 1) === '1')>{{ __('Merit and certificate') }}</option>
                        <option value="0" @selected((string) old('certificate_enabled', $program->certificate_enabled ?? 1) === '0')>{{ __('Merit only — no certificate') }}</option>
                    </select>
                    <small>{{ __('Students still receive merit points when certificates are disabled.') }}</small>
                </div>
                <input type="hidden" name="certificate_template" value="{{ old('certificate_template', $program->certificate_template ?? 'standard_placeholder') }}">
            </div>
        </section>

        <section class="pmr-card">
            <h2>{{ __('Attendance location settings') }}</h2>
            <p>{{ __('Set the venue name. Geofence radius and GPS verification are fully optional and will never block or force students to turn on their location.') }}</p>
            <div class="pmr-grid">
                <div class="pmr-field full">
                    <label for="venue">{{ __('Venue') }} <span>*</span></label>
                    <input id="venue" name="venue" required maxlength="180" value="{{ old('venue', $program->venue ?? '') }}" placeholder="{{ __('e.g., Dewan Serbaguna / Bilik Seminar / Padang') }}">
                </div>

                <details class="pmr-optional-location" @if(old('geofence_radius_m', $program->geofence_radius_m ?? null) !== null || old('latitude', $program->latitude ?? null) !== null || old('longitude', $program->longitude ?? null) !== null || $errors->has('latitude') || $errors->has('longitude') || $errors->has('geofence_radius_m')) open @endif>
                    <summary>{{ __('Optional GPS coordinates & Geofence radius') }}</summary>
                    <div class="pmr-optional-location-fields">
                        <div class="pmr-field full">
                            <label for="geofence_radius_m">{{ __('Geofence radius (metres) — Optional') }}</label>
                            <input id="geofence_radius_m" type="number" min="10" max="5000" name="geofence_radius_m" value="{{ old('geofence_radius_m', $program->geofence_radius_m ?? 50) }}">
                            <small style="color: var(--text-secondary, #746b62);">{{ __('Optional coverage radius in metres. Students can still check in without turning on GPS.') }}</small>
                        </div>
                        <div class="pmr-field">
                            <label for="latitude">{{ __('Venue latitude (Optional)') }}</label>
                            <input id="latitude" type="number" step="0.0000001" name="latitude" value="{{ old('latitude', $program->latitude ?? '') }}" placeholder="{{ __('e.g., 5.753123') }}">
                        </div>
                        <div class="pmr-field">
                            <label for="longitude">{{ __('Venue longitude (Optional)') }}</label>
                            <input id="longitude" type="number" step="0.0000001" name="longitude" value="{{ old('longitude', $program->longitude ?? '') }}" placeholder="{{ __('e.g., 102.554123') }}">
                        </div>
                        <p class="pmr-optional-location-note">{{ __('Leave these fields blank if you do not need GPS geofence validation. Students and guests will be able to record attendance instantly via QR code without device location prompts.') }}</p>
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

    const sync = () => {
        const method = document.querySelector('[name="paperwork_method"]:checked')?.value || 'pdf';
        if (box) box.hidden = false;
        if (input) {
            input.accept = method === 'docx' ? '.docx' : '.pdf';
            input.required = false;
        }
        const formal = document.querySelector('[name="registration_type"]:checked')?.value === 'approved_program';
        if (paperworkSection) paperworkSection.hidden = !formal;
    };

    document.querySelectorAll('[name="paperwork_method"]').forEach(el => el.addEventListener('change', sync));
    document.querySelectorAll('[name="registration_type"]').forEach(el => el.addEventListener('change', sync));
    sync();
});
</script>
@endsection
