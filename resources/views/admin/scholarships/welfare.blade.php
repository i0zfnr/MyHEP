@extends('layouts.app')

@section('title', __('Kebajikan Pelajar'))

@push('styles')
<style>
    .welfare-shell { display:grid; gap:16px; }
    .welfare-hero, .welfare-card, .welfare-stat { background:var(--surface, #fff); border:1px solid var(--border-soft, #eadfce); border-radius:16px; box-shadow:0 12px 30px rgba(42,31,20,.08); }
    .welfare-hero { padding:24px; }
    .welfare-hero h1 { margin:5px 0 7px; font-size:clamp(1.55rem,3vw,2.15rem); }
    .welfare-hero p { margin:0; max-width:760px; color:var(--text-muted, #75695f); }
    .welfare-eyebrow { color:var(--role-accent, #2f7896); font-size:.75rem; font-weight:800; letter-spacing:.1em; }
    .welfare-stats { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:12px; }
    .welfare-stat { padding:18px; }
    .welfare-stat span { display:block; color:var(--text-muted, #75695f); font-size:.75rem; font-weight:700; text-transform:uppercase; }
    .welfare-stat strong { display:block; margin-top:8px; font-size:1.55rem; }
    .welfare-card { overflow:hidden; }
    .welfare-filters { display:grid; grid-template-columns:minmax(220px,1fr) 180px 180px auto auto; gap:10px; padding:16px; align-items:center; }
    .welfare-filters input, .welfare-filters select { min-height:44px; border:1px solid var(--border-soft, #dfceb9); border-radius:10px; padding:0 12px; background:var(--surface, #fff); color:inherit; }
    .welfare-table-wrap { overflow:auto; }
    .welfare-table { width:100%; border-collapse:collapse; }
    .welfare-table th, .welfare-table td { padding:13px 15px; border-top:1px solid var(--border-soft, #eadfce); text-align:left; vertical-align:middle; white-space:nowrap; }
    .welfare-table th { color:var(--text-muted, #75695f); font-size:.73rem; text-transform:uppercase; letter-spacing:.05em; }
    .welfare-table small { color:var(--text-muted, #75695f); }
    .welfare-pagination { padding:14px 16px; }
    @media (max-width:900px) { .welfare-stats { grid-template-columns:repeat(2,minmax(0,1fr)); } .welfare-filters { grid-template-columns:1fr 1fr; } .welfare-filters input { grid-column:1/-1; } }
    @media (max-width:560px) { .welfare-stats, .welfare-filters { grid-template-columns:1fr; } .welfare-filters input { grid-column:auto; } }
</style>
@endpush

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
                        <td>{{ $record->oku_status === 'yes' ? __('OKU').' · '.($record->oku_category ?: __('Not specified')) : ((float) $record->{{ __('family_income') }} <= 5249 ? 'B40' : '-') }}</td>
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
        <div class="welfare-pagination">{{ $records->links() }}</div>
    </section>
</div>
@endsection
