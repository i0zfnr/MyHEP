@extends('layouts.app')

@section('title', __('Student Movement Records'))

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ __('Student Movement Records') }}</h2>
@endsection

@push('styles')
<style>
    .mv-admin { display:flex; flex-direction:column; gap:1rem; }
    .mv-kpis { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:.85rem; }
    @media (min-width: 920px) { .mv-kpis { grid-template-columns:repeat(4, minmax(0, 1fr)); } }
    .mv-kpi {
        position:relative;
        padding:1rem 1.05rem;
        border-radius:16px;
        border:1px solid rgba(255,255,255,.22);
        background:
            linear-gradient(180deg, rgba(255,255,255,.92), rgba(247,242,237,.82)),
            radial-gradient(circle at 88% 12%, rgba(164,141,120,.18), transparent 34%);
        box-shadow:0 18px 34px rgba(27,20,14,.12);
        overflow:hidden;
    }
    .mv-kpi::after {
        content:'';
        position:absolute;
        left:0;
        right:0;
        bottom:0;
        height:3px;
        background:linear-gradient(90deg, #8a7362, #d5b89c);
    }
    .mv-kpi-label {
        font-size:.72rem;
        font-weight:800;
        text-transform:uppercase;
        letter-spacing:.06em;
        color:#866f5e;
        margin-bottom:.35rem;
    }
    .mv-kpi-value { font-size:2rem; font-weight:800; color:#2d1f14; line-height:1; }
    .mv-toolbar { display:grid; grid-template-columns:1fr; gap:.85rem; align-items:start; }
    @media (min-width: 1100px) { .mv-toolbar { grid-template-columns:1.2fr .8fr; } }
    .mv-actions { display:flex; gap:.55rem; flex-wrap:wrap; justify-content:flex-end; }
    .mv-filter-grid { display:grid; grid-template-columns:1fr; gap:.75rem; }
    @media (min-width: 880px) { .mv-filter-grid { grid-template-columns:1.3fr repeat(5, minmax(0, 1fr)) auto auto; } }
    .mv-field { display:flex; flex-direction:column; gap:.3rem; }
    .mv-field span { font-size:.72rem; font-weight:700; color:var(--text-muted); }
    .mv-field input,
    .mv-field select { width:100%; min-width:0; }
    .mv-table-note { color:var(--text-muted); font-size:.78rem; margin-top:.15rem; }
    .mv-student { font-weight:700; color:var(--text); }
    .mv-sub { color:var(--text-muted); font-size:.76rem; }
    .mv-empty { padding:2rem 1rem; text-align:center; color:var(--text-muted); }
    body[data-theme="dark"] .mv-kpi {
        border-color:rgba(226,209,192,.16);
        background:
            linear-gradient(180deg, rgba(33,29,26,.92), rgba(18,16,14,.86)),
            radial-gradient(circle at 88% 12%, rgba(215,191,168,.14), transparent 34%);
        box-shadow:0 20px 38px rgba(0,0,0,.32);
    }
    body[data-theme="dark"] .mv-kpi-label { color:#cdb7a4; }
    body[data-theme="dark"] .mv-kpi-value,
    body[data-theme="dark"] .mv-student { color:#f7efe8; }
</style>
@endpush

@section('content')
<div class="ui-shell mv-admin">
    @if(session('success'))
        <div class="ui-card"><div class="ui-card-body" style="color:#166534;">{{ session('success') }}</div></div>
    @endif

    <div class="ui-hero">
        <h3>{{ __('Movement Monitoring') }}</h3>
        <p>{{ __('Track student movement live, review curfew compliance, and move quickly between records, violations, and QR controls.') }}</p>
    </div>

    <div class="mv-kpis">
        <div class="mv-kpi"><div class="mv-kpi-label">{{ __('Outside Now') }}</div><div class="mv-kpi-value">{{ $summary['outside_now'] }}</div></div>
        <div class="mv-kpi"><div class="mv-kpi-label">{{ __('Returned Today') }}</div><div class="mv-kpi-value">{{ $summary['returned_today'] }}</div></div>
        <div class="mv-kpi"><div class="mv-kpi-label">{{ __('Check-Outs Today') }}</div><div class="mv-kpi-value">{{ $summary['checkouts_today'] }}</div></div>
        <div class="mv-kpi"><div class="mv-kpi-label">{{ __('Late Returns') }}</div><div class="mv-kpi-value">{{ $summary['late_returns'] }}</div></div>
    </div>

    <section class="ui-card">
        <div class="ui-card-head mv-toolbar">
            <div>
                <strong>{{ __('Search & Filter') }}</strong>
                <div class="mv-table-note">{{ __('Filter by student, date, movement type, status, or rule result.') }}</div>
            </div>
            <div class="mv-actions">
                <a class="ui-btn" href="{{ route('admin.movements.outside') }}">{{ __('Outside Campus') }}</a>
                <a class="ui-btn" href="{{ route('admin.movements.violations') }}">{{ __('Violations') }}</a>
                <a class="ui-btn" href="{{ route('admin.movements.qr') }}">{{ __('QR Code') }}</a>
                <a class="ui-btn" href="{{ route('admin.movements.settings') }}">{{ __('Settings') }}</a>
            </div>
        </div>
        <div class="ui-card-body">
            <form method="GET" class="mv-filter-grid">
                <label class="mv-field">
                    <span>{{ __('Search') }}</span>
                    <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('Student / matric / programme') }}">
                </label>
                <label class="mv-field">
                    <span>{{ __('Start Date') }}</span>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                </label>
                <label class="mv-field">
                    <span>{{ __('End Date') }}</span>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                </label>
                <label class="mv-field">
                    <span>{{ __('Movement Type') }}</span>
                    <select name="movement_type_id">
                        <option value="">{{ __('All Movement Types') }}</option>
                        @foreach($movementTypes as $type)
                            <option value="{{ $type->id }}" @selected(($filters['movement_type_id'] ?? '') == $type->id)>{{ __($type->name) }}</option>
                        @endforeach
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
                <button class="ui-btn primary" type="submit" style="align-self:end;">{{ __('Filter') }}</button>
                <a class="ui-btn" href="{{ route('admin.movements.export', request()->query()) }}" style="align-self:end;">{{ __('Export CSV') }}</a>
            </form>
        </div>
    </section>

    <section class="ui-card">
        <div style="overflow-x:auto;">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>{{ __('Student') }}</th>
                        <th>{{ __('Programme') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Check-Out') }}</th>
                        <th>{{ __('Return') }}</th>
                        <th>{{ __('Status') }}</th>
                        <th>{{ __('Rule') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td><span class="mv-student">{{ $record->student_name }}</span><br><span class="mv-sub">{{ $record->matric_no }}</span></td>
                            <td>{{ $record->program }}</td>
                            <td>{{ __($record->movement_type_name) }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($record->checkout_at)->format('d M Y, h:i A') }}</td>
                            <td>{{ $record->return_at ? \Illuminate\Support\Carbon::parse($record->return_at)->format('d M Y, h:i A') : '-' }}</td>
                            <td><span class="ui-status status-{{ $record->movement_status === 'outside' ? 'pending' : 'confirmed' }}">{{ __($record->movement_status) }}</span></td>
                            <td><span class="ui-status status-{{ $record->rule_status === 'late' ? 'rejected' : ($record->rule_status === 'pending' ? 'pending' : 'confirmed') }}">{{ __($record->rule_status) }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="mv-empty">{{ __('No movement records found.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ui-card-body">{{ $records->links() }}</div>
    </section>
</div>
@endsection
