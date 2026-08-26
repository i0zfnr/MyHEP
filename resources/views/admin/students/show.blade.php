@extends('layouts.app')

@section('title', __('Student Profile'))



@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ __('Student Profile') }}</h2>
@endsection

@section('content')
<div class="profile-wrap">
    <section class="ui-card">
        <div class="ui-card-body">
            <div class="profile-head">
                @if(!empty($student->photo))
                    <img class="profile-photo" src="{{ asset('storage/' . $student->photo) }}" alt="{{ __('Profile photo') }}">
                @else
                    <div class="profile-photo profile-photo-empty">{{ strtoupper(substr($student->full_name ?? 'S', 0, 1)) }}</div>
                @endif
                <div class="profile-title">
                    <h3>{{ $student->full_name }}</h3>
                    <span>{{ $student->matric_no }} | {{ $student->class_name ?: $student->program }}</span>
                    <span>{{ ($student->residence_status ?? 'inside_campus') === 'live_out' ? __('Live Out / Outside Campus') : __('Inside Campus') }}: {{ $student->room_number ?: '-' }}</span>
                </div>
                <div class="ui-actions" style="margin-left:auto;">
                    <a class="ui-btn" href="{{ route('admin.movements.index') }}">{{ __('Movement Records') }}</a>
                    <a class="ui-btn" href="{{ route('admin.students.index') }}">{{ __('Student List') }}</a>
                </div>
            </div>
        </div>
    </section>

    <section class="ui-card">
        <div class="ui-card-body">
            <div class="profile-section-title">{{ __('Student Details') }}</div>
            <div class="profile-grid">
                <div class="profile-field"><span>{{ __('IC No.') }}</span><b>{{ maskIdentityNumber($student->ic_no) }}</b></div>
                <div class="profile-field"><span>{{ __('Email') }}</span><b>{{ $student->email ?: '-' }}</b></div>
                <div class="profile-field"><span>{{ __('Phone') }}</span><b>{{ $student->phone ?: '-' }}</b></div>
                <div class="profile-field"><span>{{ __('Semester') }}</span><b>{{ $student->semester ?: '-' }}</b></div>
                <div class="profile-field"><span>{{ __('Academic Session') }}</span><b>{{ $student->academic_session ?: '-' }}</b></div>
                <div class="profile-field"><span>{{ __('Date of Birth') }}</span><b>{{ $student->date_of_birth ?: '-' }}</b></div>
                <div class="profile-field"><span>{{ __('Religion') }}</span><b>{{ $student->religion ?: '-' }}</b></div>
                <div class="profile-field"><span>{{ __('Race') }}</span><b>{{ $student->race ?: '-' }}</b></div>
                <div class="profile-field"><span>{{ __('Home Address') }}</span><b>{{ $student->address ?: '-' }}</b></div>
                <div class="profile-field"><span>{{ __('Study Address') }}</span><b>{{ $student->study_address ?: '-' }}</b></div>
            </div>
        </div>
    </section>

    <section class="ui-card">
        <div class="ui-card-body">
            <div class="profile-section-title">{{ __('Guardian Details') }}</div>
            <div class="profile-grid">
                <div class="profile-field"><span>{{ __('Guardian Name') }}</span><b>{{ $student->guardian_name ?: '-' }}</b></div>
                <div class="profile-field"><span>{{ __('Guardian IC') }}</span><b>{{ maskIdentityNumber($student->guardian_ic_no) }}</b></div>
                <div class="profile-field"><span>{{ __('Guardian Phone') }}</span><b>{{ $student->guardian_phone ?: '-' }}</b></div>
                <div class="profile-field"><span>{{ __('Mother IC') }}</span><b>{{ maskIdentityNumber($student->mother_ic_no) }}</b></div>
                <div class="profile-field"><span>{{ __('Family Income') }}</span><b>{{ $student->family_income !== null ? 'RM ' . number_format((float) $student->family_income, 2) : '-' }}</b></div>
                <div class="profile-field"><span>{{ __('OKU Status') }}</span><b>{{ $student->oku_status === 'yes' ? __('Yes') : ($student->oku_status === 'no' ? __('No') : '-') }}</b></div>
                <div class="profile-field"><span>{{ __('OKU Registration No.') }}</span><b>{{ $student->oku_registration_no ?: '-' }}</b></div>
                <div class="profile-field"><span>{{ __('OKU Category') }}</span><b>{{ $student->oku_category ?: '-' }}</b></div>
                <div class="profile-field"><span>{{ __('Guardian Address') }}</span><b>{{ $student->guardian_address ?: '-' }}</b></div>
            </div>
        </div>
    </section>

    <section class="ui-card">
        <div class="ui-card-head">
            <strong>{{ __('Latest Movement Records') }}</strong>
        </div>
        <div style="overflow-x:auto;">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Plate No.') }}</th>
                        <th>{{ __('Check-Out') }}</th>
                        <th>{{ __('Return') }}</th>
                        <th>{{ __('Rule') }}</th>
                        <th>{{ __('Late Explanation') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($latestMovements as $movement)
                        <tr>
                            <td>{{ __($movement->movement_type_name) }}</td>
                            <td>{{ $movement->vehicle_plate_no ?: '-' }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($movement->checkout_at)->format('d M Y, h:i A') }}</td>
                            <td>{{ $movement->return_at ? \Illuminate\Support\Carbon::parse($movement->return_at)->format('d M Y, h:i A') : '-' }}</td>
                            <td><span class="ui-status status-{{ $movement->rule_status === 'late' ? 'rejected' : ($movement->rule_status === 'pending' ? 'pending' : 'confirmed') }}">{{ __($movement->rule_status) }}</span></td>
                            <td>{{ $movement->late_explanation ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="profile-empty">{{ __('No movement records yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
