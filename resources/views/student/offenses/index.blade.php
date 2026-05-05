@extends('layouts.app')

@section('title', __('Kesalahan Saya'))

@push('styles')
<style>
    .wrap { max-width: 1050px; margin: 0 auto; }
    .quick { display:flex; gap:8px; flex-wrap:wrap; margin-bottom:12px; }
    .card { background:#fff; border:1px solid #ede4d9; border-radius:12px; margin-bottom:12px; overflow:hidden; }
    .card-head { padding:10px 14px; border-bottom:1px solid #f0e7dc; display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap; }
    .card-body { padding:12px 14px; }
    .status-badge { display:inline-block; border-radius:99px; padding:.2rem .6rem; font-size:11px; font-weight:700; text-transform:uppercase; border:1px solid #ede4d9; }
    .status-unpaid, .status-rejected { background:#fef2f2; color:#b91c1c; border-color:#fecaca; }
    .status-applied, .status-pending { background:#fff7ed; color:#b45309; border-color:#fed7aa; }
    .status-paid, .status-approved { background:#f0fdf4; color:#15803d; border-color:#bbf7d0; }
    ul { margin:8px 0 0 18px; }
    li { margin-bottom:4px; }
    textarea { width:100%; border:1px solid #e5d8c8; border-radius:8px; padding:8px; font-size:13px; }
    .btn { display:inline-block; border:1px solid #cbb9a4; background:#fff; color:#8a7362; border-radius:8px; padding:8px 12px; text-decoration:none; font-weight:600; font-size:13px; cursor:pointer; }
    .btn-primary { border:none; color:#fff; background:linear-gradient(135deg,#A48D78,#CBB9A4); }
    .msg-ok { margin-bottom:12px; background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; border-radius:8px; padding:10px; font-size:13px; }
    .msg-err { margin-bottom:12px; background:#fef2f2; border:1px solid #fecaca; color:#991b1b; border-radius:8px; padding:10px; font-size:13px; }
    .evidence-img { margin-top:8px; max-width:260px; border:1px solid #ede4d9; border-radius:8px; display:block; }
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
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Offense') }}</h2>
@endsection

@section('content')
<div class="wrap">
    @if(session('success'))<div class="msg-ok">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="msg-err">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif

    <div class="quick">
        <a class="btn" href="{{ route('student.dashboard') }}">{{ __('Kembali ke Index') }}</a>
        <a class="btn" href="{{ route('student.vehicle-stickers.index') }}">{{ __('Permohonan Sticker') }}</a>
        <a class="btn" href="{{ route('student.rules.index') }}">{{ __('Lihat Peraturan') }}</a>
        <a class="btn" href="{{ route('student.scholarships.index') }}">{{ __('Portal Scholarship') }}</a>
    </div>

    @forelse($offenses as $offense)
        <div class="card">
            <div class="card-head">
                <div><strong>{{ __('Tarikh:') }}</strong> {{ $offense->offense_date }} {{ $offense->offense_time }}<br><strong>{{ __('Tempat:') }}</strong> {{ $offense->place }}</div>
                <div style="text-align:right;"><div><strong>{{ __('Denda:') }}</strong> RM {{ number_format((float)$offense->fine_amount, 2) }}</div><div style="margin-top:6px;"><span class="status-badge status-{{ strtolower($offense->status) }}">{{ __($offense->status) }}</span></div></div>
            </div>
            <div class="card-body">
                <div><strong>{{ __('Peraturan Dilanggar:') }}</strong></div>
                <ul>
                    @foreach(($itemsByOffense[$offense->id] ?? collect()) as $item)
                        <li>[{{ __($item->rule_reference) }}] {{ __($item->description) }} @if($item->note)<br><small>{{ __('Nota') }}: {{ $item->note }}</small>@endif</li>
                    @endforeach
                </ul>

                @if(!empty($offense->evidence_photo_path))
                    <div style="margin-top:10px;">
                        <strong>{{ __('Bukti Gambar') }}:</strong>
                        <a href="{{ asset('storage/' . $offense->evidence_photo_path) }}" target="_blank">{{ __('Lihat gambar asal') }}</a>
                        <img src="{{ asset('storage/' . $offense->evidence_photo_path) }}" alt="{{ __('Bukti Gambar') }}" class="evidence-img">
                    </div>
                @endif

                @php $app = $fineAppsByOffense[$offense->id] ?? null; @endphp
                @if($app)
                    <div style="margin-top:10px; font-size:13px; color:#7a6555;">
                        <strong>{{ __('Permohonan bayaran:') }}</strong>
                        <span class="status-badge status-{{ strtolower($app->status) }}">{{ __($app->status) }}</span>
                        @if($app->meeting_date)| <strong>{{ __('Tarikh') }}:</strong> {{ $app->meeting_date }} @endif
                    </div>
                @elseif($offense->status !== 'paid')
                    <form method="POST" action="{{ route('student.fine-applications.store') }}" style="margin-top:12px;">
                        @csrf
                        <input type="hidden" name="offense_id" value="{{ $offense->id }}">
                        <label for="note_{{ $offense->id }}" style="font-size:13px; font-weight:600; color:#7a6555;">{{ __('Catatan permohonan (optional)') }}</label>
                        <textarea id="note_{{ $offense->id }}" name="student_note" rows="2" placeholder="{{ __('Contoh: Saya ingin membuat bayaran pada minggu ini.') }}"></textarea>
                        <button class="btn btn-primary" type="submit" style="margin-top:8px;">{{ __('Mohon Bayaran Denda') }}</button>
                    </form>
                @endif
            </div>
        </div>
    @empty
        <div class="card" style="padding:14px; color:#7a6555;">{{ __('Tiada rekod kesalahan.') }}</div>
    @endforelse

    <div style="margin-top:14px;">{{ $offenses->links() }}</div>
</div>
@endsection

