@extends('layouts.app')

@section('title', __('Movement Violations'))

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ __('Movement Violations') }}</h2>
@endsection

@push('styles')
<style>
    .mv-violation-note { color:var(--text-muted); font-size:.78rem; margin-top:.15rem; }
    .mv-violation-empty { padding:2rem 1rem; text-align:center; color:var(--text-muted); }
</style>
@endpush

@section('content')
<div class="ui-shell">
    <div class="ui-hero">
        <h3>{{ __('Late Return Violations') }}</h3>
        <p>{{ __('Focus on curfew breaches so discipline review can move faster and with cleaner supporting data.') }}</p>
    </div>

    <section class="ui-card" data-filter-sheet data-filter-title="{{ __('Violation filters') }}">
        <div class="ui-card-head">
            <div>
                <strong>{{ __('Filter Violations') }}</strong>
                <div class="mv-violation-note">{{ __('Search by student or narrow the date range for late-return cases.') }}</div>
            </div>
            <a class="ui-btn" href="{{ route('admin.movements.index') }}">{{ __('All Records') }}</a>
        </div>
        <div class="ui-card-body">
            <form method="GET" class="ui-actions">
                <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Student / matric / programme') }}">
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                <button class="ui-btn primary" type="submit">{{ __('Filter') }}</button>
            </form>
        </div>
    </section>

    <section class="ui-card">
        <div style="overflow-x:auto;">
            <table class="ui-table">
                <thead><tr><th>{{ __('Student') }}</th><th>{{ __('Check-Out') }}</th><th>{{ __('Return') }}</th><th>{{ __('Late Duration') }}</th><th>{{ __('Rule') }}</th></tr></thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td><strong>{{ $record->student_name }}</strong><br><span class="muted">{{ $record->matric_no }}</span></td>
                            <td>{{ \Illuminate\Support\Carbon::parse($record->checkout_at)->format('d M Y, h:i A') }}</td>
                            <td>{{ $record->return_at ? \Illuminate\Support\Carbon::parse($record->return_at)->format('d M Y, h:i A') : '-' }}</td>
                            <td>{{ (int) $record->late_minutes }} {{ __('minutes') }}</td>
                            <td><span class="ui-status status-rejected">{{ __('Late Return') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="mv-violation-empty">{{ __('No late return violations found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ui-card-body mv-pagination-wrap">
            {{ $records->onEachSide(1)->links('vendor.pagination.studentedge') }}
        </div>
    </section>
</div>
@endsection
