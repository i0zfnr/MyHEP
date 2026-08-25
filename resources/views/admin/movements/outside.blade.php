@extends('layouts.app')

@section('title', __('Students Outside Campus'))

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ __('Students Outside Campus') }}</h2>
@endsection



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
                                        @if(adminCan('students.sensitive'))
                                            <a class="mv-mini-btn" href="{{ route('admin.students.show', $record->student_id) }}">{{ __('View Profile') }}</a>
                                        @endif
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
            {{ $records->onEachSide(1)->links('vendor.pagination.myhep') }}
        </div>
    </section>
</div>
@endsection
