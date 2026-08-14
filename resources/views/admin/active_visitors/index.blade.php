@extends('layouts.app')

@section('title', 'Active Visitors')

@push('styles')
<style>
    .visitors { max-width: 1180px; margin: 0 auto; }
    .visitors-head { display:flex; justify-content:space-between; gap:1rem; align-items:end; margin-bottom:1.25rem; }
    .visitors-head h1 { margin:0; color:var(--text); font-size:clamp(1.7rem,3vw,2.35rem); }
    .visitors-head p { margin:.4rem 0 0; color:var(--text-muted); }
    .visitor-stats { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:1rem; margin-bottom:1rem; }
    .visitor-stat,.visitor-card { border:1px solid var(--glass-line); background:var(--glass-bg-strong); box-shadow:var(--glass-shadow); border-radius:20px; }
    .visitor-stat { padding:1rem 1.1rem; }
    .visitor-stat span { display:block; color:var(--text-muted); font-size:.78rem; font-weight:800; letter-spacing:.07em; text-transform:uppercase; }
    .visitor-stat strong { display:block; margin-top:.35rem; color:var(--text); font-size:1.8rem; }
    .visitor-card { overflow:hidden; }
    .visitor-tools { padding:1rem; border-bottom:1px solid var(--glass-line); }
    .visitor-tools input { width:min(360px,100%); padding:.75rem .9rem; border:1px solid var(--border); border-radius:12px; background:var(--surface); color:var(--text); font:inherit; }
    .visitor-table { width:100%; border-collapse:collapse; }
    .visitor-table th,.visitor-table td { padding:.9rem 1rem; text-align:left; border-bottom:1px solid var(--glass-line); vertical-align:top; }
    .visitor-table th { color:var(--text-muted); font-size:.73rem; letter-spacing:.06em; text-transform:uppercase; }
    .visitor-table td { color:var(--text); }
    .visitor-table small { display:block; margin-top:.22rem; color:var(--text-muted); overflow-wrap:anywhere; }
    .visitor-badge { display:inline-flex; padding:.25rem .55rem; border-radius:999px; background:color-mix(in srgb,var(--primary) 14%,transparent); color:var(--primary); font-size:.75rem; font-weight:800; text-transform:capitalize; }
    .visitor-empty { padding:2.5rem; text-align:center; color:var(--text-muted); }
    .visitor-pages { padding:1rem; }
    @media (max-width:720px) { .visitors-head { align-items:start; flex-direction:column; } .visitor-stats { grid-template-columns:1fr; } .visitor-card { overflow-x:auto; } .visitor-table { min-width:720px; } }
</style>
@endpush

@section('content')
<div class="visitors">
    <div class="visitors-head">
        <div>
            <h1>{{ __('Active Visitors') }}</h1>
            <p>Authenticated accounts active within the last {{ config('session.lifetime', 120) }} minutes.</p>
        </div>
    </div>
    <div class="visitor-stats">
        <div class="visitor-stat"><span>{{ __('Active now') }}</span><strong>{{ $activeCount }}</strong></div>
        <div class="visitor-stat"><span>{{ __('Students') }}</span><strong>{{ $studentCount }}</strong></div>
        <div class="visitor-stat"><span>{{ __('Admins & staff') }}</span><strong>{{ $adminCount }}</strong></div>
    </div>
    <div class="visitor-card">
        <form class="visitor-tools" method="GET" action="{{ route('admin.active-visitors.index') }}">
            <input name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Search name or IP address') }}" aria-label="{{ __('Search active visitors') }}">
        </form>
        @if($sessions->isEmpty())
            <div class="visitor-empty">{{ __('No authenticated visitors are currently active.') }}</div>
        @else
            <table class="visitor-table">
                <thead><tr><th>{{ __('Account') }}</th><th>{{ __('Role') }}</th><th>{{ __('IP address') }}</th><th>{{ __('Device') }}</th><th>{{ __('Logged in') }}</th><th>{{ __('Last seen') }}</th></tr></thead>
                <tbody>
                    @foreach($sessions as $session)
                        <tr>
                            <td><strong>{{ $session->account_name ?? 'Deleted account' }}</strong></td>
                            <td><span class="visitor-badge">{{ $session->owner_type }}</span></td>
                            <td>{{ $session->ip_address ?? 'Unavailable' }}</td>
                            <td><small>{{ $session->user_agent ?: 'Unavailable' }}</small></td>
                            <td>{{ \Carbon\Carbon::parse($session->authenticated_at)->format('d M Y, H:i') }}</td>
                            <td>{{ \Carbon\Carbon::parse($session->last_seen_at)->diffForHumans() }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="visitor-pages">{{ $sessions->links() }}</div>
        @endif
    </div>
</div>
@endsection
