@extends('layouts.app')
@section('title', __('Program Management'))
@section('header')<h2 style="margin:0;font-size:1.15rem;font-weight:800;">{{ __('Program Management') }}</h2>@endsection

@push('styles')
@php
    $canUseAccent = (session('auth_user.role') === 'student' || session('auth_user.admin_role') === 'system_admin');
@endphp

@endpush
@push('styles')
@include('admin.programs.partials.design-system')
@endpush

@section('content')
<main class="pmr">
    <!-- Hero Banner -->
    <header class="pmr-hero">
        <div>
            <span class="pmr-eyebrow">{{ __('PROGRAM WORKSPACE') }}</span>
            <h1>{{ __('Program Management') }}</h1>
            <p>{{ __('Register approved programs, collect participant feedback, and track final report review and archiving.') }}</p>
        </div>
        <div class="pmr-actions">
            <a class="pmr-btn" href="{{ route('admin.program-certificates.index') }}">{{ __('Certificates') }}</a>
            <a class="pmr-btn" href="{{ route('admin.program-certificate-templates.index') }}">{{ __('Certificate Templates') }}</a>
            <a class="pmr-btn primary" href="{{ route('admin.programs.create') }}">+ {{ __('New Program') }}</a>
        </div>
    </header>

    @if(session('success'))
        <div class="alert alert-success" style="border-radius: 12px; margin-bottom: 0;">
            {{ session('success') }}
        </div>
    @endif

    <!-- 5 KPI Summary Cards -->
    <section class="pmr-kpis">
        <article class="pmr-kpi">
            <span>{{ __('Total Students') }}</span>
            <strong style="color: var(--pm-accent);">{{ number_format($stats['total_students'] ?? 0) }}</strong>
        </article>
        <article class="pmr-kpi">
            <span>{{ __('Total Programs') }}</span>
            <strong>{{ number_format($stats['total'] ?? 0) }}</strong>
        </article>
        <article class="pmr-kpi">
            <span>{{ __('Reports in Review') }}</span>
            <strong class="pmr-tone-accent">{{ number_format($stats['pending'] ?? 0) }}</strong>
        </article>
        <article class="pmr-kpi">
            <span>{{ __('Active Programs') }}</span>
            <strong class="pmr-tone-success">{{ number_format($stats['active'] ?? 0) }}</strong>
        </article>
        <article class="pmr-kpi">
            <span>{{ __('Reports Archived by KJ HEP') }}</span>
            <strong class="pmr-tone-muted">{{ number_format($stats['archived'] ?? 0) }}</strong>
        </article>
    </section>

    <!-- Filter Card -->
    <section class="pmr-card pmr-filter-card">
        <form class="pmr-filter-form" method="get">
            <input type="hidden" name="scope" value="{{ $filters['scope'] ?? 'mine' }}">
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Search program, reference, venue or director') }}">
            <select name="status">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(['active','completed'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                        {{ __(str_replace('_',' ',ucfirst($status))) }}
                    </option>
                @endforeach
            </select>
            <button class="pmr-btn" type="submit">{{ __('Filter') }}</button>
            @if(filled($filters['q'] ?? null) || filled($filters['status'] ?? null))
                <a class="pmr-tab-link" href="{{ route('admin.programs.index', ['scope' => $filters['scope'] ?? 'mine']) }}">{{ __('Clear') }}</a>
            @endif
        </form>

        <nav class="pmr-quick-tabs" aria-label="{{ __('Program filters') }}">
            <div class="pmr-tab-group">
                <span class="pmr-tab-group-label">{{ __('View') }}</span>
                <a href="{{ route('admin.programs.index', array_filter(['scope' => 'mine', 'status' => $filters['status'] ?? null])) }}" class="pmr-tab-link {{ ($filters['scope'] ?? 'mine') === 'mine' ? 'active' : '' }}">{{ __('My Programs') }}</a>
                <a href="{{ route('admin.programs.index', array_filter(['scope' => 'others', 'status' => $filters['status'] ?? null])) }}" class="pmr-tab-link {{ ($filters['scope'] ?? '') === 'others' ? 'active' : '' }}">{{ __('Other Programs') }}</a>
                <a href="{{ route('admin.programs.index', ['scope' => 'review']) }}" class="pmr-tab-link {{ ($filters['scope'] ?? '') === 'review' ? 'active' : '' }}">{{ __('Awaiting My Review') }} @if(($stats['awaiting_me'] ?? 0) > 0)<strong>({{ $stats['awaiting_me'] }})</strong>@endif</a>
            </div>
            <div class="pmr-tab-group">
                <span class="pmr-tab-group-label">{{ __('Status') }}</span>
                <a href="{{ route('admin.programs.index', ['scope' => $filters['scope'] ?? 'mine']) }}" class="pmr-tab-link {{ blank($filters['status'] ?? null) ? 'active' : '' }}">{{ __('All') }}</a>
                <a href="{{ route('admin.programs.index', array_filter(['status' => 'active', 'scope' => $filters['scope'] ?? 'mine'])) }}" class="pmr-tab-link {{ ($filters['status'] ?? '') === 'active' ? 'active' : '' }}">{{ __('Active') }}</a>
                <a href="{{ route('admin.programs.index', array_filter(['status' => 'completed', 'scope' => $filters['scope'] ?? 'mine'])) }}" class="pmr-tab-link {{ ($filters['status'] ?? '') === 'completed' ? 'active' : '' }}">{{ __('Completed / Archived') }}</a>
            </div>
        </nav>
    </section>

    <!-- Program Data Table -->
    <section class="pmr-card pmr-card-body">
        @if($programs->isEmpty())
            <div class="pmr-empty-state">
                <div class="pmr-empty-state__inner">
                    <div class="pmr-empty-state__icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24"><path d="M3.75 6.75A2.25 2.25 0 0 1 6 4.5h4.5L12 6h6a2.25 2.25 0 0 1 2.25 2.25v9A2.25 2.25 0 0 1 18 19.5H6a2.25 2.25 0 0 1-2.25-2.25z"/><path d="M8 12h8M12 8v8"/></svg>
                    </div>
                    @if(filled($filters['q'] ?? null) || filled($filters['status'] ?? null))
                        <h3>{{ __('No matching programs') }}</h3>
                        <p>{{ __('Try changing the search text or status filter.') }}</p>
                        <div class="pmr-actions"><a class="pmr-btn" href="{{ route('admin.programs.index', ['scope' => $filters['scope'] ?? 'mine']) }}">{{ __('Clear Filters') }}</a></div>
                    @elseif(($filters['scope'] ?? 'mine') === 'others')
                        <h3>{{ __('No other programs available') }}</h3>
                        <p>{{ __('Programs managed by other staff will appear here.') }}</p>
                        <div class="pmr-actions"><a class="pmr-btn" href="{{ route('admin.programs.index', ['scope' => 'mine']) }}">{{ __('View My Programs') }}</a></div>
                    @elseif(($filters['scope'] ?? 'mine') === 'review')
                        <h3>{{ __('No reports awaiting your review') }}</h3>
                        <p>{{ __('Reports will appear here when they are assigned to you.') }}</p>
                        <div class="pmr-actions"><a class="pmr-btn" href="{{ route('admin.programs.index', ['scope' => 'mine']) }}">{{ __('View My Programs') }}</a></div>
                    @else
                        <h3>{{ __('No programs created yet') }}</h3>
                        <p>{{ __('Create a program to manage attendance, questionnaires, reports, points, and certificates.') }}</p>
                    @endif
                </div>
            </div>
        @else
        <div style="overflow-x: auto;">
            <table class="pmr-table">
                <thead>
                    <tr>
                        <th>{{ __('Program Details') }}</th>
                        <th>{{ __('Program Director') }}</th>
                        <th>{{ __('Status & Method') }}</th>
                        <th style="text-align: right;">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($programs as $program)
                        <tr>
                            <td>
                                <strong style="font-size: 0.95rem; color: var(--text-primary, #241d16);">{{ $program->title }}</strong>
                                <div style="color: var(--text-secondary, #746b62); font-size: 0.8rem; margin-top: 2px;">
                                    {{ $program->reference_no ?: __('No reference number') }}
                                    @if($program->venue) &middot; {{ $program->venue }} @endif
                                </div>
                            </td>
                            <td>
                                <strong>{{ $program->director_name ?: __('Unknown staff') }}</strong>
                                <div style="color: var(--text-secondary, #746b62); font-size: 0.8rem;">{{ __('Program Director') }}</div>
                            </td>
                            <td>
                                <span class="pmr-badge {{ $program->status }}">
                                    {{ __(str_replace('_',' ',$program->status)) }}
                                </span>
                                <div style="color: var(--text-secondary, #746b62); font-size: 0.8rem; margin-top: 2px;">
                                    {{ $program->registration_type === 'attendance_only_activity' ? __('Attendance-only activity') : strtoupper($program->paperwork_method) }} &middot; {{ __('Report:') }} {{ __(str_replace('_', ' ', $program->report_status ?: 'not generated')) }}
                                </div>
                                @if(($filters['scope'] ?? '') === 'review')
                                    <div style="color:var(--pm-accent);font-size:.76rem;font-weight:800;margin-top:.3rem;">
                                        {{ match($program->report_status) {
                                            'pending_tpsa' => __('TPA / TPSA / TPSP review'),
                                            'pending_director' => __('Polytechnic Director review'),
                                            'pending_kj_hep' => __('KJ HEP acceptance'),
                                            default => __('Report review'),
                                        } }}
                                    </div>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                @if($program->can_view_detail)
                                    <div style="display:inline-flex; gap:6px; align-items:center; justify-content:flex-end;">
                                        @if(($filters['scope'] ?? '') === 'review')
                                            <a class="pmr-btn primary" href="{{ route('admin.programs.operations', $program->id) }}#programReport">{{ __('Review Report') }}</a>
                                        @else
                                            <a class="pmr-btn" href="{{ route('admin.programs.show', $program->id) }}">{{ __('View Detail') }}</a>
                                        @endif

                                        @if($program->can_manage ?? false)
                                            <form method="post" action="{{ route('admin.programs.destroy', $program->id) }}" onsubmit="return confirm('{{ __('Padam program ini secara kekal?') }}')" style="display:inline-block; margin:0;">
                                                @csrf
                                                @method('delete')
                                                <button class="pmr-btn" type="submit" style="color:#b91c1c; border-color:#fecaca; padding:6px 10px;" title="{{ __('Padam Program') }}">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                    @if($program->is_owned ?? false)<div style="font-size:.72rem;color:var(--text-secondary);margin-top:.25rem;">{{ __('You manage this program') }}</div>@endif
                                @else
                                    <span class="pmr-btn" style="opacity: 0.55; cursor: not-allowed;" title="{{ __('Access restricted by authorization policy.') }}">
                                        {{ __('Restricted') }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </section>

    <div style="margin-top: 10px;">
        {{ $programs->links() }}
    </div>
</main>
@endsection
