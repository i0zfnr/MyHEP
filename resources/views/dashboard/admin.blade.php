@extends('layouts.app')

@section('title', ($isLecturer ?? false) ? __('Staff Dashboard') : __('Admin Dashboard'))

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">


@endpush

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:600;color:var(--c-text-primary,#1A1714);">{{ ($isLecturer ?? false) ? __('Staff Dashboard') : __('Admin Dashboard') }}</h2>
@endsection

@section('content')
<div class="adash">

    @if (session('success'))
        <div class="ui-alert-success">{{ session('success') }}</div>
    @endif

    {{-- ── Hero ── --}}
    <div class="dash-hero">
        <div class="dash-hero-text">
            <span class="dash-hero-label">{{ __('Overview') }}</span>
            <h3>{{ ($isLecturer ?? false) ? __('Staff Dashboard') : __('Admin Dashboard') }}</h3>
            <p>
                @if($isLecturer ?? false)
                    {{ __('Overview of your programs, approval workflow, and assigned reviews.') }}
                @elseif($hasDisciplineAccess && $hasScholarshipAccess)
                    {{ __('Overview of the discipline and scholarship modules.') }}
                @elseif($hasMovementAccess && !$hasDisciplineAccess && !$hasScholarshipAccess)
                    {{ __('Overview of guard house monitoring and student movement.') }}
                @elseif($hasDisciplineAccess)
                    {{ __('Overview of the student discipline module.') }}
                @elseif($hasScholarshipAccess)
                    {{ __('Overview of the student scholarship module.') }}
                @else
                    {{ __('This account has no module access.') }}
                @endif
            </p>
        </div>
        <div class="dash-hero-actions">
            @if(!empty($analytics['domains']))
            <div class="viz-mode" data-dashboard-visualization-toggle role="group" aria-label="{{ __('Dashboard view mode') }}">
                <button type="button" class="viz-mode-btn" data-dashboard-mode="cards" aria-pressed="true">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h4a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1V5zm10 0a1 1 0 011-1h4a1 1 0 011 1v3a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 17a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1v-2zm10-6a1 1 0 011-1h4a1 1 0 011 1v8a1 1 0 01-1 1h-4a1 1 0 01-1-1v-8z"/></svg>
                    {{ __('Cards') }}
                </button>
                <button type="button" class="viz-mode-btn" data-dashboard-mode="graphs" aria-pressed="false">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    {{ __('Graphs') }}
                </button>
            </div>
            @endif
            <div class="dash-hero-date">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" style="display:inline;vertical-align:-2px;margin-right:4px;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                {{ now()->format('d M Y') }}
            </div>
        </div>
    </div>

    {{-- ── Portal Utama ── --}}
    <div class="portal-card liquid-command-bar">
        <div class="portal-card-head">{{ __('Portal Utama') }}</div>
        <div class="portal-links">
            @if((session('auth_user.admin_role') ?? null) !== 'guard')
                <a href="{{ route('admin.reports.monthly') }}" class="portal-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    {{ __('Laporan Bulanan') }}
                </a>
            @endif
            @if($canAccessMovementModule)
                <a href="{{ route('admin.movements.qr') }}" class="portal-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75h4.5v4.5h-4.5zm12 0h4.5v4.5h-4.5zm-12 12h4.5v4.5h-4.5zm12 0h4.5v4.5h-4.5zM9 6h6M6 9v6M18 9v6M9 18h6"/></svg>
                    {{ __('Guard House QR') }}
                </a>
                <a href="{{ route('admin.movements.index') }}" class="portal-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m4-2a8 8 0 11-16 0 8 8 0 0116 0z"/></svg>
                    {{ __('Student Movement') }}
                </a>
                <a href="{{ route('admin.students.index') }}" class="portal-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/></svg>
                    {{ __('Senarai Pelajar') }}
                </a>
            @endif
            @if($hasDisciplineAccess)
                @if($canRegisterOffense)
                <a href="{{ route('admin.offenses.create') }}" class="portal-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    {{ __('Daftar Kesalahan') }}
                </a>
                @endif
                @if($canViewOffenseList)
                <a href="{{ route('admin.offenses.index') }}" class="portal-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                    {{ __('Senarai Kesalahan') }}
                </a>
                @endif
            @endif
            @if($hasScholarshipAccess)
                <a href="{{ route('admin.scholarships.index') }}" class="portal-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6m0-6l-3.5-2M12 20l-9-5"/></svg>
                    {{ __('Rekod Scholarship') }}
                </a>
                @unless($hasMovementAccess)
                    <a href="{{ route('admin.students.index') }}" class="portal-link">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0z"/></svg>
                        {{ __('Senarai Pelajar') }}
                    </a>
                @endunless
                <a href="{{ route('admin.scholarship-announcements.index') }}" class="portal-link">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                    {{ __('Pengumuman Scholarship') }}
                </a>
            @endif
        </div>
    </div>

    @include('dashboard.partials.admin_analytics', ['analytics' => $analytics])

    @if(($showSystemMonitoring ?? false) && !empty($systemMonitoring))
        <p class="section-heading">{{ __('System Monitoring') }}</p>
        @php
            $cpuPercent = $systemMonitoring['cpu_percent'];
            $ramPercent = $systemMonitoring['ram_percent'];
            $diskPercent = $systemMonitoring['disk_percent'];
            $cpuState = $cpuPercent !== null && $cpuPercent >= 85 ? 'error' : ($cpuPercent !== null && $cpuPercent >= 70 ? 'warn' : '');
            $ramState = $ramPercent !== null && $ramPercent >= 85 ? 'error' : ($ramPercent !== null && $ramPercent >= 70 ? 'warn' : '');
            $diskState = $diskPercent !== null && $diskPercent >= 90 ? 'error' : ($diskPercent !== null && $diskPercent >= 75 ? 'warn' : '');
            $overallLoad = round(collect([$cpuPercent, $ramPercent, $diskPercent])->filter(fn ($v) => $v !== null)->avg() ?? 0, 1);
            $trendBase = $overallLoad > 0 ? $overallLoad : 42;
            $trend = [
                max(8, $trendBase - 18),
                max(8, $trendBase - 10),
                max(8, $trendBase - 7),
                max(8, $trendBase + 9),
                max(8, $trendBase - 3),
                max(8, $trendBase - 14),
                max(8, $trendBase - 6),
            ];
            $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
            $activeTrendIndex = 3;
        @endphp

        <div class="monitor-grid" data-system-monitoring data-live-url="{{ route('admin.system-monitoring.live') }}" style="grid-template-columns:1fr;gap:.75rem;">
            <div class="monitor-kpi-grid">
                <div class="monitor-kpi">
                    <div class="monitor-kpi-top">
                        <span class="monitor-kpi-label">{{ __('CPU Usage') }}</span>
                        <span class="monitor-pill {{ $cpuState ?: 'ok' }}" data-monitor="cpu-pill">{{ $cpuPercent !== null ? number_format($cpuPercent, 1) . '%' : 'N/A' }}</span>
                    </div>
                    <div class="monitor-kpi-value" data-monitor="cpu-value">{{ $cpuPercent !== null ? number_format($cpuPercent, 1) . '%' : 'N/A' }}</div>
                    <div class="monitor-kpi-sub">{{ __('Current processing load') }}</div>
                </div>
                <div class="monitor-kpi">
                    <div class="monitor-kpi-top">
                        <span class="monitor-kpi-label">{{ __('Memory Usage') }}</span>
                        <span class="monitor-pill {{ $ramState ?: 'ok' }}" data-monitor="ram-pill">{{ $ramPercent !== null ? number_format($ramPercent, 1) . '%' : 'N/A' }}</span>
                    </div>
                    <div class="monitor-kpi-value" data-monitor="ram-value">{{ $systemMonitoring['ram_usage_text'] }}</div>
                    <div class="monitor-kpi-sub" data-monitor="ram-limit">Limit: {{ $systemMonitoring['ram_limit_text'] }}</div>
                </div>
                <div class="monitor-kpi">
                    <div class="monitor-kpi-top">
                        <span class="monitor-kpi-label">{{ __('Disk Usage') }}</span>
                        <span class="monitor-pill {{ $diskState ?: 'ok' }}" data-monitor="disk-pill">{{ $diskPercent !== null ? number_format($diskPercent, 1) . '%' : 'N/A' }}</span>
                    </div>
                    <div class="monitor-kpi-value" data-monitor="disk-value">{{ $systemMonitoring['disk_used_text'] }}</div>
                    <div class="monitor-kpi-sub" data-monitor="disk-total">Total: {{ $systemMonitoring['disk_total_text'] }}</div>
                </div>
                <div class="monitor-kpi">
                    <div class="monitor-kpi-top">
                        <span class="monitor-kpi-label">{{ __('Database') }}</span>
                        <span class="monitor-pill {{ $systemMonitoring['db_status'] === 'ok' ? 'ok' : 'error' }}" data-monitor="db-pill">DB {{ strtoupper($systemMonitoring['db_status']) }}</span>
                    </div>
                    <div class="monitor-kpi-value" data-monitor="db-value">{{ $systemMonitoring['maintenance'] ? 'Maintenance ON' : 'Healthy' }}</div>
                    <div class="monitor-kpi-sub" data-monitor="server-sub">Server: {{ $systemMonitoring['server_time'] }}</div>
                </div>
            </div>

            <div class="monitor-two-up">
                <div class="monitor-card">
                    <div class="monitor-head">
                        <span class="monitor-title">{{ __('System Performance') }}</span>
                        <span class="monitor-pill {{ $systemMonitoring['maintenance'] ? 'warn' : 'ok' }}" data-monitor="maintenance-pill">
                            {{ $systemMonitoring['maintenance'] ? 'Maintenance ON' : 'Maintenance OFF' }}
                        </span>
                    </div>

                    <div class="perf-circle-wrap">
                        <div class="perf-circle" data-monitor="overall-circle" style="--angle: {{ max(0, min(360, ($overallLoad / 100) * 360)) }}deg;">
                            <div class="perf-circle-text">
                                <strong data-monitor="overall-value">{{ number_format($overallLoad, 1) }}%</strong>
                                <span>{{ __('Overall Load') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="meter-wrap">
                        <div class="meter-row">
                            <span class="meter-label">{{ __('CPU Usage') }}</span>
                            <span class="meter-value" data-monitor="cpu-meter-value">{{ $cpuPercent !== null ? number_format($cpuPercent, 1) . '%' : 'N/A' }}</span>
                        </div>
                        <div class="meter-track"><div class="meter-fill {{ $cpuState }}" data-monitor="cpu-meter" style="width: {{ $cpuPercent !== null ? max(1, min(100, $cpuPercent)) : 0 }}%;"></div></div>
                    </div>
                    <div class="meter-wrap">
                        <div class="meter-row">
                            <span class="meter-label">{{ __('Memory') }}</span>
                            <span class="meter-value" data-monitor="ram-meter-value">{{ $ramPercent !== null ? number_format($ramPercent, 1) . '%' : 'N/A' }}</span>
                        </div>
                        <div class="meter-track"><div class="meter-fill {{ $ramState }}" data-monitor="ram-meter" style="width: {{ $ramPercent !== null ? max(1, min(100, $ramPercent)) : 0 }}%;"></div></div>
                    </div>
                    <div class="meter-wrap" style="margin-bottom:0;">
                        <div class="meter-row">
                            <span class="meter-label">{{ __('Disk') }}</span>
                            <span class="meter-value" data-monitor="disk-meter-value">{{ $diskPercent !== null ? number_format($diskPercent, 1) . '%' : 'N/A' }}</span>
                        </div>
                        <div class="meter-track"><div class="meter-fill {{ $diskState }}" data-monitor="disk-meter" style="width: {{ $diskPercent !== null ? max(1, min(100, $diskPercent)) : 0 }}%;"></div></div>
                    </div>
                </div>

                <div class="monitor-card">
                    <div class="trend-head">
                        <span class="trend-title">Resource Trend (Weekly)</span>
                        <span class="trend-meta">{{ __('Last 7 Days') }}</span>
                    </div>
                    <div class="trend-chart">
                        @foreach($trend as $i => $bar)
                            <div class="trend-col {{ $i === $activeTrendIndex ? 'active' : '' }}">
                                <div class="trend-bar-wrap">
                                    <div class="trend-bar" data-monitor-trend="{{ $i }}" style="height: {{ max(8, min(100, $bar)) }}%;"></div>
                                </div>
                                <span class="trend-day">{{ $days[$i] }}</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="monitor-list" style="margin-top:.75rem;">
                        <div class="monitor-item"><span class="monitor-key">{{ __('Server Time') }}</span><span class="monitor-val" data-monitor="server-time">{{ $systemMonitoring['server_time'] }}</span></div>
                        <div class="monitor-item"><span class="monitor-key">{{ __('PHP Version') }}</span><span class="monitor-val" data-monitor="php-version">{{ $systemMonitoring['php_version'] }}</span></div>
                        <div class="monitor-item"><span class="monitor-key">{{ __('Laravel Version') }}</span><span class="monitor-val" data-monitor="laravel-version">{{ $systemMonitoring['laravel_version'] }}</span></div>
                        <div class="monitor-item"><span class="monitor-key">{{ __('OS') }}</span><span class="monitor-val" data-monitor="os">{{ $systemMonitoring['os'] }}</span></div>
                        <div class="monitor-item"><span class="monitor-key">{{ __('1-min Load Avg') }}</span><span class="monitor-val" data-monitor="load-1m">{{ $systemMonitoring['load_1m'] !== null ? number_format($systemMonitoring['load_1m'], 2) : 'N/A' }}</span></div>
                        <div class="monitor-item"><span class="monitor-key">{{ __('RAM Peak') }}</span><span class="monitor-val" data-monitor="ram-peak">{{ $systemMonitoring['ram_peak_text'] }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($isLecturer ?? false)
        @include('dashboard.partials.staff_program_dashboard')
    @endif

    {{-- ── Discipline Module ── --}}
    @if(!$isLecturer && $hasMovementAccess)
        <p class="section-heading">{{ $hasDisciplineAccess ? __('Discipline') : __('Student Movement') }}</p>

        <div class="stats-grid">
            <div class="stat-card accent">
                <div class="stat-label">{{ __('Jumlah Pelajar') }}</div>
                <div class="stat-value">{{ $totalStudents }}</div>
            </div>
            @if($hasDisciplineAccess)
                <div class="stat-card blue">
                    <div class="stat-label">{{ __('Jumlah Kesalahan') }}</div>
                    <div class="stat-value">{{ $totalOffenses }}</div>
                </div>
                <div class="stat-card red">
                    <div class="stat-label">{{ __('Kes Unpaid') }}</div>
                    <div class="stat-value">{{ $unpaidOffenses }}</div>
                </div>
                <div class="stat-card gold">
                    <div class="stat-label">{{ __('Rekod Belum Disahkan') }}</div>
                    <div class="stat-value">{{ $pendingFineApplications }}</div>
                </div>
            @endif
            <div class="stat-card accent">
                <div class="stat-label">{{ __('Outside Now') }}</div>
                <div class="stat-value">{{ $outsideNow ?? 0 }}</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-label">{{ __('Check-Outs Today') }}</div>
                <div class="stat-value">{{ $movementCheckoutsToday ?? 0 }}</div>
            </div>
            <div class="stat-card red">
                <div class="stat-label">{{ __('Late Returns') }}</div>
                <div class="stat-value">{{ $movementLateReturns ?? 0 }}</div>
            </div>
            <div class="stat-card gold">
                <div class="stat-label">{{ __('Overnight Stay') }}</div>
                <div class="stat-value">{{ $movementOvernightRecords ?? 0 }}</div>
            </div>
        </div>

        @if($hasDisciplineAccess)
        <div class="two-col">
            <div class="data-card">
                <div class="data-card-head">
                    <strong>{{ __('Rekod Kesalahan Terkini') }}</strong>
                    @if($canViewOffenseList)<a class="btn-ghost" href="{{ route('admin.offenses.index') }}">
                        {{ __('Lihat Semua') }}
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>@endif
                </div>
                @if($recentOffenses->isEmpty())
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.3"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        {{ __('Tiada rekod kesalahan.') }}
                    </div>
                @else
                    <div style="overflow-x:auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>{{ __('Pelajar') }}</th>
                                    <th>{{ __('No Matrik') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentOffenses as $offense)
                                    <tr>
                                        <td style="font-weight:500;">{{ $offense->student_name }}</td>
                                        <td style="color:var(--c-text-secondary);font-family:monospace;font-size:0.8rem;">{{ $offense->matric_no }}</td>
                                        <td><span class="badge status-{{ $offense->status }}">{{ __($offense->status) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="data-card">
                <div class="data-card-head">
                    <strong>{{ __('Resit Bayaran Terkini') }}</strong>
                    @if($canViewOffenseList)<a class="btn-ghost" href="{{ route('admin.offenses.index', ['status' => 'applied']) }}">
                        {{ __('Buka Senarai Kesalahan') }}
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>@endif
                </div>
                @if($recentFineApplications->isEmpty())
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.3"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        {{ __('Tiada resit bayaran terbaru.') }}
                    </div>
                @else
                    <div style="overflow-x:auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>{{ __('Pelajar') }}</th>
                                    <th>{{ __('Lokasi') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentFineApplications as $application)
                                    <tr>
                                        <td style="font-weight:500;">{{ $application->student_name }}</td>
                                        <td style="color:var(--c-text-secondary);">{{ $application->place }}</td>
                                        <td><span class="badge status-{{ $application->status }}">{{ __($application->status) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        @else
        <div class="two-col">
            <div class="data-card">
                <div class="data-card-head">
                    <strong>{{ __('Guard House Access') }}</strong>
                    <a class="btn-ghost" href="{{ route('admin.movements.qr') }}">
                        {{ __('Buka QR') }}
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.3"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75h4.5v4.5h-4.5zm12 0h4.5v4.5h-4.5zm-12 12h4.5v4.5h-4.5zm12 0h4.5v4.5h-4.5zM9 6h6M6 9v6M18 9v6M9 18h6"/></svg>
                    {{ __('Buka paparan QR guard house untuk tayangan monitor dan pengesahan imbasan pelajar.') }}
                </div>
            </div>

            <div class="data-card">
                <div class="data-card-head">
                    <strong>{{ __('Live Monitoring') }}</strong>
                    <a class="btn-ghost" href="{{ route('admin.movements.outside') }}">
                        {{ __('Pantau') }}
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="empty-state">
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m4-2a8 8 0 11-16 0 8 8 0 0116 0z"/></svg>
                    {{ __('Pantau pelajar di luar kampus, semak kelewatan, dan buka senarai pelajar untuk semakan pantas di pondok pengawal.') }}
                </div>
            </div>
        </div>
        @endif
    @endif

    {{-- ── Scholarship Module ── --}}
    @if(!$isLecturer && $hasScholarshipAccess)
        <p class="section-heading">{{ __('Scholarship') }}</p>

        <div class="stats-grid">
            <div class="stat-card accent">
                <div class="stat-label">{{ __('Jumlah Rekod Scholarship') }}</div>
                <div class="stat-value">{{ $totalScholarshipRecords }}</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-label">{{ __('Scholarship Aktif') }}</div>
                <div class="stat-value">{{ $activeScholarships }}</div>
            </div>
            <div class="stat-card gold">
                <div class="stat-label">{{ __('Rekod Belum Disahkan') }}</div>
                <div class="stat-value">{{ $pendingScholarships }}</div>
            </div>
            <div class="stat-card" style="border-left:3px solid var(--c-text-muted);">
                <div class="stat-label">{{ __('Pengumuman Terkini') }}</div>
                <div class="stat-value">{{ $recentScholarshipAnnouncements->count() }}</div>
            </div>
        </div>

        <div class="two-col">
            <div class="data-card">
                <div class="data-card-head">
                    <strong>{{ __('Rekod Scholarship Terkini') }}</strong>
                    <a class="btn-ghost" href="{{ route('admin.scholarships.index') }}">
                        {{ __('Lihat Semua') }}
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                @if($recentScholarshipRecords->isEmpty())
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6"/></svg>
                        {{ __('Tiada rekod scholarship.') }}
                    </div>
                @else
                    <div style="overflow-x:auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>{{ __('Pelajar') }}</th>
                                    <th>{{ __('No Matrik') }}</th>
                                    <th>{{ __('Jenis') }}</th>
                                    <th>{{ __('Status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentScholarshipRecords as $record)
                                    <tr>
                                        <td style="font-weight:500;">{{ $record->student_name }}</td>
                                        <td style="color:var(--c-text-secondary);font-family:monospace;font-size:0.8rem;">{{ $record->matric_no }}</td>
                                        <td style="color:var(--c-text-secondary);">{{ $record->type }}</td>
                                        <td><span class="badge status-{{ $record->status }}">{{ __($record->status) }}</span></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="data-card">
                <div class="data-card-head">
                    <strong>{{ __('Pengumuman Scholarship Terkini') }}</strong>
                    <a class="btn-ghost" href="{{ route('admin.scholarship-announcements.index') }}">
                        {{ __('Semak') }}
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                @if($recentScholarshipAnnouncements->isEmpty())
                    <div class="empty-state">
                        <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.3"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        {{ __('Tiada pengumuman scholarship.') }}
                    </div>
                @else
                    <div style="overflow-x:auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>{{ __('Tajuk') }}</th>
                                    <th>{{ __('Jenis') }}</th>
                                    <th>{{ __('Tarikh') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentScholarshipAnnouncements as $news)
                                    <tr>
                                        <td style="font-weight:500;">{{ $news->title }}</td>
                                        <td style="color:var(--c-text-secondary);">{{ $news->type }}</td>
                                        <td style="color:var(--c-text-muted);font-size:0.78rem;white-space:nowrap;">{{ $news->created_at ? \Illuminate\Support\Carbon::parse($news->created_at)->format('d M Y') : '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if(!$isLecturer && !$hasDisciplineAccess && !$hasScholarshipAccess && !$hasMovementAccess)
        <div class="no-access">
            <div class="icon-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" style="color:#9E9892;"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            </div>
            <strong style="font-size:0.9rem;color:var(--c-text-primary);">{{ __('Tiada Akses Modul') }}</strong>
            <p>{{ __('Akses modul untuk akaun ini belum dikonfigurasi.') }}</p>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const dashboard = document.querySelector('.adash');
    const modeButtons = document.querySelectorAll('[data-dashboard-mode]');
    if (dashboard && modeButtons.length) {
        const preferenceKey = `myhep-dashboard-viz-{{ session('auth_user.id') }}-{{ session('auth_user.admin_role') }}`;
        const applyMode = (mode) => {
            dashboard.setAttribute('data-dashboard-mode', mode);
            modeButtons.forEach((button) => {
                button.setAttribute('aria-pressed', button.dataset.dashboardMode === mode ? 'true' : 'false');
            });
        };
        applyMode(window.localStorage.getItem(preferenceKey) === 'on' ? 'graphs' : 'cards');
        modeButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const mode = button.dataset.dashboardMode;
                applyMode(mode);
                window.localStorage.setItem(preferenceKey, mode === 'graphs' ? 'on' : 'off');
            });
        });
    }

    const root = document.querySelector('[data-system-monitoring]');
    if (!root) return;

    const liveUrl = root.dataset.liveUrl;
    const $ = (name) => root.querySelector(`[data-monitor="${name}"]`);
    const formatPercent = (value) => value === null || value === undefined ? 'N/A' : `${Number(value).toFixed(1)}%`;
    const stateClass = (value, warnAt, errorAt) => {
        if (value === null || value === undefined) return 'ok';
        if (Number(value) >= errorAt) return 'error';
        if (Number(value) >= warnAt) return 'warn';
        return 'ok';
    };
    const setPill = (el, text, state) => {
        if (!el) return;
        el.textContent = text;
        el.classList.remove('ok', 'warn', 'error');
        el.classList.add(state);
    };
    const setText = (name, value) => {
        const el = $(name);
        if (el) el.textContent = value;
    };
    const setMeter = (name, value, state) => {
        const el = $(name);
        if (!el) return;
        el.style.width = value === null || value === undefined ? '0%' : `${Math.max(1, Math.min(100, Number(value)))}%`;
        el.classList.remove('warn', 'error');
        if (state !== 'ok') el.classList.add(state);
    };

    let monitoringTimer = null;
    let monitoringRequest = null;

    async function refreshMonitoring() {
        if (document.hidden || monitoringRequest) return;

        monitoringRequest = new AbortController();
        try {
            const response = await fetch(liveUrl, {
                headers: { Accept: 'application/json' },
                signal: monitoringRequest.signal,
            });
            if (!response.ok) return;

            const payload = await response.json();
            const data = payload.data || {};
            const cpuState = stateClass(data.cpu_percent, 70, 85);
            const ramState = stateClass(data.ram_percent, 70, 85);
            const diskState = stateClass(data.disk_percent, 75, 90);
            const dbState = data.db_status === 'ok' ? 'ok' : 'error';
            const maintenanceState = data.maintenance ? 'warn' : 'ok';

            setPill($('cpu-pill'), formatPercent(data.cpu_percent), cpuState);
            setText('cpu-value', formatPercent(data.cpu_percent));
            setText('cpu-meter-value', formatPercent(data.cpu_percent));
            setMeter('cpu-meter', data.cpu_percent, cpuState);

            setPill($('ram-pill'), formatPercent(data.ram_percent), ramState);
            setText('ram-value', data.ram_usage_text || '-');
            setText('ram-limit', `Limit: ${data.ram_limit_text || '-'}`);
            setText('ram-meter-value', formatPercent(data.ram_percent));
            setMeter('ram-meter', data.ram_percent, ramState);

            setPill($('disk-pill'), formatPercent(data.disk_percent), diskState);
            setText('disk-value', data.disk_used_text || '-');
            setText('disk-total', `Total: ${data.disk_total_text || '-'}`);
            setText('disk-meter-value', formatPercent(data.disk_percent));
            setMeter('disk-meter', data.disk_percent, diskState);

            setPill($('db-pill'), `DB ${(data.db_status || 'error').toUpperCase()}`, dbState);
            setText('db-value', data.maintenance ? 'Maintenance ON' : 'Healthy');
            setText('server-sub', `Server: ${data.server_time || '-'}`);
            setPill($('maintenance-pill'), data.maintenance ? 'Maintenance ON' : 'Maintenance OFF', maintenanceState);

            setText('overall-value', formatPercent(data.overall_load));
            const circle = $('overall-circle');
            if (circle) circle.style.setProperty('--angle', `${Math.max(0, Math.min(360, (Number(data.overall_load || 0) / 100) * 360))}deg`);

            setText('server-time', data.server_time || '-');
            setText('php-version', data.php_version || '-');
            setText('laravel-version', data.laravel_version || '-');
            setText('os', data.os || '-');
            setText('load-1m', data.load_1m === null || data.load_1m === undefined ? 'N/A' : Number(data.load_1m).toFixed(2));
            setText('ram-peak', data.ram_peak_text || '-');

            (data.trend || []).forEach((value, index) => {
                const bar = root.querySelector(`[data-monitor-trend="${index}"]`);
                if (bar) bar.style.height = `${Math.max(8, Math.min(100, Number(value)))}%`;
            });
        } catch (error) {
            // Keep the last rendered values if the live endpoint is temporarily unavailable.
        } finally {
            monitoringRequest = null;
        }
    }

    const scheduleMonitoring = () => {
        window.clearInterval(monitoringTimer);
        monitoringTimer = document.hidden ? null : window.setInterval(refreshMonitoring, 15000);
        if (!document.hidden) refreshMonitoring();
    };

    document.addEventListener('visibilitychange', scheduleMonitoring);
    window.addEventListener('pagehide', () => {
        window.clearInterval(monitoringTimer);
        monitoringRequest?.abort();
    }, { once:true });
    scheduleMonitoring();
});
</script>
@endpush
