@extends('layouts.app')

@section('title', __('Senarai Kesalahan'))

@push('styles')
<style>
    .wrap { max-width: 1100px; margin: 0 auto; }
    .card { background:#fff; border:1px solid #ede4d9; border-radius:12px; overflow:hidden; }
    .head { padding:12px 16px; border-bottom:1px solid #ede4d9; display:flex; justify-content:space-between; align-items:center; gap:10px; }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:10px 12px; border-bottom:1px solid #f0e7dc; font-size:13px; text-align:left; }
    th { font-size:11px; text-transform:uppercase; color:#7a6555; letter-spacing:.05em; background:#faf7f4; }
    .status { display:inline-block; border-radius:99px; padding:.2rem .6rem; font-size:11px; font-weight:700; text-transform:uppercase; border:1px solid #ede4d9; }
    .unpaid { background:#fef2f2; color:#b91c1c; border-color:#fecaca; }
    .applied { background:#fff7ed; color:#b45309; border-color:#fed7aa; }
    .paid { background:#e7f3f3; color:#28686c; border-color:#b9ddde; }
    .btn { display:inline-block; border:1px solid #cbb9a4; background:#fff; color:#8a7362; border-radius:8px; padding:8px 12px; text-decoration:none; font-weight:600; font-size:13px; cursor:pointer; }
    .btn-success { border-color:#b9ddde; color:#1f5559; background:#e7f3f3; }
    .btn-danger { border-color:#fecaca; color:#b91c1c; background:#fef2f2; }
    .ok { margin-bottom:12px; background:#e7f3f3; border:1px solid #b9ddde; color:#1f5559; border-radius:8px; padding:10px; font-size:13px; }
    .err { margin-bottom:12px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:8px; padding:10px; font-size:13px; }
    .actions-cell { display:flex; gap:6px; flex-wrap:wrap; }
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
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Senarai Kesalahan Pelajar') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="card">
        <div class="head">
            <h1 style="margin:0;font-size:20px;">{{ __('Senarai Kesalahan Pelajar') }}</h1>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a class="btn" href="{{ route('admin.dashboard') }}">{{ __('Dashboard') }}</a>
                <a class="btn" href="{{ route('admin.offenses.export', request()->query()) }}">{{ __('Export CSV') }}</a>
                <a class="btn" href="{{ route('admin.offenses.create') }}">{{ __('Daftar Kesalahan') }}</a>
            </div>
        </div>
        <div class="filters" data-filter-sheet data-filter-title="{{ __('Offense filters') }}">
            <form method="GET" action="{{ route('admin.offenses.index') }}">
                <div class="filter-grid">
                    <div>
                        <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Cari nama pelajar / matrik / tempat') }}">
                    </div>
                    <div>
                        <select name="status">
                            <option value="">{{ __('Semua status') }}</option>
                            <option value="unpaid" {{ ($filters['status'] ?? '') === 'unpaid' ? 'selected' : '' }}>{{ __('unpaid') }}</option>
                            <option value="applied" {{ ($filters['status'] ?? '') === 'applied' ? 'selected' : '' }}>{{ __('applied') }}</option>
                            <option value="paid" {{ ($filters['status'] ?? '') === 'paid' ? 'selected' : '' }}>{{ __('paid') }}</option>
                        </select>
                    </div>
                    <div>
                        <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div>
                        <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="filter-actions">
                        <button class="btn" type="submit">{{ __('Filter') }}</button>
                        <a class="btn" href="{{ route('admin.offenses.index') }}">{{ __('Reset') }}</a>
                    </div>
                </div>
            </form>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead><tr><th>{{ __('Pelajar') }}</th><th>{{ __('No. Matrik') }}</th><th>{{ __('Tarikh') }}</th><th>{{ __('Masa') }}</th><th>{{ __('Tempat') }}</th><th>{{ __('Bukti') }}</th><th>{{ __('Denda (RM)') }}</th><th>{{ __('Status') }}</th><th>{{ __('Tindakan') }}</th></tr></thead>
                <tbody>
                    @forelse($offenses as $offense)
                        <tr>
                            <td>{{ $offense->student_name }}</td>
                            <td>{{ $offense->matric_no }}</td>
                            <td>{{ $offense->offense_date }}</td>
                            <td>{{ $offense->offense_time }}</td>
                            <td>{{ $offense->place }}</td>
                            <td>
                                @if(($offense->evidence_count ?? 0) > 0)
                                    <a class="btn" href="{{ asset('storage/' . $offense->evidence_photos[0]->photo_path) }}" target="_blank" data-media-viewer data-media-title="{{ __('Evidence Photo') }}" style="padding:6px 10px; font-size:12px;">{{ __('Lihat') }} ({{ $offense->evidence_count }})</a>
                                @endif
                                @if(!empty($offense->payment_receipt?->receipt_path))
                                    <a class="btn" href="{{ asset('storage/' . $offense->payment_receipt->receipt_path) }}" target="_blank" data-media-viewer data-media-title="{{ __('Payment Receipt') }}" style="padding:6px 10px; font-size:12px;">{{ __('View Receipt') }}</a>
                                @endif
                                @if(($offense->evidence_count ?? 0) === 0 && empty($offense->payment_receipt?->receipt_path))
                                    <span style="color:#7a6555;">-</span>
                                @endif
                            </td>
                            <td>{{ number_format((float)$offense->fine_amount, 2) }}</td>
                            <td><span class="status {{ $offense->status }}">{{ __($offense->status) }}</span></td>
                            <td>
                                <div class="actions-cell">
                                    <a class="btn" href="{{ route('admin.offenses.edit', $offense->id) }}">Edit</a>
                                    <a class="btn" href="{{ route('admin.offenses.print', $offense->id) }}" target="_blank">Print</a>
                                    <a class="btn" href="{{ route('admin.offenses.pdf', $offense->id) }}">PDF</a>

                                    @if($offense->status !== 'paid')
                                        <form method="POST" action="{{ route('admin.offenses.mark-paid', $offense->id) }}" style="margin:0;">
                                            @csrf
                                            <button class="btn btn-success" type="submit">Mark Paid</button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('admin.offenses.destroy', $offense->id) }}" style="margin:0;"
                                        data-confirm-title="{{ __('Delete offense') }}"
                                        data-confirm-message="{{ __('Delete this offense record?') }}"
                                        data-confirm-action="{{ __('Delete') }}"
                                        data-confirm-tone="danger">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" style="text-align:center;color:#7a6555;">Tiada rekod kesalahan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:14px;">{{ $offenses->links() }}</div>
</div>
@endsection


