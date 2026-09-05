@extends('layouts.app')

@section('title', 'Senarai Pelajar')



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Pelajar') }}</h2>
@endsection

@section('content')
<div class="wrap student-list-wrap">
    @php($canViewSensitiveStudents = adminCan('students.sensitive'))
    @php($canExportStudents = adminCan('students.export'))
    @php($canManageStudents = adminCan('students.manage'))
    @php($canManageCompletionBypass = session('auth_user.admin_role') === 'system_admin')
    @php($hasStudentActions = $canViewSensitiveStudents || $canManageStudents)
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    @if(session('auth_user.admin_role') === 'system_admin')
        <details class="student-danger-zone">
            <summary class="danger-zone-summary">
                <div class="danger-zone-summary-left">
                    <span class="danger-icon-badge" aria-hidden="true">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </span>
                    <div class="danger-title-group">
                        <strong class="danger-zone-title">{{ __('Danger Zone: Padam Semua Rekod Pelajar') }}</strong>
                        <span class="danger-zone-sub">{{ __('Tindakan kekal yang tidak boleh diundur') }}</span>
                    </div>
                </div>
                <div class="danger-zone-summary-right">
                    <span class="danger-pill-tag">{{ __('Perhatian') }}</span>
                    <svg class="danger-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                </div>
            </summary>
            <div class="danger-zone-content">
                <p class="danger-warning-msg">
                    <strong>{{ __('Amaran Kritikal:') }}</strong> {{ __('Tindakan ini akan memadam semua data pelajar, dokumen, foto, sesi, rekod biasiswa, disiplin, dan pergerakan secara kekal.') }}
                </p>
                <form class="danger-zone-form" method="POST" action="{{ route('admin.students.destroy-all') }}" data-confirm-title="{{ __('Delete all student data') }}" data-confirm-message="This permanently deletes every student, their documents, photos, sessions, scholarship, discipline, and movement records. This cannot be undone." data-confirm-action="Delete All Students" data-confirm-tone="danger">
                    @csrf
                    @method('DELETE')
                    <div class="danger-form-row">
                        <input class="danger-confirm-input" name="confirmation" required autocomplete="off" placeholder="{{ __('Taip DELETE ALL STUDENTS untuk sahkan') }}" aria-label="{{ __('Confirmation') }}">
                        <button class="btn-danger-submit" type="submit">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                            <span>{{ __('Padam Semua Pelajar') }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </details>
    @endif

    @if($canManageStudents)
        <div class="card student-import-card">
            <div class="import-card-head">
                <div class="import-head-title-wrap">
                    <span class="import-head-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                    </span>
                    <div>
                        <h3 class="import-card-title">{{ __('Import & Kemaskini Data Pelajar') }}</h3>
                        <p class="import-card-subtitle">{{ __('Muat naik fail senarai pelajar (.CSV, .XLSX) sehingga 50 MB') }}</p>
                    </div>
                </div>
            </div>

            <div class="import-card-body">
                <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data" class="student-import-form">
                    @csrf
                    <div class="import-form-grid">
                        <div class="import-file-section">
                            <label class="import-file-dropzone" for="student_file">
                                <span class="dropzone-icon">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                </span>
                                <div class="dropzone-text">
                                    <span class="dropzone-label-primary">{{ __('Pilih atau seret fail ke sini') }}</span>
                                    <span class="dropzone-label-sub" id="student_file_chosen">{{ __('Format disokong: .CSV, .XLSX, .TXT') }}</span>
                                </div>
                                <span class="dropzone-btn">{{ __('Pilih Fail') }}</span>
                                <input id="student_file" type="file" name="student_file" accept=".csv,.txt,.xlsx" required onchange="document.getElementById('student_file_chosen').textContent = this.files[0] ? this.files[0].name : 'Format disokong: .CSV, .XLSX, .TXT';">
                            </label>

                            <div class="import-supported-tags">
                                <span class="import-tags-label">{{ __('Lajur Disokong:') }}</span>
                                <span class="import-tag">Nama Pelajar</span>
                                <span class="import-tag">No IC</span>
                                <span class="import-tag">No Matrik</span>
                                <span class="import-tag">Program</span>
                                <span class="import-tag">Telefon</span>
                                <span class="import-tag">Emel</span>
                                <span class="import-tag">Semester</span>
                                <span class="import-tag">Sesi Akademik</span>
                            </div>
                        </div>

                        <div class="import-action-section">
                            <button class="btn-import-submit" type="submit">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                <span>{{ __('Import & Kemaskini') }}</span>
                            </button>
                        </div>
                    </div>
                </form>

                @if(session('import_result'))
                    @php($result = session('import_result'))
                    <div class="import-summary-grid">
                        <div class="import-summary-stat">
                            <span class="stat-lbl">{{ __('Jumlah Baris') }}</span>
                            <strong class="stat-val">{{ number_format($result['total_rows'] ?? 0) }}</strong>
                        </div>
                        <div class="import-summary-stat is-success">
                            <span class="stat-lbl">{{ __('Pelajar Baharu') }}</span>
                            <strong class="stat-val">{{ number_format($result['students_created'] ?? 0) }}</strong>
                        </div>
                        <div class="import-summary-stat is-info">
                            <span class="stat-lbl">{{ __('Dikemaskini') }}</span>
                            <strong class="stat-val">{{ number_format($result['students_updated'] ?? 0) }}</strong>
                        </div>
                        <div class="import-summary-stat is-muted">
                            <span class="stat-lbl">{{ __('Dilangkau') }}</span>
                            <strong class="stat-val">{{ number_format($result['skipped'] ?? 0) }}</strong>
                        </div>
                    </div>
                    @if(!empty($result['errors']))
                        <div class="import-errors-box">
                            <h5 class="import-errors-title">{{ __('Ralat Semasa Import:') }}</h5>
                            <ul class="error-list">
                                @foreach($result['errors'] as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endif

    <div class="stats student-stats-grid">
        <a class="stat stat-link {{ empty($filters['password_status']) ? 'is-active' : '' }}" href="{{ route('admin.students.index', array_merge(request()->except('page', 'password_status'), ['password_status' => ''])) }}">
            <div class="stat-label">{{ __('Total Pelajar') }}</div>
            <div class="stat-value">{{ number_format($studentStats['total']) }}</div>
        </a>
        @if($canViewSensitiveStudents)
            <a class="stat stat-link {{ ($filters['password_status'] ?? '') === 'default' ? 'is-active' : '' }}" href="{{ route('admin.students.index', array_merge(request()->except('page'), ['password_status' => 'default'])) }}">
                <div class="stat-label">{{ __('Default IC') }}</div>
                <div class="stat-value">{{ number_format($studentStats['default_ic']) }}</div>
            </a>
            <a class="stat stat-link {{ ($filters['password_status'] ?? '') === 'custom' ? 'is-active' : '' }}" href="{{ route('admin.students.index', array_merge(request()->except('page'), ['password_status' => 'custom'])) }}">
                <div class="stat-label">{{ __('Custom Password') }}</div>
                <div class="stat-value">{{ number_format($studentStats['custom_password']) }}</div>
            </a>
        @endif
    </div>

    <div class="card student-card">
        <div class="head student-list-head">
            <div class="student-head-title-wrap">
                <h1 class="student-list-title">{{ __('Senarai Pelajar') }}</h1>
                <span class="student-list-count">{{ number_format($studentStats['total']) }} {{ __('rekod') }}</span>
            </div>
            <div class="student-head-actions">
                <a class="btn-head-action" href="{{ route('admin.dashboard') }}">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    <span>{{ __('Dashboard') }}</span>
                </a>
                @if($canExportStudents)
                    <a class="btn-head-action" href="{{ route('admin.students.export', request()->query()) }}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <span>{{ __('Export CSV') }}</span>
                    </a>
                @endif
                @if($canManageStudents)
                    <form method="POST" action="{{ route('admin.students.photos.destroy-all') }}" onsubmit="return confirm('{{ __('Are you sure you want to delete all student profile photos? Student records will remain and they will be prompted to upload fresh formal matric card photos.') }}');" style="display:inline-flex;margin:0;">
                        @csrf
                        @method('DELETE')
                        <button class="btn-head-action warn" type="submit" title="{{ __('Delete all student profile photos to require uploading new formal matric card photos') }}">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" y1="11" x2="10" y2="17"/><line x1="14" y1="11" x2="14" y2="17"/></svg>
                            <span>{{ __('Delete All Photos') }}</span>
                        </button>
                    </form>
                    <a class="btn-head-action primary" href="{{ route('admin.students.create') }}">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        <span>{{ __('Tambah Pelajar') }}</span>
                    </a>
                @endif
            </div>
        </div>

        <div class="filters student-filters-bar" data-filter-sheet data-filter-title="{{ __('Student filters') }}">
            <form method="GET" action="{{ route('admin.students.index') }}" data-live-filter-form data-live-filter-delay="350">
                <div class="student-filter-row">
                    <div class="filter-field filter-q">
                        <svg class="filter-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ $canViewSensitiveStudents ? __('Cari nama / IC...') : __('Cari nama...') }}" autocomplete="off">
                    </div>
                    <div class="filter-field filter-matric">
                        <input type="text" name="matric_no" value="{{ $filters['matric_no'] ?? '' }}" placeholder="{{ __('No matrik...') }}" autocomplete="off">
                    </div>
                    <div class="filter-field filter-program">
                        <input type="text" name="program" value="{{ $filters['program'] ?? '' }}" placeholder="{{ __('Program...') }}" autocomplete="off">
                    </div>
                    @if($canViewSensitiveStudents)
                        <div class="filter-field filter-pwd">
                            <select name="password_status">
                                <option value="">{{ __('Semua status kata laluan') }}</option>
                                <option value="default" {{ ($filters['password_status'] ?? '') === 'default' ? 'selected' : '' }}>{{ __('Default IC') }}</option>
                                <option value="custom" {{ ($filters['password_status'] ?? '') === 'custom' ? 'selected' : '' }}>{{ __('Custom Password') }}</option>
                            </select>
                        </div>
                    @endif
                    @if(!empty(array_filter($filters ?? [])))
                        <a class="filter-reset-btn" href="{{ route('admin.students.index') }}" title="{{ __('Reset filters') }}">&times;</a>
                    @endif
                    <span data-live-filter-status aria-live="polite" class="filter-live-status"></span>
                </div>
            </form>
        </div>

        <div data-live-filter-results>
        <div class="student-table-wrap" data-no-virtual="true">
            <table class="students-table">
                <thead>
                    <tr>
                        <th class="th-student">{{ __('Nama Pelajar') }}</th>
                        <th class="th-matric">{{ __('No. Matrik') }}</th>
                        @if($canViewSensitiveStudents)<th class="th-ic">{{ __('No. IC') }}</th>@endif
                        <th class="th-program">{{ __('Program') }}</th>
                        @if($canViewSensitiveStudents)
                            <th class="th-phone">{{ __('No. Telefon') }}</th>
                            <th class="th-pwd">{{ __('Status Kata Laluan') }}</th>
                        @endif
                        @if($hasStudentActions)<th class="th-actions">{{ __('Tindakan') }}</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td class="td-student">
                                <div class="student-identity">
                                    <span class="student-avatar" aria-hidden="true">
                                        {{ strtoupper(substr(trim($student->full_name), 0, 2)) }}
                                        @if($student->photo ?? null)
                                            <img src="{{ asset('storage/' . ltrim($student->photo, '/')) }}" alt="" width="32" height="32" loading="lazy" decoding="async" onerror="this.remove()">
                                        @endif
                                    </span>
                                    <span class="student-name-block">
                                        <span class="student-name">{{ $student->full_name }}</span>
                                        @php($photoStatus = $student->profile_photo_status ?? (filled($student->photo ?? null) ? 'legacy' : 'missing'))
                                        @if(filled($student->photo ?? null))
                                            <small style="display:inline-flex;margin-top:4px;padding:3px 8px;border-radius:999px;border:1px solid var(--border);font-size:.68rem;font-weight:800;text-transform:uppercase;color:var(--text-muted);">
                                                {{ $photoStatus === 'approved' ? __('Photo approved') : ($photoStatus === 'pending' ? __('Photo pending review') : __('Photo not approved')) }}
                                            </small>
                                        @else
                                            <small style="display:inline-flex;margin-top:4px;padding:3px 8px;border-radius:999px;border:1px solid var(--border);font-size:.68rem;font-weight:800;text-transform:uppercase;color:var(--text-muted);">
                                                {{ __('No approved photo') }}
                                            </small>
                                        @endif
                                    </span>
                                </div>
                            </td>
                            <td class="td-matric"><span class="matric-pill">{{ $student->matric_no ?: '-' }}</span></td>
                            @if($canViewSensitiveStudents)<td class="td-ic"><span class="ic-pill">{{ maskIdentityNumber($student->ic_no) }}</span></td>@endif
                            <td class="td-program"><span class="prog-pill">{{ $student->program ?: '-' }}</span></td>
                            @if($canViewSensitiveStudents)
                                <td class="td-phone">{{ $student->phone ?: '-' }}</td>
                                <td class="td-pwd">
                                    @if((int) $student->has_custom_password === 1)
                                        <span class="pwd-badge pwd-custom">{{ __('Custom Password') }}</span>
                                    @else
                                        <span class="pwd-badge pwd-default">{{ __('Default IC') }}</span>
                                    @endif
                                </td>
                            @endif
                            @if($hasStudentActions)<td class="td-actions">
                                <div class="actions-cell student-actions-cell">
                                    @if($canViewSensitiveStudents)
                                        <a class="stu-btn stu-btn-view" href="{{ route('admin.students.show', $student->id) }}" title="{{ __('View Profile') }}">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                            <span>{{ __('View Profile') }}</span>
                                        </a>
                                    @endif
                                    @if($canManageStudents)
                                        <a class="stu-btn stu-btn-edit" href="{{ route('admin.students.edit', $student->id) }}" title="{{ __('Edit') }}">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            <span>{{ __('Edit') }}</span>
                                        </a>
                                        @if(filled($student->photo) && ($student->profile_photo_status ?? null) !== 'approved')
                                            <form method="POST" action="{{ route('admin.students.photos.approve', $student->id) }}" style="margin:0;display:inline-flex;"
                                                data-confirm-title="{{ __('Approve Profile Photo') }}"
                                                data-confirm-message="{{ __('Approve this as the student official matric/profile photo?') }}"
                                                data-confirm-action="{{ __('Approve Photo') }}">
                                                @csrf
                                                @method('PATCH')
                                                <button class="stu-btn stu-btn-view" type="submit" title="{{ __('Approve Photo') }}">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
                                                    <span>{{ __('Approve Photo') }}</span>
                                                </button>
                                            </form>
                                        @endif
                                        @if(filled($student->photo))
                                            <form method="POST" action="{{ route('admin.students.photos.reject', $student->id) }}" style="margin:0;display:inline-flex;"
                                                data-confirm-title="{{ __('Tolak Gambar Profil') }}"
                                                data-confirm-message="{{ __('Adakah anda pasti mahu menolak dan memadam gambar profil pelajar ini? Pelajar akan diminta memuat naik gambar kad matrik formal baharu.') }}"
                                                data-confirm-action="{{ __('Tolak Gambar') }}"
                                                data-confirm-tone="danger">
                                                @csrf
                                                @method('DELETE')
                                                <button class="stu-btn stu-btn-warn" type="submit" title="{{ __('Tolak Foto') }}">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                                    <span>{{ __('Tolak Foto') }}</span>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.students.reset-password', $student->id) }}" style="margin:0;display:inline-flex;"
                                            data-confirm-title="{{ __('Reset password') }}"
                                            data-confirm-message="{{ __('Reset this student password to NRIC?') }}"
                                            data-confirm-action="{{ __('Reset Password') }}">
                                            @csrf
                                            <button class="stu-btn stu-btn-key" type="submit" title="{{ __('Reset password to NRIC') }}">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                                <span>{{ __('Reset Password') }}</span>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.students.destroy', $student->id) }}" style="margin:0;display:inline-flex;"
                                            data-confirm-title="{{ __('Delete student') }}"
                                            data-confirm-message="{{ __('Delete this student record?') }}"
                                            data-confirm-action="{{ __('Delete') }}"
                                            data-confirm-tone="danger">
                                            @csrf
                                            @method('DELETE')
                                            <button class="stu-btn stu-btn-danger" type="submit" title="{{ __('Delete') }}">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                <span>{{ __('Delete') }}</span>
                                            </button>
                                        </form>
                                    @endif
                                    @if($canManageCompletionBypass)
                                        <form method="POST" action="{{ route('admin.students.profile-completion-bypass', $student->id) }}" style="margin:0;display:inline-flex;"
                                            data-confirm-title="{{ (bool) ($student->profile_completion_bypass ?? false) ? __('Require Profile Completion') : __('Allow Incomplete Profile Access') }}"
                                            data-confirm-message="{{ (bool) ($student->profile_completion_bypass ?? false) ? __('This student will again be required to complete profile and scholarship status information before using the system.') : __('This student can use the system without completing profile and scholarship status information.') }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="stu-btn {{ (bool) ($student->profile_completion_bypass ?? false) ? 'stu-btn-warn' : 'stu-btn-view' }}" type="submit" title="{{ __('Toggle profile completion requirement') }}">
                                                <span>{{ (bool) ($student->profile_completion_bypass ?? false) ? __('Require Profile') : __('Allow Incomplete Access') }}</span>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.students.blacklist', $student->id) }}" style="margin:0;display:inline-flex;"
                                            data-confirm-title="{{ (bool) ($student->is_blacklisted ?? false) ? __('Remove Student Blacklist') : __('Blacklist Student') }}"
                                            data-confirm-message="{{ (bool) ($student->is_blacklisted ?? false) ? __('This student can sign in and use the system again.') : __('This immediately blocks the student from signing in and revokes active sessions. Their records will remain.') }}"
                                            data-confirm-tone="{{ (bool) ($student->is_blacklisted ?? false) ? 'default' : 'danger' }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="stu-btn {{ (bool) ($student->is_blacklisted ?? false) ? 'stu-btn-view' : 'stu-btn-danger' }}" type="submit" title="{{ __('Toggle student blacklist') }}">
                                                <span>{{ (bool) ($student->is_blacklisted ?? false) ? __('Unblacklist') : __('Blacklist') }}</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>@endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ $canViewSensitiveStudents ? 7 : 3 }}" style="text-align:center;color:#7a6555;padding:2rem 1rem;">{{ __('Tiada rekod pelajar.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
        <div class="student-pagination" style="margin-top:14px;padding:0 1rem 1rem;">{{ $students->onEachSide(1)->links('vendor.pagination.myhep') }}</div>
    </div>
</div>
@endsection


