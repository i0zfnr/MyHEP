@extends('layouts.app')

@section('title', __('Active Visitors'))
@section('header')<h2>{{ __('Active Visitors') }}</h2>@endsection



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
