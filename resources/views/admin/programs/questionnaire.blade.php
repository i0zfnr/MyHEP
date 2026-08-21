@extends('layouts.app')

@section('title', __('Questionnaire Builder - ').$program->title)

@push('styles')
<style>
.pmr {
    max-width: 1300px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}
.pmr-hero {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1.5rem;
    padding: 1.5rem 1.75rem;
    border-radius: 18px;
    background: var(--surface, #fff);
    border: 1px solid var(--border, #eadac8);
    box-shadow: 0 10px 28px rgba(36,26,18,0.06);
}
.pmr-hero h1 { margin: 0.2rem 0 0.25rem; font-size: 1.45rem; font-weight: 850; color: var(--text, #241d16); }
.pmr-hero p { margin: 0; font-size: 0.85rem; color: var(--text-muted, #746b62); }
.pmr-eyebrow { font-size: 0.72rem; font-weight: 850; letter-spacing: 0.08em; text-transform: uppercase; color: var(--pm-accent, #b99150); }

.pmr-actions {
    display: flex;
    align-items: center;
    gap: 0.65rem;
    flex-wrap: wrap;
}
.pmr-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    gap: 0.5rem !important;
    min-height: 42px !important;
    padding: 0.55rem 1.1rem !important;
    border-radius: 10px !important;
    border: 1px solid var(--border, #eadac8) !important;
    background: var(--surface, #fff) !important;
    color: var(--text, #241d16) !important;
    font-size: 0.86rem !important;
    font-weight: 750 !important;
    text-decoration: none !important;
    cursor: pointer !important;
    white-space: nowrap !important;
    transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease !important;
}
.pmr-btn:hover {
    transform: translateY(-1px) !important;
    box-shadow: 0 6px 16px rgba(36,26,18,0.08) !important;
    background: var(--surface-hover, #fdfbf7) !important;
}
.pmr-btn.primary {
    background: var(--pm-accent, #b99150) !important;
    border-color: var(--pm-accent, #b99150) !important;
    color: #fff !important;
}
.pmr-btn.primary:hover {
    background: color-mix(in srgb, var(--pm-accent, #b99150) 88%, #000) !important;
}
.pmr-btn.public-checkin {
    background: #0284c7 !important;
    border-color: #0284c7 !important;
    color: #fff !important;
}
.pmr-btn.public-checkin:hover {
    background: #0369a1 !important;
}
.pmr-btn svg, .pmr svg {
    width: 16px !important;
    height: 16px !important;
    flex-shrink: 0 !important;
    stroke: currentColor !important;
    stroke-width: 2 !important;
    fill: none !important;
}

.pmr-card {
    background: var(--surface, #fff);
    border: 1px solid var(--border, #eadac8);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 4px 18px rgba(36,26,18,0.04);
}
.pmr-card h2 { margin: 0.2rem 0 0.4rem; font-size: 1.15rem; font-weight: 800; }
.pmr-card p.subtitle { margin: 0 0 1.25rem; font-size: 0.86rem; color: var(--text-muted, #746b62); line-height: 1.45; }

.pmr-mode-panel {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    padding: 1.1rem 1.25rem;
    border-radius: 12px;
    background: color-mix(in srgb, var(--pm-accent, #b99150) 6%, var(--surface, #fff));
    border: 1px solid color-mix(in srgb, var(--pm-accent, #b99150) 20%, var(--border, #eadac8));
}
.pmr-mode-panel select {
    width: 100%;
    padding: 9px 12px;
    border-radius: 9px;
    border: 1px solid var(--border, #eadac8);
    font-size: 0.92rem;
    background: #fff;
}
.pmr-mode-actions {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    margin-top: 0.5rem;
    flex-wrap: wrap;
}

.pmr-ai-box {
    margin-top: 1.25rem;
    padding: 1.25rem;
    border-radius: 14px;
    background: linear-gradient(135deg, color-mix(in srgb, #6366f1 8%, var(--surface, #fff)), color-mix(in srgb, var(--pm-accent, #b99150) 8%, var(--surface, #fff)));
    border: 1px solid color-mix(in srgb, #6366f1 24%, var(--border, #eadac8));
}
.pmr-ai-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr;
    gap: 12px;
}
.pmr-ai-grid label {
    font-size: 0.75rem;
    font-weight: 800;
    text-transform: uppercase;
    color: var(--text-muted, #746b62);
    display: block;
    margin-bottom: 4px;
}
.pmr-ai-grid select {
    width: 100%;
    padding: 9px 12px;
    border-radius: 8px;
    border: 1px solid var(--border, #eadac8);
    background: #fff;
    font-size: 0.88rem;
}

.pmr-q-item {
    padding: 1.15rem;
    border-radius: 12px;
    border: 1px solid var(--border, #eadac8);
    background: color-mix(in srgb, var(--surface, #fff) 96%, var(--pm-accent, #b99150));
    margin-bottom: 12px;
    transition: transform 150ms ease, box-shadow 150ms ease;
}
.pmr-q-item:hover {
    box-shadow: 0 4px 14px rgba(36,26,18,0.06);
}
.pmr-q-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 0.75rem;
}
.pmr-q-title {
    display: flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.88rem;
    font-weight: 800;
}
.pmr-q-number {
    display: inline-grid;
    place-items: center;
    width: 24px;
    height: 24px;
    border-radius: 7px;
    background: var(--pm-accent, #b99150);
    color: #fff;
    font-size: 0.75rem;
    font-weight: 900;
}
.pmr-q-field {
    display: block;
    margin-bottom: 0.65rem;
}
.pmr-q-field span {
    display: block;
    font-size: 0.72rem;
    font-weight: 800;
    color: var(--text-muted, #746b62);
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.pmr-q-field input, .pmr-q-field select {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid var(--border, #eadac8);
    border-radius: 8px;
    font-size: 0.9rem;
    background: #fff;
    color: var(--text, #241d16);
    box-sizing: border-box;
}
.pmr-q-required {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--text, #241d16);
    cursor: pointer;
    margin-top: 4px;
}
.pmr-remove {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    min-height: 32px;
    padding: 0.3rem 0.6rem;
    border: 0;
    border-radius: 8px;
    background: transparent;
    color: var(--se-danger, #b42318);
    font: inherit;
    font-size: 0.76rem;
    font-weight: 800;
    cursor: pointer;
}
.pmr-remove:hover {
    background: color-mix(in srgb, var(--se-danger, #b42318) 8%, transparent);
}
.pmr-remove svg {
    width: 14px !important;
    height: 14px !important;
    fill: none;
    stroke: currentColor;
    stroke-width: 2;
}

.pmr-published-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    padding: 0.65rem 1rem;
    border-radius: 10px;
    background: color-mix(in srgb, #21835a 12%, var(--surface, #fff));
    color: #187048;
    border: 1px solid color-mix(in srgb, #21835a 30%, var(--border, #eadac8));
    font-size: 0.84rem;
    font-weight: 800;
}
.pmr-published-badge::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
    box-shadow: 0 0 0 3px color-mix(in srgb, currentColor 18%, transparent);
}

@media (max-width: 768px) {
    .pmr-hero {
        flex-direction: column;
        align-items: stretch;
    }
    .pmr-ai-grid {
        grid-template-columns: 1fr;
    }
}
</style>
@endpush

@push('styles')
@include('admin.programs.partials.design-system')
@endpush

@section('content')
<main class="pmr">
    @if(session('success'))
        <div class="alert alert-success" style="border-radius: 12px; margin-bottom: 0;">{{ session('success') }}</div>
    @endif
    @if(isset($errors) && $errors->any())
        <div class="alert alert-danger" style="border-radius: 12px; margin-bottom: 0;">
            @foreach($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    @endif

    <!-- Hero Header with Modern Actions -->
    <header class="pmr-hero">
        <div>
            <span class="pmr-eyebrow">{{ __('PROGRAM QUESTIONNAIRE BUILDER') }}</span>
            <h1>{{ $program->title }}</h1>
            <p>{{ $program->reference_no ?: __('No reference number') }} &middot; {{ __('Venue:') }} <strong>{{ $program->venue ?: __('Not set') }}</strong></p>
        </div>
        <div class="pmr-actions">
            <!-- Toggle Analytics & Visualization Button -->
            <button type="button" class="pmr-btn" id="btnToggleAnalytics" style="background: linear-gradient(135deg, rgba(212,175,55,0.15), rgba(99,102,241,0.15)); border-color: var(--pm-accent); color: var(--text); font-weight: 850; gap: 0.6rem;">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
                <span id="btnToggleAnalyticsText">{{ __('Statistik & Visualisasi') }}</span>
                <span id="analyticsStatusPill" style="font-size: 0.7rem; padding: 2px 8px; border-radius: 999px; background: rgba(16,185,129,0.2); color: #059669; font-weight: 900; border: 1px solid rgba(16,185,129,0.4);">ON</span>
            </button>
            <a class="pmr-btn" href="{{ route('admin.programs.operations', $program->id) }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
                {{ __('Back to Operations') }}
            </a>
            <a class="pmr-btn public-checkin" href="{{ $publicCheckinUrl }}" target="_blank" rel="noopener">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 5h5v5"/><path d="m10 14 9-9"/><path d="M19 13v5a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h5"/></svg>
                {{ __('Open Public Check-in') }}
            </a>
        </div>
    </header>

    <!-- WOW Interactive Visual Analytics & Statistics Dashboard -->
    <section id="analyticsDashboardSection" class="pmr-card" style="border: 1px solid rgba(212,175,55,0.35); background: linear-gradient(170deg, var(--surface, #fff), color-mix(in srgb, var(--pm-accent, #b99150) 4%, var(--surface, #fff))); box-shadow: 0 12px 35px rgba(36,26,18,0.06);">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
            <div>
                <span class="pmr-eyebrow" style="color: #6366f1; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 20V10M12 20V4M6 20v-6"/></svg>
                    {{ __('VISUALISASI & ANALISIS MAKLUM BALAS') }}
                </span>
                <h2 style="font-size: 1.35rem; margin: 0.2rem 0 0.1rem; color: var(--text);">{{ __('Statistik Prestasi & Kepuasan Peserta') }}</h2>
                <p style="margin: 0; font-size: 0.84rem; color: var(--text-muted);">{{ __('Ringkasan analisis soal selidik program, pecahan responden dan skor terperinci secara masa nyata.') }}</p>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <span style="font-size: 0.78rem; font-weight: 750; color: var(--text-muted);">{{ __('Kadar Respons:') }}</span>
                <strong style="font-size: 0.95rem; color: var(--pm-accent);">{{ $analytics['response_rate'] }}%</strong>
            </div>
        </div>

        <!-- 1. Executive Metric KPI Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
            <!-- KPI 1: Jumlah Responden -->
            <div style="background: var(--surface, #fff); border: 1px solid var(--border); border-radius: 14px; padding: 1.15rem; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                    <span style="font-size: 0.74rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">{{ __('Jumlah Responden') }}</span>
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(99,102,241,0.12); color: #6366f1; display: grid; place-items: center;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </span>
                </div>
                <strong style="font-size: 1.8rem; font-weight: 900; color: var(--text); display: block;">{{ number_format($analytics['total_responses']) }}</strong>
                <span style="font-size: 0.8rem; color: var(--text-muted);">{{ __('Daripada :total peserta hadir (:rate%)', ['total' => $analytics['total_attendances'], 'rate' => $analytics['response_rate']]) }}</span>
            </div>

            <!-- KPI 2: Indeks Kepuasan -->
            <div style="background: var(--surface, #fff); border: 1px solid var(--border); border-radius: 14px; padding: 1.15rem; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                    <span style="font-size: 0.74rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">{{ __('Indeks Kepuasan (CSI)') }}</span>
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(212,175,55,0.15); color: #b99150; display: grid; place-items: center;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    </span>
                </div>
                <div style="display: flex; align-items: baseline; gap: 0.4rem;">
                    <strong style="font-size: 1.8rem; font-weight: 900; color: var(--pm-accent);">{{ $analytics['overall_avg'] > 0 ? number_format($analytics['overall_avg'], 2) : '0.00' }}</strong>
                    <span style="font-size: 0.9rem; color: var(--text-muted); font-weight: 700;">/ 5.0</span>
                </div>
                <span style="font-size: 0.8rem; color: #059669; font-weight: 750;">{{ $analytics['satisfaction_percentage'] }}% {{ __('Penarafan Positif') }}</span>
            </div>

            <!-- KPI 3: Pecahan Pelajar PB vs Tetamu Luar -->
            <div style="background: var(--surface, #fff); border: 1px solid var(--border); border-radius: 14px; padding: 1.15rem; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                    <span style="font-size: 0.74rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">{{ __('Pecahan Kategori') }}</span>
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16,185,129,0.12); color: #059669; display: grid; place-items: center;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                    </span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.2rem;">
                    <div>
                        <strong style="font-size: 1.25rem; font-weight: 900; color: #059669;">{{ number_format($analytics['internal_count']) }}</strong>
                        <span style="display: block; font-size: 0.75rem; color: var(--text-muted);">{{ __('Pelajar PB') }}</span>
                    </div>
                    <div style="width: 1px; height: 26px; background: var(--border);"></div>
                    <div>
                        <strong style="font-size: 1.25rem; font-weight: 900; color: #0284c7;">{{ number_format($analytics['external_count']) }}</strong>
                        <span style="display: block; font-size: 0.75rem; color: var(--text-muted);">{{ __('Tetamu Luar') }}</span>
                    </div>
                </div>
            </div>

            <!-- KPI 4: Jumlah Soalan -->
            <div style="background: var(--surface, #fff); border: 1px solid var(--border); border-radius: 14px; padding: 1.15rem; box-shadow: 0 4px 12px rgba(0,0,0,0.02);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                    <span style="font-size: 0.74rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">{{ __('Item Borang Soalan') }}</span>
                    <span style="width: 32px; height: 32px; border-radius: 8px; background: rgba(234,179,8,0.14); color: #ca8a04; display: grid; place-items: center;">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    </span>
                </div>
                <strong style="font-size: 1.8rem; font-weight: 900; color: var(--text); display: block;">{{ count($questions) }}</strong>
                <span style="font-size: 0.8rem; color: var(--text-muted);">{{ __('Standard Format SA-04 Politeknik') }}</span>
            </div>
        </div>

        <!-- 2. Dual Visual Charts Grid: Donut Pie + Rating Histogram -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 1.25rem; margin-bottom: 1.5rem;">
            <!-- Left Chart: Donut Breakdown (Pie Chart) -->
            <div style="background: var(--surface, #fff); border: 1px solid var(--border); border-radius: 16px; padding: 1.35rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                    <div>
                        <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); display: inline-flex; align-items: center; gap: 0.35rem;">
                            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 2a10 10 0 0 1 10 10H12z"/></svg>
                            {{ __('CARTA KOMPOSISI') }}
                        </span>
                        <h3 style="font-size: 1.05rem; font-weight: 850; margin: 0.1rem 0 0;">{{ __('Pecahan Komposisi Peserta') }}</h3>
                    </div>
                    <span style="font-size: 0.76rem; font-weight: 800; background: rgba(212,175,55,0.12); color: var(--pm-accent); padding: 0.25rem 0.65rem; border-radius: 999px;">
                        {{ $analytics['total_attendances'] }} {{ __('Peserta') }}
                    </span>
                </div>

                @php
                    $totalAtt = max(1, $analytics['total_attendances']);
                    $pbPct = round(($analytics['internal_count'] / $totalAtt) * 100);
                    $extPct = 100 - $pbPct;
                    // SVG Donut calculation
                    $radius = 42;
                    $circ = 2 * pi() * $radius; // ~263.89
                    $pbOffset = $circ * ($pbPct / 100);
                @endphp

                <div style="display: flex; align-items: center; justify-content: center; gap: 2rem; flex-wrap: wrap; padding: 0.75rem 0;">
                    <!-- SVG Pie / Donut Chart -->
                    <div style="position: relative; width: 140px; height: 140px;">
                        <svg width="140" height="140" viewBox="0 0 100 100" style="transform: rotate(-90deg);">
                            <!-- Background Track -->
                            <circle cx="50" cy="50" r="{{ $radius }}" fill="transparent" stroke="#f1f5f9" stroke-width="14" />
                            <!-- External Segment -->
                            <circle cx="50" cy="50" r="{{ $radius }}" fill="transparent" stroke="#0284c7" stroke-width="14" stroke-dasharray="{{ $circ }}" stroke-dashoffset="0" />
                            <!-- Internal PB Segment -->
                            <circle cx="50" cy="50" r="{{ $radius }}" fill="transparent" stroke="#10b981" stroke-width="14" stroke-dasharray="{{ $pbOffset }} {{ $circ }}" stroke-linecap="round" />
                        </svg>
                        <div style="position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center;">
                            <strong style="font-size: 1.35rem; font-weight: 900; color: var(--text); line-height: 1;">{{ $pbPct }}%</strong>
                            <span style="font-size: 0.68rem; font-weight: 750; color: var(--text-muted); text-transform: uppercase;">PB Pelajar</span>
                        </div>
                    </div>

                    <!-- Legend & Details -->
                    <div style="display: flex; flex-direction: column; gap: 0.75rem; min-width: 150px;">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.45rem;">
                                <span style="width: 12px; height: 12px; border-radius: 4px; background: #10b981;"></span>
                                <span style="font-size: 0.84rem; font-weight: 750; color: var(--text);">{{ __('Pelajar PB') }}</span>
                            </div>
                            <strong style="font-size: 0.88rem; color: #059669;">{{ $analytics['internal_count'] }} ({{ $pbPct }}%)</strong>
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem;">
                            <div style="display: flex; align-items: center; gap: 0.45rem;">
                                <span style="width: 12px; height: 12px; border-radius: 4px; background: #0284c7;"></span>
                                <span style="font-size: 0.84rem; font-weight: 750; color: var(--text);">{{ __('Tetamu Luar') }}</span>
                            </div>
                            <strong style="font-size: 0.88rem; color: #0284c7;">{{ $analytics['external_count'] }} ({{ $extPct }}%)</strong>
                        </div>
                        <div style="border-top: 1px dashed var(--border); padding-top: 0.6rem; display: flex; align-items: center; justify-content: space-between; font-size: 0.8rem; color: var(--text-muted);">
                            <span>{{ __('Jumlah Responden') }}</span>
                            <strong style="color: var(--text);">{{ $analytics['total_responses'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Chart: Score Distribution Histogram Bars (5★ to 1★) -->
            <div style="background: var(--surface, #fff); border: 1px solid var(--border); border-radius: 16px; padding: 1.35rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                    <div>
                        <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">{{ __('TABURAN SKOR KESELURUHAN') }}</span>
                        <h3 style="font-size: 1.05rem; font-weight: 850; margin: 0.1rem 0 0;">{{ __('Pecahan Penarafan Bintang') }}</h3>
                    </div>
                    <span style="font-size: 0.76rem; font-weight: 800; background: rgba(99,102,241,0.12); color: #6366f1; padding: 0.25rem 0.65rem; border-radius: 999px;">
                        Skala 1 - 5
                    </span>
                </div>

                @php
                    $totalRatings = max(1, array_sum($analytics['rating_distribution']));
                    $ratingLabels = [
                        5 => ['label' => '5 ★ Sangat Cemerlang', 'color' => '#d4af37'],
                        4 => ['label' => '4 ★ Cemerlang / Setuju', 'color' => '#10b981'],
                        3 => ['label' => '3 ★ Sederhana / Baik', 'color' => '#0284c7'],
                        2 => ['label' => '2 ★ Kurang Memuaskan', 'color' => '#f59e0b'],
                        1 => ['label' => '1 ★ Sangat Lemah', 'color' => '#ef4444'],
                    ];
                @endphp

                <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                    @foreach([5, 4, 3, 2, 1] as $star)
                        @php
                            $cnt = $analytics['rating_distribution'][$star] ?? 0;
                            $pct = round(($cnt / $totalRatings) * 100);
                            $cfg = $ratingLabels[$star];
                        @endphp
                        <div>
                            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.8rem; font-weight: 750; margin-bottom: 0.2rem;">
                                <span style="color: var(--text);">{{ $cfg['label'] }}</span>
                                <span style="color: var(--text-muted);"><strong>{{ $cnt }}</strong> ({{ $pct }}%)</span>
                            </div>
                            <div style="height: 9px; border-radius: 999px; background: #f1f5f9; overflow: hidden; width: 100%;">
                                <div style="height: 100%; width: {{ $pct }}%; background: {{ $cfg['color'] }}; border-radius: 999px; transition: width 0.5s ease;"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- 3. Question Item Performance Breakdown (SA-04 Questions Histogram) -->
        @if(!empty($analytics['question_stats']))
            <div style="background: var(--surface, #fff); border: 1px solid var(--border); border-radius: 16px; padding: 1.35rem; margin-bottom: 1.5rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                        <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">{{ __('ANALISIS PRESTASI SETIAP ITEM SOALAN') }}</span>
                        <h3 style="font-size: 1.05rem; font-weight: 850; margin: 0.1rem 0 0;">{{ __('Skor Purata Mengikut Soalan SA-04') }}</h3>
                    </div>
                    <span style="font-size: 0.76rem; font-weight: 800; color: var(--text-muted);">
                        {{ count($analytics['question_stats']) }} {{ __('Item Dinilai') }}
                    </span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1rem;">
                    @foreach($analytics['question_stats'] as $idx => $qStat)
                        @php
                            $maxScore = $qStat['type'] === 'rating_4' ? 4 : 5;
                            $score = $qStat['avg_score'] ?? 0;
                            $scorePct = $maxScore > 0 ? round(($score / $maxScore) * 100) : 0;
                            $barColor = $scorePct >= 80 ? '#10b981' : ($scorePct >= 60 ? '#d4af37' : '#f59e0b');
                        @endphp
                        <div style="border: 1px solid var(--border); border-radius: 12px; padding: 1rem; background: var(--bg-alt, #faf7f2);">
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <div style="display: flex; align-items: center; gap: 0.4rem;">
                                    <span style="width: 22px; height: 22px; border-radius: 6px; background: var(--pm-accent); color: #fff; font-size: 0.72rem; font-weight: 900; display: grid; place-items: center;">{{ $idx + 1 }}</span>
                                    <span style="font-size: 0.84rem; font-weight: 800; color: var(--text); line-height: 1.35;">{{ \Illuminate\Support\Str::limit($qStat['text'], 65) }}</span>
                                </div>
                                @if($score > 0)
                                    <strong style="font-size: 0.95rem; font-weight: 900; color: {{ $barColor }}; white-space: nowrap;">
                                        {{ number_format($score, 2) }} <span style="font-size: 0.72rem; color: var(--text-muted);">/{{ $maxScore }}</span>
                                    </strong>
                                @endif
                            </div>

                            @if($score > 0)
                                <div style="height: 7px; border-radius: 999px; background: #e2e8f0; overflow: hidden; margin-bottom: 0.35rem;">
                                    <div style="height: 100%; width: {{ $scorePct }}%; background: {{ $barColor }}; border-radius: 999px;"></div>
                                </div>
                                <div style="display: flex; justify-content: space-between; font-size: 0.72rem; color: var(--text-muted);">
                                    <span>{{ $qStat['total_answers'] }} {{ __('respons direkodkan') }}</span>
                                    <span style="font-weight: 750; color: {{ $barColor }};">{{ $scorePct }}%</span>
                                </div>
                            @else
                                <span style="font-size: 0.75rem; color: var(--text-muted); font-style: italic;">{{ __('Tiada respons berangka lagi') }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- 4. Qualitative Feedback Highlights Stream -->
        @if(!empty($analytics['recent_comments']) && $analytics['recent_comments']->isNotEmpty())
            <div style="background: var(--surface, #fff); border: 1px solid var(--border); border-radius: 16px; padding: 1.35rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1rem;">
                    <div>
                        <span style="font-size: 0.72rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted);">{{ __('KOMEN & SUARA PESERTA') }}</span>
                        <h3 style="font-size: 1.05rem; font-weight: 850; margin: 0.1rem 0 0;">{{ __('Maklum Balas Terkini Peserta') }}</h3>
                    </div>
                    <span style="font-size: 0.76rem; font-weight: 800; color: #6366f1;">{{ count($analytics['recent_comments']) }} {{ __('Komen Terkini') }}</span>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 0.85rem;">
                    @foreach($analytics['recent_comments'] as $comm)
                        <div style="background: var(--bg-alt, #faf7f2); border: 1px solid var(--border); border-radius: 12px; padding: 0.95rem;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.4rem;">
                                <strong style="font-size: 0.86rem; color: var(--text);">{{ $comm->full_name }}</strong>
                                @if($comm->satisfaction_rating)
                                    <span style="font-size: 0.78rem; font-weight: 800; color: #b99150;">★ {{ $comm->satisfaction_rating }}/5</span>
                                @endif
                            </div>
                            <p style="font-size: 0.82rem; color: var(--text-secondary, #4b5563); margin: 0 0 0.5rem; font-style: italic; line-height: 1.4;">
                                "{{ \Illuminate\Support\Str::limit($comm->feedback_comments, 140) }}"
                            </p>
                            <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.72rem; color: var(--text-muted);">
                                <span>{{ $comm->attendee_type === 'internal' ? 'Pelajar PB' : ($comm->institution_or_unit ?: 'Tetamu Luar') }}</span>
                                <span>{{ \Illuminate\Support\Carbon::parse($comm->checked_in_at)->diffForHumans() }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    <!-- 1. Questionnaire Publishing Mode Configuration -->
    <section class="pmr-card">
        <span class="pmr-eyebrow" style="display: inline-flex; align-items: center; gap: 0.35rem;">
            <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
            {{ __('KAWALAN SOAL SELIDIK & PENERBITAN') }}
        </span>
        <h2>{{ __('Tetapan Penerbitan Soal Selidik') }}</h2>
        <p class="subtitle">{{ __('Tentukan cara penerbitan borang maklum balas ini: secara terus dalam portal pelajar Politeknik Besut tanpa imbasan QR, melalui imbasan QR, atau ditutup/deraf.') }}</p>

        <form method="post" action="{{ route('admin.programs.questionnaire-setting.update', $program->id) }}" class="pmr-mode-panel">
            @csrf @method('put')
            <label for="participationMode" style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted, #746b62);">{{ __('Mod Penerbitan') }}</label>
            <select id="participationMode" name="questionnaire_publish_mode">
                <option value="internal_system" @selected(($program->questionnaire_publish_mode ?? 'internal_system') === 'internal_system' && $program->questionnaire_enabled)>
                    {{ __('Mod 1: Terus Dalam Sistem (Pelajar PB jawab terus di portal/PWA tanpa imbas QR)') }}
                </option>
                <option value="qr_code" @selected(($program->questionnaire_publish_mode ?? '') === 'qr_code' && $program->questionnaire_enabled)>
                    {{ __('Mod 2: Mod Imbasan QR (PWA Pelajar & Tetamu Luar imbas kod QR)') }}
                </option>
                <option value="closed" @selected(!$program->questionnaire_enabled || ($program->questionnaire_publish_mode ?? '') === 'closed')>
                    {{ __('Soal Selidik Ditutup / Deraf (Mod Kehadiran Sahaja)') }}
                </option>
            </select>
            <div class="pmr-mode-actions">
                <button class="pmr-btn primary" type="submit">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                    {{ __('Simpan Tetapan Mod') }}
                </button>
            </div>
        </form>
    </section>

    <!-- 2. Interactive Questionnaire Builder Workspace -->
    <div id="questionnaireBuilderContent">

        <!-- Question Editor Form with 100% Official SA-04(1) Baseline -->
        <section class="pmr-card">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 0.75rem; border-bottom: 1px solid var(--border); padding-bottom: 0.75rem;">
                <div>
                    <span class="pmr-eyebrow" style="display: inline-flex; align-items: center; gap: 0.35rem;">
                        <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                        {{ __('TEMPLAT RASMI SA-04(1) (P00) (24-12-24)') }}
                    </span>
                    <h2>{{ __('Borang Penilaian Program & Soal Selidik') }}</h2>
                </div>
                <div style="display: flex; gap: 0.6rem; flex-wrap: wrap;">
                    <button type="button" class="pmr-btn" id="btnLoadSa04" style="background: rgba(212,175,55,0.12); border-color: var(--pm-accent); color: var(--text); font-weight: 800; font-size: 0.82rem;">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"/></svg>
                        {{ __('Muat Semula Templat Rasmi SA-04(1)') }}
                    </button>
                    <button type="button" class="pmr-btn" onclick="addQuestionRow()" style="font-size: 0.82rem;">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        {{ __('Tambah Soalan Tambahan') }}
                    </button>
                </div>
            </div>

            <!-- Official Scoring Guide Banner -->
            <div style="background: var(--bg-alt, #faf7f2); border: 1px solid var(--border); border-radius: 12px; padding: 0.9rem 1.15rem; margin-bottom: 1.25rem;">
                <span style="font-size: 0.76rem; font-weight: 800; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 0.4rem;">
                    {{ __('Panduan Skor Borang SA-04(1):') }}
                </span>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap; font-size: 0.82rem; font-weight: 700; color: var(--text);">
                    <span><strong>1:</strong> Sangat Tidak Setuju</span>
                    <span><strong>2:</strong> Tidak Setuju</span>
                    <span><strong>3:</strong> Setuju</span>
                    <span><strong>4:</strong> Sangat Setuju</span>
                </div>
            </div>

            <form method="post" action="{{ route('admin.programs.survey.save', $program->id) }}">
                @csrf
                <input type="hidden" name="title" value="{{ $survey->title ?? 'Borang Penilaian Program [SA-04(1)] - '.$program->title }}">
                <input type="hidden" name="description" value="{{ $survey->description ?? 'Sila maklumkan pandangan anda terhadap program latihan yang telah diikuti ini dengan menanda pada ruangan yang sesuai berpandukan kepada skor di atas.' }}">

                <div id="questionsContainer">
                    @forelse($questions as $index => $q)
                        <div class="pmr-q-item">
                            <div class="pmr-q-head">
                                <div class="pmr-q-title">
                                    <span class="pmr-q-number">{{ $index + 1 }}</span>
                                    <span>{{ __('Soalan') }} {{ $index + 1 }}</span>
                                </div>
                                <button type="button" class="pmr-btn" style="padding: 4px 10px; font-size: 0.76rem; min-height: 28px; color: #dc2626; border-color: rgba(220,38,38,0.25);" onclick="this.closest('.pmr-q-item').remove()">
                                    <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                    {{ __('Padam') }}
                                </button>
                            </div>
                            <label class="pmr-q-field">
                                <span>{{ __('Teks Soalan') }}</span>
                                <input type="text" name="questions[{{ $index }}][question_text]" value="{{ $q->question_text }}" required>
                            </label>
                            <label class="pmr-q-field">
                                <span>{{ __('Jenis Jawapan / Skala') }}</span>
                                <select name="questions[{{ $index }}][question_type]">
                                    <option value="rating_4" @selected($q->question_type === 'rating_4')>{{ __('Skala Likert 1-4 (Borang SA-04: Sangat Tidak Setuju - Sangat Setuju)') }}</option>
                                    <option value="rating_5" @selected($q->question_type === 'rating_5')>{{ __('Skala 1-5 Bintang (Sangat Rendah - Sangat Cemerlang)') }}</option>
                                    <option value="text" @selected($q->question_type === 'text')>{{ __('Jawapan Bertulis / Ulasan (Long Written Answer)') }}</option>
                                </select>
                            </label>
                            <label class="pmr-q-required">
                                <input type="hidden" name="questions[{{ $index }}][is_required]" value="0">
                                <input type="checkbox" name="questions[{{ $index }}][is_required]" value="1" @checked($q->is_required)>
                                <span>{{ __('Soalan wajib dijawab') }}</span>
                            </label>
                        </div>
                    @empty
                        <!-- Standard SA-04(1) items will be dynamically initialized via JS -->
                    @endforelse
                </div>

                <div style="display: flex; gap: 10px; margin-top: 1.25rem; flex-wrap: wrap;">
                    <button type="button" class="pmr-btn" onclick="addQuestionRow()">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        {{ __('Tambah Soalan') }}
                    </button>
                    <button type="submit" class="pmr-btn primary">
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        {{ __('Simpan Soal Selidik') }}
                    </button>
                </div>
            </form>

            @if($survey && $survey->status !== 'published')
                <form method="post" action="{{ route('admin.programs.survey.publish', $program->id) }}" style="margin-top: 14px; border-top: 1px solid var(--border, #eadac8); padding-top: 14px;">
                    @csrf
                    <button type="submit" class="pmr-btn primary" style="width: 100%; justify-content: center; min-height: 44px; font-size: 0.95rem;">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        {{ __('Terbitkan Soal Selidik Kepada Peserta') }}
                    </button>
                </form>
            @endif
        </section>

    </div>
</main>

<script>
let questionCounter = {{ count($questions) }};

// 100% Official Borang SA-04(1) (P00) (24-12-24) Template Items
const officialSa04Questions = [
    // Penilaian Penceramah
    { text: 'Objektif latihan tercapai', type: 'rating_4', required: true },
    { text: 'Kandungan latihan adalah sesuai', type: 'rating_4', required: true },
    { text: 'Penyampaian yang baik dan berkesan', type: 'rating_4', required: true },
    { text: 'Penggunaan alat bantuan mengajar dengan berkesan.', type: 'rating_4', required: true },

    // Penilaian Pelaksanaan Latihan
    { text: 'Suasana tempat latihan yang sesuai / kondusif', type: 'rating_4', required: true },
    { text: 'Perancangan dan perlaksanaan program telah dibuat dengan lancar', type: 'rating_4', required: true },
    { text: 'Masa yang diperuntukan bagi setiap modul adalah sesuai', type: 'rating_4', required: true },

    // Penilaian Keberkesanan Latihan Terhadap Peserta
    { text: 'Meningkatkan pengetahuan / pemahaman.', type: 'rating_4', required: true },
    { text: 'Lebih berkeyakinan mengajar modul berkenaan / menjalankan tugas berkaitan / mengaplikasi apa yang dipelajari.', type: 'rating_4', required: true },
    { text: 'Pada keseluruhannya latihan ini adalah berjaya dan bermanfaat.', type: 'rating_4', required: true },

    // Ulasan Peserta
    { text: 'Kesediaan untuk berkongsi ilmu yang diperolehi berkaitan latihan. YA / TIDAK, jika TIDAK sila nyatakan sebab.', type: 'text', required: false }
];

function escapeQuestionValue(value) {
    return String(value).replace(/[&<>'"]/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;'
    })[character]);
}

function addQuestionRow(text = '', type = 'rating_4', required = true) {
    const container = document.getElementById('questionsContainer');
    if (!container) return;
    const div = document.createElement('div');
    div.className = 'pmr-q-item';
    div.innerHTML = `
        <div class="pmr-q-head">
            <div class="pmr-q-title">
                <span class="pmr-q-number">${questionCounter + 1}</span>
                <span>{{ __('Soalan') }} ${questionCounter + 1}</span>
            </div>
            <button type="button" class="pmr-btn" style="padding: 4px 10px; font-size: 0.76rem; min-height: 28px; color: #dc2626; border-color: rgba(220,38,38,0.25);" onclick="this.closest('.pmr-q-item').remove()">
                <svg viewBox="0 0 24 24" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                {{ __('Padam') }}
            </button>
        </div>
        <label class="pmr-q-field">
            <span>{{ __('Teks Soalan') }}</span>
            <input type="text" name="questions[${questionCounter}][question_text]" value="${escapeQuestionValue(text)}" required placeholder="{{ __('Masukkan teks soalan') }}">
        </label>
        <label class="pmr-q-field">
            <span>{{ __('Jenis Jawapan / Skala') }}</span>
            <select name="questions[${questionCounter}][question_type]">
                <option value="rating_4" ${type === 'rating_4' ? 'selected' : ''}>{{ __('Skala Likert 1-4 (Borang SA-04: Sangat Tidak Setuju - Sangat Setuju)') }}</option>
                <option value="rating_5" ${type === 'rating_5' ? 'selected' : ''}>{{ __('Skala 1-5 Bintang (Sangat Rendah - Sangat Cemerlang)') }}</option>
                <option value="text" ${type === 'text' ? 'selected' : ''}>{{ __('Jawapan Bertulis / Ulasan (Long Written Answer)') }}</option>
            </select>
        </label>
        <label class="pmr-q-required">
            <input type="hidden" name="questions[${questionCounter}][is_required]" value="0">
            <input type="checkbox" name="questions[${questionCounter}][is_required]" value="1" ${required ? 'checked' : ''}>
            <span>{{ __('Soalan wajib dijawab') }}</span>
        </label>
    `;
    container.appendChild(div);
    questionCounter++;
}

document.getElementById('btnLoadSa04')?.addEventListener('click', () => {
    const container = document.getElementById('questionsContainer');
    if (!container) return;
    container.innerHTML = '';
    questionCounter = 0;
    officialSa04Questions.forEach(q => {
        addQuestionRow(q.text, q.type, q.required);
    });
});

// Auto-populate with official SA-04 questions if container is currently empty
if (questionCounter === 0) {
    document.getElementById('btnLoadSa04')?.click();
}

// Analytics Dashboard ON/OFF Toggle Logic
const btnToggleAnalytics = document.getElementById('btnToggleAnalytics');
const analyticsSection = document.getElementById('analyticsDashboardSection');
const analyticsStatusPill = document.getElementById('analyticsStatusPill');

function setAnalyticsVisibility(show) {
    if (!analyticsSection) return;
    if (show) {
        analyticsSection.style.display = 'block';
        if (analyticsStatusPill) {
            analyticsStatusPill.textContent = 'ON';
            analyticsStatusPill.style.background = 'rgba(16,185,129,0.2)';
            analyticsStatusPill.style.color = '#059669';
            analyticsStatusPill.style.borderColor = 'rgba(16,185,129,0.4)';
        }
    } else {
        analyticsSection.style.display = 'none';
        if (analyticsStatusPill) {
            analyticsStatusPill.textContent = 'OFF';
            analyticsStatusPill.style.background = 'rgba(100,116,139,0.2)';
            analyticsStatusPill.style.color = '#64748b';
            analyticsStatusPill.style.borderColor = 'rgba(100,116,139,0.4)';
        }
    }
    localStorage.setItem('show_questionnaire_analytics', show ? 'true' : 'false');
}

// Load saved preference, default to true (ON)
const savedAnalyticsState = localStorage.getItem('show_questionnaire_analytics');
const isAnalyticsVisible = savedAnalyticsState === null ? true : savedAnalyticsState === 'true';
setAnalyticsVisibility(isAnalyticsVisible);

btnToggleAnalytics?.addEventListener('click', () => {
    const isCurrentlyVisible = analyticsSection.style.display !== 'none';
    setAnalyticsVisibility(!isCurrentlyVisible);
});
</script>
@endsection
