@extends('layouts.app')

@section('title', __('Active Visitors'))
@section('header')<h2>{{ __('Active Visitors') }}</h2>@endsection

@push('styles')
<style>
    .visitors { max-width: 1180px; margin: 0 auto; }
    .visitors-head { display:flex; justify-content:space-between; gap:1rem; align-items:flex-end; margin-bottom:1.25rem; flex-wrap:wrap; }
    .visitors-head h1 { margin:0; color:var(--text); font-size:clamp(1.7rem,3vw,2.35rem); }
    .visitors-head p { margin:.4rem 0 0; color:var(--text-muted); }
    .visitors-actions { display:flex; align-items:center; gap:.75rem; }
    .btn-clear-logs {
        display:inline-flex; align-items:center; gap:.5rem;
        padding:.65rem 1.15rem; border-radius:12px;
        background:rgba(239,68,68,0.12); color:#dc2626;
        border:1px solid rgba(239,68,68,0.25);
        font:inherit; font-size:.82rem; font-weight:750;
        cursor:pointer; transition:all .2s ease;
    }
    .btn-clear-logs:hover {
        background:#dc2626; color:#ffffff; border-color:#dc2626;
        box-shadow:0 4px 12px rgba(220,38,38,0.25);
    }
    body[data-theme="dark"] .btn-clear-logs {
        background:rgba(239,68,68,0.18); color:#f87171; border-color:rgba(239,68,68,0.3);
    }
    body[data-theme="dark"] .btn-clear-logs:hover {
        background:#ef4444; color:#17120c;
    }
    .visitor-stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1rem; margin-bottom:1rem; }
    .visitor-stat,.visitor-card { border:1px solid var(--glass-line); background:var(--glass-bg-strong); box-shadow:var(--glass-shadow); border-radius:20px; }
    .visitor-stat { padding:1.1rem 1.25rem; }
    .visitor-stat span { display:block; color:var(--text-muted); font-size:.78rem; font-weight:800; letter-spacing:.07em; text-transform:uppercase; }
    .visitor-stat strong { display:block; margin-top:.35rem; color:var(--text); font-size:1.8rem; }
    .visitor-card { overflow:hidden; }
    .visitor-tools { padding:1rem 1.25rem; border-bottom:1px solid var(--glass-line); display:flex; justify-content:space-between; align-items:center; gap:1rem; flex-wrap:wrap; }
    .visitor-tools form { display:flex; flex:1; max-width:420px; }
    .visitor-tools input { width:100%; padding:.75rem .95rem; border:1px solid var(--border); border-radius:12px; background:var(--surface); color:var(--text); font:inherit; }
    .visitor-table { width:100%; border-collapse:collapse; }
    .visitor-table th,.visitor-table td { padding:.9rem 1rem; text-align:left; border-bottom:1px solid var(--glass-line); vertical-align:middle; }
    .visitor-table th { color:var(--text-muted); font-size:.73rem; letter-spacing:.06em; text-transform:uppercase; }
    .visitor-table td { color:var(--text); }
    .visitor-table small { display:block; margin-top:.22rem; color:var(--text-muted); overflow-wrap:anywhere; }
    .visitor-badge { display:inline-flex; padding:.25rem .55rem; border-radius:999px; background:color-mix(in srgb,var(--primary) 14%,transparent); color:var(--primary); font-size:.75rem; font-weight:800; text-transform:capitalize; }
    .btn-delete-row {
        display:inline-flex; align-items:center; justify-content:center;
        width:32px; height:32px; border-radius:8px;
        border:1px solid transparent; background:transparent;
        color:var(--text-muted); cursor:pointer; transition:all .15s ease;
    }
    .btn-delete-row:hover {
        background:rgba(239,68,68,0.12); color:#dc2626; border-color:rgba(239,68,68,0.2);
    }
    .visitor-empty { padding:3rem; text-align:center; color:var(--text-muted); font-size:.95rem; }
    .visitor-pages { padding:1rem; }
    @media (max-width:720px) { .visitors-head { align-items:start; flex-direction:column; } .visitor-stats { grid-template-columns:1fr; } .visitor-card { overflow-x:auto; } .visitor-table { min-width:720px; } }
</style>
@endpush

@section('content')
<div class="visitors">
    <div class="visitors-head">
        <div>
            <h1>{{ __('Active Visitors') }}</h1>
            <p>{{ __('Authenticated accounts active within the last :minutes minutes.', ['minutes' => config('session.lifetime', 120)]) }}</p>
        </div>
        <div class="visitors-actions">
            <form method="POST" action="{{ route('admin.active-visitors.clear') }}" onsubmit="return confirm('{{ __('Are you sure you want to clear all active visitor logs?') }}');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-clear-logs">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                    <span>{{ __('Clear Logs') }}</span>
                </button>
            </form>
        </div>
    </div>

    <div class="visitor-stats">
        <div class="visitor-stat"><span>{{ __('Active now') }}</span><strong>{{ $activeCount }}</strong></div>
        <div class="visitor-stat"><span>{{ __('Students') }}</span><strong>{{ $studentCount }}</strong></div>
        <div class="visitor-stat"><span>{{ __('Admins & staff') }}</span><strong>{{ $adminCount }}</strong></div>
    </div>

    <div class="visitor-card">
        <div class="visitor-tools">
            <form method="GET" action="{{ route('admin.active-visitors.index') }}">
                <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Search name or IP address') }}" aria-label="{{ __('Search active visitors') }}">
            </form>
        </div>
        @if($sessions->isEmpty())
            <div class="visitor-empty">{{ __('No authenticated visitors are currently active.') }}</div>
        @else
            <table class="visitor-table">
                <thead>
                    <tr>
                        <th>{{ __('Account') }}</th>
                        <th>{{ __('Role') }}</th>
                        <th>{{ __('IP address') }}</th>
                        <th>{{ __('Device') }}</th>
                        <th>{{ __('Logged in') }}</th>
                        <th>{{ __('Last seen') }}</th>
                        <th style="text-align:right;">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sessions as $session)
                        <tr>
                            <td><strong>{{ $session->account_name ?? __('Deleted account') }}</strong></td>
                            <td><span class="visitor-badge">{{ $session->owner_type }}</span></td>
                            <td>{{ $session->ip_address ?? __('Unavailable') }}</td>
                            <td><small>{{ $session->user_agent ?: __('Unavailable') }}</small></td>
                            <td>{{ \Carbon\Carbon::parse($session->authenticated_at)->format('d M Y, H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($session->last_seen_at)->diffForHumans() }}</td>
                            <td style="text-align:right;">
                                <form method="POST" action="{{ route('admin.active-visitors.destroy', $session->id) }}" onsubmit="return confirm('{{ __('Delete this visitor record?') }}');" style="display:inline;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-delete-row" title="{{ __('Delete record') }}">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="visitor-pages">{{ $sessions->links() }}</div>
        @endif
    </div>
</div>
@endsection
