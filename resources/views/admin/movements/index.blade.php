@extends('layouts.app')

@section('title', __('Student Movement Records'))

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ __('Student Movement Records') }}</h2>
@endsection



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

    <div class="mv-kpis">
        <div class="mv-kpi"><div class="mv-kpi-label">{{ __('Outside Now') }}</div><div class="mv-kpi-value">{{ $summary['outside_now'] }}</div></div>
        <div class="mv-kpi"><div class="mv-kpi-label">{{ __('Returned Today') }}</div><div class="mv-kpi-value">{{ $summary['returned_today'] }}</div></div>
        <div class="mv-kpi"><div class="mv-kpi-label">{{ __('Check-Outs Today') }}</div><div class="mv-kpi-value">{{ $summary['checkouts_today'] }}</div></div>
        <div class="mv-kpi"><div class="mv-kpi-label">{{ __('Late Returns') }}</div><div class="mv-kpi-value">{{ $summary['late_returns'] }}</div></div>
    </div>

    <section class="ui-card" data-filter-sheet data-filter-title="{{ __('Movement filters') }}">
        <div class="ui-card-head mv-quick-nav">
            <div class="mv-quick-nav-copy">
                <strong>{{ __('Find student movement') }}</strong>
                <span>{{ __('Type a student name, matric number, or programme. Results update without reloading the page.') }}</span>
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
            <form method="GET" id="movementFilterForm" data-movement-filter-form>
                <div class="mv-search-row">
                    <label class="mv-field mv-search-input">
                        <span>{{ __('Student search') }}</span>
                        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Start typing a name, matric number, or programme') }}" autocomplete="off" data-movement-search>
                        <button type="button" class="mv-search-clear" data-movement-clear aria-label="{{ __('Clear search') }}" title="{{ __('Clear search') }}">×</button>
                    </label>
                    <button class="ui-btn primary" type="submit">{{ __('Search') }}</button>
                </div>

                <details class="mv-advanced" @if(count($activeFilterValues) > (filled($filters['q'] ?? null) ? 1 : 0)) open @endif>
                    <summary>{{ __('Advanced filters') }}</summary>
                    <div class="mv-filter-grid">
                        <label class="mv-field"><span>{{ __('Start Date') }}</span><input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"></label>
                        <label class="mv-field"><span>{{ __('End Date') }}</span><input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"></label>
                        <label class="mv-field">
                            <span>{{ __('Movement Type') }}</span>
                            <select name="movement_type_id">
                                <option value="">{{ __('All Movement Types') }}</option>
                                @foreach($movementTypes as $type)<option value="{{ $type->id }}" @selected(($filters['movement_type_id'] ?? '') == $type->id)>{{ __($type->name) }}</option>@endforeach
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
                    </div>
                    <div class="mv-filter-buttons" style="margin-top:.8rem;">
                        <button class="ui-btn primary" type="submit">{{ __('Apply filters') }}</button>
                        <button class="ui-btn" type="button" data-movement-reset>{{ __('Reset filters') }}</button>
                    </div>
                </details>
            </form>
            <div class="mv-filter-actions" style="margin-top:.75rem;">
                <div class="mv-filter-meta">
                    <div class="mv-results-badge">
                        <strong data-movement-loaded-count>{{ $records->count() }}</strong>
                        <span>{{ __('Loaded records') }}</span>
                    </div>
                    <span class="mv-search-feedback" data-movement-search-feedback aria-live="polite"></span>
                </div>
                <div class="mv-filter-buttons">
                    <a class="ui-btn" href="{{ route('admin.movements.export', request()->query()) }}" data-movement-export>{{ __('Export CSV') }}</a>
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
                        <th>{{ __('Movement') }}</th>
                        <th>{{ __('Check-Out') }}</th>
                        <th>{{ __('Return') }}</th>
                        <th>{{ __('Status') }}</th>
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
                                        <div class="mv-sub">{{ $record->program }} · {{ ($record->residence_status ?? 'inside_campus') === 'live_out' ? __('Live Out') : __('Inside Campus') }}</div>
                                        @if(adminCan('students.sensitive'))
                                            <div class="mv-student-actions">
                                                <a class="mv-mini-btn" href="{{ route('admin.students.show', $record->student_id) }}">{{ __('View Profile') }}</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="mv-type-badge">{{ __($record->movement_type_name) }}</span>
                                <div class="mv-sub" style="margin-top:.35rem;">{{ $record->vehicle_plate_no ?: '-' }} · {{ $record->checkpoint_name }}</div>
                            </td>
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
                            <td>
                                <span class="ui-status status-{{ $record->movement_status === 'outside' ? 'pending' : 'confirmed' }}">{{ __($record->movement_status) }}</span>
                                <div style="margin-top:.4rem;"><span class="ui-status status-{{ $record->rule_status === 'late' ? 'rejected' : ($record->rule_status === 'pending' ? 'pending' : 'confirmed') }}">{{ __($record->rule_status) }}</span></div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="mv-empty">{{ __('No movement records found.') }}</td></tr>
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
    const filterForm = document.querySelector('[data-movement-filter-form]');
    const searchInput = filterForm?.querySelector('[data-movement-search]');
    const clearButton = filterForm?.querySelector('[data-movement-clear]');
    const resetButton = filterForm?.querySelector('[data-movement-reset]');
    const feedback = document.querySelector('[data-movement-search-feedback]');
    const exportLink = document.querySelector('[data-movement-export]');

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
    let filterRequest = null;
    let searchTimer = null;

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
        const profileAction = record.student_profile_url
            ? `<div class="mv-student-actions"><a class="mv-mini-btn" href="${escapeHtml(record.student_profile_url)}">${escapeHtml(record.view_profile_label)}</a></div>`
            : '';

        return `
            <tr data-record-row data-record-id="${Number(record.id)}">
                <td>
                    <div class="mv-student-card">
                        ${photo}
                        <div>
                            <span class="mv-student">${escapeHtml(record.student_name)}</span><br>
                            <span class="mv-sub">${escapeHtml(record.matric_no)}</span>
                            <div class="mv-sub">${escapeHtml(record.program)} · ${escapeHtml(record.residence_label)}</div>
                            ${profileAction}
                        </div>
                    </div>
                </td>
                <td><span class="mv-type-badge">${escapeHtml(record.movement_type_label)}</span><div class="mv-sub" style="margin-top:.35rem;">${escapeHtml(record.vehicle_plate_no)} · ${escapeHtml(record.checkpoint_name)}</div></td>
                <td><div class="mv-time"><strong>${escapeHtml(record.checkout_date)}</strong><span>${escapeHtml(record.checkout_time)}</span></div></td>
                <td>${returnCell}</td>
                <td><span class="ui-status status-${escapeHtml(record.movement_status_tone)}">${escapeHtml(record.movement_status_label)}</span><div style="margin-top:.4rem;"><span class="ui-status status-${escapeHtml(record.rule_status_tone)}">${escapeHtml(record.rule_status_label)}</span></div></td>
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

    const setSearchFeedback = (message, loadingState = false) => {
        if (!feedback) return;
        feedback.textContent = message;
        feedback.classList.toggle('is-loading', loadingState);
    };

    const render = () => {
        renderFrame = null;

        if (records.length === 0) {
            tbody.innerHTML = `<tr><td colspan="5" class="mv-empty">${escapeHtml(viewport.dataset.emptyLabel)}</td></tr>`;
            if (loadedCount) loadedCount.textContent = '0';
            return;
        }

        const start = Math.max(0, Math.floor(viewport.scrollTop / rowHeight) - overscan);
        const visibleCount = Math.ceil(viewport.clientHeight / rowHeight) + (overscan * 2);
        const end = Math.min(records.length, start + visibleCount);
        const topHeight = start * rowHeight;
        const bottomHeight = Math.max(0, (records.length - end) * rowHeight);

        tbody.innerHTML = `
            <tr class="mv-virtual-spacer" aria-hidden="true" style="--mv-spacer-height:${topHeight}px"><td colspan="5"></td></tr>
            ${records.slice(start, end).map(rowHtml).join('')}
            <tr class="mv-virtual-spacer" aria-hidden="true" style="--mv-spacer-height:${bottomHeight}px"><td colspan="5"></td></tr>
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

    const applyFilters = async ({ updateHistory = true } = {}) => {
        if (!(filterForm instanceof HTMLFormElement)) return;

        filterRequest?.abort();
        filterRequest = new AbortController();
        const params = new URLSearchParams(new FormData(filterForm));
        [...params.entries()].forEach(([key, value]) => {
            if (!String(value).trim()) params.delete(key);
        });
        const url = new URL(filterForm.action || window.location.href, window.location.origin);
        url.search = params.toString();

        setSearchFeedback(filterForm.dataset.loadingLabel || '{{ __('Finding matching students...') }}', true);
        viewport.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                signal: filterRequest.signal,
            });
            if (!response.ok) throw new Error('Movement filter request failed');

            const payload = await response.json();
            records.splice(0, records.length, ...(Array.isArray(payload.data) ? payload.data : []));
            recordIds.clear();
            records.forEach((record) => recordIds.add(Number(record.id)));
            nextCursor = payload.next_cursor || null;
            hasMore = Boolean(payload.has_more && nextCursor);
            viewport.dataset.endpoint = url.toString();
            viewport.scrollTop = 0;
            render();
            setStatus(hasMore ? viewport.dataset.readyLabel : viewport.dataset.completeLabel);
            setSearchFeedback(records.length === 1
                ? '{{ __('1 matching movement loaded') }}'
                : `{{ __(':count matching movements loaded', ['count' => '__COUNT__']) }}`.replace('__COUNT__', records.length));

            if (exportLink instanceof HTMLAnchorElement) {
                const exportUrl = new URL(exportLink.href, window.location.origin);
                exportUrl.search = params.toString();
                exportLink.href = exportUrl.toString();
            }
            if (updateHistory) window.history.replaceState({}, '', `${url.pathname}${url.search}`);
        } catch (error) {
            if (error?.name !== 'AbortError') {
                setSearchFeedback('{{ __('Search could not be completed. Try again.') }}');
            }
        } finally {
            viewport.removeAttribute('aria-busy');
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
    filterForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        applyFilters();
    });
    searchInput?.addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(() => applyFilters(), 350);
    });
    clearButton?.addEventListener('click', () => {
        if (!(searchInput instanceof HTMLInputElement)) return;
        searchInput.value = '';
        searchInput.focus();
        applyFilters();
    });
    resetButton?.addEventListener('click', () => {
        if (!(filterForm instanceof HTMLFormElement)) return;
        filterForm.reset();
        filterForm.querySelectorAll('input:not([type="search"]), select').forEach((field) => {
            if (field instanceof HTMLInputElement) field.value = '';
            if (field instanceof HTMLSelectElement) field.selectedIndex = 0;
        });
        applyFilters();
    });

    viewport.dataset.virtualReady = 'true';
    if (fallbackPagination) fallbackPagination.hidden = true;
    render();
    setStatus(hasMore ? viewport.dataset.readyLabel : viewport.dataset.completeLabel);
    maybeLoadMore();
})();
</script>
@endpush
