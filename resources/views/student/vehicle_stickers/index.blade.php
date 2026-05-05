@extends('layouts.app')

@section('title', __('Permohonan Sticker Kenderaan'))

@push('styles')
<style>
    .wrap { max-width: 1050px; margin: 0 auto; }
    .quick { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px; }
    .card { background:#fff; border:1px solid #ede4d9; border-radius:12px; overflow:hidden; margin-bottom:12px; }
    .head { padding:12px 16px; border-bottom:1px solid #ede4d9; }
    .body { padding:12px 16px; }
    .grid { display:grid; grid-template-columns:1fr; gap:10px; }
    @media (min-width: 900px) { .grid { grid-template-columns:1fr 1fr auto; } }
    label { font-size:13px; font-weight:600; color:#7a6555; display:block; margin-bottom:6px; }
    input, select { width:100%; border:1px solid #e5d8c8; border-radius:8px; padding:8px 10px; font-size:13px; }
    .doc-grid { display:grid; grid-template-columns:1fr; gap:10px; margin-top:10px; }
    @media (min-width: 900px) { .doc-grid { grid-template-columns:1fr 1fr 1fr; } }
    .doc-link { font-size:12px; color:#8a7362; text-decoration:underline; }
    .doc-thumb { margin-top:6px; width:110px; height:78px; border:1px solid #ede4d9; border-radius:8px; object-fit:cover; }
    .btn { display:inline-block; border:1px solid #cbb9a4; background:#fff; color:#8a7362; border-radius:8px; padding:8px 12px; text-decoration:none; font-weight:600; font-size:13px; cursor:pointer; }
    .btn-primary { border:none; color:#fff; background:linear-gradient(135deg,#A48D78,#CBB9A4); }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:10px 12px; border-bottom:1px solid #f0e7dc; font-size:13px; text-align:left; }
    th { font-size:11px; text-transform:uppercase; color:#7a6555; letter-spacing:.05em; background:#faf7f4; }
    .status-badge { display:inline-block; border-radius:99px; padding:.2rem .6rem; font-size:11px; font-weight:700; text-transform:uppercase; border:1px solid #ede4d9; }
    .status-pending { background:#fff7ed; color:#b45309; border-color:#fed7aa; }
    .status-approved, .status-confirmed { background:#f0fdf4; color:#15803d; border-color:#bbf7d0; }
    .status-rejected, .status-unpaid { background:#fef2f2; color:#b91c1c; border-color:#fecaca; }
    .ok { margin-bottom:12px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:8px; padding:10px; font-size:13px; }
    .err { margin-bottom:12px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:8px; padding:10px; font-size:13px; }
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
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Sticker Kenderaan') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="quick">
        <a class="btn" href="{{ route('student.dashboard') }}">{{ __('Kembali ke Index') }}</a>
        <a class="btn" href="{{ route('student.offenses.index') }}">{{ __('Mohon Bayaran Denda') }}</a>
        <a class="btn" href="{{ route('student.rules.index') }}">{{ __('Lihat Peraturan') }}</a>
        <a class="btn" href="{{ route('student.scholarships.index') }}">{{ __('Portal Scholarship') }}</a>
    </div>

    <div class="card">
        <div class="head"><strong>{{ __('Permohonan Baru') }}</strong></div>
        <div class="body">
            <form method="POST" action="{{ route('student.vehicle-stickers.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid">
                    <div>
                        <label for="vehicle_no">{{ __('No. Kenderaan') }}</label>
                        <input id="vehicle_no" type="text" name="vehicle_no" value="{{ old('vehicle_no') }}" placeholder="{{ __('Contoh:') }} TBA1234" required>
                    </div>
                    <div>
                        <label for="vehicle_type">{{ __('Jenis Kenderaan') }}</label>
                        <select id="vehicle_type" name="vehicle_type" required>
                            <option value="">{{ __('Pilih jenis') }}</option>
                            @foreach(['Motosikal', 'Kereta', 'Lain-lain'] as $type)
                                <option value="{{ $type }}" {{ old('vehicle_type') === $type ? 'selected' : '' }}>{{ __($type) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex; align-items:end;">
                        <button class="btn btn-primary" type="submit">{{ __('Hantar Permohonan') }}</button>
                    </div>
                </div>
                <div class="doc-grid">
                    <div>
                        <label for="license_card_image">{{ __('Gambar Kad Lesen') }}</label>
                        <input id="license_card_image" type="file" name="license_card_image" accept="image/jpeg,image/png,image/webp" required>
                    </div>
                    <div>
                        <label for="parent_permission_image">{{ __('Surat Kebenaran Ibu Bapa') }}</label>
                        <input id="parent_permission_image" type="file" name="parent_permission_image" accept="image/jpeg,image/png,image/webp" required>
                    </div>
                    <div>
                        <label for="vehicle_plate_image">{{ __('Gambar Kenderaan (Nombor Plat Jelas)') }}</label>
                        <input id="vehicle_plate_image" type="file" name="vehicle_plate_image" accept="image/jpeg,image/png,image/webp" required>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="head"><strong>{{ __('Rekod Permohonan Saya') }}</strong></div>
        <div style="overflow-x:auto;">
            <table>
                <thead><tr><th>{{ __('No. Kenderaan') }}</th><th>{{ __('Jenis') }}</th><th>{{ __('Dokumen') }}</th><th>{{ __('Status') }}</th><th>{{ __('Disemak Oleh') }}</th><th>{{ __('Tarikh') }}</th></tr></thead>
                <tbody>
                    @forelse($applications as $app)
                        <tr>
                            <td>{{ $app->vehicle_no }}</td>
                            <td>{{ $app->vehicle_type }}</td>
                            <td>
                                @if($app->license_card_path)
                                    <a class="doc-link" href="{{ asset('storage/' . $app->license_card_path) }}" target="_blank">{{ __('Kad Lesen') }}</a><br>
                                @endif
                                @if($app->parent_permission_path)
                                    <a class="doc-link" href="{{ asset('storage/' . $app->parent_permission_path) }}" target="_blank">{{ __('Surat Ibu Bapa') }}</a><br>
                                @endif
                                @if($app->vehicle_photo_path)
                                    <a class="doc-link" href="{{ asset('storage/' . $app->vehicle_photo_path) }}" target="_blank">{{ __('Gambar Kenderaan') }}</a>
                                @endif
                            </td>
                            <td><span class="status-badge status-{{ strtolower($app->status) }}">{{ __($app->status) }}</span></td>
                            <td>{{ $app->approved_by_name ?: '-' }}</td>
                            <td>{{ $app->created_at ? \Illuminate\Support\Carbon::parse($app->created_at)->format('Y-m-d') : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;color:#7a6555;">{{ __('Tiada rekod permohonan sticker.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:14px;">{{ $applications->links() }}</div>
</div>
@endsection

