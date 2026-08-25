@extends('layouts.app')

@section('title', __('Food Bank Siswa'))

@section('header')
    <h2 style="margin:0;font-size:1.15rem;font-weight:800;color:var(--text);">{{ __('Food Bank Siswa') }}</h2>
@endsection

@section('content')
<div class="ui-shell fb-container">
    <!-- Hero Card -->
    <div class="fb-hero-card">
        <div class="fb-hero-content">
            <div class="fb-hero-badge">
                <span class="fb-badge-dot"></span>
                <span>{{ __('Inisiatif Kebajikan HEP') }}</span>
            </div>
            <h3>{{ __('Food Bank Siswa Politeknik Besut') }}</h3>
            <p>{{ __('Inisiatif khas Hal Ehwal Pelajar (Unit Kebajikan) untuk menyediakan bantuan makanan percuma kepada para pelajar Politeknik Besut yang memerlukan.') }}</p>
        </div>
        <div class="fb-hero-action">
            <a href="{{ route('student.movements.scan') }}" class="fb-btn-gold">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                <span>{{ __('Imbas QR Food Bank') }}</span>
            </a>
        </div>
    </div>

    <!-- Student Stats -->
    <div class="fb-stats-grid">
        <div class="fb-stat-card">
            <div class="fb-stat-icon-wrap icon-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
            </div>
            <div class="fb-stat-info">
                <div class="fb-stat-val">{{ $totalClaims }}</div>
                <div class="fb-stat-lbl">{{ __('Jumlah Penebusan') }}</div>
            </div>
        </div>

        <div class="fb-stat-card">
            <div class="fb-stat-icon-wrap icon-accent">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="fb-stat-info">
                <div class="fb-stat-val">{{ $claimsThisMonth }}</div>
                <div class="fb-stat-lbl">{{ __('Penebusan Bulan Ini') }}</div>
            </div>
        </div>

        <div class="fb-stat-card">
            <div class="fb-stat-icon-wrap icon-success">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
            </div>
            <div class="fb-stat-info">
                <div class="fb-stat-val text-date">{{ $lastClaim ? \Carbon\Carbon::parse($lastClaim->claimed_at)->format('d/m/Y') : __('Belum Ada') }}</div>
                <div class="fb-stat-lbl">{{ __('Penebusan Terakhir') }}</div>
            </div>
        </div>
    </div>

    <!-- Quick Scan / Action Card -->
    <div class="fb-guide-card">
        <div class="fb-guide-header">
            <div class="fb-guide-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12"/>
                    <line x1="12" y1="8" x2="12.01" y2="8"/>
                </svg>
            </div>
            <div>
                <h4>{{ __('Cara Menebus Makanan Percuma') }}</h4>
                <p>{{ __('Kunjungi kaunter Food Bank di HEP. Halakan kamera telefon atau gunakan butang di bawah untuk mengimbas poster QR rasmi.') }}</p>
            </div>
        </div>
        <div class="fb-guide-actions">
            <a href="{{ route('student.movements.scan') }}" class="fb-btn-gold">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg>
                <span>{{ __('Buka Pengimbas QR') }}</span>
            </a>
            <a href="{{ route('student.foodbank.claim') }}" class="fb-btn-outline">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                <span>{{ __('Tebus Terus (Semasa di Kaunter)') }}</span>
            </a>
        </div>
    </div>

    <!-- Student Claims History -->
    <div class="fb-history-card">
        <div class="fb-history-head">
            <div class="fb-history-head-left">
                <div class="fb-history-head-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <h4 class="fb-history-title">{{ __('Sejarah Penebusan Makanan Anda') }}</h4>
            </div>
            <span class="fb-history-badge">{{ $claims->total() }} {{ __('rekod') }}</span>
        </div>

        <div class="fb-history-list">
            @forelse($claims as $c)
                <div class="fb-history-item">
                    <div class="fb-item-left">
                        <div class="fb-item-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        </div>
                        <div>
                            <strong class="fb-item-location">{{ $c->location ?: __('Food Bank Siswa') }}</strong>
                            <span class="fb-item-date">{{ \Carbon\Carbon::parse($c->claimed_at)->format('d/m/Y, h:i A') }}</span>
                        </div>
                    </div>
                    <div>
                        <span class="fb-status-pill">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>{{ __('Selesai Ditebus') }}</span>
                        </span>
                    </div>
                </div>
            @empty
                <div class="fb-empty-state">
                    <div class="fb-empty-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8h1a4 4 0 0 1 0 8h-1"/>
                            <path d="M2 8h16v9a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4V8z"/>
                            <line x1="6" y1="1" x2="6" y2="4"/>
                            <line x1="10" y1="1" x2="10" y2="4"/>
                            <line x1="14" y1="1" x2="14" y2="4"/>
                        </svg>
                    </div>
                    <h5>{{ __('Tiada Penebusan Direkodkan') }}</h5>
                    <p>{{ __('Anda belum membuat sebarang penebusan Food Bank lagi. Lawati kaunter HEP untuk mengambil makanan percuma.') }}</p>
                </div>
            @endforelse
        </div>

        @if($claims->hasPages())
            <div style="margin-top:1.25rem;">
                {{ $claims->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
