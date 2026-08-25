@extends('layouts.app')

@section('title', __('bug_reports.admin_page_title'))



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

        <form method="GET" action="{{ route('admin.bug-reports.index') }}" class="bugs-filters" data-filter-sheet data-filter-title="{{ __('Bug report filters') }}">
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
                                @php($emailState = $bugReport->email_notification_status ?? 'pending')
                                <span class="bug-email-state {{ $emailState }}">Email: {{ ucfirst($emailState) }}</span>
                            </div>
                            @if($emailState === 'failed' && $bugReport->email_notification_error)
                                <div class="bug-email-error"><strong>{{ __('Email delivery error:') }}</strong> {{ $bugReport->email_notification_error }}</div>
                            @endif
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
                                    <a href="{{ asset('storage/' . $bugReport->screenshot_path) }}" target="_blank" data-media-viewer data-media-title="{{ __('Bug report screenshot') }}">
                                        <img src="{{ asset('storage/' . $bugReport->screenshot_path) }}" alt="{{ $bugReport->subject }}">
                                    </a>
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
