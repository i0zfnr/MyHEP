@extends('layouts.app')

@section('title', __('Scholarship Saya'))



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
                @if($records->isEmpty())
                    <div class="sch-empty-state">
                        <span class="sch-empty-state-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path stroke-linecap="round" stroke-linejoin="round" d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>
                        </span>
                        <strong>{{ __('Tiada rekod scholarship.') }}</strong>
                        <p>{{ __('Rekod biasiswa anda akan dipaparkan di sini apabila tersedia.') }}</p>
                        <a class="sch-empty-action" href="{{ route('student.scholarship-status.form') }}">{{ __('Isi Borang Status Biasiswa') }}</a>
                    </div>
                @else
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
                            @foreach($records as $record)
                            @php($statusClass = strtolower((string) ($record->status ?? 'none')))
                            <tr>
                                <td data-label="{{ __('Jenis') }}">{{ ucfirst((string) $record->type) }}</td>
                                <td data-label="{{ __('Penyedia') }}">{{ $record->provider_name ?: '-' }}</td>
                                <td data-label="{{ __('Jumlah') }}">{{ $record->amount !== null ? number_format((float)$record->amount, 2) : '-' }}</td>
                                <td data-label="{{ __('Status') }}"><span class="status-badge status-{{ $statusClass }}">{{ __($record->status) }}</span></td>
                                <td data-label="{{ __('Tarikh') }}">{{ $record->created_at ? \Illuminate\Support\Carbon::parse($record->created_at)->format('d M Y') : '-' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
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
