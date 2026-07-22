@extends('layouts.app')

@section('title', __('Profil Pelajar'))

@push('styles')
<style>
    .wrap { max-width: 900px; margin: 0 auto; }
    .card { background:#fff; border:1px solid #ede4d9; border-radius:12px; overflow:hidden; }
    .card h2 { margin:0; padding:14px 16px; border-bottom:1px solid #ede4d9; font-size:16px; }
    .body { padding:16px; }
    .grid { display:grid; grid-template-columns:1fr; gap:12px; }
    @media (min-width:900px) { .grid-2 { grid-template-columns:1fr 1fr; } }
    .section-title { margin: 18px 0 10px; font-size: 14px; font-weight: 800; color:#6e5745; text-transform: uppercase; letter-spacing: .04em; }
    label { font-size:13px; font-weight:600; color:#7a6555; display:block; margin-bottom:6px; }
    input, textarea, select { width:100%; border:1px solid #e5d8c8; border-radius:8px; padding:9px 10px; font-size:14px; }
    input[readonly] { background:#faf7f4; color:#7a6555; }
    .actions { display:flex; gap:10px; flex-wrap:wrap; margin-top:14px; }
    .btn { display:inline-block; border:1px solid #cbb9a4; background:#fff; color:#8a7362; border-radius:8px; padding:9px 14px; text-decoration:none; font-weight:600; font-size:14px; cursor:pointer; }
    .btn-primary { background:linear-gradient(135deg,#A48D78,#CBB9A4); color:#fff; border:none; }
    .ok { margin-bottom:12px; background:#e7f3f3; border:1px solid #b9ddde; color:#1f5559; border-radius:8px; padding:10px; font-size:13px; }
    .err { margin-bottom:12px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:8px; padding:10px; font-size:13px; }
    .photo-row { display:flex; gap:14px; align-items:center; flex-wrap:wrap; margin-bottom:14px; }
    .profile-photo { width:92px; height:92px; border-radius:12px; object-fit:cover; border:1px solid #e5d8c8; background:#faf7f4; }
    .profile-photo[hidden], .photo-placeholder[hidden] { display:none; }
    .photo-placeholder { display:flex; align-items:center; justify-content:center; color:#8a7362; font-weight:800; font-size:28px; }
    .required-note { margin-bottom:12px; background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; border-radius:8px; padding:10px; font-size:13px; }
    body.profile-crop-open { overflow:hidden !important; }
    .profile-crop-modal {
        position:fixed;
        inset:0;
        z-index:1800;
        display:grid;
        place-items:center;
        padding:clamp(12px, 3vw, 24px);
        background:rgba(17,13,10,.38);
        opacity:0;
        visibility:hidden;
        pointer-events:none;
        backdrop-filter:blur(24px) saturate(78%) brightness(.82);
        -webkit-backdrop-filter:blur(24px) saturate(78%) brightness(.82);
        transition:opacity .24s var(--se-motion-ease), visibility 0s linear .32s;
    }
    .profile-crop-modal.is-open { opacity:1; visibility:visible; pointer-events:auto; transition-delay:0s; }
    .profile-crop-dialog {
        width:min(520px, 100%);
        max-height:calc(100dvh - 24px);
        display:grid;
        grid-template-rows:auto minmax(0, 1fr) auto;
        overflow:hidden;
        border:1px solid var(--liquid-rim);
        border-radius:20px;
        color:var(--se-text);
        background:var(--liquid-popup);
        box-shadow:var(--liquid-shadow);
        backdrop-filter:blur(34px) saturate(165%);
        -webkit-backdrop-filter:blur(34px) saturate(165%);
        transform:translate3d(0, 18px, 0) scale(.97);
        transform-origin:center;
        transition:transform .4s var(--se-motion-ease);
    }
    .profile-crop-modal.is-open .profile-crop-dialog { transform:translate3d(0,0,0) scale(1); }
    .profile-crop-head { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; border-bottom:1px solid var(--se-border); }
    .profile-crop-head h2 { margin:0; padding:0; border:0; background:none; font-size:1rem; color:var(--se-text); }
    .profile-crop-head h2::before { display:none; }
    .profile-crop-close, .profile-crop-tool {
        min-width:44px;
        min-height:44px;
        display:inline-grid;
        place-items:center;
        border:1px solid var(--se-border-strong);
        border-radius:12px;
        background:var(--liquid-surface-strong);
        color:var(--se-text);
        cursor:pointer;
    }
    .profile-crop-stage { min-height:280px; background:#11100f; overflow:hidden; }
    .profile-crop-stage img { display:block; max-width:100%; }
    .profile-crop-controls { display:grid; gap:12px; padding:14px 16px calc(14px + env(safe-area-inset-bottom, 0px)); border-top:1px solid var(--se-border); }
    .profile-crop-tools { display:flex; align-items:center; justify-content:center; gap:8px; flex-wrap:wrap; }
    .profile-crop-tool { padding:0 12px; font-size:.78rem; font-weight:700; }
    .profile-crop-actions { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .profile-crop-actions .btn { min-height:44px; text-align:center; }
    @media (max-width: 600px) {
        .profile-crop-dialog { max-height:calc(100dvh - 16px); border-radius:18px; }
        .profile-crop-stage { min-height:0; height:min(48dvh, 420px); }
        .profile-crop-controls { gap:10px; padding:12px; }
        .profile-crop-tool { flex:1 1 calc(33.333% - 8px); }
    }
    @media (prefers-reduced-motion: reduce) {
        .profile-crop-modal, .profile-crop-dialog { transition:none; }
    }
    /* Student UX Identity v2 */
    :root {
        --stu-ink: #1f1d1a;
        --stu-muted: #6f675f;
        --stu-line: #e9ded1;
        --stu-soft: #fbf7f1;
        --stu-accent: #7b5b43;
        --stu-accent-2: #b69172;
        --stu-glow: rgba(123, 91, 67, 0.18);
    }
    body {
        background:
            radial-gradient(1200px 520px at -8% -18%, #efe2d4 0%, transparent 56%),
            radial-gradient(900px 350px at 108% -14%, #f3e9de 0%, transparent 54%),
            linear-gradient(180deg, #faf7f2 0%, #f5efe7 100%);
    }
    .wrap,
    .student-dashboard,
    .dash-student,
    .sdash,
    .adash {
        width: min(1160px, 100%);
    }
    .card,
    .panel,
    .box,
    .stat,
    .summary,
    .tile {
        border: 1px solid var(--stu-line) !important;
        border-radius: 16px;
        background: linear-gradient(180deg, #fff 0%, #fffdfa 100%);
        box-shadow: 0 1px 2px rgba(31, 29, 26, 0.07), 0 10px 24px rgba(61, 46, 34, 0.06);
        transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
    }
    .card:hover,
    .panel:hover,
    .box:hover,
    .stat:hover,
    .summary:hover,
    .tile:hover {
        transform: translateY(-2px);
        border-color: #dcc7b0 !important;
        box-shadow: 0 5px 14px rgba(31, 29, 26, 0.11), 0 18px 30px rgba(61, 46, 34, 0.10);
    }
    .head,
    .card h2,
    .card h3,
    .section-head,
    .panel-head {
        position: relative;
        background: linear-gradient(180deg, #fff 0%, #fbf4ec 100%);
    }
    .head::before,
    .card h2::before,
    .card h3::before,
    .section-head::before,
    .panel-head::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: linear-gradient(180deg, var(--stu-accent) 0%, var(--stu-accent-2) 100%);
    }
    .btn,
    .button,
    button[type='submit'],
    input[type='submit'] {
        border-radius: 10px;
        border: 1px solid #ceb79f;
        background: linear-gradient(180deg, #ffffff 0%, #f9f3ec 100%);
        color: #685141;
        font-weight: 700;
        transition: transform 170ms ease, box-shadow 170ms ease, border-color 170ms ease, color 170ms ease;
    }
    .btn:hover,
    .button:hover,
    button[type='submit']:hover,
    input[type='submit']:hover {
        transform: translateY(-1px);
        border-color: #ba9b7b;
        box-shadow: 0 8px 16px rgba(97, 73, 52, 0.13);
    }
    .btn-primary,
    .primary,
    .is-primary {
        border-color: #7f6249 !important;
        background: linear-gradient(135deg, #7b5b43 0%, #b69172 100%) !important;
        color: #fff !important;
    }
    input,
    select,
    textarea {
        border-color: #decdb8 !important;
        background: #fffdfb;
        color: var(--stu-ink);
        transition: border-color 150ms ease, box-shadow 150ms ease, background-color 150ms ease;
    }
    input:focus,
    select:focus,
    textarea:focus {
        border-color: #b89576 !important;
        box-shadow: 0 0 0 4px rgba(184, 149, 118, 0.18);
        outline: none;
        background: #fff;
    }
    .filters,
    .toolbar,
    .search-bar {
        background: linear-gradient(180deg, #fffdfb 0%, #faf3eb 100%);
        border-top: 1px solid #efe2d5;
        border-bottom: 1px solid #efe2d5;
    }
    table tbody tr {
        transition: background-color 140ms ease;
    }
    table tbody tr:hover {
        background: #fcf7f1;
    }
    .badge,
    .status,
    .pill {
        border-radius: 999px;
        font-weight: 700;
    }
    @media (max-width: 980px) {
        .head,
        .toolbar,
        .actions {
            align-items: flex-start;
        }
        .head > div,
        .head form,
        .toolbar > div,
        .toolbar form {
            width: 100%;
        }
        .stats,
        .summary-grid,
        .cards-grid {
            grid-template-columns: 1fr !important;
        }
        table th,
        table td {
            font-size: 12px !important;
            padding: 9px 10px !important;
        }
    }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Profil Pelajar') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <form method="POST" action="{{ route('student.profile.update') }}" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <h2>{{ __('Maklumat Akaun') }}</h2>
            <div class="body">
                <div class="required-note">{{ __('Upload a clear profile photo before using the system. You may complete the other details now or update them later.') }}</div>
                <div class="photo-row">
                    <img class="profile-photo" data-profile-photo-preview src="{{ !empty($student->photo) ? asset('storage/' . $student->photo) : '' }}" alt="{{ __('Profile photo') }}" @if(empty($student->photo)) hidden @endif>
                    <div class="profile-photo photo-placeholder" data-profile-photo-placeholder @if(!empty($student->photo)) hidden @endif>{{ strtoupper(substr($student->full_name ?? 'P', 0, 1)) }}</div>
                    <div style="flex:1; min-width:220px;">
                        <label for="profile_photo">{{ __('Gambar Profil') }}</label>
                        <input id="profile_photo" type="file" name="profile_photo" accept="image/jpeg,image/png,image/webp" data-profile-photo-input data-invalid-type="{{ __('Choose a JPG, PNG, or WEBP image.') }}" {{ empty($student->photo) ? 'required' : '' }}>
                        <small style="display:block;margin-top:6px;color:#7a6555;">{{ __('JPG, PNG, or WEBP. Maximum 50MB for testing.') }}</small>
                    </div>
                </div>
                <div class="grid grid-2">
                    <div>
                        <label>{{ __('Nama Penuh') }}</label>
                        <input type="text" value="{{ $student->full_name }}" readonly>
                    </div>
                    <div>
                        <label>{{ __('No. Matrik') }}</label>
                        <input type="text" value="{{ $student->matric_no ?: '-' }}" readonly>
                    </div>
                </div>

                <div class="grid grid-2" style="margin-top:12px;">
                    <div>
                        <label>{{ __('No. IC') }}</label>
                        <input type="text" value="{{ $student->ic_no }}" readonly>
                    </div>
                    <div>
                        <label>{{ __('Program') }}</label>
                        <input type="text" value="{{ $student->program }}" readonly>
                    </div>
                </div>

                <div class="section-title">{{ __('Maklumat Pelajar') }}</div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div>
                        <label for="semester">{{ __('Semester') }}</label>
                        <input id="semester" type="text" name="semester" value="{{ old('semester', data_get($student, 'semester')) }}" placeholder="{{ __('Contoh:') }} 4">
                    </div>
                    <div>
                        <label for="academic_session">{{ __('Sesi') }}</label>
                        <input id="academic_session" type="text" name="academic_session" value="{{ old('academic_session', data_get($student, 'academic_session')) }}" placeholder="{{ __('Contoh:') }} 2025/2026">
                    </div>
                </div>

                <div class="grid grid-2" style="margin-top:12px;">
                    <div>
                        <label for="email">{{ __('Email') }}</label>
                        <input id="email" type="email" name="email" value="{{ old('email', data_get($student, 'email')) }}" placeholder="{{ __('Contoh:') }} nama@email.com">
                    </div>
                    <div>
                        <label for="phone">{{ __('No. Telefon') }}</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone', $student->phone) }}" placeholder="{{ __('Contoh:') }} 0123456789">
                    </div>
                </div>

                <div class="grid grid-2" style="margin-top:12px;">
                    <div>
                        <label for="religion">{{ __('Agama') }}</label>
                        <input id="religion" type="text" name="religion" value="{{ old('religion', data_get($student, 'religion')) }}">
                    </div>
                    <div>
                        <label for="race">{{ __('Bangsa') }}</label>
                        <input id="race" type="text" name="race" value="{{ old('race', data_get($student, 'race')) }}">
                    </div>
                </div>

                <div class="grid grid-2" style="margin-top:12px;">
                    <div>
                        <label for="parliament">{{ __('Parlimen') }}</label>
                        <input id="parliament" type="text" name="parliament" value="{{ old('parliament', data_get($student, 'parliament')) }}">
                    </div>
                    <div>
                        <label for="dun">{{ __('DUN') }}</label>
                        <input id="dun" type="text" name="dun" value="{{ old('dun', data_get($student, 'dun')) }}">
                    </div>
                </div>

                <div class="grid grid-2" style="margin-top:12px;">
                    <div>
                        <label for="date_of_birth">{{ __('Tarikh Lahir') }}</label>
                        <input id="date_of_birth" type="date" name="date_of_birth" value="{{ old('date_of_birth', data_get($student, 'date_of_birth')) }}">
                    </div>
                    <div></div>
                </div>

                <div style="margin-top:12px;">
                    <label for="address">{{ __('Alamat Rumah') }}</label>
                    <textarea id="address" name="address" rows="3" placeholder="{{ __('Alamat rumah') }}">{{ old('address', $student->address) }}</textarea>
                </div>

                <div class="section-title">{{ __('Maklumat Penjaga') }}</div>
                <div class="grid grid-2" style="margin-top:12px;">
                    <div>
                        <label for="guardian_name">{{ __('Nama Penjaga') }}</label>
                        <input id="guardian_name" type="text" name="guardian_name" value="{{ old('guardian_name', data_get($student, 'guardian_name')) }}">
                    </div>
                    <div>
                        <label for="guardian_ic_no">{{ __('No. KP Penjaga') }}</label>
                        <input id="guardian_ic_no" type="text" name="guardian_ic_no" value="{{ old('guardian_ic_no', data_get($student, 'guardian_ic_no')) }}">
                    </div>
                </div>

                <div class="grid grid-2" style="margin-top:12px;">
                    <div>
                        <label for="guardian_phone">{{ __('No. Telefon Penjaga') }}</label>
                        <input id="guardian_phone" type="text" name="guardian_phone" value="{{ old('guardian_phone', data_get($student, 'guardian_phone')) }}">
                    </div>
                    <div>
                        <label for="mother_ic_no">{{ __('No. IC/KP Ibu') }}</label>
                        <input id="mother_ic_no" type="text" name="mother_ic_no" value="{{ old('mother_ic_no', data_get($student, 'mother_ic_no')) }}">
                    </div>
                </div>

                <div class="grid grid-2" style="margin-top:12px;">
                    <div>
                        <label for="guardian_occupation">{{ __('Pekerjaan Penjaga') }}</label>
                        <input id="guardian_occupation" type="text" name="guardian_occupation" value="{{ old('guardian_occupation', data_get($student, 'guardian_occupation')) }}">
                    </div>
                    <div>
                        <label for="family_income">{{ __('Pendapatan Keluarga (RM)') }}</label>
                        <input id="family_income" type="number" name="family_income" value="{{ old('family_income', data_get($student, 'family_income')) }}" min="0" step="0.01" placeholder="0.00">
                    </div>
                </div>

                <div style="margin-top:12px;">
                    <label for="guardian_address">{{ __('Alamat Penjaga') }}</label>
                    <textarea id="guardian_address" name="guardian_address" rows="3" placeholder="{{ __('Alamat penjaga') }}">{{ old('guardian_address', data_get($student, 'guardian_address')) }}</textarea>
                </div>

                <div class="section-title">{{ __('Maklumat Tempat Tinggal Semasa Pengajian') }}</div>
                <div class="grid grid-2" style="margin-top:12px;">
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
                <div style="margin-top:12px;">
                    <label for="study_address">{{ __('Alamat Tempat Tinggal Semasa') }}</label>
                    <textarea id="study_address" name="study_address" rows="3" placeholder="{{ __('Alamat semasa pengajian') }}">{{ old('study_address', data_get($student, 'study_address')) }}</textarea>
                </div>
            </div>
        </div>

        <div class="actions">
            <button class="btn btn-primary" type="submit">{{ __('Simpan Profil') }}</button>
            <a class="btn" href="{{ route('student.dashboard') }}">{{ __('Kembali') }}</a>
        </div>
    </form>

    <form method="POST" action="{{ route('student.profile.password.update') }}" style="margin-top:14px;">
        @csrf
        <div class="card">
            <h2>{{ __('Tukar Kata Laluan') }}</h2>
            <div class="body">
                <div class="grid grid-2">
                    <div>
                        <label for="current_password">{{ __('Kata Laluan Semasa') }}</label>
                        <input id="current_password" type="password" name="current_password" required placeholder="{{ __('Jika belum tukar, guna No. IC anda') }}">
                    </div>
                    <div></div>
                </div>

                <div class="grid grid-2" style="margin-top:12px;">
                    <div>
                        <label for="new_password">{{ __('Kata Laluan Baharu') }}</label>
                        <input id="new_password" type="password" name="new_password" required minlength="8" placeholder="{{ __('Minimum 8 aksara') }}">
                    </div>
                    <div>
                        <label for="new_password_confirmation">{{ __('Sahkan Kata Laluan Baharu') }}</label>
                        <input id="new_password_confirmation" type="password" name="new_password_confirmation" required minlength="8">
                    </div>
                </div>

                <div class="actions">
                    <button class="btn btn-primary" type="submit">{{ __('Tukar Kata Laluan') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="profile-crop-modal" data-profile-crop-modal aria-hidden="true">
    <section class="profile-crop-dialog" role="dialog" aria-modal="true" aria-labelledby="profileCropTitle">
        <header class="profile-crop-head">
            <h2 id="profileCropTitle">{{ __('Adjust profile photo') }}</h2>
            <button type="button" class="profile-crop-close" data-profile-crop-action="cancel" aria-label="{{ __('Cancel photo crop') }}">&times;</button>
        </header>
        <div class="profile-crop-stage">
            <img data-profile-crop-image alt="{{ __('Selected profile photo') }}">
        </div>
        <footer class="profile-crop-controls">
            <div class="profile-crop-tools">
                <button type="button" class="profile-crop-tool" data-profile-crop-action="rotate-left">{{ __('Rotate left') }}</button>
                <button type="button" class="profile-crop-tool" data-profile-crop-action="rotate-right">{{ __('Rotate right') }}</button>
                <button type="button" class="profile-crop-tool" data-profile-crop-action="reset">{{ __('Reset') }}</button>
            </div>
            <div class="profile-crop-actions">
                <button type="button" class="btn" data-profile-crop-action="cancel">{{ __('Cancel') }}</button>
                <button type="button" class="btn btn-primary" data-profile-crop-action="apply">{{ __('Use photo') }}</button>
            </div>
        </footer>
    </section>
</div>
@endsection


