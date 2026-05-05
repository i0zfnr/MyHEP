@extends('layouts.app')

@section('title', 'Permohonan Bayaran Denda')

@push('styles')
<style>
    .wrap { max-width: 1100px; margin: 0 auto; }
    .card { background:#fff; border:1px solid #ede4d9; border-radius:12px; overflow:hidden; }
    .head { padding:12px 16px; border-bottom:1px solid #ede4d9; display:flex; justify-content:space-between; align-items:center; gap:10px; }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:10px 12px; border-bottom:1px solid #f0e7dc; font-size:13px; text-align:left; vertical-align:top; }
    th { font-size:11px; text-transform:uppercase; color:#7a6555; letter-spacing:.05em; background:#faf7f4; }
    .status { display:inline-block; border-radius:99px; padding:.2rem .6rem; font-size:11px; font-weight:700; text-transform:uppercase; border:1px solid #ede4d9; }
    .pending { background:#fff7ed; color:#b45309; border-color:#fed7aa; }
    .approved { background:#f0fdf4; color:#15803d; border-color:#bbf7d0; }
    .rejected { background:#fef2f2; color:#b91c1c; border-color:#fecaca; }
    .btn { display:inline-block; border:1px solid #cbb9a4; background:#fff; color:#8a7362; border-radius:8px; padding:8px 12px; text-decoration:none; font-weight:600; font-size:13px; }
    .btn-primary { border:none; color:#fff; background:linear-gradient(135deg,#A48D78,#CBB9A4); }
    .btn-danger { border-color:#fecaca; color:#b91c1c; }
    .msg-ok { margin-bottom:12px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:8px; padding:10px; font-size:13px; }
    .msg-err { margin-bottom:12px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:8px; padding:10px; font-size:13px; }
    .decision { display:flex; gap:6px; flex-wrap:wrap; }
    .decision input[type="date"] { border:1px solid #e5d8c8; border-radius:8px; padding:6px 8px; }
    .filters { padding: 12px 16px; border-bottom:1px solid #ede4d9; background:#fcfaf8; }
    .filter-grid { display:grid; grid-template-columns:1fr; gap:8px; }
    @media (min-width: 900px) { .filter-grid { grid-template-columns: 2fr 1fr 1fr 1fr auto; } }
    .filters input, .filters select {
        width:100%; border:1px solid #e5d8c8; border-radius:8px; padding:8px 10px; font-size:13px; background:#fff;
    }
    .filter-actions { display:flex; gap:8px; align-items:center; }
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
        width: min(1180px, 100%);
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
        .stats {
            grid-template-columns: 1fr !important;
        }
        th,
        td {
            font-size: 12px !important;
            padding: 9px 10px !important;
        }
    }</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">Permohonan Bayaran Denda</h2>
@endsection

@section('content')
<div class="wrap">
    @if(session('success'))<div class="msg-ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="msg-err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="card">
        <div class="head">
            <h1 style="margin:0;font-size:20px;">Permohonan Bayaran Denda</h1>
            <div style="display:flex; gap:8px; flex-wrap:wrap;"><a class="btn" href="{{ route('admin.dashboard') }}">Dashboard</a><a class="btn" href="{{ route('admin.fine-applications.export', request()->query()) }}">Export CSV</a><a class="btn" href="{{ route('admin.offenses.index') }}">Senarai Kesalahan</a></div>
        </div>
        <div class="filters">
            <form method="GET" action="{{ route('admin.fine-applications.index') }}">
                <div class="filter-grid">
                    <div>
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari nama pelajar / matrik / tempat">
                    </div>
                    <div>
                        <select name="status">
                            <option value="">Semua status</option>
                            <option value="pending" {{ ($filters['status'] ?? '') === 'pending' ? 'selected' : '' }}>{{ __('pending') }}</option>
                            <option value="approved" {{ ($filters['status'] ?? '') === 'approved' ? 'selected' : '' }}>{{ __('approved') }}</option>
                            <option value="rejected" {{ ($filters['status'] ?? '') === 'rejected' ? 'selected' : '' }}>{{ __('rejected') }}</option>
                        </select>
                    </div>
                    <div>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="filter-actions">
                        <button class="btn" type="submit">Filter</button>
                        <a class="btn" href="{{ route('admin.fine-applications.index') }}">Reset</a>
                    </div>
                </div>
            </form>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead><tr><th>Pelajar</th><th>Kesalahan</th><th>Catatan Pelajar</th><th>Status</th><th>Meeting Date</th><th>Tindakan</th></tr></thead>
                <tbody>
                    @forelse($applications as $app)
                        <tr>
                            <td>{{ $app->student_name }}<br><span style="color:#7a6555">{{ $app->matric_no }}</span></td>
                            <td>{{ $app->offense_date }}<br>{{ $app->place }}<br>RM {{ number_format((float)$app->fine_amount,2) }}</td>
                            <td>{{ $app->student_note ?: '-' }}</td>
                            <td><span class="status {{ $app->status }}">{{ __($app->status) }}</span></td>
                            <td>{{ $app->meeting_date ?: '-' }}</td>
                            <td>
                                @if($app->status === 'pending')
                                    <div class="decision">
                                        <form method="POST" action="{{ route('admin.fine-applications.decision', $app->id) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <input type="date" name="meeting_date" required>
                                            <button class="btn btn-primary" type="submit">Lulus</button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.fine-applications.decision', $app->id) }}">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <button class="btn btn-danger" type="submit">Tolak</button>
                                        </form>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align:center;color:#7a6555;">Tiada permohonan bayaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:14px;">{{ $applications->links() }}</div>
</div>
@endsection


