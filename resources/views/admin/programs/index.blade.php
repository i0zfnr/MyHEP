@extends('layouts.app')
@section('title', __('Program Management'))
@section('header')<h2 style="margin:0;font-size:1.15rem;font-weight:800;">{{ __('Program Management') }}</h2>@endsection

@push('styles')
@php
    $canUseAccent = (session('auth_user.role') === 'student' || session('auth_user.admin_role') === 'system_admin');
@endphp
<style>
.pmr {
    --pm-accent: {{ $canUseAccent ? 'var(--se-primary, #C8A96A)' : '#C8A96A' }};
    display: grid;
    gap: 1.25rem;
    color: var(--text, #241d16);
    max-width: 1360px;
    margin: 0 auto;
    padding: 1.5rem 1rem;
    font-family: inherit;
}
.pmr-hero, .pmr-card, .pmr-kpi {
    background: var(--surface, #fff);
    border: 1px solid color-mix(in srgb, var(--pm-accent) 22%, var(--border, #eadac8));
    border-radius: 18px;
    box-shadow: var(--glass-shadow, 0 14px 36px rgba(0,0,0,0.06));
    backdrop-filter: blur(var(--glass-blur, 16px));
}
.pmr-hero {
    padding: 1.75rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1.5rem;
    background: linear-gradient(135deg, var(--surface, #fff), color-mix(in srgb, var(--pm-accent) 10%, var(--surface, #fff)));
}
.pmr-eyebrow {
    font-size: .72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .12em;
    color: var(--pm-accent);
}
.pmr h1 {
    font-size: 2rem;
    margin: .35rem 0;
    font-weight: 800;
    color: var(--text, #241d16);
}
.pmr p {
    color: var(--text-muted, #746b62);
    margin: .25rem 0;
    font-size: 0.92rem;
}

.pmr-actions {
    display: flex;
    align-items: center;
    gap: .65rem;
}
.pmr-btn {
    min-height: 44px;
    border: 1px solid var(--border, #eadac8);
    border-radius: 12px;
    padding: .7rem 1.1rem;
    background: var(--surface, #fff);
    color: var(--text, #241d16);
    font-weight: 800;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    font-size: 0.88rem;
    transition: background 0.15s ease, border-color 0.15s ease;
}
.pmr-btn:hover { background: color-mix(in srgb, var(--pm-accent) 8%, var(--surface, #fff)); }
.pmr-btn.primary {
    background: var(--pm-accent);
    color: #fff;
    border-color: var(--pm-accent);
    box-shadow: 0 4px 14px color-mix(in srgb, var(--pm-accent) 30%, transparent);
}
.pmr-btn.primary:hover {
    background: color-mix(in srgb, var(--pm-accent) 85%, #000);
    border-color: color-mix(in srgb, var(--pm-accent) 85%, #000);
    color: #fff;
}
.pmr-empty-state {
    min-height: 250px;
    display: grid;
    place-items: center;
    padding: 2.5rem 1.25rem;
    text-align: center;
    background: linear-gradient(180deg, color-mix(in srgb, var(--pm-accent) 3%, var(--surface, #fff)), var(--surface, #fff));
}
.pmr-empty-state__inner { max-width: 470px; }
.pmr-empty-state__icon {
    width: 58px;
    height: 58px;
    display: grid;
    place-items: center;
    margin: 0 auto 1rem;
    border: 1px solid color-mix(in srgb, var(--pm-accent) 35%, var(--border, #eadac8));
    border-radius: 16px;
    background: color-mix(in srgb, var(--pm-accent) 10%, var(--surface, #fff));
    color: var(--pm-accent-strong);
}
.pmr-empty-state__icon svg { width: 27px; height: 27px; fill: none; stroke: currentColor; stroke-width: 1.8; stroke-linecap: round; stroke-linejoin: round; }
.pmr-empty-state h3 { margin: 0 0 .35rem; font-size: 1.08rem; }
.pmr-empty-state p { margin: 0 auto; font-size: .86rem; }
.pmr-empty-state .pmr-actions { justify-content: center; margin-top: 1.1rem; flex-wrap: wrap; }

/* KPI Summary Cards */
.pmr-kpis {
    display: grid;
    grid-template-columns: repeat(5, minmax(0, 1fr));
    gap: 0.8rem;
}
.pmr-kpi {
    padding: 1.1rem 1.25rem;
    position: relative;
    overflow: hidden;
}
.pmr-kpi:after {
    content: '';
    position: absolute;
    width: 58px;
    height: 58px;
    border-radius: 50%;
    right: -18px;
    top: -18px;
    background: color-mix(in srgb, var(--pm-accent) 14%, transparent);
    pointer-events: none;
}
.pmr-kpi span {
    font-size: .72rem;
    color: var(--text-muted, #746b62);
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
}
.pmr-kpi strong {
    display: block;
    font-size: 1.8rem;
    margin-top: .45rem;
    font-weight: 800;
    color: var(--text, #241d16);
}

/* Filter Card */
.pmr-filter-card {
    padding: 1.25rem;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
}
.pmr-filter-form {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.75rem;
    flex: 1;
}
.pmr-filter-form input, .pmr-filter-form select {
    min-height: 44px;
    border: 1px solid var(--border, #eadac8);
    border-radius: 12px;
    padding: .7rem .9rem;
    background: var(--surface, #fff);
    color: var(--text, #241d16);
    font-size: 0.9rem;
}
.pmr-filter-form input { flex: 1; min-width: 260px; }
.pmr-filter-form input:focus, .pmr-filter-form select:focus {
    outline: none;
    border-color: var(--pm-accent);
}

.pmr-quick-tabs {
    display: flex;
    align-items: center;
    gap: 10px;
    overflow-x: auto;
}
.pmr-tab-group { display:flex; align-items:center; gap:3px; padding:3px; border:1px solid var(--border,#eadac8); border-radius:10px; background:color-mix(in srgb,var(--pm-accent) 3%,var(--surface,#fff)); }
.pmr-tab-group + .pmr-tab-group { margin-left:2px; }
.pmr-tab-group-label { padding:0 5px 0 3px; color:var(--text-muted,#746b62); font-size:.62rem; font-weight:800; letter-spacing:.055em; text-transform:uppercase; white-space:nowrap; }
.pmr-tab-link {
    display: inline-flex;
    padding: 6px 14px;
    min-height:36px;
    align-items:center;
    border-radius:7px;
    font-size: 0.78rem;
    font-weight: 800;
    color: var(--text-muted, #746b62);
    text-decoration: none;
    background: transparent;
    transition: all 0.15s ease;
    white-space: nowrap;
}
.pmr-tab-link:hover {
    background: color-mix(in srgb, var(--pm-accent) 12%, transparent);
    color: var(--pm-accent);
}
.pmr-tab-link.active {
    background: color-mix(in srgb,var(--pm-accent) 16%,var(--surface,#fff));
    color: var(--pm-accent-strong);
    box-shadow:inset 0 0 0 1px color-mix(in srgb,var(--pm-accent) 22%,transparent);
}

/* Table List Section */
.pmr-card-body { padding: 1.25rem; }
.pmr-table {
    width: 100%;
    border-collapse: collapse;
}
.pmr-table th, .pmr-table td {
    text-align: left;
    padding: 0.95rem 1rem;
    border-bottom: 1px solid var(--border, #eadac8);
    font-size: 0.88rem;
    color: var(--text, #241d16);
}
.pmr-table tr:last-child td { border-bottom: 0; }
.pmr-table th {
    font-size: 0.7rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--text-muted, #746b62);
    font-weight: 800;
    background: color-mix(in srgb, var(--pm-accent) 5%, var(--surface, #fff));
}
.pmr-badge {
    display: inline-flex;
    padding: 0.3rem 0.65rem;
    border-radius: 99px;
    background: color-mix(in srgb, var(--pm-accent) 12%, transparent);
    color: var(--pm-accent);
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
}
.pmr-badge.pending_tpsa, .pmr-badge.pending_director, .pmr-badge.pending_kj_hep {
    background: #fff5df;
    color: #a15c08;
}
.pmr-badge.active, .pmr-badge.archived, .pmr-badge.completed {
    background: #e7f7ee;
    color: #18734a;
}
.pmr-badge.rejected {
    background: #fff0ee;
    color: #b42318;
}

body[data-theme="dark"] .pmr-badge.pending_tpsa, 
body[data-theme="dark"] .pmr-badge.pending_director,
body[data-theme="dark"] .pmr-badge.pending_kj_hep {
    background: rgba(161, 92, 8, 0.25);
    color: #f59e0b;
}
body[data-theme="dark"] .pmr-badge.active, 
body[data-theme="dark"] .pmr-badge.archived, 
body[data-theme="dark"] .pmr-badge.completed {
    background: rgba(40, 104, 108, 0.3);
    color: #34d399;
}
body[data-theme="dark"] .pmr-badge.rejected {
    background: rgba(180, 35, 24, 0.25);
    color: #fca5a5;
}

@media (max-width: 1050px) {
    .pmr-kpis { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 760px) {
    .pmr-hero { flex-direction: column; align-items: flex-start; }
    .pmr-filter-card, .pmr-filter-form { flex-direction: column; align-items: stretch; }
    .pmr-kpis { grid-template-columns: 1fr; }
}
</style>
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
                                    @if(($filters['scope'] ?? '') === 'review')
                                        <a class="pmr-btn primary" href="{{ route('admin.programs.operations', $program->id) }}#programReport">{{ __('Review Report') }}</a>
                                    @else
                                        <a class="pmr-btn" href="{{ route('admin.programs.show', $program->id) }}">{{ __('View Detail') }}</a>
                                    @endif
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
