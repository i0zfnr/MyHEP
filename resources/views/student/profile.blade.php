@extends('layouts.app')

@section('title', __('Profil Pelajar'))

@section('header')
    <h2 style="margin:0;font-size:1.15rem;font-weight:800;color:var(--text);">{{ __('Profil Pelajar') }}</h2>
@endsection

@section('content')
<div class="student-profile-page">
    @php
        $studentPhoto = data_get($student, 'photo');
        $studentName = data_get($student, 'full_name', 'Pelajar');
        $studentInitial = strtoupper(substr((string) $studentName, 0, 1) ?: 'P');
        $studentMatric = data_get($student, 'matric_no') ?: '-';
        $studentIc = data_get($student, 'ic_no');
        $studentProgram = data_get($student, 'program') ?: '-';
        $photoStatus = data_get($student, 'profile_photo_status') ?? (filled($studentPhoto) ? 'legacy' : 'missing');
        $enforceStudentProfilePhoto = (bool) ($enforceStudentProfilePhoto ?? false);
        $profileCompletionBypass = (bool) data_get($student, 'profile_completion_bypass', false);
    @endphp

    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if(session('warning'))<div class="required-note" style="margin-bottom:14px;font-weight:700;">{{ session('warning') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('student.profile.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="card profile-card">
            <div class="profile-card-head">
                <div class="profile-head-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <h2>{{ __('Account & Matric Photo Information') }}</h2>
            </div>
            <div class="body">
                @if($enforceStudentProfilePhoto)
                    <!-- Standard Matric Photo Guideline Card -->
                    <div class="guideline-box">
                        <div class="guideline-header">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                            <span>{{ __('Official Profile Photo Standard (Matric Card Format)') }}</span>
                        </div>
                        <div class="guideline-grid">
                            <div class="guideline-chip">
                                <span class="guideline-icon-badge is-face">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/><path d="m16 11 2 2 4-4"/></svg>
                                </span>
                                <div><strong>{{ __('Clear Face:') }}</strong> {{ __('Look directly into the camera with bright and neutral lighting.') }}</div>
                            </div>
                            <div class="guideline-chip">
                                <span class="guideline-icon-badge is-formal">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 3h12l-2 5-4 2-4-2-2-5z"/><path d="M12 10l1.8 10-1.8 2-1.8-2L12 10z" fill="currentColor"/></svg>
                                </span>
                                <div><strong>{{ __('Formal Attire:') }}</strong> {{ __('Collared shirt, blazer, or neat formal hijab.') }}</div>
                            </div>
                            <div class="guideline-chip">
                                <span class="guideline-icon-badge is-bg">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                                </span>
                                <div><strong>{{ __('Plain Background:') }}</strong> {{ __('Plain background (blue/white/neutral) without distractions.') }}</div>
                            </div>
                            <div class="guideline-chip is-prohibited">
                                <span class="guideline-icon-badge is-prohibited">
                                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                                </span>
                                <div><strong>{{ __('Prohibited:') }}</strong> {{ __('Avatars/anime, group photos, sunglasses, hats, or beauty filters.') }}</div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="photo-row">
                    <img class="profile-photo" data-profile-photo-preview src="{{ filled($studentPhoto) ? asset('storage/' . $studentPhoto) : '' }}" alt="{{ __('Profile photo') }}" @if(blank($studentPhoto)) hidden @endif>
                    <div class="profile-photo photo-placeholder" data-profile-photo-placeholder @if(filled($studentPhoto)) hidden @endif>{{ $studentInitial }}</div>
                    <div style="flex:1; min-width:220px;">
                        <label for="profile_photo">{{ __('Profile Photo (Passport Format 3:4)') }}</label>
                        <input id="profile_photo" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" data-profile-photo-input data-invalid-type="{{ __('Choose a JPG, PNG, or WEBP image.') }}" {{ blank($studentPhoto) && ! $profileCompletionBypass ? 'required' : '' }}>
                        <small style="display:block;margin-top:6px;color:var(--text-muted);">
                            {{ $enforceStudentProfilePhoto
                                ? __('JPG, PNG, or WEBP. The system provides an automatic face cropper and alignment validator.')
                                : __('JPG, PNG, or WEBP image file.')
                            }}
                        </small>
                        @if($enforceStudentProfilePhoto)
                            <small style="display:block;margin-top:8px;font-weight:800;color:{{ $photoStatus === 'approved' ? '#047857' : '#9a6700' }};">
                                @if($photoStatus === 'approved')
                                    {{ __('Photo status: approved by admin.') }}
                                @elseif($photoStatus === 'pending')
                                    {{ __('Photo status: pending admin review. You can update the photo again if it is not formal.') }}
                                @elseif($photoStatus === 'rejected')
                                    {{ __('Photo status: rejected. Please upload a new formal matric photo.') }}
                                @else
                                    {{ __('Photo status: not approved yet. Upload a formal matric photo and wait for admin approval.') }}
                                @endif
                            </small>
                        @endif
                    </div>
                </div>
                <div class="grid grid-2">
                    <div>
                        <label>{{ __('Nama Penuh') }}</label>
                        <input type="text" value="{{ $studentName }}" readonly>
                    </div>
                    <div>
                        <label>{{ __('No. Matrik') }}</label>
                        <input type="text" value="{{ $studentMatric }}" readonly>
                    </div>
                </div>

                <div class="grid grid-2" style="margin-top:14px;">
                    <div>
                        <label>{{ __('No. IC') }}</label>
                        <input type="text" value="{{ $studentIc ? maskIdentityNumber($studentIc) : '-' }}" readonly>
                    </div>
                    <div>
                        <label>{{ __('Program') }}</label>
                        <input type="text" value="{{ $studentProgram }}" readonly>
                    </div>
                </div>

                <div class="section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    <span>{{ __('Maklumat Pelajar') }}</span>
                </div>
                <div class="grid grid-2" style="margin-top:14px;">
                    <div>
                        <label for="semester">{{ __('Semester') }}</label>
                        <input id="semester" type="text" name="semester" value="{{ old('semester', data_get($student, 'semester')) }}" placeholder="{{ __('Contoh:') }} 4">
                    </div>
                    <div>
                        <label for="academic_session">{{ __('Sesi') }}</label>
                        <input id="academic_session" type="text" name="academic_session" value="{{ old('academic_session', data_get($student, 'academic_session')) }}" placeholder="{{ __('Contoh:') }} 2025/2026">
                    </div>
                </div>

                <div class="grid grid-2" style="margin-top:14px;">
                    <div>
                        <label for="email">{{ __('Email') }}</label>
                        <input id="email" type="email" name="email" value="{{ old('email', data_get($student, 'email')) }}" placeholder="{{ __('Contoh:') }} nama@email.com">
                    </div>
                    <div>
                        <label for="phone">{{ __('No. Telefon') }}</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone', data_get($student, 'phone')) }}" placeholder="{{ __('Contoh:') }} 0123456789">
                    </div>
                </div>

                <div class="grid grid-2" style="margin-top:14px;">
                    <div>
                        <label for="religion">{{ __('Agama') }}</label>
                        <input id="religion" type="text" name="religion" value="{{ old('religion', data_get($student, 'religion')) }}">
                    </div>
                    <div>
                        <label for="race">{{ __('Bangsa') }}</label>
                        <input id="race" type="text" name="race" value="{{ old('race', data_get($student, 'race')) }}">
                    </div>
                </div>

                <div class="grid grid-2" style="margin-top:14px;">
                    <div>
                        <label for="date_of_birth">{{ __('Tarikh Lahir') }} <span style="font-size:11px; font-weight:normal; color:var(--text-muted);">(DD/MM/YYYY)</span></label>
                        @php
                            $rawDob = old('date_of_birth', data_get($student, 'date_of_birth'));
                            $formattedDob = '';
                            if ($rawDob) {
                                try {
                                    $formattedDob = \Illuminate\Support\Carbon::parse($rawDob)->format('d/m/Y');
                                } catch (\Throwable $e) {
                                    $formattedDob = $rawDob;
                                }
                            }
                        @endphp
                        <input id="date_of_birth" type="text" name="date_of_birth" value="{{ $formattedDob }}" placeholder="DD/MM/YYYY (Contoh: 17/07/2006)" maxlength="10" inputmode="numeric" {{ ! $profileCompletionBypass ? 'required' : '' }} autocomplete="off">
                        <small style="color:var(--text-muted); font-size:11px; margin-top:4px; display:block;">{{ __('Taip tarikh lahir mengikut format Hari/Bulan/Tahun (Contoh: 17/07/2006)') }}</small>
                    </div>
                    <div></div>
                </div>

                <div style="margin-top:14px;">
                    <label for="address">{{ __('Alamat Rumah') }}</label>
                    <textarea id="address" name="address" rows="3" placeholder="{{ __('Alamat rumah') }}">{{ old('address', data_get($student, 'address')) }}</textarea>
                </div>

                <div class="section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    <span>{{ __('Maklumat Penjaga') }}</span>
                </div>
                <div class="grid grid-2" style="margin-top:14px;">
                    <div>
                        <label for="guardian_name">{{ __('Nama Penjaga') }}</label>
                        <input id="guardian_name" type="text" name="guardian_name" value="{{ old('guardian_name', data_get($student, 'guardian_name')) }}">
                    </div>
                    <div>
                        <label for="guardian_ic_no">{{ __('No. KP Penjaga') }}</label>
                        <input id="guardian_ic_no" type="text" name="guardian_ic_no" value="{{ old('guardian_ic_no', data_get($student, 'guardian_ic_no')) }}">
                    </div>
                </div>

                <div class="grid grid-2" style="margin-top:14px;">
                    <div>
                        <label for="guardian_phone">{{ __('No. Telefon Penjaga') }}</label>
                        <input id="guardian_phone" type="text" name="guardian_phone" value="{{ old('guardian_phone', data_get($student, 'guardian_phone')) }}">
                    </div>
                    <div>
                        <label for="mother_ic_no">{{ __('No. IC/KP Ibu') }}</label>
                        <input id="mother_ic_no" type="text" name="mother_ic_no" value="{{ old('mother_ic_no', data_get($student, 'mother_ic_no')) }}">
                    </div>
                </div>

                <div style="margin-top:14px;">
                    <label for="family_income">{{ __('Pendapatan Keluarga (RM)') }}</label>
                    <input id="family_income" type="number" name="family_income" value="{{ old('family_income', data_get($student, 'family_income')) }}" min="0" step="0.01" placeholder="0.00">
                </div>

                <div style="margin-top:14px;">
                    <label for="guardian_address">{{ __('Alamat Penjaga') }}</label>
                    <textarea id="guardian_address" name="guardian_address" rows="3" placeholder="{{ __('Alamat penjaga') }}">{{ old('guardian_address', data_get($student, 'guardian_address')) }}</textarea>
                </div>

                <div class="section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <span>{{ __('Maklumat Tempat Tinggal Semasa Pengajian') }}</span>
                </div>
                <div class="grid grid-2" style="margin-top:14px;">
                    <div>
                        <label for="residence_status">{{ __('Status Kediaman') }}</label>
                        <select id="residence_status" name="residence_status">
                            <option value="inside_campus" @selected(old('residence_status', $student->residence_status ?? 'inside_campus') === 'inside_campus')>{{ __('Dalam Kampus') }}</option>
                            <option value="live_out" @selected(old('residence_status', $student->residence_status ?? 'inside_campus') === 'live_out')>{{ __('Live Out / Luar Kampus') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="room_number">{{ __('No. Bilik') }}</label>
                        <input id="room_number" type="text" name="room_number" value="{{ old('room_number', data_get($student, 'room_number')) }}" placeholder="{{ __('Contoh:') }} AL306">
                    </div>
                </div>
                <div style="margin-top:14px;">
                    <label for="study_address">{{ __('Alamat Tempat Tinggal Semasa') }}</label>
                    <textarea id="study_address" name="study_address" rows="3" placeholder="{{ __('Alamat semasa pengajian') }}">{{ old('study_address', data_get($student, 'study_address')) }}</textarea>
                </div>
            </div>
        </div>

        <div class="profile-actions-bar">
            <button class="btn btn-primary" type="submit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                <span>{{ __('Simpan Profil') }}</span>
            </button>
            <a class="btn btn-outline" href="{{ route('student.dashboard') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                <span>{{ __('Kembali') }}</span>
            </a>
        </div>
    </form>

    <form method="POST" action="{{ route('student.profile.password.update') }}" style="margin-top:1.5rem;">
        @csrf
        <div class="card profile-card">
            <div class="profile-card-head">
                <div class="profile-head-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <h2>{{ __('Tukar Kata Laluan') }}</h2>
            </div>
            <div class="body">
                <div class="grid grid-2">
                    <div>
                        <label for="current_password">{{ __('Kata Laluan Semasa') }}</label>
                        <input id="current_password" type="password" name="current_password" required placeholder="{{ __('Jika belum tukar, guna No. IC anda') }}">
                    </div>
                    <div></div>
                </div>

                <div class="grid grid-2" style="margin-top:14px;">
                    <div>
                        <label for="new_password">{{ __('Kata Laluan Baharu') }}</label>
                        <input id="new_password" type="password" name="new_password" required minlength="8" placeholder="{{ __('Minimum 8 aksara') }}">
                    </div>
                    <div>
                        <label for="new_password_confirmation">{{ __('Sahkan Kata Laluan Baharu') }}</label>
                        <input id="new_password_confirmation" type="password" name="new_password_confirmation" required minlength="8">
                    </div>
                </div>

                <div class="profile-actions-bar" style="margin-top:1.5rem;">
                    <button class="btn btn-primary" type="submit">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        <span>{{ __('Tukar Kata Laluan') }}</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="profile-crop-modal" data-profile-crop-modal
    data-profile-standard-enabled="{{ $enforceStudentProfilePhoto ? 'true' : 'false' }}"
    data-text-verified="{{ __('Face Verified') }}"
    data-text-unclear="{{ __('Unclear Face') }}"
    data-text-evaluating="{{ __('Evaluating Face...') }}"
    data-text-confirm-warning="{{ __('The system detected that your face may be unclear or outside the oval guide. Are you sure you want to use this photo as your official matric photo?') }}"
    aria-hidden="true">
    <section class="profile-crop-dialog" role="dialog" aria-modal="true" aria-labelledby="profileCropTitle">
        <header class="profile-crop-head">
            <h2 id="profileCropTitle">{{ __('Matric Card Profile Photo Editor') }}</h2>
            <button type="button" class="profile-crop-close" data-profile-crop-action="cancel" aria-label="{{ __('Cancel photo crop') }}">&times;</button>
        </header>
        <div class="profile-crop-stage">
            <img data-profile-crop-image alt="{{ __('Selected profile photo') }}">
            @if($enforceStudentProfilePhoto)
                <div class="face-guide-overlay" data-face-guide-overlay aria-hidden="true">
                    <div class="face-guide-silhouette">
                        <span class="face-guide-text">{{ __('Place Face Here') }}</span>
                    </div>
                </div>
            @endif
        </div>
        <footer class="profile-crop-controls">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
                <div class="profile-crop-tools">
                    <button type="button" class="profile-crop-tool" data-profile-crop-action="rotate-left" title="{{ __('Rotate left') }}">&#8634; {{ __('Left') }}</button>
                    <button type="button" class="profile-crop-tool" data-profile-crop-action="rotate-right" title="{{ __('Rotate right') }}">&#8635; {{ __('Right') }}</button>
                    <button type="button" class="profile-crop-tool" data-profile-crop-action="reset" title="{{ __('Reset position') }}">{{ __('Reset') }}</button>
                    @if($enforceStudentProfilePhoto)
                        <button type="button" class="profile-crop-tool" data-profile-crop-action="toggle-guide" title="{{ __('Show/hide face guide overlay') }}">{{ __('Guide') }}</button>
                    @endif
                </div>
                @if($enforceStudentProfilePhoto)
                    <div id="faceDetectionStatus" class="face-detect-status is-checking" data-face-detection-status>
                        <span>🔍</span> <span>{{ __('Evaluating Face...') }}</span>
                    </div>
                @endif
            </div>
            <div class="profile-crop-actions">
                <button type="button" class="btn" data-profile-crop-action="cancel">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" data-profile-crop-action="apply">{{ __('Use photo') }}</button>
            </div>
        </footer>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const dobInput = document.getElementById('date_of_birth');
    if (!dobInput) return;

    dobInput.addEventListener('input', function (e) {
        let v = e.target.value.replace(/\D/g, '').slice(0, 8);
        if (v.length >= 5) {
            e.target.value = v.slice(0, 2) + '/' + v.slice(2, 4) + '/' + v.slice(4);
        } else if (v.length >= 3) {
            e.target.value = v.slice(0, 2) + '/' + v.slice(2);
        } else {
            e.target.value = v;
        }
    });

    dobInput.addEventListener('blur', function (e) {
        let v = e.target.value.trim();
        // If user typed 8 digits without slashes, format it
        if (/^\d{8}$/.test(v)) {
            e.target.value = v.slice(0, 2) + '/' + v.slice(2, 4) + '/' + v.slice(4);
        }
    });
});
</script>
@endsection
