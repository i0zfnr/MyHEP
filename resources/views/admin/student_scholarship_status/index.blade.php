@extends('layouts.app')

@section('title', __('Pengumpulan Data Biasiswa Pelajar'))

@push('styles')
<style>
    .wrap { max-width: 1180px; margin: 0 auto; }
    .card { background:#fff; border:1px solid #ede4d9; border-radius:12px; overflow:hidden; margin-bottom:12px; }
    .head { padding:12px 16px; border-bottom:1px solid #ede4d9; display:flex; justify-content:space-between; align-items:center; gap:8px; flex-wrap:wrap; }
    .body { padding:12px 16px; }
    .stats { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; }
    @media (min-width: 980px) { .stats { grid-template-columns:repeat(4,1fr); } }
    .stat { border:1px solid #ede4d9; border-radius:10px; padding:10px; background:#fcfaf8; }
    .stat .label { font-size:11px; text-transform:uppercase; color:#7a6555; letter-spacing:.04em; margin-bottom:3px; }
    .stat .value { font-size:24px; color:#2d1f14; font-weight:700; line-height:1; }
    .btn { display:inline-block; border:1px solid #cbb9a4; background:#fff; color:#8a7362; border-radius:8px; padding:8px 12px; text-decoration:none; font-weight:600; font-size:13px; cursor:pointer; }
    input, select { border:1px solid #e5d8c8; border-radius:8px; padding:8px 10px; font-size:13px; background:#fff; }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:10px 12px; border-bottom:1px solid #f0e7dc; font-size:13px; text-align:left; vertical-align:top; }
    th { font-size:11px; text-transform:uppercase; color:#7a6555; letter-spacing:.05em; background:#faf7f4; }
    .badge { display:inline-block; border-radius:99px; padding:.2rem .6rem; font-size:11px; font-weight:700; text-transform:uppercase; border:1px solid #ede4d9; }
    .badge.yes { background:#e7f3f3; color:#28686c; border-color:#b9ddde; }
    .badge.no { background:#fef2f2; color:#b91c1c; border-color:#fecaca; }
    .badge.none { background:#faf7f4; color:#7a6555; border-color:#ede4d9; }
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
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Pengumpulan Data Biasiswa Pelajar') }}</h2>
@endsection

@section('content')
<div class="wrap">
    <div class="card">
        <div class="body">
            <div class="stats" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
                <div class="stat"><div class="label">{{ __('Jumlah Pelajar') }}</div><div class="value">{{ $summary['total_students'] }}</div></div>
                <div class="stat"><div class="label">{{ __('Borang Dihantar') }}</div><div class="value">{{ $summary['submitted'] }}</div></div>
                <div class="stat"><div class="label">{{ __('Menerima Biasiswa') }}</div><div class="value" style="color:#0284c7;">{{ $summary['scholarship'] }}</div></div>
                <div class="stat"><div class="label">{{ __('Bantuan Kebajikan') }}</div><div class="value" style="color:#059669;">{{ $summary['welfare'] }}</div></div>
                <div class="stat"><div class="label">{{ __('Tiada Biasiswa/Bantuan') }}</div><div class="value" style="color:#7a6555;">{{ $summary['none'] }}</div></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="head">
            <strong>{{ __('Senarai Status Biasiswa & Bantuan Kebajikan Pelajar') }}</strong>
            <form method="GET" action="{{ route('admin.student-scholarship-status.index') }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Cari nama / matrik / penaja / kebajikan') }}">
                <select name="type">
                    <option value="all" {{ ($filters['type'] ?? 'all') === 'all' ? 'selected' : '' }}>{{ __('Semua Status') }}</option>
                    <option value="scholarship" {{ ($filters['type'] ?? '') === 'scholarship' ? 'selected' : '' }}>{{ __('Biasiswa / Penajaan') }}</option>
                    <option value="welfare" {{ ($filters['type'] ?? '') === 'welfare' ? 'selected' : '' }}>{{ __('Bantuan Kebajikan') }}</option>
                    <option value="none" {{ ($filters['type'] ?? '') === 'none' ? 'selected' : '' }}>{{ __('Tiada Biasiswa / Bantuan') }}</option>
                    <option value="unsubmitted" {{ ($filters['type'] ?? '') === 'unsubmitted' ? 'selected' : '' }}>{{ __('Belum Hantar') }}</option>
                </select>
                <button class="btn" type="submit">{{ __('Tapis') }}</button>
                <a class="btn" href="{{ route('admin.student-scholarship-status.index') }}">{{ __('Reset') }}</a>
            </form>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Pelajar') }}</th>
                        <th>{{ __('Program') }}</th>
                        <th>{{ __('Kategori / Status') }}</th>
                        <th>{{ __('Butiran / Penaja / Kebajikan') }}</th>
                        <th>{{ __('Maklumat Waris / Pendapatan') }}</th>
                        <th>{{ __('Jumlah (RM)') }}</th>
                        <th>{{ __('Dokumen Sokongan') }}</th>
                        <th>{{ __('Tarikh Hantar') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $row)
                        @php
                            $appType = $row->application_type ?? ($row->has_scholarship === 'yes' ? 'scholarship' : ($row->has_scholarship === 'no' ? 'none' : null));
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $row->full_name }}</strong><br>
                                <small style="color:#7a6555;">{{ $row->matric_no }}</small>
                            </td>
                            <td>{{ $row->program }}</td>
                            <td>
                                @if($appType === 'scholarship')
                                    <span class="badge yes" style="background:#e0f2fe; color:#0369a1; border-color:#bae6fd;">🎓 {{ __('Biasiswa') }}</span>
                                @elseif($appType === 'welfare')
                                    <span class="badge yes" style="background:#d1fae5; color:#047857; border-color:#a7f3d0;">🤝 {{ __('Kebajikan') }}</span>
                                @elseif($appType === 'none')
                                    <span class="badge no" style="background:#f3f4f6; color:#4b5563; border-color:#e5e7eb;">❌ {{ __('Tiada') }}</span>
                                @else
                                    <span class="badge none">{{ __('Belum Hantar') }}</span>
                                @endif
                            </td>
                            <td>
                                @if($appType === 'scholarship')
                                    <strong>{{ $row->sponsor_name ?: '-' }}</strong>
                                    @if($row->notes)<br><small style="color:#7a6555;">{{ $row->notes }}</small>@endif
                                @elseif($appType === 'welfare')
                                    <strong style="color:#047857;">{{ $row->welfare_category ?: 'Bantuan Kebajikan' }}</strong>
                                    @if($row->welfare_description)<br><small style="color:#4b5563; font-style:italic;">{{ Str::limit($row->welfare_description, 60) }}</small>@endif
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($appType === 'welfare')
                                    <div style="font-size:12px;">
                                        <strong>Waris:</strong> {{ $row->guardian_name ?: '-' }} ({{ $row->guardian_relationship ?: 'Waris' }})<br>
                                        <strong>Tel:</strong> {{ $row->guardian_phone ?: '-' }}<br>
                                        <strong>Gaji:</strong> {{ $row->family_income !== null ? 'RM ' . number_format((float) $row->family_income, 2) : '-' }}
                                        @if($row->dependents_count) ({{ $row->dependents_count }} tanggungan) @endif
                                    </div>
                                @else
                                    <span style="color:#9ca3af;">-</span>
                                @endif
                            </td>
                            <td>
                                @if($appType === 'scholarship')
                                    {{ $row->monthly_amount !== null ? 'RM ' . number_format((float) $row->monthly_amount, 2) . '/bln' : '-' }}
                                @elseif($appType === 'welfare')
                                    {{ $row->welfare_amount !== null ? 'RM ' . number_format((float) $row->welfare_amount, 2) : 'Bantuan Khas' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($row->doc_id)
                                    <a class="btn" style="padding:4px 8px; font-size:11px;" href="{{ route('admin.student-scholarship-status.documents.download', $row->doc_id) }}">
                                        📄 {{ __('Muat Turun') }}
                                    </a>
                                    <br><small style="font-size:10px; color:#7a6555;">{{ Str::limit($row->doc_name, 20) }}</small>
                                @else
                                    <span style="color:#9ca3af;">-</span>
                                @endif
                            </td>
                            <td>{{ $row->submitted_at ? \Illuminate\Support\Carbon::parse($row->submitted_at)->format('d/m/Y H:i') : '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center;color:#7a6555;padding:24px;">{{ __('Tiada rekod dijumpai.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div style="margin-top:14px;">{{ $records->links() }}</div>
</div>
@endsection


