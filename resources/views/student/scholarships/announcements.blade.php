@extends('layouts.app')

@section('title', __('Pengumuman Biasiswa'))

@push('styles')
<style>
    .ann-page {
        width: min(1160px, 100%);
        margin: 0 auto;
        display: grid;
        gap: 16px;
    }

    .ann-hero {
        position: relative;
        overflow: hidden;
        border: 1px solid #e8d8c6;
        border-radius: 24px;
        padding: clamp(18px, 4vw, 30px);
        background:
            radial-gradient(420px 220px at 100% 0%, rgba(255, 226, 190, .24) 0%, transparent 60%),
            radial-gradient(300px 180px at 0% 100%, rgba(148, 213, 184, .16) 0%, transparent 62%),
            linear-gradient(135deg, #2b2119 0%, #5f4737 48%, #9a7a60 100%);
        color: #fff8f0;
        box-shadow: 0 20px 40px rgba(48, 34, 24, .20);
    }

    .ann-hero::after {
        content: '';
        position: absolute;
        inset: auto -60px -90px auto;
        width: 220px;
        height: 220px;
        border-radius: 999px;
        background: rgba(255, 244, 232, .08);
        filter: blur(2px);
    }

    .ann-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 10px;
        padding: 7px 11px;
        border: 1px solid rgba(255, 240, 226, .18);
        border-radius: 999px;
        background: rgba(255, 255, 255, .08);
        color: #ffe7cf;
        font-size: 11px;
        font-weight: 800;
        letter-spacing: .08em;
        text-transform: uppercase;
        backdrop-filter: blur(10px);
    }

    .ann-hero h3 {
        margin: 0;
        max-width: 760px;
        font-size: clamp(1.7rem, 4vw, 2.6rem);
        line-height: 1.08;
        font-weight: 800;
    }

    .ann-hero p {
        margin: 12px 0 0;
        max-width: 760px;
        color: rgba(255, 241, 228, .86);
        font-size: 14px;
        line-height: 1.65;
    }

    .ann-toolbar {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
    }

    .ann-actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .ann-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 9px 13px;
        border: 1px solid #dcc6ae;
        border-radius: 999px;
        background: linear-gradient(180deg, #fff 0%, #f9f2ea 100%);
        color: #694f3a;
        text-decoration: none;
        font-size: 12px;
        font-weight: 800;
        transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease, color 160ms ease;
    }

    .ann-chip:hover {
        transform: translateY(-1px);
        border-color: #bc9b79;
        color: #4f3928;
        box-shadow: 0 10px 16px rgba(95, 72, 53, .12);
    }

    .ann-chip.primary {
        border-color: #7d5f45;
        background: linear-gradient(135deg, #7d5f45, #b18d6e);
        color: #fff;
    }

    .ann-helper {
        color: #7b6758;
        font-size: 12px;
        font-weight: 600;
    }

    .ann-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
    }

    .ann-stat {
        position: relative;
        overflow: hidden;
        border: 1px solid #eadfce;
        border-radius: 18px;
        padding: 14px 16px;
        background:
            radial-gradient(200px 120px at 100% 0%, rgba(188, 155, 121, .08) 0%, transparent 62%),
            linear-gradient(180deg, #fff 0%, #fffdfa 100%);
        box-shadow: 0 10px 22px rgba(57, 43, 32, .07);
    }

    .ann-stat-label {
        font-size: 11px;
        font-weight: 800;
        color: #7a6656;
        text-transform: uppercase;
        letter-spacing: .06em;
    }

    .ann-stat-value {
        margin-top: 6px;
        font-size: 30px;
        line-height: 1;
        font-weight: 800;
        color: #2b211a;
    }

    .ann-stat-sub {
        margin-top: 6px;
        color: #8a7566;
        font-size: 12px;
    }

    .ann-content {
        display: block;
    }

    .ann-list-card {
        border: 1px solid #eadfce;
        border-radius: 18px;
        background: linear-gradient(180deg, #fff 0%, #fffdfa 100%);
        box-shadow: 0 12px 26px rgba(57, 43, 32, .08);
        overflow: hidden;
    }

    .ann-section-head {
        position: relative;
        padding: 14px 16px 14px 18px;
        border-bottom: 1px solid #eee2d4;
        background: linear-gradient(180deg, #fff 0%, #fbf4ec 100%);
    }

    .ann-section-head::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, #8f6f52, #c7a98b);
    }

    .ann-section-head strong {
        display: block;
        font-size: 15px;
        color: #2d221a;
    }

    .ann-section-head span {
        display: block;
        margin-top: 3px;
        color: #7a6656;
        font-size: 12px;
    }

    .ann-list {
        display: grid;
        gap: 12px;
        padding: 14px;
    }

    .ann-item {
        border: 1px solid #eee1d3;
        border-radius: 16px;
        padding: 15px;
        background:
            linear-gradient(180deg, #fffdfb 0%, #fff 100%);
        box-shadow: 0 8px 18px rgba(57, 43, 32, .05);
        transition: transform 170ms ease, box-shadow 170ms ease, border-color 170ms ease;
    }

    .ann-item:hover {
        transform: translateY(-2px);
        border-color: #d6bea6;
        box-shadow: 0 16px 28px rgba(57, 43, 32, .10);
    }

    .ann-item-top {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        justify-content: space-between;
    }

    .ann-item-title {
        margin: 0;
        font-size: 20px;
        line-height: 1.2;
        font-weight: 800;
        color: #2d221a;
    }

    .ann-item-date {
        flex: 0 0 auto;
        color: #8a7566;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .ann-meta {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
        margin: 10px 0 12px;
    }

    .ann-type {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: .22rem .6rem;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        border: 1px solid #e7d9ca;
    }

    .ann-type.scholarship {
        background: #e0f2fe;
        color: #075985;
        border-color: #bae6fd;
    }

    .ann-type.welfare {
        background: #fff7ed;
        color: #b45309;
        border-color: #fed7aa;
    }

    .ann-type.general {
        background: #f5ede6;
        color: #8a7362;
        border-color: #d8c2ad;
    }

    .ann-hint {
        color: #7f6b5c;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .ann-body {
        margin: 0;
        color: #4f3b2d;
        font-size: 14px;
        line-height: 1.78;
        white-space: pre-line;
    }

    .ann-footer {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        margin-top: 14px;
    }

    .ann-link {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        border: 1px solid #ccb49d;
        color: #6e5745;
        border-radius: 10px;
        padding: 8px 11px;
        font-size: 12px;
        font-weight: 800;
        background: #fff;
    }

    .ann-link:hover {
        background: #f7efe7;
    }

    .ann-empty {
        padding: 26px 18px;
        text-align: center;
        color: #7a6656;
        font-size: 14px;
        border: 1px dashed #dfceb9;
        border-radius: 16px;
        background: #fffdfa;
        margin: 14px;
    }

    .ann-pagination nav {
        display: flex;
        justify-content: center;
    }

    body[data-theme="dark"] .ann-hero {
        border-color: rgba(226, 209, 192, .16);
        background:
            radial-gradient(420px 220px at 100% 0%, rgba(255, 227, 183, .14) 0%, transparent 60%),
            radial-gradient(300px 180px at 0% 100%, rgba(95, 190, 145, .10) 0%, transparent 62%),
            linear-gradient(135deg, rgba(43, 33, 25, .96) 0%, rgba(77, 58, 45, .94) 48%, rgba(122, 93, 71, .92) 100%);
        box-shadow: 0 24px 44px rgba(0, 0, 0, .32);
    }

    body[data-theme="dark"] .ann-chip {
        background: rgba(255,255,255,.07);
        border-color: rgba(226, 209, 192, .18);
        color: #fff7ef;
    }

    body[data-theme="dark"] .ann-chip.primary {
        background: linear-gradient(135deg, #b99b82 0%, #e4cdb7 100%);
        color: #18120d;
        border-color: rgba(255,255,255,.08);
    }

    body[data-theme="dark"] .ann-helper,
    body[data-theme="dark"] .ann-stat-label,
    body[data-theme="dark"] .ann-stat-sub,
    body[data-theme="dark"] .ann-item-date,
    body[data-theme="dark"] .ann-side-mini span,
    body[data-theme="dark"] .ann-side-note,
    body[data-theme="dark"] .ann-hint,
    body[data-theme="dark"] .ann-empty {
        color: rgba(247,239,232,.70) !important;
    }

    body[data-theme="dark"] .ann-stat,
    body[data-theme="dark"] .ann-list-card,
    body[data-theme="dark"] .ann-item {
        background:
            linear-gradient(145deg, rgba(31, 27, 23, .88), rgba(14, 13, 12, .80)),
            radial-gradient(circle at 10% 0%, rgba(255,255,255,.06), transparent 36%) !important;
        border-color: rgba(226, 209, 192, .14) !important;
        box-shadow: 0 18px 36px rgba(0,0,0,.24), inset 0 1px 0 rgba(255,255,255,.05);
    }

    body[data-theme="dark"] .ann-stat-value,
    body[data-theme="dark"] .ann-item-title,
    body[data-theme="dark"] .ann-section-head strong {
        color: #fff7ef !important;
    }

    body[data-theme="dark"] .ann-section-head {
        background: linear-gradient(180deg, rgba(255,255,255,.06), rgba(255,255,255,.02)) !important;
        border-color: rgba(226, 209, 192, .12) !important;
    }

    body[data-theme="dark"] .ann-section-head span,
    body[data-theme="dark"] .ann-body {
        color: rgba(247,239,232,.80) !important;
    }

    body[data-theme="dark"] .ann-link {
        background: rgba(255,255,255,.06) !important;
        border-color: rgba(226, 209, 192, .16) !important;
        color: #fff7ef !important;
    }

    body[data-theme="dark"] .ann-link:hover {
        background: rgba(215,191,168,.14) !important;
    }

    @media (max-width: 980px) {
        .ann-stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 700px) {
        .ann-page {
            gap: 12px;
        }

        .ann-toolbar {
            align-items: flex-start;
        }

        .ann-actions {
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 2px;
            width: 100%;
        }

        .ann-chip {
            flex: 0 0 auto;
        }

        .ann-stats {
            grid-template-columns: 1fr;
        }

        .ann-item-top {
            flex-direction: column;
            gap: 6px;
        }

        .ann-item-title {
            font-size: 18px;
        }
    }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:#2d1f14;">{{ __('Pengumuman Biasiswa') }}</h2>
@endsection

@section('content')
@php
    $items = $announcements->getCollection();
    $scholarshipOnly = $items->filter(fn ($item) => strtolower((string) ($item->type ?? 'general')) === 'scholarship')->count();
    $welfareOnly = $items->filter(fn ($item) => strtolower((string) ($item->type ?? 'general')) === 'welfare')->count();
    $generalOnly = $items->filter(fn ($item) => strtolower((string) ($item->type ?? 'general')) === 'general')->count();
@endphp
<div class="ann-page">
    <section class="ann-hero">
        <span class="ann-eyebrow">{{ __('Peluang Biasiswa Pelajar') }}</span>
        <h3>{{ __('Semak pilihan biasiswa, bantuan, dan maklumat tajaan terkini dalam satu halaman yang lebih jelas.') }}</h3>
        <p>{{ __('Gunakan halaman ini untuk melihat tawaran semasa, memahami ringkasan setiap pengumuman, dan membuka pautan rasmi apabila anda mahu membuat semakan lanjut.') }}</p>
    </section>

    <div class="ann-toolbar">
        <div class="ann-actions">
            <a class="ann-chip" href="{{ route('student.scholarships.index') }}">{{ __('Kembali ke Portal Biasiswa') }}</a>
            <a class="ann-chip" href="{{ route('student.dashboard') }}">{{ __('Kembali ke Dashboard') }}</a>
            <a class="ann-chip primary" href="{{ route('student.scholarship-status.form') }}">{{ __('Isi Borang Status Biasiswa') }}</a>
        </div>
        <div class="ann-helper">{{ __('Pilih pengumuman yang sesuai untuk semakan lanjut.') }}</div>
    </div>

    <div class="ann-stats">
        <article class="ann-stat">
            <div class="ann-stat-label">{{ __('Jumlah Pengumuman') }}</div>
            <div class="ann-stat-value">{{ $announcements->total() }}</div>
            <div class="ann-stat-sub">{{ __('Semua maklumat yang sedang dipaparkan untuk pelajar.') }}</div>
        </article>
        <article class="ann-stat">
            <div class="ann-stat-label">{{ __('Biasiswa') }}</div>
            <div class="ann-stat-value">{{ $scholarshipOnly }}</div>
            <div class="ann-stat-sub">{{ __('Tawaran atau makluman berkaitan biasiswa utama.') }}</div>
        </article>
        <article class="ann-stat">
            <div class="ann-stat-label">{{ __('Bantuan / Kebajikan') }}</div>
            <div class="ann-stat-value">{{ $welfareOnly }}</div>
            <div class="ann-stat-sub">{{ __('Maklumat bantuan kewangan atau sokongan pelajar.') }}</div>
        </article>
        <article class="ann-stat">
            <div class="ann-stat-label">{{ __('Umum') }}</div>
            <div class="ann-stat-value">{{ $generalOnly }}</div>
            <div class="ann-stat-sub">{{ __('Hebahan umum berkaitan tajaan dan maklumat tambahan.') }}</div>
        </article>
    </div>

    <div class="ann-content">
        <section class="ann-list-card">
            <div class="ann-section-head">
                <strong>{{ __('Senarai Pengumuman Biasiswa') }}</strong>
                <span>{{ __('Lihat butiran setiap pilihan dan buka pautan rasmi jika tersedia.') }}</span>
            </div>

            @if($announcements->count())
                <div class="ann-list">
                    @foreach($announcements as $item)
                        @php($annType = strtolower((string) ($item->type ?? 'general')))
                        <article class="ann-item">
                            <div class="ann-item-top">
                                <h3 class="ann-item-title">{{ $item->title }}</h3>
                                <div class="ann-item-date">{{ $item->created_at ? \Illuminate\Support\Carbon::parse($item->created_at)->format('d M Y') : '-' }}</div>
                            </div>

                            <div class="ann-meta">
                                <span class="ann-type {{ $annType }}">{{ $annType === 'general' ? __('Umum') : ($annType === 'welfare' ? __('Bantuan / Kebajikan') : __('Biasiswa')) }}</span>
                                <span class="ann-hint">{{ __('Untuk semakan pelajar') }}</span>
                            </div>

                            <p class="ann-body">{{ $item->body }}</p>

                            <div class="ann-footer">
                                <div class="ann-helper">{{ __('Baca ringkasan dahulu sebelum membuka pautan rasmi.') }}</div>
                                @if($item->link_url)
                                    <a class="ann-link" href="{{ $item->link_url }}" target="_blank" rel="noopener">
                                        {{ $item->link_label ?: __('Buka Pautan') }}
                                    </a>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="ann-empty">{{ __('Tiada pengumuman biasiswa buat masa ini.') }}</div>
            @endif
        </section>

    </div>

    <div class="ann-pagination">{{ $announcements->links() }}</div>
</div>
@endsection
