@extends('layouts.app')

@section('title', __('Penebusan Food Bank Berjaya'))

@section('header')
    <h2 style="margin:0;font-size:1.15rem;font-weight:800;color:var(--text);">{{ __('Penebusan Food Bank Siswa') }}</h2>
@endsection



@section('content')
<div class="fb-success-shell">
    <div class="fb-success-card">
        <div class="fb-success-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </div>

        <h3 class="fb-success-title">
            {{ $isNew ? __('Penebusan Makanan Berjaya!') : __('Rekod Penebusan Disahkan!') }}
        </h3>
        <p class="fb-success-sub">
            {{ __('Terima kasih. Maklumat anda telah direkodkan secara automatik ke dalam sistem Food Bank Siswa Politeknik Besut.') }}
        </p>

        <div class="fb-voucher-details">
            <div class="fb-voucher-row">
                <span class="fb-voucher-label">{{ __('Nama Pelajar') }}</span>
                <span class="fb-voucher-value">{{ $student->full_name }}</span>
            </div>
            <div class="fb-voucher-row">
                <span class="fb-voucher-label">{{ __('No. Matrik') }}</span>
                <span class="fb-voucher-value">{{ $student->matric_no }}</span>
            </div>
            <div class="fb-voucher-row">
                <span class="fb-voucher-label">{{ __('Program / Jabatan') }}</span>
                <span class="fb-voucher-value">{{ $student->program ?: 'Politeknik Besut' }}</span>
            </div>
            <div class="fb-voucher-row">
                <span class="fb-voucher-label">{{ __('Semester / Sesi') }}</span>
                <span class="fb-voucher-value">Semester {{ $student->semester ?: '-' }} ({{ $student->academic_session ?: '-' }})</span>
            </div>
            <div class="fb-voucher-row">
                <span class="fb-voucher-label">{{ __('Masa Penebusan') }}</span>
                <span class="fb-voucher-value">{{ \Carbon\Carbon::parse($claim->claimed_at)->format('d/m/Y, h:i A') }}</span>
            </div>
            <div class="fb-voucher-row">
                <span class="fb-voucher-label">{{ __('Lokasi Food Bank') }}</span>
                <span class="fb-voucher-value">{{ $claim->location }}</span>
            </div>
            <div class="fb-voucher-row">
                <span class="fb-voucher-label">{{ __('Jumlah Penebusan Anda') }}</span>
                <span class="fb-voucher-value" style="color:#059669;">{{ $totalStudentClaims }} kali</span>
            </div>
        </div>

        <div class="fb-notice-box">
            <svg style="width:18px;height:18px;flex-shrink:0;margin-top:2px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
            <span>
                <strong>{{ __('Peringatan Mesra:') }}</strong> {{ __('Sila ambil pek makanan yang disediakan di kaunter mengikut keperluan anda. Semoga bermanfaat untuk pembelajaran anda di kampus!') }}
            </span>
        </div>

        <a href="{{ route('student.foodbank.index') }}" class="fb-btn-done">
            {{ __('Kembali ke Portal Food Bank') }}
        </a>
    </div>
</div>
@endsection
