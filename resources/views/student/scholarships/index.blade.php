@extends('layouts.app')

@section('title', __('Scholarship Saya'))

@push('styles')
<style>
    .sch-shell { max-width: 1120px; margin: 0 auto; display: grid; gap: 14px; }

    .sch-hero {
        border: 1px solid #e6d7c7;
        border-radius: 18px;
        padding: clamp(14px, 3vw, 20px) clamp(14px, 4vw, 20px);
        background:
            radial-gradient(700px 200px at 100% 0%, rgba(193, 160, 128, .18) 0%, transparent 55%),
            linear-gradient(130deg, #2b2119 0%, #6d5340 55%, #8f735b 100%);
        color: #fff;
        box-shadow: 0 12px 28px rgba(39, 28, 21, .18);
    }
    .sch-hero-label { display:inline-block; font-size:11px; font-weight:700; letter-spacing:.09em; text-transform:uppercase; color:#ffe7cf; margin-bottom:8px; }
    .sch-hero h3 { margin:0; font-size:1.55rem; line-height:1.2; font-weight:700; }
    .sch-hero p { margin:7px 0 0; color:rgba(255,240,226,.9); font-size:13px; }

    .sch-actions { display:flex; gap:8px; flex-wrap:wrap; }
    .sch-chip {
        display:inline-flex; align-items:center; gap:6px;
        border:1px solid #d9c6b1; border-radius:999px;
        padding:7px 12px; font-size:12px; font-weight:700;
        white-space: nowrap;
        text-decoration:none; color:#6a523f;
        background: linear-gradient(180deg, #fff 0%, #f9f2ea 100%);
        transition: all .16s ease;
    }
    .sch-chip:hover { transform: translateY(-1px); border-color:#bc9b79; color:#4f3b2d; box-shadow:0 8px 14px rgba(95,72,53,.14); }
    .sch-chip.primary { color:#fff; border-color:#7d5f45; background:linear-gradient(135deg,#7d5f45,#b18d6e); }

    .sch-stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:10px; }
    .sch-stat {
        border:1px solid #eadfce; border-radius:14px; background:#fffdfa;
        padding:12px 14px; box-shadow:0 8px 18px rgba(57,43,32,.06);
    }
    .sch-stat-label { font-size:11px; font-weight:700; color:#7a6656; text-transform:uppercase; letter-spacing:.05em; }
    .sch-stat-value { margin-top:3px; font-size:26px; font-weight:800; color:#2b211a; line-height:1; }

    .sch-grid { display:grid; grid-template-columns:1.35fr .9fr; gap:12px; align-items:start; }
    .sch-card { border:1px solid #eadfce; border-radius:14px; background:#fff; overflow:hidden; box-shadow:0 10px 24px rgba(57,43,32,.07); }
    .sch-head { padding:13px 15px; border-bottom:1px solid #eee2d4; background:linear-gradient(180deg,#fff 0%,#fbf4ec 100%); position:relative; }
    .sch-head::before { content:''; position:absolute; left:0; top:0; bottom:0; width:3px; background:linear-gradient(180deg,#8f6f52,#c7a98b); }
    .sch-head strong { font-size:15px; color:#2d221a; }

    .sch-table-wrap { overflow:auto; }
    .sch-table { width:100%; border-collapse:collapse; }
    .sch-table th, .sch-table td { padding:11px 12px; border-bottom:1px solid #f0e8de; text-align:left; font-size:13px; }
    .sch-table th { background:#fbf6ef; font-size:11px; color:#7b6758; text-transform:uppercase; letter-spacing:.06em; }
    .sch-table tbody tr:hover { background:#fdf9f4; }
    .sch-table td { color: #3c2f24; }

    .status-badge { display:inline-block; border-radius:999px; padding:.22rem .62rem; font-size:11px; font-weight:800; text-transform:uppercase; border:1px solid #e9ddcf; }
    .status-pending { background:#fff7ed; color:#b45309; border-color:#fed7aa; }
    .status-confirmed, .status-approved { background:#f0fdf4; color:#15803d; border-color:#bbf7d0; }
    .status-rejected, .status-unpaid { background:#fef2f2; color:#b91c1c; border-color:#fecaca; }
    .status-none { background:#faf7f4; color:#7a6555; border-color:#ede4d9; }

    .ann-list { display:grid; gap:10px; padding:12px; }
    .ann-item { border:1px solid #efe3d7; border-radius:12px; padding:10px 11px; background:#fffdfa; }
    .ann-title { margin:0 0 4px; font-size:14px; color:#2d221a; font-weight:700; }
    .ann-meta { display:flex; gap:8px; align-items:center; margin-bottom:6px; }
    .ann-type { display:inline-block; border-radius:99px; padding:.16rem .55rem; font-size:10px; font-weight:700; text-transform:uppercase; border:1px solid #e7d9ca; }
    .ann-type.scholarship { background:#e0f2fe; color:#075985; border-color:#bae6fd; }
    .ann-type.welfare { background:#fff7ed; color:#b45309; border-color:#fed7aa; }
    .ann-type.general { background:#f5ede6; color:#8a7362; border-color:#cbb9a4; }
    .ann-date { font-size:11px; color:#7a6555; }
    .ann-body { margin:0; font-size:12px; color:#4f3b2d; line-height:1.55; }
    .ann-link { display:inline-flex; margin-top:8px; text-decoration:none; border:1px solid #ccb49d; color:#6e5745; border-radius:8px; padding:6px 10px; font-size:12px; font-weight:700; background:#fff; }
    .ann-link:hover { background:#f7efe7; }

    .sch-empty { padding:20px 12px; text-align:center; color:#7a6656; font-size:13px; }
    .sch-pagination { margin-top: 2px; }
    .sch-pagination nav { display: flex; justify-content: center; }

    body[data-theme="dark"] .sch-shell {
        gap: 16px;
    }
    body[data-theme="dark"] .sch-hero {
        background:
            linear-gradient(135deg, rgba(56, 44, 35, .92), rgba(22, 19, 16, .82)),
            radial-gradient(720px 240px at 100% 0%, rgba(215,191,168,.16) 0%, transparent 58%),
            radial-gradient(420px 180px at 0% 100%, rgba(95,190,145,.08) 0%, transparent 60%) !important;
        border-color: rgba(226, 209, 192, .18);
        box-shadow:
            0 22px 50px rgba(0,0,0,.34),
            inset 0 1px 0 rgba(255,255,255,.08);
        backdrop-filter: blur(18px) saturate(126%);
        -webkit-backdrop-filter: blur(18px) saturate(126%);
    }
    body[data-theme="dark"] .sch-hero-label {
        color: #d7bfa8;
    }
    body[data-theme="dark"] .sch-hero h3 {
        color: #fff7ef;
    }
    body[data-theme="dark"] .sch-hero p {
        color: rgba(247,239,232,.72);
    }
    body[data-theme="dark"] .sch-chip {
        background: rgba(255,255,255,.075);
        border-color: rgba(226, 209, 192, .18);
        color: #fff7ef;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.08);
        backdrop-filter: blur(12px) saturate(126%);
        -webkit-backdrop-filter: blur(12px) saturate(126%);
    }
    body[data-theme="dark"] .sch-chip:hover {
        background: rgba(215,191,168,.15);
        border-color: rgba(215,191,168,.38);
        color: #fff7ef;
        box-shadow: 0 12px 28px rgba(0,0,0,.26), inset 0 1px 0 rgba(255,255,255,.10);
    }
    body[data-theme="dark"] .sch-chip.primary {
        background: linear-gradient(135deg, #b99b82 0%, #e4cdb7 100%);
        border-color: rgba(255,255,255,.10);
        color: #17110d;
    }
    body[data-theme="dark"] .sch-stat,
    body[data-theme="dark"] .sch-card {
        background:
            linear-gradient(145deg, rgba(31, 27, 23, .88), rgba(14, 13, 12, .78)),
            radial-gradient(circle at 10% 0%, rgba(255,255,255,.06), transparent 36%) !important;
        border-color: rgba(226, 209, 192, .16) !important;
        box-shadow:
            0 18px 38px rgba(0,0,0,.24),
            inset 0 1px 0 rgba(255,255,255,.07) !important;
        backdrop-filter: blur(16px) saturate(126%);
        -webkit-backdrop-filter: blur(16px) saturate(126%);
    }
    body[data-theme="dark"] .sch-stat-label,
    body[data-theme="dark"] .ann-date,
    body[data-theme="dark"] .ann-body,
    body[data-theme="dark"] .sch-empty {
        color: rgba(247,239,232,.66) !important;
    }
    body[data-theme="dark"] .sch-stat-value,
    body[data-theme="dark"] .ann-title {
        color: #fff7ef !important;
        text-shadow: 0 1px 1px rgba(0,0,0,.24);
    }
    body[data-theme="dark"] .sch-head {
        background: linear-gradient(180deg, rgba(255,255,255,.08), rgba(255,255,255,.025)) !important;
        border-color: rgba(226, 209, 192, .14) !important;
    }
    body[data-theme="dark"] .sch-head::before {
        background: linear-gradient(180deg, #d7bfa8 0%, #5fbe91 100%) !important;
    }
    body[data-theme="dark"] .sch-head strong {
        color: #fff7ef !important;
    }
    body[data-theme="dark"] .sch-table th {
        background: rgba(255,255,255,.055) !important;
        border-color: rgba(226, 209, 192, .13) !important;
        color: #b9aa9d !important;
    }
    body[data-theme="dark"] .sch-table td {
        border-color: rgba(226, 209, 192, .12) !important;
        color: #f7efe8 !important;
    }
    body[data-theme="dark"] .sch-table tbody tr {
        background: rgba(12, 11, 10, .22) !important;
    }
    body[data-theme="dark"] .sch-table tbody tr:hover {
        background: rgba(215,191,168,.075) !important;
    }
    body[data-theme="dark"] .ann-item {
        background: rgba(255,255,255,.055) !important;
        border-color: rgba(226, 209, 192, .15) !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.06);
    }
    body[data-theme="dark"] .ann-link {
        background: rgba(255,255,255,.075) !important;
        border-color: rgba(226, 209, 192, .18) !important;
        color: #fff7ef !important;
    }
    body[data-theme="dark"] .ann-link:hover {
        background: rgba(215,191,168,.15) !important;
        border-color: rgba(215,191,168,.38) !important;
    }

    @media (max-width: 980px) {
        .sch-grid { grid-template-columns:1fr; }
        .sch-stats { grid-template-columns:repeat(3, minmax(0, 1fr)); }
    }

    @media (max-width: 760px) {
        .sch-shell { gap: 12px; }
        .sch-stats { grid-template-columns: 1fr; }
        .sch-actions {
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 2px;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }
        .sch-chip { flex: 0 0 auto; }
        .sch-head strong { font-size: 14px; }
        .sch-table-wrap { overflow: visible; padding: 10px; }
        .sch-table { display: block; width: 100%; }
        .sch-table thead { display: none; }
        .sch-table tbody { display: grid; gap: 10px; }
        .sch-table tr {
            display: block;
            border: 1px solid #efe3d5;
            border-radius: 12px;
            padding: 4px 10px;
            background: #fffdfa;
        }
        .sch-table td {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            padding: 9px 0;
            border-bottom: 1px dashed #efe4d7;
            font-size: 13px;
        }
        .sch-table td:last-child { border-bottom: none; }
        .sch-table td::before {
            content: attr(data-label);
            position: static;
            min-width: 98px;
            flex: 0 0 98px;
            font-size: 10px;
            font-weight: 700;
            color: #8b7868;
            text-transform: uppercase;
            letter-spacing: .05em;
        }
        .ann-list { padding: 10px; }
        .ann-item { padding: 10px; }
        .ann-title { font-size: 13px; }
    }

    @media (max-width: 420px) {
        .sch-hero h3 { font-size: 1.35rem; }
        .sch-hero p { font-size: 12px; }
        .sch-stat-value { font-size: 22px; }
        .sch-table td::before {
            min-width: 90px;
            flex-basis: 90px;
        }
    }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Scholarship') }}</h2>
@endsection

@section('content')
@php
    $recordsOnPage = $records->getCollection();
    $activeOnPage = $recordsOnPage->filter(fn ($r) => in_array(strtolower((string) $r->status), ['approved', 'confirmed']))->count();
@endphp
<div class="sch-shell">
    <section class="sch-hero">
        <span class="sch-hero-label">{{ __('Student Scholarship Portal') }}</span>
        <h3>{{ __('Biasiswa') }}</h3>
        <p>{{ __('Semak rekod biasiswa anda, status semasa, dan pengumuman terbaru dalam satu paparan.') }}</p>
    </section>

    <div class="sch-actions">
        <a class="sch-chip" href="{{ route('student.dashboard') }}">{{ __('Kembali ke Index') }}</a>
        <a class="sch-chip" href="{{ route('student.offenses.index') }}">{{ __('Semak Offense') }}</a>
        <a class="sch-chip" href="{{ route('student.vehicle-stickers.index') }}">{{ __('Permohonan Sticker') }}</a>
        <a class="sch-chip" href="{{ route('student.rules.index') }}">{{ __('Lihat Peraturan') }}</a>
        <a class="sch-chip" href="{{ route('student.scholarships.announcements') }}">{{ __('Lihat Pengumuman Biasiswa') }}</a>
        <a class="sch-chip primary" href="{{ route('student.scholarship-status.form') }}">{{ __('Isi Borang Status Biasiswa') }}</a>
    </div>

    <div class="sch-stats">
        <article class="sch-stat">
            <div class="sch-stat-label">{{ __('Jumlah Rekod') }}</div>
            <div class="sch-stat-value">{{ $records->total() }}</div>
        </article>
        <article class="sch-stat">
            <div class="sch-stat-label">{{ __('Aktif (Paparan Ini)') }}</div>
            <div class="sch-stat-value">{{ $activeOnPage }}</div>
        </article>
        <article class="sch-stat">
            <div class="sch-stat-label">{{ __('Pengumuman') }}</div>
            <div class="sch-stat-value">{{ $announcements->count() }}</div>
        </article>
    </div>

    <div class="sch-grid">
        <section class="sch-card">
            <div class="sch-head"><strong>{{ __('Rekod Scholarship Saya') }}</strong></div>
            <div class="sch-table-wrap">
                <table class="sch-table">
                    <thead>
                        <tr>
                            <th>{{ __('Jenis') }}</th>
                            <th>{{ __('Penyedia') }}</th>
                            <th>{{ __('Jumlah (RM)') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Tarikh') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            @php($statusClass = strtolower((string) ($record->status ?? 'none')))
                            <tr>
                                <td data-label="{{ __('Jenis') }}">{{ ucfirst((string) $record->type) }}</td>
                                <td data-label="{{ __('Penyedia') }}">{{ $record->provider_name ?: '-' }}</td>
                                <td data-label="{{ __('Jumlah') }}">{{ $record->amount !== null ? number_format((float)$record->amount, 2) : '-' }}</td>
                                <td data-label="{{ __('Status') }}"><span class="status-badge status-{{ $statusClass }}">{{ __($record->status) }}</span></td>
                                <td data-label="{{ __('Tarikh') }}">{{ $record->created_at ? \Illuminate\Support\Carbon::parse($record->created_at)->format('d M Y') : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="sch-empty">{{ __('Tiada rekod scholarship.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="sch-card">
            <div class="sch-head"><strong>{{ __('Pengumuman Scholarship') }}</strong></div>
            <div class="ann-list">
                @forelse($announcements as $item)
                    @php($annType = strtolower((string) ($item->type ?? 'general')))
                    <article class="ann-item">
                        <h3 class="ann-title">{{ $item->title }}</h3>
                        <div class="ann-meta">
                            <span class="ann-type {{ $annType }}">{{ strtoupper($annType) }}</span>
                            <span class="ann-date">{{ \Illuminate\Support\Carbon::parse($item->created_at)->format('d M Y') }}</span>
                        </div>
                        <p class="ann-body">{{ \Illuminate\Support\Str::limit($item->body, 180) }}</p>
                        @if($item->link_url)
                            <a class="ann-link" href="{{ $item->link_url }}" target="_blank" rel="noopener">
                                {{ $item->link_label ?: __('Buka Pautan') }}
                            </a>
                        @endif
                    </article>
                @empty
                    <div class="sch-empty">{{ __('Tiada pengumuman semasa.') }}</div>
                @endforelse
            </div>
        </section>
    </div>

    <div class="sch-pagination">{{ $records->links() }}</div>
</div>
@endsection
