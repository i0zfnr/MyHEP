@extends('layouts.app')

@section('title', __('Movement Violations'))

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ __('Movement Violations') }}</h2>
@endsection

@push('styles')
<style>
    .mv-violation-note { color:var(--text-muted); font-size:.78rem; margin-top:.15rem; }
    .mv-violation-empty { padding:2rem 1rem; text-align:center; color:var(--text-muted); }
    .mv-student-card { display:flex; align-items:center; gap:.7rem; min-width:220px; }
    .mv-avatar { width:48px; height:48px; border-radius:10px; object-fit:cover; border:1px solid rgba(226,209,192,.24); background:rgba(255,255,255,.06); flex:0 0 48px; }
    .mv-avatar-empty { display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-weight:800; }
    .mv-mini-btn { display:inline-flex; align-items:center; margin-top:.35rem; padding:.28rem .5rem; border-radius:8px; border:1px solid rgba(226,209,192,.18); color:var(--text); text-decoration:none; font-size:.7rem; font-weight:750; }
    .mv-explanation { max-width:320px; color:var(--text-muted); line-height:1.45; }
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
                <thead><tr><th>{{ __('Student') }}</th><th>{{ __('Check-Out') }}</th><th>{{ __('Return') }}</th><th>{{ __('Late Duration') }}</th><th>{{ __('Explanation') }}</th><th>{{ __('Rule') }}</th></tr></thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td>
                                <div class="mv-student-card">
                                    @if(!empty($record->student_photo))
                                        <img class="mv-avatar" src="{{ asset('storage/' . $record->student_photo) }}" alt="{{ __('Profile photo') }}">
                                    @else
                                        <div class="mv-avatar mv-avatar-empty">{{ strtoupper(substr($record->student_name ?? 'S', 0, 1)) }}</div>
                                    @endif
                                    <div>
                                        <strong>{{ $record->student_name }}</strong><br>
                                        <span class="muted">{{ $record->matric_no }}</span><br>
                                        <a class="mv-mini-btn" href="{{ route('admin.students.show', $record->student_id) }}">{{ __('View Profile') }}</a>
                                    </div>
                                </div>
                            </td>
                            <td>{{ \Illuminate\Support\Carbon::parse($record->checkout_at)->format('d M Y, h:i A') }}</td>
                            <td>{{ $record->return_at ? \Illuminate\Support\Carbon::parse($record->return_at)->format('d M Y, h:i A') : '-' }}</td>
                            <td>{{ (int) $record->late_minutes }} {{ __('minutes') }}</td>
                            <td><div class="mv-explanation">{{ $record->late_explanation ?: '-' }}</div></td>
                            <td><span class="ui-status status-rejected">{{ __('Late Return') }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="mv-violation-empty">{{ __('No late return violations found.') }}</td></tr>
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
