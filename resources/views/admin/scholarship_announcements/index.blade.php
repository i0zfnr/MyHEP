@extends('layouts.app')

@section('title', 'Pengumuman Scholarship')

@push('styles')
<style>
    .wrap { max-width: 1150px; margin: 0 auto; }
    .card { background:#fff; border:1px solid #ede4d9; border-radius:12px; overflow:hidden; }
    .head { padding:12px 16px; border-bottom:1px solid #ede4d9; display:flex; justify-content:space-between; align-items:center; gap:10px; }
    .filters { padding: 12px 16px; border-bottom:1px solid #ede4d9; background:#fcfaf8; }
    .filter-grid { display:grid; grid-template-columns:1fr; gap:8px; }
    @media (min-width: 900px) { .filter-grid { grid-template-columns: 2fr 1fr auto; } }
    .filters input, .filters select { width:100%; border:1px solid #e5d8c8; border-radius:8px; padding:8px 10px; font-size:13px; background:#fff; }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:10px 12px; border-bottom:1px solid #f0e7dc; font-size:13px; text-align:left; vertical-align:top; }
    th { font-size:11px; text-transform:uppercase; color:#7a6555; letter-spacing:.05em; background:#faf7f4; }
    .pill { display:inline-block; border-radius:99px; padding:.2rem .6rem; font-size:11px; font-weight:700; text-transform:uppercase; border:1px solid #ede4d9; }
    .scholarship { background:#e0f2fe; color:#075985; border-color:#bae6fd; }
    .welfare { background:#fff7ed; color:#b45309; border-color:#fed7aa; }
    .general { background:#f5ede6; color:#8a7362; border-color:#cbb9a4; }
    .btn { display:inline-block; border:1px solid #cbb9a4; background:#fff; color:#8a7362; border-radius:8px; padding:8px 12px; text-decoration:none; font-weight:600; font-size:13px; cursor:pointer; }
    .btn-danger { border-color:#fecaca; color:#b91c1c; background:#fef2f2; }
    .ok { margin-bottom:12px; background:#e7f3f3; border:1px solid #b9ddde; color:#1f5559; border-radius:8px; padding:10px; font-size:13px; }
    .err { margin-bottom:12px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:8px; padding:10px; font-size:13px; }
    .actions-cell { display:flex; gap:6px; flex-wrap:wrap; }
    .ann-head-actions { display:flex; gap:8px; flex-wrap:wrap; }
    .ann-table-wrap { overflow-x:auto; }
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
    }
    @media (max-width: 720px) {
        .card {
            overflow: visible;
            border-radius: 18px;
        }
        .head {
            flex-direction: column;
            align-items: stretch;
            padding: 14px;
        }
        .head h1 {
            font-size: 1rem !important;
            line-height: 1.35;
        }
        .ann-head-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
        }
        .ann-head-actions .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 42px;
            padding: .65rem .55rem;
            text-align: center;
        }
        .ann-head-actions .btn:first-child {
            grid-column: 1 / -1;
        }
        .ann-table-wrap {
            overflow: visible;
            padding: 10px;
        }
        .announcement-table,
        .announcement-table tbody {
            display: block;
            width: 100%;
        }
        .announcement-table thead {
            display: none;
        }
        .announcement-table tr {
            display: grid;
            gap: 0;
            width: 100%;
            margin-bottom: 10px;
            overflow: hidden;
            border: 1px solid var(--liquid-stroke-soft, #dfceb9);
            border-radius: 16px;
            background:
                linear-gradient(145deg, rgba(255,255,255,.62), transparent 48%),
                var(--liquid-surface, rgba(255,255,255,.54));
            box-shadow: 0 12px 28px rgba(73, 50, 29, .09), inset 0 1px 0 rgba(255,255,255,.78);
        }
        .announcement-table tr:last-child {
            margin-bottom: 0;
        }
        .announcement-table td {
            display: grid;
            grid-template-columns: 88px minmax(0, 1fr);
            align-items: start;
            gap: 10px;
            width: 100%;
            max-width: none !important;
            padding: 10px 12px !important;
            border: 0 !important;
            border-bottom: 1px solid color-mix(in srgb, var(--se-border, #eadfce) 72%, transparent) !important;
            color: var(--se-text, #241a12);
            line-height: 1.5;
            overflow-wrap: anywhere;
        }
        .announcement-table td:last-child {
            border-bottom: 0 !important;
        }
        .announcement-table td::before {
            content: attr(data-label);
            color: var(--se-text-muted, #8b7c6f);
            font-size: .64rem;
            font-weight: 850;
            letter-spacing: .08em;
            line-height: 1.4;
            text-transform: uppercase;
        }
        .announcement-table .ann-description,
        .announcement-table .ann-actions {
            grid-template-columns: 1fr;
        }
        .announcement-table .ann-description::before,
        .announcement-table .ann-actions::before {
            margin-bottom: 2px;
        }
        .announcement-table .ann-title {
            font-weight: 800;
        }
        .announcement-table .ann-link a {
            color: var(--se-primary-strong, #7d582f);
            font-weight: 750;
            text-underline-offset: 3px;
        }
        .announcement-table .actions-cell {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            width: 100%;
        }
        .announcement-table .actions-cell form,
        .announcement-table .actions-cell .btn {
            width: 100%;
        }
        .announcement-table .actions-cell .btn {
            min-height: 42px;
            text-align: center;
        }
        .announcement-table .ann-empty {
            display: block;
            padding: 1.4rem !important;
            text-align: center !important;
        }
        .announcement-table .ann-empty::before {
            content: none;
        }
    }</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">Pengumuman Scholarship</h2>
@endsection

@section('content')
<div class="wrap">
    @if(session('success'))<div class="ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="card">
        <div class="head">
            <h1 style="margin:0;font-size:20px;">Pengurusan Pengumuman Scholarship</h1>
            <div class="ann-head-actions">
                <a class="btn" href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a class="btn" href="{{ route('admin.scholarship-announcements.export', request()->query()) }}">Export CSV</a>
                <a class="btn" href="{{ route('admin.scholarship-announcements.create') }}">Tambah Pengumuman</a>
            </div>
        </div>

        <div class="filters" data-filter-sheet data-filter-title="{{ __('Announcement filters') }}">
            <form method="GET" action="{{ route('admin.scholarship-announcements.index') }}">
                <div class="filter-grid">
                    <div><input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Cari tajuk/penerangan"></div>
                    <div>
                        <select name="type">
                            <option value="">Semua jenis</option>
                            @foreach(['scholarship','welfare','general'] as $type)
                                <option value="{{ $type }}" {{ ($filters['type'] ?? '') === $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex; gap:8px;">
                        <button class="btn" type="submit">Filter</button>
                        <a class="btn" href="{{ route('admin.scholarship-announcements.index') }}">Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="ann-table-wrap">
            <table class="announcement-table">
                <thead>
                    <tr>
                        <th>Tajuk</th>
                        <th>Jenis</th>
                        <th>Penerangan</th>
                        <th>Link</th>
                        <th>Dicipta Oleh</th>
                        <th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($announcements as $item)
                        <tr>
                            <td class="ann-title" data-label="Tajuk">{{ $item->title }}</td>
                            <td data-label="Jenis"><span class="pill {{ $item->type }}">{{ $item->type }}</span></td>
                            <td class="ann-description" data-label="Penerangan">{{ $item->body }}</td>
                            <td class="ann-link" data-label="Link">
                                @if($item->link_url)
                                    <a href="{{ $item->link_url }}" target="_blank" rel="noopener">{{ $item->link_label ?: 'Buka Link' }}</a>
                                @else
                                    -
                                @endif
                            </td>
                            <td data-label="Dicipta Oleh">{{ $item->admin_name }}</td>
                            <td class="ann-actions" data-label="Tindakan">
                                <div class="actions-cell">
                                    <a class="btn" href="{{ route('admin.scholarship-announcements.edit', $item->id) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.scholarship-announcements.destroy', $item->id) }}" style="margin:0;"
                                        data-confirm-title="{{ __('Delete announcement') }}"
                                        data-confirm-message="{{ __('Delete this announcement?') }}"
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
                        <tr><td class="ann-empty" colspan="6">Tiada pengumuman scholarship.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:14px;">{{ $announcements->links() }}</div>
</div>
@endsection


