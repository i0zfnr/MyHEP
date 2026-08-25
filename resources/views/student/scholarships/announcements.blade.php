@extends('layouts.app')

@section('title', __('Pengumuman Biasiswa'))



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
