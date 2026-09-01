@extends('layouts.app')

@section('title', __('Dashboard Pelajar'))

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;400;500;600&display=swap" rel="stylesheet">


@endpush

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:600;color:var(--sand-800);font-family:'DM Sans',system-ui,sans-serif;">
        {{ __('Dashboard Pelajar') }}
    </h2>
@endsection

@section('content')
@php
    $studentName = $studentProfile->full_name ?? ($authUser['name'] ?? __('Pelajar'));
    $studentMatric = $studentProfile->matric_no ?? ($authUser['matric_no'] ?? '-');
    $studentProgram = $studentProfile->program ?? ($authUser['program'] ?? '-');
    $studentClass = $studentProfile->class_name ?? '-';
    $studentSemester = $studentProfile->semester ?? ($authUser['semester'] ?? '-');
    $studentSession = $studentProfile->academic_session ?? '-';
    $studentIcNo = maskIdentityNumber($studentProfile->ic_no ?? null);
    $jsLocale = app()->getLocale() === 'ms' ? 'ms-MY' : 'en-GB';
@endphp
<div class="sdash">

    {{-- â”€â”€ SUCCESS FLASH â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    @if(session('success'))
    <div class="alert alert-success" role="alert">
        <div class="alert-icon">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/></svg>
        </div>
        <div class="alert-body">
            <strong>{{ __('Berjaya') }}</strong>
            <p>{{ session('success') }}</p>
        </div>
    </div>
    @endif

    {{-- â”€â”€ UNPAID FINE ALERT â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    @if($showPaymentAlert ?? false)
    <div class="alert alert-danger" role="alert">
        <div class="alert-icon">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
        </div>
        <div class="alert-body">
            <strong>{{ __('Anda mempunyai denda yang belum dibayar') }}</strong>
            <p>{{ __('Sila semak rekod kesalahan dan hantar permohonan bayaran dengan segera untuk mengelakkan tindakan lanjut.') }}</p>
        </div>
        <a href="{{ route('student.offenses.index') }}" class="action-btn primary alert-action">
            {{ __('Bayar Sekarang') }}
        </a>
    </div>
    @endif

    {{-- â”€â”€ SCHOLARSHIP FORM ALERT â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    @if($needsScholarshipStatusSubmission ?? false)
    <div class="alert alert-warn" role="alert">
        <div class="alert-icon">
            <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a.75.75 0 000 1.5h.253a.25.25 0 01.244.304l-.459 2.066A1.75 1.75 0 0010.747 15H11a.75.75 0 000-1.5h-.253a.25.25 0 01-.244-.304l.459-2.066A1.75 1.75 0 009.253 9H9z" clip-rule="evenodd"/></svg>
        </div>
        <div class="alert-body">
            <strong>{{ __('Lengkapkan Borang Data Biasiswa') }}</strong>
            <p>{{ __('Sila hantar status biasiswa anda untuk tujuan pengumpulan data pelajar Politeknik Besut.') }}</p>
        </div>
        <a href="{{ route('student.scholarship-status.form') }}" class="action-btn alert-action">
            {{ __('Isi Borang') }}
        </a>
    </div>
    @endif

    {{-- â”€â”€ HERO â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div class="hero">
        <div class="hero-text">
            <p class="hero-eyebrow">{{ __('MyHEP') }}</p>
            <h3 class="hero-name">{{ __('Selamat Datang,') }}<br>{{ $studentName }}</h3>
            <p class="hero-sub">{{ $studentMatric }} &nbsp;&middot;&nbsp; {{ $studentProgram }}</p>
        </div>
        <div class="hero-right">
            <div class="hero-badge">
                <span class="hero-badge-label">{{ __('Semester') }}</span>
                <span class="hero-badge-value">{{ $studentSemester ?: '-' }}</span>
            </div>
            <div class="hero-meta-grid">
                <div class="hero-meta-item">
                    <span class="hero-meta-label">{{ __('Sesi') }}</span>
                    <span class="hero-meta-value">{{ $studentSession ?: '-' }}</span>
                </div>
                <div class="hero-meta-item">
                    <span class="hero-meta-label">{{ __('Kelas') }}</span>
                    <span class="hero-meta-value">{{ $studentClass ?: '-' }}</span>
                </div>
                <div class="hero-meta-item">
                    <span class="hero-meta-label">{{ __('Tarikh') }}</span>
                    <span class="hero-meta-value" id="heroTodayDate">-</span>
                </div>
                <div class="hero-meta-item">
                    <span class="hero-meta-label">{{ __('Masa') }}</span>
                    <span class="hero-meta-value" id="heroClock">-</span>
                </div>
                <div class="hero-meta-item" style="grid-column: 1 / -1;">
                    <span class="hero-meta-label">{{ __('No. IC') }}</span>
                    <span class="hero-meta-value">{{ $studentIcNo ?: '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- â”€â”€ STATS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div class="student-stats-section">
        <p class="section-label">{{ __('Ringkasan Akaun') }}</p>
        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-icon sand">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v2.5h-2.5a.75.75 0 000 1.5h2.5v2.5a.75.75 0 001.5 0v-2.5h2.5a.75.75 0 000-1.5h-2.5v-2.5z" clip-rule="evenodd"/></svg>
                </div>
                <div class="stat-label">{{ __('Jumlah Kesalahan') }}</div>
                <div class="stat-value">{{ $totalOffenses ?? 0 }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon red">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                </div>
                <div class="stat-label">{{ __('Denda Belum Bayar') }}</div>
                <div class="stat-value {{ ($unpaidOffenses ?? 0) > 0 ? 'red' : '' }}">{{ $unpaidOffenses ?? 0 }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon gold">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 10.818v2.614A3.13 3.13 0 0011.888 13c.482-.315.612-.648.612-.875 0-.227-.13-.56-.612-.875a3.13 3.13 0 00-1.138-.432zM8.33 8.62c.053.055.115.11.184.164.208.16.46.284.736.363V6.603a2.45 2.45 0 00-.35.13c-.14.065-.27.143-.386.233-.377.292-.514.627-.514.909 0 .184.058.39.33.615z"/><path fill-rule="evenodd" d="M9.25 3.75a.75.75 0 00-1.5 0V4.5c-1.113.259-2 1.01-2 2.136 0 .828.433 1.476 1.02 1.898.529.38 1.186.58 1.73.718v2.898a3.84 3.84 0 01-.585-.234 1.698 1.698 0 01-.346-.244.75.75 0 00-1.06 1.06c.188.188.42.35.676.483.51.264 1.12.413 1.815.43V14.25a.75.75 0 001.5 0v-.82c1.113-.258 2-1.01 2-2.136 0-.828-.433-1.476-1.02-1.898-.529-.38-1.186-.58-1.73-.718V6.08c.2.033.38.085.534.157.19.088.344.204.463.337a.75.75 0 101.103-1.017 3.246 3.246 0 00-.848-.613 4.53 4.53 0 00-1.252-.33V3.75z" clip-rule="evenodd"/></svg>
                </div>
                <div class="stat-label">{{ __('Biasiswa Aktif') }}</div>
                <div class="stat-value">{{ $activeScholarships ?? 0 }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon amber">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M15.988 3.012A2.25 2.25 0 0118 5.25v6.5A2.25 2.25 0 0115.75 14H13.5v-3.379a3 3 0 00-.879-2.121l-3.12-3.121a3 3 0 00-1.402-.791V2.25A2.25 2.25 0 0110.25 0h4.5a2.25 2.25 0 011.238.012zM11.5 3.25a.75.75 0 01.75-.75h1.5a.75.75 0 010 1.5h-1.5a.75.75 0 01-.75-.75zm.75 4.25a.75.75 0 000 1.5h.5a.75.75 0 000-1.5h-.5z" clip-rule="evenodd"/><path d="M3.5 6A1.5 1.5 0 002 7.5v9A1.5 1.5 0 003.5 18h7a1.5 1.5 0 001.5-1.5v-5.879a1.5 1.5 0 00-.44-1.06L8.44 6.439A1.5 1.5 0 007.378 6H3.5z"/></svg>
                </div>
                <div class="stat-label">{{ __('Permohonan Bayaran') }}</div>
                <div class="stat-value">{{ $pendingFineApplications ?? 0 }}</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon teal">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M6.5 3c-1.051 0-2.093.04-3.125.117A1.49 1.49 0 002 4.607V10.5h9V4.606c0-.771-.59-1.43-1.375-1.489A41.568 41.568 0 006.5 3zM2 12v2.5A1.5 1.5 0 003.5 16h.041a3 3 0 015.918 0h.791a.75.75 0 00.75-.75V12H2z"/><path d="M6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM13.25 5a.75.75 0 00-.75.75v8.514a3.001 3.001 0 014.893 1.44c.37-.275.607-.714.607-1.204V7.5a2.5 2.5 0 00-2.5-2.5h-2.25zM14.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/></svg>
                </div>
                <div class="stat-label">{{ __('Status Stiker') }}</div>
                <div class="stat-value sm {{ ($stickerStatusLabel ?? 'none') === 'approved' ? 'teal' : '' }}">
                    {{ __($stickerStatusLabel ?? 'Tiada') }}
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon sand">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v3.19l2.22 2.22a.75.75 0 101.06-1.06l-1.78-1.78V6.75z" clip-rule="evenodd"/></svg>
                </div>
                <div class="stat-label">{{ __('Campus Status') }}</div>
                <div class="stat-value sm {{ ($movementStatusLabel ?? '') === 'Inside Campus' ? 'teal' : 'red' }}">
                    {{ __($movementStatusLabel ?? 'Inside Campus') }}
                </div>
            </div>

        </div>
    </div>

    {{-- â”€â”€ PORTAL CARDS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div class="student-portal-section">
        <p class="section-label">{{ __('Portal Utama') }}</p>
        <div class="portal-grid">

            <a href="{{ route('student.scholarships.index') }}" class="portal-card scholarship">
                <div class="portal-card-icon gold">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10.75 10.818v2.614A3.13 3.13 0 0011.888 13c.482-.315.612-.648.612-.875 0-.227-.13-.56-.612-.875a3.13 3.13 0 00-1.138-.432zM8.33 8.62c.053.055.115.11.184.164.208.16.46.284.736.363V6.603a2.45 2.45 0 00-.35.13c-.14.065-.27.143-.386.233-.377.292-.514.627-.514.909 0 .184.058.39.33.615z"/><path fill-rule="evenodd" d="M9.25 3.75a.75.75 0 00-1.5 0V4.5c-1.113.259-2 1.01-2 2.136 0 .828.433 1.476 1.02 1.898.529.38 1.186.58 1.73.718v2.898a3.84 3.84 0 01-.585-.234 1.698 1.698 0 01-.346-.244.75.75 0 00-1.06 1.06c.188.188.42.35.676.483.51.264 1.12.413 1.815.43V14.25a.75.75 0 001.5 0v-.82c1.113-.258 2-1.01 2-2.136 0-.828-.433-1.476-1.02-1.898-.529-.38-1.186-.58-1.73-.718V6.08c.2.033.38.085.534.157.19.088.344.204.463.337a.75.75 0 101.103-1.017 3.246 3.246 0 00-.848-.613 4.53 4.53 0 00-1.252-.33V3.75z" clip-rule="evenodd"/></svg>
                </div>
                <h4>{{ __('Scholarship & Bantuan') }}</h4>
                <p>{{ __('Rekod bantuan, status permohonan, bukti penerimaan, dan pengumuman terkini.') }}</p>
                <span class="portal-card-cta">
                    {{ __('Buka portal') }}
                    <svg viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M1 8a.5.5 0 01.5-.5h11.793l-3.147-3.146a.5.5 0 01.708-.708l4 4a.5.5 0 010 .708l-4 4a.5.5 0 01-.708-.708L13.293 8.5H1.5A.5.5 0 011 8z" clip-rule="evenodd"/></svg>
                </span>
            </a>

            <a href="{{ route('student.offenses.index') }}" class="portal-card offense">
                <div class="portal-card-icon red">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/></svg>
                </div>
                <h4>{{ __('Rekod Kesalahan & Denda') }}</h4>
                <p>{{ __('Semak kesalahan, status denda, sejarah, dan hantar permohonan bayaran kepada pentadbir.') }}</p>
                <span class="portal-card-cta">
                    {{ __('Buka portal') }}
                    <svg viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M1 8a.5.5 0 01.5-.5h11.793l-3.147-3.146a.5.5 0 01.708-.708l4 4a.5.5 0 010 .708l-4 4a.5.5 0 01-.708-.708L13.293 8.5H1.5A.5.5 0 011 8z" clip-rule="evenodd"/></svg>
                </span>
            </a>

            <a href="{{ route('student.profile') }}" class="portal-card profile">
                <div class="portal-card-icon sand">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path d="M10 8a3 3 0 100-6 3 3 0 000 6zM3.465 14.493a1.23 1.23 0 00.41 1.412A9.957 9.957 0 0010 18c2.31 0 4.438-.784 6.131-2.1.43-.333.604-.903.408-1.41a7.002 7.002 0 00-13.074.003z"/></svg>
                </div>
                <h4>{{ __('Profil Pelajar') }}</h4>
                <p>{{ __('Kemaskini maklumat peribadi, nombor hubungan, dan tukar kata laluan akaun.') }}</p>
                <span class="portal-card-cta">
                    {{ __('Buka portal') }}
                    <svg viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M1 8a.5.5 0 01.5-.5h11.793l-3.147-3.146a.5.5 0 01.708-.708l4 4a.5.5 0 010 .708l-4 4a.5.5 0 01-.708-.708L13.293 8.5H1.5A.5.5 0 011 8z" clip-rule="evenodd"/></svg>
                </span>
            </a>

            <a href="{{ route('student.movements.index') }}" class="portal-card profile">
                <div class="portal-card-icon sand">
                    <svg viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm.75-11.25a.75.75 0 00-1.5 0v3.19l2.22 2.22a.75.75 0 101.06-1.06l-1.78-1.78V6.75z" clip-rule="evenodd"/></svg>
                </div>
                <h4>{{ __('Pergerakan Kampus') }}</h4>
                <p>{{ __('Semak status masuk/keluar kampus, imbas QR guard house, dan lihat sejarah pergerakan.') }}</p>
                <span class="portal-card-cta">
                    {{ __('Buka portal') }}
                    <svg viewBox="0 0 16 16" fill="currentColor"><path fill-rule="evenodd" d="M1 8a.5.5 0 01.5-.5h11.793l-3.147-3.146a.5.5 0 01.708-.708l4 4a.5.5 0 010 .708l-4 4a.5.5 0 01-.708-.708L13.293 8.5H1.5A.5.5 0 011 8z" clip-rule="evenodd"/></svg>
                </span>
            </a>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(() => {
    const dateNode = document.getElementById('heroTodayDate');
    const timeNode = document.getElementById('heroClock');
    if (!dateNode || !timeNode) return;

    const locale = navigator.language || @json($jsLocale);
    const updateClock = () => {
        const now = new Date();
        dateNode.textContent = now.toLocaleDateString(locale, {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        });
        timeNode.textContent = now.toLocaleTimeString(locale, {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        });
    };

    updateClock();
    setInterval(updateClock, 1000);
})();
</script>
@endpush
