@extends('layouts.app')

@section('title', __('Kebajikan Pelajar'))



@section('header')
    <h2 style="margin:0;font-size:1.1rem;font-weight:700;">{{ __('Kebajikan Pelajar') }}</h2>
@endsection

@section('content')
<div class="welfare-shell">
    <section class="welfare-hero">
        <div>
            <span class="welfare-eyebrow">{{ __('WELFARE RECORDS') }}</span>
            <h1>{{ __('Kebajikan Pelajar') }}</h1>
            <p>{{ __('Track students receiving welfare assistance and review their B40, OKU, guardian and household information.') }}</p>
        </div>
    </section>

    <section class="welfare-stats">
        <article class="welfare-stat"><span>{{ __('Total Records') }}</span><strong>{{ number_format($summary['total']) }}</strong></article>
        <article class="welfare-stat"><span>{{ __('Confirmed') }}</span><strong>{{ number_format($summary['confirmed']) }}</strong></article>
        <article class="welfare-stat"><span>{{ __('Pending') }}</span><strong>{{ number_format($summary['pending']) }}</strong></article>
        <article class="welfare-stat"><span>{{ __('Confirmed Amount') }}</span><strong>RM {{ number_format($summary['total_amount'], 2) }}</strong></article>
    </section>

    <section class="welfare-card">
        <form method="GET" action="{{ route('admin.welfare.index') }}" class="welfare-filters">
            <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Search name, matric number or program') }}">
            <select name="status">
                <option value="">{{ __('All statuses') }}</option>
                @foreach(['pending', 'confirmed', 'rejected'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ __(ucfirst($status)) }}</option>
                @endforeach
            </select>
            <select name="group">
                <option value="all">{{ __('All groups') }}</option>
                <option value="oku" @selected(($filters['group'] ?? 'all') === 'oku')>{{ __('OKU Students') }}</option>
                <option value="b40" @selected(($filters['group'] ?? 'all') === 'b40')>{{ __('B40 Students') }}</option>
            </select>
            <button class="ui-btn ui-btn-primary" type="submit">{{ __('Filter') }}</button>
            <a class="ui-btn" href="{{ route('admin.welfare.index') }}">{{ __('Reset') }}</a>
        </form>

        <div class="welfare-table-wrap" data-lenis-prevent>
            <table class="welfare-table">
                <thead><tr><th>{{ __('Student') }}</th><th>{{ __('Program') }}</th><th>{{ __('Welfare Group') }}</th><th>{{ __('Provider') }}</th><th>{{ __('Amount') }}</th><th>{{ __('Household Income') }}</th><th>{{ __('Status') }}</th><th>{{ __('Action') }}</th></tr></thead>
                <tbody>
                @forelse($records as $record)
                    <tr>
                        <td><strong>{{ $record->full_name }}</strong><br><small>{{ $record->matric_no ?: '-' }}</small></td>
                        <td>{{ $record->program }}</td>
                        <td>{{ $record->oku_status === 'yes' ? __('OKU').' · '.($record->oku_category ?: __('Not specified')) : ((float) $record->family_income <= 5249 ? 'B40' : '-') }}</td>
                        <td>{{ $record->provider_name ?: '-' }}</td>
                        <td>{{ $record->amount !== null ? 'RM '.number_format((float) $record->amount, 2) : '-' }}</td>
                        <td>{{ $record->family_income !== null ? 'RM '.number_format((float) $record->family_income, 2) : '-' }}</td>
                        <td><span class="ui-badge">{{ __(ucfirst($record->status)) }}</span></td>
                        <td><a class="ui-btn" href="{{ route('admin.students.show', $record->student_id) }}">{{ __('View Student') }}</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8"><div class="ui-empty-state"><strong>{{ __('No welfare records found') }}</strong><span>{{ __('Welfare assistance records will appear here.') }}</span></div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="welfare-pagination">{{ $records->onEachSide(1)->links('vendor.pagination.myhep') }}</div>
    </section>
</div>
@endsection
