@extends('layouts.app')

@section('title', __('Pengumuman Disiplin'))

@push('styles')
<style>
    .wrap { max-width: 1000px; margin: 0 auto; }
    .card { background:#fff; border:1px solid #ede4d9; border-radius:12px; overflow:hidden; }
    .item { padding:14px 16px; border-bottom:1px solid #f0e7dc; }
    .item:last-child { border-bottom:none; }
    .title { margin:0 0 6px; font-size:16px; color:#2d1f14; }
    .meta { font-size:12px; color:#7a6555; margin-bottom:8px; }
    .body { margin:0; font-size:13px; color:#4f3b2d; line-height:1.5; }
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
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Pengumuman Disiplin') }}</h2>
@endsection

@section('content')
<div class="wrap">
    <div class="card">
        @forelse($announcements as $item)
            <article class="item">
                <h3 class="title">{{ $item->title }}</h3>
                <div class="meta">{{ $item->created_at ? \Illuminate\Support\Carbon::parse($item->created_at)->format('Y-m-d') : '-' }} · {{ $item->admin_name }}</div>
                <p class="body">{{ $item->body }}</p>
            </article>
        @empty
            <div class="item" style="color:#7a6555;">{{ __('Tiada pengumuman disiplin.') }}</div>
        @endforelse
    </div>

    <div style="margin-top:14px;">{{ $announcements->links() }}</div>
</div>
@endsection

