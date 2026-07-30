@extends('layouts.app')

@section('title', __('Student Movement Records'))

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ __('Student Movement Records') }}</h2>
@endsection

@push('styles')
<style>
    .mv-admin { display:flex; flex-direction:column; gap:1rem; }
    .mv-kpis { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:.85rem; }
    @media (min-width: 920px) { .mv-kpis { grid-template-columns:repeat(4, minmax(0, 1fr)); } }
    .mv-kpi {
        position:relative;
        padding:1rem 1.05rem;
        border-radius:16px;
        border:1px solid rgba(255,255,255,.22);
        background:
            linear-gradient(180deg, rgba(255,255,255,.92), rgba(247,242,237,.82)),
            radial-gradient(circle at 88% 12%, rgba(164,141,120,.18), transparent 34%);
        box-shadow:0 18px 34px rgba(27,20,14,.12);
        overflow:hidden;
    }
    .mv-kpi::after {
        content:'';
        position:absolute;
        left:0;
        right:0;
        bottom:0;
        height:3px;
        background:linear-gradient(90deg, #8a7362, #d5b89c);
    }
    .mv-kpi-label {
        font-size:.72rem;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.06em;
        color:#866f5e;
        margin-bottom:.35rem;
    }
    .mv-kpi-value { font-size:2rem; font-weight:800; color:#2d1f14; line-height:1; }
    .mv-toolbar { display:grid; grid-template-columns:1fr; gap:.85rem; align-items:start; }
    @media (min-width: 1100px) { .mv-toolbar { grid-template-columns:1.2fr .8fr; } }
    .mv-actions { display:flex; gap:.55rem; flex-wrap:wrap; justify-content:flex-end; }
    .mv-actions .ui-btn { min-width:0; }
    .mv-actions .ui-btn.active {
        border-color:rgba(95,190,145,.42);
        background:rgba(95,190,145,.12);
        color:#d8f7e7;
        box-shadow:inset 0 0 0 1px rgba(95,190,145,.14);
    }
    .mv-filter-shell {
        display:grid;
        gap:1rem;
    }
    .mv-filter-top {
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap:1rem;
        flex-wrap:wrap;
    }
    .mv-filter-copy {
        display:grid;
        gap:.3rem;
    }
    .mv-filter-title {
        font-size:1rem;
        font-weight:800;
        color:var(--text);
    }
    .mv-filter-desc {
        color:var(--text-muted);
        font-size:.8rem;
        max-width:720px;
    }
    .mv-results-badge {
        display:inline-flex;
        align-items:center;
        gap:.45rem;
        padding:.55rem .8rem;
        border-radius:999px;
        border:1px solid rgba(226,209,192,.14);
        background:rgba(255,255,255,.04);
        color:var(--text-muted);
        font-size:.78rem;
        font-weight:700;
    }
    .mv-results-badge strong { color:var(--text); font-size:.95rem; }
    .mv-filter-grid { display:grid; grid-template-columns:1fr; gap:.8rem; }
    @media (min-width: 880px) { .mv-filter-grid { grid-template-columns:1.35fr repeat(6, minmax(0, 1fr)); } }
    .mv-field { display:flex; flex-direction:column; gap:.3rem; }
    .mv-field span { font-size:.72rem; font-weight:700; color:var(--text-muted); }
    .mv-field input,
    .mv-field select { width:100%; min-width:0; }
    .mv-field input::placeholder { color:#8e8175; }
    .mv-filter-actions {
        display:flex;
        flex-wrap:wrap;
        gap:.65rem;
        align-items:center;
        justify-content:space-between;
    }
    .mv-filter-buttons {
        display:flex;
        flex-wrap:wrap;
        gap:.65rem;
    }
    .mv-filter-meta {
        display:flex;
        flex-wrap:wrap;
        gap:.5rem;
        align-items:center;
    }
    .mv-chip {
        display:inline-flex;
        align-items:center;
        padding:.42rem .7rem;
        border-radius:999px;
        background:rgba(255,255,255,.05);
        border:1px solid rgba(226,209,192,.14);
        color:var(--text-muted);
        font-size:.74rem;
        font-weight:700;
    }
    .mv-chip strong { color:var(--text); margin-right:.28rem; }
    .mv-table-note { color:var(--text-muted); font-size:.78rem; margin-top:.15rem; }
    .mv-student { font-weight:700; color:var(--text); }
    .mv-sub { color:var(--text-muted); font-size:.76rem; }
    .mv-student-card { display:flex; align-items:center; gap:.7rem; min-width:220px; }
    .mv-avatar { width:48px; height:48px; border-radius:10px; object-fit:cover; border:1px solid rgba(226,209,192,.24); background:rgba(255,255,255,.06); flex:0 0 48px; }
    .mv-avatar-empty { display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-weight:800; }
    .mv-student-actions { margin-top:.35rem; display:flex; gap:.35rem; flex-wrap:wrap; }
    .mv-mini-btn { display:inline-flex; align-items:center; padding:.28rem .5rem; border-radius:8px; border:1px solid rgba(226,209,192,.18); color:var(--text); text-decoration:none; font-size:.7rem; font-weight:750; }
    .mv-type-badge {
        display:inline-flex;
        align-items:center;
        padding:.34rem .64rem;
        border-radius:999px;
        background:rgba(215,191,168,.1);
        border:1px solid rgba(215,191,168,.16);
        color:#e4cfbb;
        font-size:.74rem;
        font-weight:700;
    }
    .mv-time {
        display:grid;
        gap:.18rem;
        min-width:155px;
    }
    .mv-time strong { color:var(--text); font-size:.83rem; }
    .mv-time span { color:var(--text-muted); font-size:.72rem; }
    .mv-row-quiet { color:var(--text-muted); font-size:.76rem; }
    .mv-empty { padding:2rem 1rem; text-align:center; color:var(--text-muted); }
    .mv-table-head {
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:1rem;
        padding:1rem 1rem 0;
        flex-wrap:wrap;
    }
    .mv-table-head strong { color:var(--text); font-size:.96rem; }
    .mv-table-head span { color:var(--text-muted); font-size:.78rem; }
    .mv-page-controls {
        display:flex;
        align-items:center;
        gap:.55rem;
        flex-wrap:wrap;
    }
    .mv-page-link {
        display:inline-flex;
        align-items:center;
        justify-content:center;
        min-height:36px;
        padding:.5rem .75rem;
        border-radius:10px;
        border:1px solid rgba(203,185,164,.8);
        background:linear-gradient(180deg, #fff 0%, #f9f3ec 100%);
        color:#6e5745;
        text-decoration:none;
        font-size:.78rem;
        font-weight:800;
        white-space:nowrap;
    }
    .mv-page-link.is-disabled {
        opacity:.45;
        pointer-events:none;
    }
    .mv-virtual-scroll {
        --mv-row-height: 118px;
        position:relative;
        min-height:360px;
        max-height:min(68dvh, 760px);
        overflow:auto;
        overscroll-behavior:contain;
        scrollbar-gutter:stable;
        contain:layout paint;
    }
    .mv-virtual-scroll .ui-table { min-width:1320px; table-layout:fixed; }
    .mv-virtual-scroll thead th {
        position:sticky;
        top:0;
        z-index:4;
        box-shadow:0 1px 0 var(--border);
    }
    .mv-virtual-scroll tbody tr[data-record-row] { height:var(--mv-row-height); }
    .mv-virtual-scroll tbody tr[data-record-row] > td { height:var(--mv-row-height); }
    .mv-virtual-spacer td {
        height:var(--mv-spacer-height, 0);
        padding:0 !important;
        border:0 !important;
        background:transparent !important;
    }
    .mv-explanation {
        display:-webkit-box;
        max-width:220px;
        overflow:hidden;
        -webkit-box-orient:vertical;
        -webkit-line-clamp:3;
        line-clamp:3;
    }
    .mv-lazy-status {
        display:flex;
        align-items:center;
        justify-content:center;
        gap:.65rem;
        min-height:54px;
        padding:.65rem 1rem;
        border-top:1px solid var(--border);
        color:var(--text-muted);
        font-size:.78rem;
        text-align:center;
    }
    .mv-lazy-status[hidden] { display:none; }
    .mv-lazy-spinner {
        width:18px;
        height:18px;
        border:2px solid var(--border);
        border-top-color:var(--primary);
        border-radius:50%;
        animation:mvSpin .7s linear infinite;
    }
    @keyframes mvSpin { to { transform:rotate(360deg); } }
    @media (max-width: 767px) {
        .mv-virtual-scroll {
            min-height:320px;
            max-height:62dvh;
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .mv-lazy-spinner { animation:none; }
    }
    body[data-theme="dark"] .mv-kpi {
        border-color:rgba(226,209,192,.16);
        background:
            linear-gradient(180deg, rgba(33,29,26,.92), rgba(18,16,14,.86)),
            radial-gradient(circle at 88% 12%, rgba(215,191,168,.14), transparent 34%);
        box-shadow:0 20px 38px rgba(0,0,0,.32);
    }
    body[data-theme="dark"] .mv-kpi-label { color:#cdb7a4; }
    body[data-theme="dark"] .mv-kpi-value,
    body[data-theme="dark"] .mv-student { color:#f7efe8; }
    body[data-theme="dark"] .mv-results-badge strong,
    body[data-theme="dark"] .mv-filter-title,
    body[data-theme="dark"] .mv-time strong,
    body[data-theme="dark"] .mv-chip strong,
    body[data-theme="dark"] .mv-table-head strong { color:#f7efe8; }
    @media (max-width: 880px) {
        .mv-filter-actions,
        .mv-table-head { align-items:flex-start; }
    }
</style>
@endpush

@section('content')
@php
    $activeFilterValues = array_filter([
        'search' => $filters['q'] ?? null,
        'from' => $filters['date_from'] ?? null,
        'to' => $filters['date_to'] ?? null,
        'type' => collect($movementTypes)->firstWhere('id', (int) ($filters['movement_type_id'] ?? 0))?->name,
        'status' => $filters['movement_status'] ?? null,
        'rule' => $filters['rule_status'] ?? null,
        'rows' => !empty($filters['per_page']) ? ($filters['per_page'] . ' per batch') : null,
    ], fn ($value) => filled($value));
@endphp
<div class="ui-shell mv-admin">
    @if(session('success'))
        <div class="ui-card"><div class="ui-card-body" style="color:#1f5559;">{{ session('success') }}</div></div>
    @endif

    <div class="ui-hero">
        <h3>{{ __('Movement Monitoring') }}</h3>
        <p>{{ __('Track student movement live, review curfew compliance, and move quickly between records, violations, and QR controls.') }}</p>
    </div>

    <div class="mv-kpis">
        <div class="mv-kpi"><div class="mv-kpi-label">{{ __('Outside Now') }}</div><div class="mv-kpi-value">{{ $summary['outside_now'] }}</div></div>
        <div class="mv-kpi"><div class="mv-kpi-label">{{ __('Returned Today') }}</div><div class="mv-kpi-value">{{ $summary['returned_today'] }}</div></div>
        <div class="mv-kpi"><div class="mv-kpi-label">{{ __('Check-Outs Today') }}</div><div class="mv-kpi-value">{{ $summary['checkouts_today'] }}</div></div>
        <div class="mv-kpi"><div class="mv-kpi-label">{{ __('Late Returns') }}</div><div class="mv-kpi-value">{{ $summary['late_returns'] }}</div></div>
    </div>

    <section class="ui-card" data-filter-sheet data-filter-title="{{ __('Movement filters') }}">
        <div class="ui-card-head mv-toolbar">
            <div>
                <strong>{{ __('Search & Filter') }}</strong>
                <div class="mv-table-note">{{ __('Filter by student, date, movement type, status, or rule result.') }}</div>
            </div>
            <div class="mv-actions">
                <a class="ui-btn active" href="{{ route('admin.movements.index') }}">{{ __('Records') }}</a>
                <a class="ui-btn" href="{{ route('admin.movements.outside') }}">{{ __('Outside Campus') }}</a>
                <a class="ui-btn" href="{{ route('admin.movements.violations') }}">{{ __('Violations') }}</a>
                <a class="ui-btn" href="{{ route('admin.movements.qr') }}">{{ __('QR Code') }}</a>
                @if((session('auth_user.admin_role') ?? null) === 'system_admin')
                    <a class="ui-btn" href="{{ route('admin.movements.settings') }}">{{ __('Settings') }}</a>
                @endif
            </div>
        </div>
        <div class="ui-card-body">
            <form method="GET" class="mv-filter-grid" id="movementFilterForm">
                <label class="mv-field">
                    <span>{{ __('Search') }}</span>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Student / matric / programme') }}">
                </label>
                <label class="mv-field">
                    <span>{{ __('Start Date') }}</span>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                </label>
                <label class="mv-field">
                    <span>{{ __('End Date') }}</span>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                </label>
                <label class="mv-field">
                    <span>{{ __('Movement Type') }}</span>
                    <select name="movement_type_id">
                        <option value="">{{ __('All Movement Types') }}</option>
                        @foreach($movementTypes as $type)
                            <option value="{{ $type->id }}" @selected(($filters['movement_type_id'] ?? '') == $type->id)>{{ __($type->name) }}</option>
                        @endforeach
                    </select>
                </label>
                <label class="mv-field">
                    <span>{{ __('Status') }}</span>
                    <select name="movement_status">
                        <option value="">{{ __('All Status') }}</option>
                        <option value="outside" @selected(($filters['movement_status'] ?? '') === 'outside')>{{ __('Outside Campus') }}</option>
                        <option value="returned" @selected(($filters['movement_status'] ?? '') === 'returned')>{{ __('Returned') }}</option>
                    </select>
                </label>
                <label class="mv-field">
                    <span>{{ __('Rule') }}</span>
                    <select name="rule_status">
                        <option value="">{{ __('All Rules') }}</option>
                        <option value="pending" @selected(($filters['rule_status'] ?? '') === 'pending')>{{ __('Pending') }}</option>
                        <option value="compliant" @selected(($filters['rule_status'] ?? '') === 'compliant')>{{ __('Compliant') }}</option>
                        <option value="late" @selected(($filters['rule_status'] ?? '') === 'late')>{{ __('Late Return') }}</option>
                    </select>
                </label>
                <label class="mv-field">
                    <span>{{ __('Rows') }}</span>
                    <select name="per_page">
                        <option value="25" @selected(($filters['per_page'] ?? '50') === '25')>25</option>
                        <option value="50" @selected(($filters['per_page'] ?? '50') === '50')>50</option>
                        <option value="100" @selected(($filters['per_page'] ?? '50') === '100')>100</option>
                    </select>
                </label>
            </form>
            <div class="mv-filter-actions" style="margin-top:1rem;">
                <div class="mv-filter-meta">
                    <div class="mv-results-badge">
                        <strong data-movement-loaded-count>{{ $records->count() }}</strong>
                        <span>{{ __('Loaded records') }}</span>
                    </div>
                    @foreach($activeFilterValues as $label => $value)
                        <span class="mv-chip"><strong>{{ ucfirst($label) }}:</strong> {{ __($value) }}</span>
                    @endforeach
                </div>
                <div class="mv-filter-buttons">
                    <button class="ui-btn primary" type="submit" form="movementFilterForm">{{ __('Filter') }}</button>
                    <a class="ui-btn" href="{{ route('admin.movements.index') }}">{{ __('Reset') }}</a>
                    <a class="ui-btn" href="{{ route('admin.movements.export', request()->query()) }}">{{ __('Export CSV') }}</a>
                </div>
            </div>
        </div>
    </section>

    <section class="ui-card">
        <div class="mv-table-head">
            <div>
                <strong>{{ __('Movement Timeline') }}</strong>
                <span>{{ __('Latest check-out and return activity for students.') }}</span>
            </div>
            <div class="mv-page-controls">
                @if($records->previousPageUrl())
                    <a class="mv-page-link" href="{{ $records->previousPageUrl() }}" rel="prev">{{ __('Previous') }}</a>
                @else
                    <span class="mv-page-link is-disabled">{{ __('Previous') }}</span>
                @endif
                @if($records->hasMorePages())
                    <a class="mv-page-link" href="{{ $records->nextPageUrl() }}" rel="next">{{ __('Next') }}</a>
                @else
                    <span class="mv-page-link is-disabled">{{ __('Next') }}</span>
                @endif
            </div>
        </div>
        <div
            class="mv-virtual-scroll"
            data-movement-virtual
            data-lenis-prevent
            data-endpoint="{{ route('admin.movements.index', request()->except('cursor')) }}"
            data-empty-label="{{ __('No movement records found.') }}"
            data-loading-label="{{ __('Loading more movement records...') }}"
            data-ready-label="{{ __('Scroll to load older movement records.') }}"
            data-complete-label="{{ __('All matching movement records are loaded.') }}"
            data-error-label="{{ __('Movement records could not be loaded. Try again.') }}"
        >
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>{{ __('Student') }}</th>
                        <th>{{ __('Programme') }}</th>
                        <th>{{ __('Residence') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Plate No.') }}</th>
                        <th>{{ __('Check-Out') }}</th>
                        <th>{{ __('Return') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Rule') }}</th>
                        <th>{{ __('Explanation') }}</th>
                    </tr>
                </thead>
                <tbody data-movement-rows>
                    @forelse($records as $record)
                        <tr>
                            <td>
                                <div class="mv-student-card">
                                    @if(!empty($record->student_photo))
                                        <img class="mv-avatar" src="{{ asset('storage/' . $record->student_photo) }}" alt="{{ __('Profile photo') }}" loading="lazy" decoding="async">
                                    @else
                                        <div class="mv-avatar mv-avatar-empty">{{ strtoupper(substr($record->student_name ?? 'S', 0, 1)) }}</div>
                                    @endif
                                    <div>
                                        <span class="mv-student">{{ $record->student_name }}</span><br>
                                        <span class="mv-sub">{{ $record->matric_no }}</span>
                                        <div class="mv-student-actions">
                                            <a class="mv-mini-btn" href="{{ route('admin.students.show', $record->student_id) }}">{{ __('View Profile') }}</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $record->program }}<br><span class="mv-sub">{{ $record->checkpoint_name }}</span></td>
                            <td>
                                {{ ($record->residence_status ?? 'inside_campus') === 'live_out' ? __('Live Out') : __('Inside Campus') }}
                                <br>
                                <span class="mv-sub">{{ $record->room_number ?: '-' }}</span>
                            </td>
                            <td><span class="mv-type-badge">{{ __($record->movement_type_name) }}</span></td>
                            <td>{{ $record->vehicle_plate_no ?: '-' }}</td>
                            <td>
                                <div class="mv-time">
                                    <strong>{{ \Illuminate\Support\Carbon::parse($record->checkout_at)->format('d M Y') }}</strong>
                                    <span>{{ \Illuminate\Support\Carbon::parse($record->checkout_at)->format('h:i A') }}</span>
                                </div>
                            </td>
                            <td>
                                @if($record->return_at)
                                    <div class="mv-time">
                                        <strong>{{ \Illuminate\Support\Carbon::parse($record->return_at)->format('d M Y') }}</strong>
                                        <span>{{ \Illuminate\Support\Carbon::parse($record->return_at)->format('h:i A') }}</span>
                                    </div>
                                @else
                                    <span class="mv-row-quiet">{{ __('Not returned yet') }}</span>
                                @endif
                            </td>
                            <td><span class="ui-status status-{{ $record->movement_status === 'outside' ? 'pending' : 'confirmed' }}">{{ __($record->movement_status) }}</span></td>
                            <td><span class="ui-status status-{{ $record->rule_status === 'late' ? 'rejected' : ($record->rule_status === 'pending' ? 'pending' : 'confirmed') }}">{{ __($record->rule_status) }}</span></td>
                            <td><span class="mv-row-quiet mv-explanation">{{ $record->late_explanation ?: '-' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="mv-empty">{{ __('No movement records found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mv-lazy-status" data-movement-status aria-live="polite">
            <span data-movement-status-text>
                {{ $records->hasMorePages() ? __('Scroll to load older movement records.') : __('All matching movement records are loaded.') }}
            </span>
            <button type="button" class="ui-btn" data-movement-retry hidden>{{ __('Try Again') }}</button>
        </div>
        <div class="ui-card-body mv-pagination-wrap" data-movement-pagination>
            <nav class="mv-page-controls" aria-label="{{ __('Movement record pagination') }}">
                @if($records->previousPageUrl())
                    <a class="mv-page-link" href="{{ $records->previousPageUrl() }}" rel="prev">{{ __('Previous') }}</a>
                @endif
                @if($records->nextPageUrl())
                    <a class="mv-page-link" href="{{ $records->nextPageUrl() }}" rel="next">{{ __('Next') }}</a>
                @endif
            </nav>
        </div>
    </section>
</div>
@php
    $movementVirtualSeed = [
        'records' => $recordPayload,
        'next_cursor' => $records->nextCursor()?->encode(),
        'has_more' => $records->hasMorePages(),
    ];
@endphp
<script type="application/json" id="movementVirtualSeed">@json($movementVirtualSeed)</script>
@endsection

@push('scripts')
<script>
(() => {
    const viewport = document.querySelector('[data-movement-virtual]');
    const tbody = viewport?.querySelector('[data-movement-rows]');
    const seedNode = document.getElementById('movementVirtualSeed');
    const status = document.querySelector('[data-movement-status]');
    const statusText = status?.querySelector('[data-movement-status-text]');
    const retryButton = status?.querySelector('[data-movement-retry]');
    const fallbackPagination = document.querySelector('[data-movement-pagination]');
    const loadedCount = document.querySelector('[data-movement-loaded-count]');

    if (!(viewport instanceof HTMLElement) || !(tbody instanceof HTMLElement) || !seedNode) return;

    let seed;
    try {
        seed = JSON.parse(seedNode.textContent || '{}');
    } catch {
        return;
    }

    const rowHeight = 118;
    const overscan = 6;
    const records = Array.isArray(seed.records) ? seed.records : [];
    const recordIds = new Set(records.map((record) => Number(record.id)));
    let nextCursor = seed.next_cursor || null;
    let hasMore = Boolean(seed.has_more && nextCursor);
    let loading = false;
    let renderFrame = null;

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (character) => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;',
    })[character]);

    const rowHtml = (record) => {
        const photo = record.student_photo_url
            ? `<img class="mv-avatar" src="${escapeHtml(record.student_photo_url)}" alt="${escapeHtml(record.profile_photo_label)}" loading="lazy" decoding="async">`
            : `<div class="mv-avatar mv-avatar-empty">${escapeHtml(record.student_initial)}</div>`;
        const returnCell = record.return_date
            ? `<div class="mv-time"><strong>${escapeHtml(record.return_date)}</strong><span>${escapeHtml(record.return_time)}</span></div>`
            : `<span class="mv-row-quiet">${escapeHtml(record.not_returned_label)}</span>`;

        return `
            <tr data-record-row data-record-id="${Number(record.id)}">
                <td>
                    <div class="mv-student-card">
                        ${photo}
                        <div>
                            <span class="mv-student">${escapeHtml(record.student_name)}</span><br>
                            <span class="mv-sub">${escapeHtml(record.matric_no)}</span>
                            <div class="mv-student-actions">
                                <a class="mv-mini-btn" href="${escapeHtml(record.student_profile_url)}">${escapeHtml(record.view_profile_label)}</a>
                            </div>
                        </div>
                    </div>
                </td>
                <td>${escapeHtml(record.program)}<br><span class="mv-sub">${escapeHtml(record.checkpoint_name)}</span></td>
                <td>${escapeHtml(record.residence_label)}<br><span class="mv-sub">${escapeHtml(record.room_number)}</span></td>
                <td><span class="mv-type-badge">${escapeHtml(record.movement_type_label)}</span></td>
                <td>${escapeHtml(record.vehicle_plate_no)}</td>
                <td><div class="mv-time"><strong>${escapeHtml(record.checkout_date)}</strong><span>${escapeHtml(record.checkout_time)}</span></div></td>
                <td>${returnCell}</td>
                <td><span class="ui-status status-${escapeHtml(record.movement_status_tone)}">${escapeHtml(record.movement_status_label)}</span></td>
                <td><span class="ui-status status-${escapeHtml(record.rule_status_tone)}">${escapeHtml(record.rule_status_label)}</span></td>
                <td><span class="mv-row-quiet mv-explanation">${escapeHtml(record.late_explanation)}</span></td>
            </tr>
        `;
    };

    const setStatus = (message, { busy = false, error = false } = {}) => {
        if (!status || !statusText) return;
        statusText.textContent = message;
        status.querySelector('.mv-lazy-spinner')?.remove();
        if (busy) {
            status.insertAdjacentHTML('afterbegin', '<span class="mv-lazy-spinner" aria-hidden="true"></span>');
        }
        if (retryButton) retryButton.hidden = !error;
    };

    const render = () => {
        renderFrame = null;

        if (records.length === 0) {
            tbody.innerHTML = `<tr><td colspan="10" class="mv-empty">${escapeHtml(viewport.dataset.emptyLabel)}</td></tr>`;
            if (loadedCount) loadedCount.textContent = '0';
            return;
        }

        const start = Math.max(0, Math.floor(viewport.scrollTop / rowHeight) - overscan);
        const visibleCount = Math.ceil(viewport.clientHeight / rowHeight) + (overscan * 2);
        const end = Math.min(records.length, start + visibleCount);
        const topHeight = start * rowHeight;
        const bottomHeight = Math.max(0, (records.length - end) * rowHeight);

        tbody.innerHTML = `
            <tr class="mv-virtual-spacer" aria-hidden="true" style="--mv-spacer-height:${topHeight}px"><td colspan="10"></td></tr>
            ${records.slice(start, end).map(rowHtml).join('')}
            <tr class="mv-virtual-spacer" aria-hidden="true" style="--mv-spacer-height:${bottomHeight}px"><td colspan="10"></td></tr>
        `;

        if (loadedCount) loadedCount.textContent = String(records.length);
    };

    const scheduleRender = () => {
        if (renderFrame !== null) return;
        renderFrame = window.requestAnimationFrame(render);
    };

    const loadMore = async () => {
        if (loading || !hasMore || !nextCursor) return;
        loading = true;
        setStatus(viewport.dataset.loadingLabel, { busy: true });

        try {
            const url = new URL(viewport.dataset.endpoint, window.location.origin);
            url.searchParams.set('cursor', nextCursor);
            const response = await fetch(url, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) throw new Error('Movement request failed');
            const payload = await response.json();

            (Array.isArray(payload.data) ? payload.data : []).forEach((record) => {
                const id = Number(record.id);
                if (recordIds.has(id)) return;
                recordIds.add(id);
                records.push(record);
            });

            nextCursor = payload.next_cursor || null;
            hasMore = Boolean(payload.has_more && nextCursor);
            scheduleRender();
            setStatus(hasMore ? viewport.dataset.readyLabel : viewport.dataset.completeLabel);
        } catch {
            setStatus(viewport.dataset.errorLabel, { error: true });
        } finally {
            loading = false;
        }
    };

    const maybeLoadMore = () => {
        const remaining = viewport.scrollHeight - viewport.scrollTop - viewport.clientHeight;
        if (remaining < rowHeight * 8) loadMore();
    };

    viewport.addEventListener('scroll', () => {
        scheduleRender();
        maybeLoadMore();
    }, { passive: true });
    retryButton?.addEventListener('click', loadMore);

    viewport.dataset.virtualReady = 'true';
    if (fallbackPagination) fallbackPagination.hidden = true;
    render();
    setStatus(hasMore ? viewport.dataset.readyLabel : viewport.dataset.completeLabel);
    maybeLoadMore();
})();
</script>
@endpush
