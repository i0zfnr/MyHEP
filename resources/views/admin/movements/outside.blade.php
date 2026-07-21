@extends('layouts.app')

@section('title', __('Students Outside Campus'))

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ __('Students Outside Campus') }}</h2>
@endsection

@push('styles')
<style>
    .mv-live { display:flex; flex-direction:column; gap:1rem; }
    .mv-live-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:.85rem; }
    @media (min-width: 920px) { .mv-live-grid { grid-template-columns:repeat(4, minmax(0, 1fr)); } }
    .mv-live-kpi {
        padding:1rem;
        border-radius:16px;
        border:1px solid rgba(255,255,255,.22);
        background:linear-gradient(180deg, rgba(255,255,255,.92), rgba(247,242,237,.82));
        box-shadow:0 18px 34px rgba(27,20,14,.12);
    }
    .mv-live-kpi small { display:block; font-size:.72rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#866f5e; margin-bottom:.35rem; }
    .mv-live-kpi strong { font-size:2rem; line-height:1; color:#2d1f14; }
    .mv-live-empty { padding:2rem 1rem; text-align:center; color:var(--text-muted); }
    .mv-student-card { display:flex; align-items:center; gap:.7rem; min-width:220px; }
    .mv-avatar { width:48px; height:48px; border-radius:10px; object-fit:cover; border:1px solid rgba(226,209,192,.24); background:rgba(255,255,255,.06); flex:0 0 48px; }
    .mv-avatar-empty { display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-weight:800; }
    .mv-mini-btn { display:inline-flex; align-items:center; margin-top:.35rem; padding:.28rem .5rem; border-radius:8px; border:1px solid rgba(226,209,192,.18); color:var(--text); text-decoration:none; font-size:.7rem; font-weight:750; }
    body[data-theme="dark"] .mv-live-kpi {
        border-color:rgba(226,209,192,.16);
        background:linear-gradient(180deg, rgba(33,29,26,.92), rgba(18,16,14,.86));
        box-shadow:0 20px 38px rgba(0,0,0,.32);
    }
    body[data-theme="dark"] .mv-live-kpi small { color:#cdb7a4; }
    body[data-theme="dark"] .mv-live-kpi strong { color:#f7efe8; }
</style>
@endpush

@section('content')
<div class="ui-shell mv-live">
    <div class="ui-hero">
        <h3>{{ __('Live Student Movement') }}</h3>
        <p>{{ __('View students who are still outside campus, when they left, and how long they have been away.') }}</p>
    </div>

    <div class="mv-live-grid">
        <div class="mv-live-kpi"><small>{{ __('Outside Now') }}</small><strong>{{ $summary['outside_now'] }}</strong></div>
        <div class="mv-live-kpi"><small>{{ __('Check-Outs Today') }}</small><strong>{{ $summary['checkouts_today'] }}</strong></div>
        <div class="mv-live-kpi"><small>{{ __('Late Returns') }}</small><strong>{{ $summary['late_returns'] }}</strong></div>
        <div class="mv-live-kpi"><small>{{ __('Overnight Stay') }}</small><strong>{{ $summary['overnight_records'] }}</strong></div>
    </div>

    <section class="ui-card">
        <div class="ui-card-head">
            <strong>{{ __('Currently Outside') }}</strong>
            <a class="ui-btn" href="{{ route('admin.movements.index') }}">{{ __('All Records') }}</a>
        </div>
        <div style="overflow-x:auto;">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>{{ __('Student') }}</th>
                        <th>{{ __('Residence') }}</th>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Plate No.') }}</th>
                        <th>{{ __('Check-Out') }}</th>
                        <th>{{ __('Expected Return') }}</th>
                        <th>{{ __('Duration') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        @php $checkout = \Illuminate\Support\Carbon::parse($record->checkout_at); @endphp
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
                                        <span class="muted">{{ $record->matric_no }} | {{ $record->program }}</span><br>
                                        <a class="mv-mini-btn" href="{{ route('admin.students.show', $record->student_id) }}">{{ __('View Profile') }}</a>
                                    </div>
                                </div>
                            </td>
                            <td>{{ ($record->residence_status ?? 'inside_campus') === 'live_out' ? __('Live Out') : ($record->room_number ?: __('Inside Campus')) }}</td>
                            <td>{{ __($record->movement_type_name) }}</td>
                            <td>{{ $record->vehicle_plate_no ?: '-' }}</td>
                            <td>{{ $checkout->format('d M Y, h:i A') }}</td>
                            <td>{{ $record->expected_return_at ? \Illuminate\Support\Carbon::parse($record->expected_return_at)->format('d M Y, h:i A') : '-' }}</td>
                            <td>{{ $checkout->diffForHumans(now(), true) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="mv-live-empty">{{ __('No students are currently recorded outside campus.') }}</td></tr>
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
