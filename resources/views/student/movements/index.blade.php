@extends('layouts.app')

@section('title', __('Student Movement'))

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ __('Student Movement') }}</h2>
@endsection



@section('content')
@php
    $insideCampus = !$currentMovement;
    $checkpointValid = $checkpoint !== null;
    $residenceStatus = $student->residence_status ?? 'inside_campus';
    $isLiveOut = $residenceStatus === 'live_out';
    $isCurrentlyLate = $currentMovement
        && $currentMovement->expected_return_at
        && now()->greaterThan(\Illuminate\Support\Carbon::parse($currentMovement->expected_return_at));
@endphp

<div class="ui-shell move-page">
    @if(session('success'))
        <div class="move-alert">{{ session('success') }}</div>
    @endif
    @if(session('scan_ready'))
        <div class="move-alert">{{ session('scan_ready') }}</div>
    @endif
    @if($errors->any())
        <div class="move-alert danger">{{ $errors->first() }}</div>
    @endif

    <div class="ui-hero">
        <h3>{{ __('Campus Movement') }}</h3>
        <p>{{ __('Scan the guard house QR to open a short one-time pass for check-out or return.') }}</p>
    </div>

    <div class="move-grid">
        <section class="ui-card">
            <div class="ui-card-head">
                <strong>{{ __('Detected Student Details') }}</strong>
                <span class="move-status-badge {{ $isLiveOut ? 'live-out' : 'ok' }}">
                    {{ $isLiveOut ? __('Live Out Student') : __('Inside Campus Resident') }}
                </span>
            </div>
            <div class="ui-card-body">
                <div class="move-student-grid">
                    <div class="move-meta-item">
                        <span>{{ __('Student Name') }}</span>
                        <b>{{ $student->full_name ?? '-' }}</b>
                    </div>
                    <div class="move-meta-item">
                        <span>{{ __('Matric No.') }}</span>
                        <b>{{ $student->matric_no ?? '-' }}</b>
                    </div>
                    <div class="move-meta-item">
                        <span>{{ __('Programme') }}</span>
                        <b>{{ $student->program ?? '-' }}</b>
                    </div>
                    <div class="move-meta-item">
                        <span>{{ __('Room Number') }}</span>
                        <b>{{ $isLiveOut ? __('Live Out / Outside Campus') : ($student->room_number ?: '-') }}</b>
                    </div>
                </div>
            </div>
        </section>

        <section class="ui-card">
            <div class="ui-card-head">
                <strong>{{ __('Current Status') }}</strong>
                <span class="ui-status {{ $insideCampus ? 'status-confirmed' : 'status-pending' }}">
                    {{ $insideCampus ? __('Inside Campus') : __('Outside Campus') }}
                </span>
            </div>
            <div class="ui-card-body">
                <div class="move-status">
                    <div>
                        <strong>{{ $insideCampus ? __('Inside Campus') : __('Outside Campus') }}</strong>
                        <p class="move-note">{{ $insideCampus ? __('No active outside-campus movement is open.') : __('Return to campus must be recorded at the guard house QR checkpoint.') }}</p>
                    </div>
                    <span class="move-status-badge {{ $insideCampus ? 'ok' : 'warn' }}">{{ $insideCampus ? __('Ready to Check Out') : __('Awaiting Return Scan') }}</span>
                </div>

                @if($currentMovement)
                    <div class="move-meta">
                        <div class="move-meta-item">
                            <span>{{ __('Last Check-Out') }}</span>
                            <b>{{ \Illuminate\Support\Carbon::parse($currentMovement->checkout_at)->format('d M Y, h:i A') }}</b>
                        </div>
                        <div class="move-meta-item">
                            <span>{{ __('Expected Return') }}</span>
                            <b>{{ $currentMovement->expected_return_at ? \Illuminate\Support\Carbon::parse($currentMovement->expected_return_at)->format('d M Y, h:i A') : '-' }}</b>
                        </div>
                        <div class="move-meta-item">
                            <span>{{ __('Checkpoint') }}</span>
                            <b>{{ $currentMovement->checkpoint_name ?? '-' }}</b>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        <section class="ui-card">
            <div class="ui-card-head">
                <strong>{{ $checkpointValid ? __('Confirm Movement') : __('Movement Information') }}</strong>
                @if($checkpointValid)
                    <span class="move-live-chip">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ __('QR Verified') }}
                    </span>
                @endif
            </div>
            <div class="ui-card-body">
                @if($checkpointValid)
                    <div class="move-scan-status ok" id="moveScanStatus" role="status" aria-live="polite">
                        {{ __('Latest QR verified. Complete the movement before this one-time pass expires.') }}
                    </div>
                    @if($scanExpiresAt)
                        <div class="move-scan-expiry" id="moveScanExpiry" data-expiry="{{ $scanExpiresAt->toIso8601String() }}">
                            {{ __('Scan pass expires at :time.', ['time' => $scanExpiresAt->format('d M Y, h:i:s A')]) }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('student.movements.store') }}" data-confirm-message="{{ __('Confirm this movement record?') }}" data-confirm-action="{{ __('Confirm Movement') }}">
                        @csrf
                        <input type="hidden" name="checkpoint_id" value="{{ $checkpoint->id }}">
                        <input type="hidden" name="gps_latitude" id="gpsLatitude">
                        <input type="hidden" name="gps_longitude" id="gpsLongitude">

                        <div class="move-options" style="margin-top:1rem;">
                            @foreach($movementTypes as $type)
                                @php
                                    $disabled = $currentMovement
                                        ? $type->direction !== 'return'
                                        : $type->direction === 'return';
                                @endphp
                                <label class="move-option" style="{{ $disabled ? 'opacity:.48;' : '' }}">
                                    <input type="radio" name="movement_type_id" value="{{ $type->id }}" data-direction="{{ $type->direction }}" {{ $disabled ? 'disabled' : '' }} required>
                                    <span>
                                        <span class="move-option-title">{{ __($type->name) }}</span>
                                        <span class="move-option-hint">
                                            {{ $type->direction === 'return' ? __('Close your current outside-campus record.') : __('Open a new outside-campus movement record.') }}
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div class="move-field">
                            <label for="vehiclePlateNo">{{ __('Vehicle Plate Number') }}</label>
                            <input id="vehiclePlateNo" type="text" name="vehicle_plate_no" value="{{ old('vehicle_plate_no') }}" placeholder="{{ __('Example: TBA1234') }}">
                            <small>{{ __('Every student check-out must include a vehicle plate number before confirmation. Return scans do not need it.') }}</small>
                        </div>

                        <div class="move-field" id="lateExplanationField" {{ $isCurrentlyLate ? '' : 'hidden' }}>
                            <label for="lateExplanation">{{ __('Late Check-In Explanation') }}</label>
                            <textarea id="lateExplanation" name="late_explanation" rows="4" maxlength="2000" placeholder="{{ __('Explain why you returned to campus late.') }}">{{ old('late_explanation') }}</textarea>
                            <small>{{ __('Required only when your return is after the expected return time.') }}</small>
                        </div>

                        <div class="ui-actions" style="margin-top:1rem;">
                            <button type="submit" class="ui-btn primary">{{ __('Confirm') }}</button>
                            <a href="{{ route('student.movements.index', ['reset_scan' => 1]) }}" class="ui-btn">{{ __('Reset Scan') }}</a>
                        </div>
                    </form>
                @else
                    <p class="move-note" style="margin-top:0;">{{ __('Use Scan QR at the guard house to create or close a movement record.') }}</p>
                    <div class="move-meta">
                        <div class="move-meta-item">
                            <span>{{ __('QR Requirement') }}</span>
                            <b>{{ __('Required before any movement confirmation') }}</b>
                        </div>
                        <div class="move-meta-item">
                            <span>{{ __('Pass Duration') }}</span>
                            <b>{{ __('2 minutes after scan') }}</b>
                        </div>
                        <div class="move-meta-item">
                            <span>{{ __('Record Source') }}</span>
                            <b>{{ __('Guard house checkpoint') }}</b>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    </div>

    <section class="ui-card">
        <div class="ui-card-head">
            <strong>{{ __('Movement History') }}</strong>
        </div>
        <div class="move-history-scroll" style="overflow-x:auto;">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>{{ __('Type') }}</th>
                        <th>{{ __('Plate No.') }}</th>
                        <th>{{ __('Check-Out') }}</th>
                        <th>{{ __('Return') }}</th>
                        <th>{{ __('Rule') }}</th>
                        <th>{{ __('Late') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $record)
                        <tr>
                            <td>{{ __($record->movement_type_name) }}</td>
                            <td>{{ $record->vehicle_plate_no ?: '-' }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($record->checkout_at)->format('d M Y, h:i A') }}</td>
                            <td>{{ $record->return_at ? \Illuminate\Support\Carbon::parse($record->return_at)->format('d M Y, h:i A') : '-' }}</td>
                            <td><span class="ui-status status-{{ $record->rule_status === 'late' ? 'rejected' : ($record->rule_status === 'pending' ? 'pending' : 'confirmed') }}">{{ __($record->rule_status) }}</span></td>
                            <td>{{ (int) $record->late_minutes }} {{ __('min') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="move-empty">{{ __('No movement records yet.') }}</td></tr>
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

@push('scripts')
<script>
(() => {
    const lat = document.getElementById('gpsLatitude');
    const lng = document.getElementById('gpsLongitude');
    if (lat && lng && navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
            lat.value = position.coords.latitude;
            lng.value = position.coords.longitude;
        }, () => {}, { enableHighAccuracy: true, timeout: 5000, maximumAge: 60000 });
    }

    const expiryNode = document.getElementById('moveScanExpiry');
    const plateField = document.getElementById('vehiclePlateNo');
    const lateExplanationField = document.getElementById('lateExplanationField');
    const lateExplanationInput = document.getElementById('lateExplanation');
    const movementTypeRadios = Array.from(document.querySelectorAll('input[name="movement_type_id"]'));
    const currentReturnIsLate = @json($isCurrentlyLate);

    const syncPlateRequirement = () => {
        if (!plateField || movementTypeRadios.length === 0) {
            return;
        }

        const selected = movementTypeRadios.find((radio) => radio.checked && !radio.disabled);
        const isReturn = selected?.dataset.direction === 'return';

        plateField.required = !isReturn;
        plateField.disabled = !!isReturn;
        if (lateExplanationField && lateExplanationInput) {
            const needsExplanation = !!isReturn && currentReturnIsLate;
            lateExplanationField.hidden = !needsExplanation;
            lateExplanationInput.required = needsExplanation;
            if (!needsExplanation) {
                lateExplanationInput.value = '';
            }
        }

        if (isReturn) {
            plateField.value = '';
            plateField.placeholder = @json(__('Not required for return scan'));
        } else {
            plateField.placeholder = @json(__('Example: TBA1234'));
        }
    };

    movementTypeRadios.forEach((radio) => {
        radio.addEventListener('change', syncPlateRequirement);
    });
    syncPlateRequirement();

    if (expiryNode?.dataset.expiry) {
        const expiry = new Date(expiryNode.dataset.expiry);
        const timer = window.setInterval(() => {
            const diff = expiry.getTime() - Date.now();
            if (diff <= 0) {
                clearInterval(timer);
                window.location.assign(@json(route('student.movements.index', ['reset_scan' => 1])));
                return;
            }

            const minutes = Math.floor(diff / 60000);
            const seconds = Math.floor((diff % 60000) / 1000);
            expiryNode.textContent = @json(__('Scan pass expires in :time.')).replace(':time', `${minutes}:${String(seconds).padStart(2, '0')}`);
        }, 1000);
    }
})();
</script>
@endpush
