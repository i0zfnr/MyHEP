@extends('layouts.app')

@section('title', 'Senarai Pelajar')

@push('styles')
<style>
    .page-body > .student-list-wrap {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
    }
    .card { background:#fff; border:1px solid #ede4d9; border-radius:12px; overflow:hidden; }
    .stats { display:grid; grid-template-columns:1fr; gap:10px; margin-bottom:12px; }
    @media (min-width: 900px) { .stats { grid-template-columns:repeat(3,1fr); } }
    .stat { background:#fff; border:1px solid #ede4d9; border-radius:12px; padding:12px 14px; }
    .stat-link { text-decoration:none; display:block; transition:transform .15s, box-shadow .15s; }
    .stat-link:hover { transform:translateY(-1px); box-shadow:0 6px 18px rgba(164,141,120,.12); }
    .stat-label { font-size:11px; color:#7a6555; text-transform:uppercase; letter-spacing:.05em; margin-bottom:4px; }
    .stat-value { font-size:26px; font-weight:700; color:#2d1f14; line-height:1; }
    .head { padding:12px 16px; border-bottom:1px solid #ede4d9; display:flex; justify-content:space-between; align-items:center; gap:10px; }
    .filters { padding: 12px 16px; border-bottom:1px solid #ede4d9; background:#fcfaf8; }
    .filter-grid { display:grid; grid-template-columns:1fr; gap:8px; }
    @media (min-width: 900px) { .filter-grid { grid-template-columns: 1.4fr 1fr 1.2fr 1fr auto; } }
    .filters input, .filters select { width:100%; border:1px solid #e5d8c8; border-radius:8px; padding:8px 10px; font-size:13px; background:#fff; }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:8px 10px; border-bottom:1px solid #f0e7dc; font-size:12px; text-align:left; vertical-align:middle; }
    th { font-size:11px; text-transform:uppercase; color:#7a6555; letter-spacing:.05em; background:#faf7f4; }
    .btn { display:inline-block; border:1px solid #cbb9a4; background:#fff; color:#8a7362; border-radius:8px; padding:7px 10px; text-decoration:none; font-weight:600; font-size:12px; cursor:pointer; }
    .btn-danger { border-color:#fecaca; color:#b91c1c; background:#fef2f2; }
    .btn-warn { border-color:#fed7aa; color:#b45309; background:#fff7ed; }
    .pwd-badge { display:inline-block; border-radius:99px; padding:.2rem .6rem; font-size:11px; font-weight:700; border:1px solid #ede4d9; }
    .pwd-default { background:#fff7ed; color:#b45309; border-color:#fed7aa; }
    .pwd-custom { background:#e7f3f3; color:#28686c; border-color:#b9ddde; }
    .ok { margin-bottom:12px; background:#e7f3f3; border:1px solid #b9ddde; color:#1f5559; border-radius:8px; padding:10px; font-size:13px; }
    .err { margin-bottom:12px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:8px; padding:10px; font-size:13px; }
    .actions-cell { display:flex; gap:6px; flex-wrap:wrap; }
    .import-panel { margin-bottom:12px; padding:14px 16px; }
    .import-grid { display:grid; grid-template-columns:1fr; gap:10px; align-items:end; }
    @media (min-width: 900px) { .import-grid { grid-template-columns:1.5fr auto; } }
    .import-panel label { display:block; margin-bottom:6px; font-size:13px; font-weight:700; color:#7a6555; }
    .import-panel input[type=file] { width:100%; border:1px solid #e5d8c8; border-radius:10px; padding:8px; background:#fffdfb; }
    .import-hint { margin-top:8px; color:#7a6555; font-size:12px; line-height:1.55; }
    .import-summary { display:grid; grid-template-columns:repeat(2, minmax(0,1fr)); gap:8px; margin-top:12px; }
    @media (min-width: 760px) { .import-summary { grid-template-columns:repeat(4, minmax(0,1fr)); } }
    .import-summary div { border:1px solid #eadfce; border-radius:10px; padding:10px; background:#fcfaf8; }
    .import-summary span { display:block; font-size:11px; text-transform:uppercase; font-weight:800; color:#7a6555; }
    .import-summary strong { display:block; margin-top:3px; font-size:18px; color:#2d1f14; }
    .error-list { margin:10px 0 0; padding-left:18px; color:#991b1b; font-size:12px; line-height:1.5; }
    .bulk-delete { margin-bottom:12px; border:1px solid #fecaca; border-radius:12px; background:#fff7f7; overflow:hidden; }
    .bulk-delete summary { padding:11px 14px; color:#991b1b; font-size:13px; font-weight:800; cursor:pointer; }
    .bulk-delete form { display:flex; gap:8px; align-items:center; padding:0 14px 14px; }
    .bulk-delete input { flex:1; }
    .student-name { font-weight:700; color:var(--se-text); }
    .student-identity { display:flex; align-items:center; gap:.7rem; min-width:0; }
    .student-avatar { position:relative; flex:0 0 auto; display:grid; width:34px; height:34px; place-items:center; overflow:hidden; border:1px solid var(--admin-line); border-radius:50%; background:linear-gradient(135deg,#f1e4d5,#c7a98b); color:#513b2a; font-size:.7rem; font-weight:900; letter-spacing:.03em; }
    .student-avatar img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; background:#fff; }
    .student-name-block { min-width:0; }
    .student-sub { display:none; margin-top:3px; color:var(--se-text-soft); font-size:11px; line-height:1.35; }
    .matric-cell { color:var(--se-text); white-space:nowrap; font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
    html[data-theme="dark"] .students-table .student-name { color:#f7f1e8 !important; }
    html[data-theme="dark"] .students-table .student-sub { color:#cbbba9 !important; }
    html[data-theme="dark"] .students-table .matric-cell { color:#f0e3d1 !important; }
        /* Admin UX Identity v2 */
    :root {
        --admin-ink: #241a12;
        --admin-muted: #7b6757;
        --admin-line: #eadfce;
        --admin-soft: #f8f2ea;
        --admin-accent: #8f6f52;
        --admin-accent-2: #c7a98b;
        --admin-glow: rgba(143, 111, 82, 0.18);
    }
    body {
        background:
            radial-gradient(1200px 480px at -10% -15%, #efe3d6 0%, transparent 55%),
            radial-gradient(900px 360px at 110% -10%, #f4eadf 0%, transparent 52%),
            linear-gradient(180deg, #faf7f2 0%, #f6f1ea 100%);
    }
    .wrap {
        width: 100%;
        max-width: none;
        position: relative;
        isolation: isolate;
    }
    .wrap > * + * {
        margin-top: 1rem;
    }
    .card,
    .panel {
        border: 1px solid var(--admin-line);
        border-radius: 16px;
        background: linear-gradient(180deg, #fff 0%, #fffdfa 100%);
        box-shadow:
            0 1px 2px rgba(36, 26, 18, 0.07),
            0 10px 26px rgba(61, 46, 34, 0.06);
        overflow: hidden;
        transition: transform 220ms ease, box-shadow 220ms ease, border-color 220ms ease;
    }
    .card:hover,
    .panel:hover {
        transform: translateY(-2px);
        border-color: #dfccb6;
        box-shadow:
            0 4px 14px rgba(36, 26, 18, 0.10),
            0 18px 34px rgba(61, 46, 34, 0.10);
    }
    .head,
    .card h2 {
        position: relative;
        border-bottom: 1px solid var(--admin-line);
        background:
            linear-gradient(180deg, #fff 0%, #fbf5ee 100%);
    }
    .head::before,
    .card h2::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, var(--admin-accent) 0%, var(--admin-accent-2) 100%);
    }
    .head h1,
    .card h2 {
        color: var(--admin-ink);
        letter-spacing: 0.01em;
    }
    .btn {
        border-radius: 10px;
        border: 1px solid #ceb79f;
        background: linear-gradient(180deg, #ffffff 0%, #f9f3ec 100%);
        color: #6e5745;
        font-weight: 700;
        transition: transform 170ms ease, box-shadow 170ms ease, background-color 170ms ease, border-color 170ms ease, color 170ms ease;
    }
    .btn:hover {
        transform: translateY(-1px);
        border-color: #bb9c7d;
        color: #5d4737;
        box-shadow: 0 8px 18px rgba(98, 74, 53, 0.14);
    }
    .btn:focus-visible {
        outline: none;
        box-shadow: 0 0 0 4px var(--admin-glow);
    }
    .btn-primary {
        border-color: #7f6249 !important;
        background: linear-gradient(135deg, #8f6f52 0%, #c0a183 100%) !important;
        color: #fff !important;
    }
    .btn-primary:hover {
        border-color: #6f533e !important;
        background: linear-gradient(135deg, #7a5e46 0%, #b08f70 100%) !important;
    }
    input,
    select,
    textarea {
        border-color: #dfceb9 !important;
        background: #fffdfb;
        color: var(--admin-ink);
        transition: border-color 150ms ease, box-shadow 150ms ease, background-color 150ms ease;
    }
    input::placeholder,
    textarea::placeholder {
        color: #9e8a78;
    }
    input:focus,
    select:focus,
    textarea:focus {
        border-color: #b69372 !important;
        box-shadow: 0 0 0 4px rgba(182, 147, 114, 0.19);
        outline: none;
        background: #fff;
    }
    .filters {
        background: linear-gradient(180deg, #fffdfb 0%, #faf4ed 100%);
        border-top: 1px solid #efe4d8;
        border-bottom: 1px solid #efe4d8;
    }
    table {
        width: 100%;
    }
    th {
        background: #f9f1e8 !important;
        color: #7b6757 !important;
        letter-spacing: 0.06em;
    }
    table tbody tr {
        transition: background-color 140ms ease;
    }
    table tbody tr:hover {
        background: #fcf7f1;
    }
    .ok,
    .msg-ok {
        border-radius: 12px;
        border-color: #b8e5c7 !important;
    }
    .err,
    .error,
    .msg-err {
        border-radius: 12px;
    }
    @media (max-width: 980px) {
        .head {
            align-items: flex-start;
        }
        .head > div,
        .head form {
            width: 100%;
        }
        .head .btn {
            width: auto;
        }
    }
    @media (max-width: 760px) {
        .wrap { width:100%; }
        .stats { grid-template-columns:repeat(3, minmax(0,1fr)) !important; gap:6px; margin-bottom:8px; }
        .stat { padding:8px 9px; border-radius:10px; }
        .stat-label { font-size:9px; line-height:1.2; }
        .stat-value { font-size:18px; }
        .import-panel { padding:10px; margin-bottom:8px; }
        .import-hint { display:none; }
        .head { padding:9px 10px; }
        .head h1 { font-size:16px !important; }
        .head .btn { padding:7px 9px; }
        .filters { padding:9px 10px; }
        .filter-grid { gap:7px; }
        .filters input, .filters select { padding:7px 9px; font-size:12px; }
        .student-table-wrap { overflow-x:hidden !important; }
        .students-table { table-layout:fixed; min-width:0; }
        .students-table th, .students-table td { padding:8px 9px !important; font-size:12px !important; }
        .students-table th:nth-child(1), .students-table td:nth-child(1) { width:48%; }
        .students-table th:nth-child(2), .students-table td:nth-child(2) { width:32%; }
        .students-table th:nth-child(7), .students-table td:nth-child(7) { width:20%; }
        .student-sub { display:block; }
        .student-avatar { width:30px; height:30px; }
        .col-ic, .col-phone, .col-password, .col-program { display:none; }
        .matric-cell { white-space:normal; overflow-wrap:anywhere; font-size:11px !important; }
        .actions-cell { justify-content:flex-end; }
        .actions-cell .btn { padding:6px 8px; font-size:11px; }
        .actions-cell form, .actions-cell .manage-action { display:none; }
        .student-pagination { margin-top:8px !important; }
        .student-pagination .se-pagination { padding:8px 9px; }
        .student-pagination .se-pagination-summary { display:none; }
        .student-pagination .se-pagination-controls { justify-content:space-between; width:100%; }
        .student-pagination .se-pagination-pages { display:none; }
        .student-pagination .se-page-nav { min-height:34px; padding:7px 10px; font-size:12px; }
    }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Pelajar') }}</h2>
@endsection

@section('content')
<div class="wrap student-list-wrap">
    @php($canViewSensitiveStudents = adminCan('students.sensitive'))
    @php($canExportStudents = adminCan('students.export'))
    @php($canManageStudents = adminCan('students.manage'))
    @php($hasStudentActions = $canViewSensitiveStudents || $canManageStudents)
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    @if(session('auth_user.admin_role') === 'system_admin')
        <details class="bulk-delete">
            <summary>Danger zone: delete every student record</summary>
            <form method="POST" action="{{ route('admin.students.destroy-all') }}" data-confirm-title="Delete all student data" data-confirm-message="This permanently deletes every student, their documents, photos, sessions, scholarship, discipline, and movement records. This cannot be undone." data-confirm-action="Delete All Students" data-confirm-tone="danger">
                @csrf
                @method('DELETE')
                <input name="confirmation" required autocomplete="off" placeholder="Type DELETE ALL STUDENTS to confirm" aria-label="Confirmation">
                <button class="btn btn-danger" type="submit">Delete All Students</button>
            </form>
        </details>
    @endif

    @if($canManageStudents)
        <div class="card import-panel">
            <form method="POST" action="{{ route('admin.students.import') }}" enctype="multipart/form-data">
                @csrf
                <div class="import-grid">
                    <div>
                        <label for="student_file">{{ __('Import & Update Data Pelajar') }}</label>
                        <input id="student_file" type="file" name="student_file" accept=".csv,.txt,.xlsx" required>
                        <div class="import-hint">
                            Upload satu senarai pelajar terkini dalam CSV atau Excel sehingga 50 MB. Pelajar baharu akan ditambah, manakala pelajar yang sepadan melalui No. Kad Pengenalan atau No. Matrik akan dikemaskini, termasuk Semester dan Sesi Akademik.
                            Header yang disokong termasuk Nama Pelajar, No Kad Pengenalan, No Matrik, Nama Program, Telefon, Email, Semester dan Sesi Akademik. Jika No Matrik kosong, sistem akan simpan sebagai kosong.
                        </div>
                    </div>
                    <button class="btn btn-primary" type="submit">{{ __('Import & Update') }}</button>
                </div>
            </form>

            @if(session('import_result'))
                @php($result = session('import_result'))
                <div class="import-summary">
                    <div><span>Total rows</span><strong>{{ $result['total_rows'] ?? 0 }}</strong></div>
                    <div><span>Created</span><strong>{{ $result['students_created'] ?? 0 }}</strong></div>
                    <div><span>Updated</span><strong>{{ $result['students_updated'] ?? 0 }}</strong></div>
                    <div><span>Skipped</span><strong>{{ $result['skipped'] ?? 0 }}</strong></div>
                </div>
                @if(!empty($result['errors']))
                    <ul class="error-list">
                        @foreach($result['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            @endif
        </div>
    @endif

    <div class="stats">
        <a class="stat stat-link" href="{{ route('admin.students.index', array_merge(request()->except('page', 'password_status'), ['password_status' => ''])) }}">
            <div class="stat-label">{{ __('Total Pelajar') }}</div>
            <div class="stat-value">{{ $studentStats['total'] }}</div>
        </a>
        @if($canViewSensitiveStudents)
            <a class="stat stat-link" href="{{ route('admin.students.index', array_merge(request()->except('page'), ['password_status' => 'default'])) }}">
                <div class="stat-label">{{ __('Default IC') }}</div>
                <div class="stat-value">{{ $studentStats['default_ic'] }}</div>
            </a>
            <a class="stat stat-link" href="{{ route('admin.students.index', array_merge(request()->except('page'), ['password_status' => 'custom'])) }}">
                <div class="stat-label">{{ __('Custom Password') }}</div>
                <div class="stat-value">{{ $studentStats['custom_password'] }}</div>
            </a>
        @endif
    </div>

    <div class="card">
        <div class="head">
            <h1 style="margin:0;font-size:20px;">{{ __('Senarai Pelajar') }}</h1>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a class="btn" href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                @if($canExportStudents)
                    <a class="btn" href="{{ route('admin.students.export', request()->query()) }}">{{ __('Export CSV') }}</a>
                @endif
                @if($canManageStudents)
                    <a class="btn" href="{{ route('admin.students.create') }}">{{ __('Tambah Pelajar') }}</a>
                @endif
            </div>
        </div>

        <div class="filters" data-filter-sheet data-filter-title="{{ __('Student filters') }}">
            <form method="GET" action="{{ route('admin.students.index') }}" data-live-filter-form data-live-filter-delay="350">
                <div class="filter-grid">
                    <div><input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ $canViewSensitiveStudents ? __('Cari nama / IC') : __('Cari nama') }}"></div>
                    <div><input type="text" name="matric_no" value="{{ $filters['matric_no'] ?? '' }}" placeholder="{{ __('Cari no matrik') }}"></div>
                    <div><input type="text" name="program" value="{{ $filters['program'] ?? '' }}" placeholder="{{ __('Cari program') }}"></div>
                    @if($canViewSensitiveStudents)
                        <div>
                            <select name="password_status">
                                <option value="">{{ __('Semua status kata laluan') }}</option>
                                <option value="default" {{ ($filters['password_status'] ?? '') === 'default' ? 'selected' : '' }}>{{ __('Default IC') }}</option>
                                <option value="custom" {{ ($filters['password_status'] ?? '') === 'custom' ? 'selected' : '' }}>{{ __('Custom Password') }}</option>
                            </select>
                        </div>
                    @endif
                    <span data-live-filter-status aria-live="polite" style="font-size:.75rem;color:var(--text-muted);"></span>
                </div>
            </form>
        </div>

        <div data-live-filter-results>
        <div class="student-table-wrap" style="overflow-x:auto;">
            <table class="students-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>No Matrik</th>
                        @if($canViewSensitiveStudents)<th class="col-ic">IC</th>@endif
                        <th class="col-program">Program</th>
                        @if($canViewSensitiveStudents)
                            <th class="col-phone">Telefon</th>
                            <th class="col-password">Status Kata Laluan</th>
                        @endif
                        @if($hasStudentActions)<th>Tindakan</th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td>
                                <div class="student-identity">
                                    <span class="student-avatar" aria-hidden="true">
                                        {{ strtoupper(substr(trim($student->full_name), 0, 2)) }}
                                        @if($student->photo ?? null)
                                            <img src="{{ asset('storage/' . ltrim($student->photo, '/')) }}" alt="" onerror="this.remove()">
                                        @endif
                                    </span>
                                    <span class="student-name-block">
                                        <span class="student-name">{{ $student->full_name }}</span>
                                        <span class="student-sub">{{ $student->program }}@if($canViewSensitiveStudents)<br>{{ maskIdentityNumber($student->ic_no) }}@endif</span>
                                    </span>
                                </div>
                            </td>
                            <td class="matric-cell">{{ $student->matric_no ?: '-' }}</td>
                            @if($canViewSensitiveStudents)<td class="col-ic">{{ maskIdentityNumber($student->ic_no) }}</td>@endif
                            <td class="col-program">{{ $student->program }}</td>
                            @if($canViewSensitiveStudents)
                                <td class="col-phone">{{ $student->phone ?: '-' }}</td>
                                <td class="col-password">
                                    @if((int) $student->has_custom_password === 1)
                                        <span class="pwd-badge pwd-custom">Custom Password</span>
                                    @else
                                        <span class="pwd-badge pwd-default">Default IC</span>
                                    @endif
                                </td>
                            @endif
                            @if($hasStudentActions)<td>
                                <div class="actions-cell">
                                    @if($canViewSensitiveStudents)
                                        <a class="btn" href="{{ route('admin.students.show', $student->id) }}">View Profile</a>
                                    @endif
                                    @if($canManageStudents)
                                        <a class="btn manage-action" href="{{ route('admin.students.edit', $student->id) }}">Edit</a>
                                        <form method="POST" action="{{ route('admin.students.reset-password', $student->id) }}" style="margin:0;"
                                            data-confirm-title="{{ __('Reset password') }}"
                                            data-confirm-message="{{ __('Reset this student password to NRIC?') }}"
                                            data-confirm-action="{{ __('Reset Password') }}">
                                            @csrf
                                            <button class="btn btn-warn" type="submit">Reset Password</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.students.destroy', $student->id) }}" style="margin:0;"
                                            data-confirm-title="{{ __('Delete student') }}"
                                            data-confirm-message="{{ __('Delete this student record?') }}"
                                            data-confirm-action="{{ __('Delete') }}"
                                            data-confirm-tone="danger">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-danger" type="submit">Delete</button>
                                        </form>
                                    @endif
                                </div>
                            </td>@endif
                        </tr>
                    @empty
                        <tr><td colspan="{{ $canViewSensitiveStudents ? 7 : 3 }}" style="text-align:center;color:#7a6555;">Tiada rekod pelajar.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>
        <div class="student-pagination" style="margin-top:14px;">{{ $students->onEachSide(1)->links('vendor.pagination.studentedge') }}</div>
        </div>
    </div>
</div>
@endsection


