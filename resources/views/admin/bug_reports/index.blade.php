@extends('layouts.app')

@section('title', __('bug_reports.admin_page_title'))

@push('styles')
<style>
    .bugs-wrap { max-width: 1160px; margin: 0 auto; display: grid; gap: 1rem; }
    .bugs-flash { padding: .9rem 1rem; border-radius: 16px; font-size: .9rem; font-weight: 700; }
    .bugs-flash.ok { background: rgba(34, 197, 94, .14); border: 1px solid rgba(134, 239, 172, .35); color: #d1fae5; }
    .bugs-flash.err { background: rgba(239, 68, 68, .14); border: 1px solid rgba(252, 165, 165, .35); color: #fecaca; }
    .bugs-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: .9rem; }
    .bugs-stat {
        position: relative;
        overflow: hidden;
        border-radius: 22px;
        border: 1px solid rgba(226, 209, 192, .16);
        background:
            radial-gradient(circle at top right, rgba(215, 191, 168, .12), transparent 32%),
            linear-gradient(180deg, rgba(35, 31, 27, .92), rgba(20, 18, 16, .94));
        box-shadow: 0 24px 52px rgba(0,0,0,.32), inset 0 1px 0 rgba(255,255,255,.05);
        padding: 1rem 1.1rem 1.15rem;
    }
    .bugs-stat::after {
        content: '';
        position: absolute;
        inset: auto 0 0 0;
        height: 3px;
        background: linear-gradient(90deg, rgba(95, 190, 145, .9), rgba(215, 191, 168, .65));
        opacity: .78;
    }
    .bugs-stat-label { color: #a99888; font-size: .74rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
    .bugs-stat-value { margin-top: .45rem; font-size: 2rem; font-weight: 900; color: #f7efe8; }
    .bugs-card {
        border-radius: 26px;
        border: 1px solid rgba(226, 209, 192, .14);
        background:
            linear-gradient(180deg, rgba(29, 26, 23, .94), rgba(17, 15, 13, .96));
        box-shadow: 0 28px 60px rgba(0,0,0,.34), inset 0 1px 0 rgba(255,255,255,.05);
        overflow: hidden;
    }
    .bugs-card-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.15rem 1.3rem;
        border-bottom: 1px solid rgba(226, 209, 192, .12);
        background: linear-gradient(180deg, rgba(255,255,255,.05), rgba(255,255,255,.02));
    }
    .bugs-card-head h3 { margin: 0; font-size: 1.4rem; color: #f7efe8; letter-spacing: -.02em; }
    .bugs-actions { display: flex; gap: .65rem; flex-wrap: wrap; }
    .bugs-btn,
    .bugs-select,
    .bugs-input,
    .bugs-textarea {
        border: 1px solid rgba(226, 209, 192, .18);
        border-radius: 14px;
        background: rgba(255,255,255,.04);
        color: #f7efe8;
        font: inherit;
    }
    .bugs-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .5rem;
        padding: .82rem 1.05rem;
        text-decoration: none;
        font-weight: 800;
        cursor: pointer;
        transition: transform 180ms ease, border-color 180ms ease, background-color 180ms ease, box-shadow 180ms ease;
    }
    .bugs-btn:hover {
        transform: translateY(-1px);
        border-color: rgba(226, 209, 192, .28);
        background: rgba(255,255,255,.08);
        box-shadow: 0 12px 24px rgba(0,0,0,.18);
    }
    .bugs-btn.primary {
        background: linear-gradient(135deg, #c9ae95 0%, #ecd7c3 100%);
        color: #2a1d15;
        border-color: rgba(215, 191, 168, .55);
        box-shadow: 0 12px 28px rgba(201, 174, 149, .22);
    }
    .bugs-btn.primary:hover {
        background: linear-gradient(135deg, #d7bfa8 0%, #f3e2d2 100%);
        border-color: rgba(236, 215, 195, .8);
    }
    .bugs-btn.danger {
        background: rgba(239, 68, 68, .12);
        border-color: rgba(248, 113, 113, .28);
        color: #fecaca;
        box-shadow: none;
    }
    .bugs-btn.danger:hover {
        background: rgba(239, 68, 68, .18);
        border-color: rgba(252, 165, 165, .42);
        color: #ffe4e6;
    }
    .bugs-filters {
        display: grid;
        grid-template-columns: minmax(0, 1.6fr) 220px auto auto;
        gap: .8rem;
        padding: 1rem 1.3rem;
        border-bottom: 1px solid rgba(226, 209, 192, .12);
    }
    .bugs-input,
    .bugs-select,
    .bugs-textarea {
        width: 100%;
        padding: .85rem .95rem;
    }
    .bugs-input::placeholder,
    .bugs-textarea::placeholder { color: #8e8175; }
    .bugs-input:focus,
    .bugs-select:focus,
    .bugs-textarea:focus {
        outline: none;
        border-color: rgba(215, 191, 168, .5);
        box-shadow: 0 0 0 4px rgba(215, 191, 168, .12);
        background: rgba(255,255,255,.06);
    }
    .bugs-textarea { min-height: 112px; resize: vertical; }
    .bugs-list { display: grid; gap: 1rem; padding: 1rem 1.3rem 1.3rem; }
    .bug-item {
        border-radius: 20px;
        border: 1px solid rgba(226, 209, 192, .12);
        background:
            radial-gradient(circle at top right, rgba(95, 190, 145, .06), transparent 28%),
            linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.025));
        overflow: hidden;
    }
    .bug-item-head {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1rem .9rem;
        border-bottom: 1px solid rgba(226, 209, 192, .1);
    }
    .bug-title { margin: 0; font-size: 1.05rem; color: #f7efe8; }
    .bug-meta { display: flex; flex-wrap: wrap; gap: .55rem; margin-top: .55rem; }
    .bug-pill {
        display: inline-flex;
        align-items: center;
        padding: .32rem .62rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .06em;
        text-transform: uppercase;
        border: 1px solid transparent;
    }
    .bug-pill.category { background: rgba(215, 191, 168, .12); border-color: rgba(215, 191, 168, .18); color: #e4cfbb; }
    .bug-pill.status-new { background: rgba(96, 165, 250, .14); border-color: rgba(147, 197, 253, .2); color: #bfdbfe; }
    .bug-pill.status-in_progress { background: rgba(245, 158, 11, .14); border-color: rgba(253, 186, 116, .2); color: #fdba74; }
    .bug-pill.status-resolved { background: rgba(34, 197, 94, .14); border-color: rgba(134, 239, 172, .2); color: #bbf7d0; }
    .bug-pill.status-closed { background: rgba(148, 163, 184, .14); border-color: rgba(203, 213, 225, .2); color: #cbd5e1; }
    .bug-date { color: #a99888; font-size: .85rem; white-space: nowrap; }
    .bug-item-body {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(280px, .85fr);
        gap: 1rem;
        padding: 1rem;
    }
    .bug-copy { color: #cdbeb0; line-height: 1.72; }
    .bug-copy p { margin: 0 0 .9rem; }
    .bug-copy strong { color: #f7efe8; }
    .bug-kv { display: grid; gap: .45rem; margin-bottom: 1rem; }
    .bug-link { color: #e4cfbb; font-weight: 700; word-break: break-all; }
    .bug-shot {
        margin-top: .95rem;
        border-radius: 14px;
        border: 1px solid rgba(226, 209, 192, .14);
        overflow: hidden;
        background: rgba(255,255,255,.03);
    }
    .bug-shot img { display: block; width: 100%; height: auto; }
    .bug-side form { display: grid; gap: .8rem; }
    .bug-side-actions { display: flex; gap: .75rem; flex-wrap: wrap; align-items: center; }
    .bug-side-actions form { margin: 0; }
    .bug-notes-meta {
        color: #a99888;
        font-size: .8rem;
        line-height: 1.6;
    }
    .bug-empty {
        padding: 1.4rem;
        text-align: center;
        color: #9e8f81;
        font-weight: 700;
    }
    @media (max-width: 980px) {
        .bugs-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .bugs-filters { grid-template-columns: 1fr; }
        .bug-item-body { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .bugs-stats { grid-template-columns: 1fr; }
        .bugs-card-head,
        .bug-item-head { flex-direction: column; align-items: flex-start; }
    }
</style>
@endpush

@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;color:var(--text,#f7efe8);">{{ __('bug_reports.admin_heading') }}</h2>
@endsection

@section('content')
<div class="bugs-wrap">
    @if(session('success'))
        <div class="bugs-flash ok">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="bugs-flash err">{{ $errors->first() }}</div>
    @endif

    <div class="bugs-stats">
        <div class="bugs-stat">
            <div class="bugs-stat-label">{{ __('bug_reports.status_new') }}</div>
            <div class="bugs-stat-value">{{ $stats['new'] }}</div>
        </div>
        <div class="bugs-stat">
            <div class="bugs-stat-label">{{ __('bug_reports.status_in_progress') }}</div>
            <div class="bugs-stat-value">{{ $stats['in_progress'] }}</div>
        </div>
        <div class="bugs-stat">
            <div class="bugs-stat-label">{{ __('bug_reports.status_resolved') }}</div>
            <div class="bugs-stat-value">{{ $stats['resolved'] }}</div>
        </div>
        <div class="bugs-stat">
            <div class="bugs-stat-label">{{ __('bug_reports.status_closed') }}</div>
            <div class="bugs-stat-value">{{ $stats['closed'] }}</div>
        </div>
    </div>

    <section class="bugs-card">
        <div class="bugs-card-head">
            <h3>{{ __('bug_reports.admin_queue_title') }}</h3>
            <div class="bugs-actions">
                <a href="{{ route('admin.dashboard') }}" class="bugs-btn">{{ __('Dashboard') }}</a>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.bug-reports.index') }}" class="bugs-filters">
            <input class="bugs-input" type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('bug_reports.search_placeholder') }}">
            <select class="bugs-select" name="status">
                <option value="all">{{ __('bug_reports.status_all') }}</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? 'all') === $status)>{{ __('bug_reports.status_' . $status) }}</option>
                @endforeach
            </select>
            <button type="submit" class="bugs-btn">{{ __('Filter') }}</button>
            <a href="{{ route('admin.bug-reports.index') }}" class="bugs-btn">{{ __('Reset') }}</a>
        </form>

        <div class="bugs-list">
            @forelse($bugReports as $bugReport)
                <article class="bug-item">
                    <div class="bug-item-head">
                        <div>
                            <h4 class="bug-title">{{ $bugReport->subject }}</h4>
                            <div class="bug-meta">
                                <span class="bug-pill category">{{ __('bug_reports.category_' . $bugReport->category) }}</span>
                                <span class="bug-pill status-{{ $bugReport->status }}">{{ __('bug_reports.status_' . $bugReport->status) }}</span>
                                <span class="bug-pill category">#{{ $bugReport->id }}</span>
                            </div>
                        </div>
                        <div class="bug-date">{{ \Illuminate\Support\Carbon::parse($bugReport->created_at)->format('d M Y, h:i A') }}</div>
                    </div>

                    <div class="bug-item-body">
                        <div class="bug-copy">
                            <div class="bug-kv">
                                <div><strong>{{ __('bug_reports.form_name') }}:</strong> {{ $bugReport->reporter_name }}</div>
                                <div><strong>{{ __('bug_reports.form_email') }}:</strong> {{ $bugReport->reporter_email }}</div>
                                @if($bugReport->page_url)
                                    <div>
                                        <strong>{{ __('bug_reports.form_page_url') }}:</strong>
                                        <a href="{{ $bugReport->page_url }}" class="bug-link" target="_blank" rel="noopener">{{ $bugReport->page_url }}</a>
                                    </div>
                                @endif
                            </div>

                            <p><strong>{{ __('bug_reports.form_description') }}:</strong></p>
                            <p>{{ $bugReport->description }}</p>

                            @if($bugReport->screenshot_path)
                                <div class="bug-shot">
                                    <img src="{{ asset('storage/' . $bugReport->screenshot_path) }}" alt="{{ $bugReport->subject }}">
                                </div>
                            @endif
                        </div>

                        <div class="bug-side">
                            <form method="POST" action="{{ route('admin.bug-reports.update', $bugReport->id) }}">
                                @csrf
                                @method('PUT')

                                <div>
                                    <label for="status-{{ $bugReport->id }}" style="display:block;margin-bottom:.45rem;font-size:.78rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#a99888;">{{ __('bug_reports.form_status') }}</label>
                                    <select class="bugs-select" id="status-{{ $bugReport->id }}" name="status">
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}" @selected($bugReport->status === $status)>{{ __('bug_reports.status_' . $status) }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label for="admin_notes-{{ $bugReport->id }}" style="display:block;margin-bottom:.45rem;font-size:.78rem;font-weight:800;letter-spacing:.06em;text-transform:uppercase;color:#a99888;">{{ __('bug_reports.form_admin_notes') }}</label>
                                    <textarea class="bugs-textarea" id="admin_notes-{{ $bugReport->id }}" name="admin_notes">{{ $bugReport->admin_notes }}</textarea>
                                </div>

                                <div class="bug-side-actions">
                                    <button type="submit" class="bugs-btn primary">{{ __('bug_reports.save_update') }}</button>
                            </form>

                            <form method="POST" action="{{ route('admin.bug-reports.destroy', $bugReport->id) }}"
                                data-confirm-title="{{ __('bug_reports.delete_title') }}"
                                data-confirm-message="{{ __('bug_reports.delete_message') }}"
                                data-confirm-action="{{ __('Delete') }}"
                                data-confirm-tone="danger">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bugs-btn danger">{{ __('bug_reports.delete_button') }}</button>
                            </form>
                                </div>

                            @if($bugReport->resolved_at)
                                <div class="bug-notes-meta">
                                    {{ __('bug_reports.resolved_meta', [
                                        'name' => $bugReport->resolved_by_name ?: '-',
                                        'time' => \Illuminate\Support\Carbon::parse($bugReport->resolved_at)->format('d M Y, h:i A'),
                                    ]) }}
                                </div>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div class="bug-empty">{{ __('bug_reports.empty_state') }}</div>
            @endforelse
        </div>
    </section>

    <div>{{ $bugReports->links() }}</div>
</div>
@endsection
