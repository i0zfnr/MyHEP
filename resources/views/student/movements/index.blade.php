@extends('layouts.app')

@section('title', __('Student Movement'))

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ __('Student Movement') }}</h2>
@endsection

@push('styles')
<style>
    .move-grid { display:grid; grid-template-columns:1fr; gap:1rem; }
    @media (min-width: 920px) { .move-grid { grid-template-columns:.95fr 1.05fr; } }
    .move-status { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
    .move-status strong { display:block; font-size:1.4rem; color:var(--text); }
    .move-status-badge { display:inline-flex; align-items:center; gap:.4rem; padding:.45rem .8rem; border-radius:999px; font-size:.78rem; font-weight:800; }
    .move-status-badge.ok { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
    .move-status-badge.warn { background:#fff7ed; color:#b45309; border:1px solid #fed7aa; }
    .move-note { margin:.25rem 0 0; color:var(--text-muted); }
    .move-meta { display:grid; grid-template-columns:1fr; gap:.65rem; margin-top:1rem; }
    @media (min-width: 640px) { .move-meta { grid-template-columns:repeat(3, minmax(0, 1fr)); } }
    .move-student-grid { display:grid; grid-template-columns:1fr; gap:.65rem; }
    @media (min-width: 640px) { .move-student-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); } }
    .move-meta-item { border:1px solid var(--border); border-radius:10px; padding:.8rem; background:rgba(255,255,255,.55); }
    .move-meta-item span { display:block; font-size:.68rem; text-transform:uppercase; letter-spacing:.05em; color:var(--text-muted); margin-bottom:.25rem; }
    .move-meta-item b { font-size:.85rem; color:var(--text); }
    .move-options { display:grid; grid-template-columns:1fr; gap:.65rem; }
    @media (min-width: 640px) { .move-options { grid-template-columns:repeat(2, minmax(0, 1fr)); } }
    .move-option { border:1px solid var(--border); border-radius:12px; padding:.8rem; background:#fff; display:flex; gap:.55rem; align-items:flex-start; cursor:pointer; }
    .move-option input { margin-top:.25rem; }
    .move-option-title { display:block; font-weight:800; color:var(--text); }
    .move-option-hint { display:block; margin-top:.18rem; font-size:.76rem; color:var(--text-muted); }
    .move-alert { border:1px solid #fed7aa; background:#fff7ed; color:#9a3412; border-radius:12px; padding:.85rem 1rem; margin-bottom:1rem; }
    .move-alert.danger { border-color:#fecaca; background:#fef2f2; color:#991b1b; }
    .move-empty { padding:2rem 1rem; text-align:center; color:var(--text-muted); }
    .move-scan-card { display:flex; flex-direction:column; gap:1rem; }
    .move-scan-head { display:flex; align-items:flex-start; justify-content:space-between; gap:1rem; flex-wrap:wrap; }
    .move-scan-actions { display:flex; gap:.45rem; flex-wrap:wrap; }
    .move-scanner {
        position:relative;
        isolation:isolate;
        border:1px solid rgba(188, 151, 112, .38);
        border-radius:18px;
        overflow:hidden;
        min-height:320px;
        height:clamp(320px, 54vw, 420px);
        background:
            radial-gradient(circle at 50% 44%, rgba(214,173,115,.20), transparent 30%),
            radial-gradient(circle at 12% 8%, rgba(255,255,255,.08), transparent 24%),
            linear-gradient(145deg, #1c1713 0%, #0e0c0a 72%);
        box-shadow:
            0 22px 52px rgba(34, 24, 16, .24),
            inset 0 1px 0 rgba(255,255,255,.12),
            inset 0 -1px 0 rgba(0,0,0,.36);
    }
    .move-scanner::after {
        content:'';
        position:absolute;
        inset:0;
        z-index:2;
        pointer-events:none;
        background:
            linear-gradient(180deg, rgba(0,0,0,.24), transparent 25%, transparent 72%, rgba(0,0,0,.38)),
            radial-gradient(circle at center, transparent 44%, rgba(0,0,0,.34) 100%);
    }
    .move-scanner.is-live {
        border-color: transparent;
        box-shadow:0 26px 60px rgba(0,0,0,.34), inset 0 1px 0 rgba(255,255,255,.08);
    }
    .move-scanner-toolbar {
        position:absolute;
        top:.75rem;
        left:.75rem;
        right:.75rem;
        z-index:6;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:.6rem;
        padding:.55rem;
        border:1px solid rgba(255,244,226,.20);
        border-radius:16px;
        background:
            linear-gradient(145deg, rgba(255,255,255,.13), transparent 48%),
            rgba(18,14,11,.48);
        box-shadow:0 16px 38px rgba(0,0,0,.22), inset 0 1px 0 rgba(255,255,255,.14);
        backdrop-filter:blur(24px) saturate(165%);
        -webkit-backdrop-filter:blur(24px) saturate(165%);
    }
    .move-scanner-state {
        display:inline-flex;
        align-items:center;
        gap:.4rem;
        min-width:0;
        color:#fff7ef;
        font-size:.7rem;
        font-weight:850;
        letter-spacing:.025em;
        white-space:nowrap;
    }
    .move-scanner-state::before {
        content:'';
        width:8px;
        height:8px;
        flex:0 0 8px;
        border-radius:50%;
        background:#d7bfa8;
        box-shadow:0 0 0 4px rgba(215,191,168,.13);
    }
    .move-scanner-state[data-state="live"]::before,
    .move-scanner-state[data-state="verified"]::before {
        background:#6ee7a4;
        box-shadow:0 0 0 4px rgba(110,231,164,.14), 0 0 14px rgba(110,231,164,.46);
        animation:moveScannerPulse 1.8s ease-in-out infinite;
    }
    .move-scanner-state[data-state="error"]::before {
        background:#fca5a5;
        box-shadow:0 0 0 4px rgba(252,165,165,.14);
    }
    .move-scanner-toolbar .ui-btn {
        min-height:38px;
        gap:.38rem;
        padding:.48rem .72rem;
        color:#fff7ef;
    }
    .move-scanner-toolbar .ui-btn svg {
        width:15px;
        height:15px;
        flex:0 0 15px;
    }
    .move-scanner-toolbar .ui-btn:disabled {
        opacity:.42;
        cursor:not-allowed;
    }
    .move-scanner video {
        position:absolute;
        inset:0;
        width:100%;
        height:100%;
        object-fit:cover;
        background:#000;
        opacity:0;
        filter:saturate(1.06) contrast(1.03);
        transition:opacity .24s ease;
        z-index:1;
    }
    .move-scanner.is-live video { opacity:1; }
    .move-scanner-overlay {
        display:none !important;
    }
    .move-scan-placeholder {
        position:absolute;
        inset:0;
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        padding:5.5rem 1.5rem 6rem;
        text-align:center;
        color:#d9c9ba;
        z-index:3;
    }
    .move-scan-placeholder-copy {
        display:flex;
        flex-direction:column;
        align-items:center;
        gap:.7rem;
        width:min(100%, 420px);
        padding:1.25rem;
        border:1px solid rgba(255,244,226,.12);
        border-radius:20px;
        background:
            linear-gradient(145deg, rgba(255,255,255,.10), transparent 46%),
            rgba(22,17,13,.42);
        box-shadow:0 20px 44px rgba(0,0,0,.20), inset 0 1px 0 rgba(255,255,255,.09);
        backdrop-filter:blur(18px) saturate(145%);
        -webkit-backdrop-filter:blur(18px) saturate(145%);
    }
    .move-scan-placeholder strong { color:#fff7ef; font-size:1.02rem; }
    .move-scan-placeholder span {
        max-width:24rem;
        line-height:1.55;
    }
    .move-scan-status {
        border:1px solid var(--border);
        border-radius:12px;
        padding:.85rem 1rem;
        background:rgba(255,255,255,.58);
        color:var(--text-muted);
        font-size:.84rem;
    }
    .move-scanner-feedback {
        position:absolute;
        left:.75rem;
        right:.75rem;
        bottom:.75rem;
        z-index:6;
        display:grid;
        gap:.35rem;
    }
    .move-scanner-feedback .move-scan-status {
        border-color:rgba(255,244,226,.18);
        border-radius:16px;
        background:
            linear-gradient(145deg, rgba(255,255,255,.10), transparent 48%),
            rgba(18,14,11,.58);
        color:#eadfd5;
        box-shadow:0 16px 38px rgba(0,0,0,.22), inset 0 1px 0 rgba(255,255,255,.11);
        backdrop-filter:blur(24px) saturate(165%);
        -webkit-backdrop-filter:blur(24px) saturate(165%);
    }
    .move-scanner-feedback .move-scan-status.ok { border-color:rgba(110,231,164,.42); color:#c9f7dd; }
    .move-scanner-feedback .move-scan-status.warn { border-color:rgba(251,191,36,.38); color:#fde7a8; }
    .move-scanner-feedback .move-scan-status.danger { border-color:rgba(252,165,165,.4); color:#fecaca; }
    .move-scan-expiry {
        width:max-content;
        max-width:100%;
        margin:0 .35rem;
        color:rgba(255,247,239,.78);
        font-size:.7rem;
        font-weight:700;
    }
    @keyframes moveScannerPulse {
        0%, 100% { transform:scale(1); }
        50% { transform:scale(1.18); }
    }
    .move-scan-status.ok { border-color:#bbf7d0; background:#f0fdf4; color:#166534; }
    .move-scan-status.warn { border-color:#fed7aa; background:#fff7ed; color:#9a3412; }
    .move-scan-status.danger { border-color:#fecaca; background:#fef2f2; color:#991b1b; }
    .move-live-chip {
        display:inline-flex;
        align-items:center;
        gap:.35rem;
        padding:.42rem .78rem;
        border-radius:999px;
        border:1px solid #bbf7d0;
        background:#f0fdf4;
        color:#166534;
        font-size:.75rem;
        font-weight:800;
    }
    .move-live-chip svg { width:12px; height:12px; }
    .move-status-badge.live-out { background:#eff6ff; color:#1d4ed8; border:1px solid #bfdbfe; }
    .move-field { display:grid; gap:.38rem; margin-top:1rem; }
    .move-field label { font-size:.78rem; font-weight:800; color:var(--text); }
    .move-field small { color:var(--text-muted); font-size:.75rem; }
    .move-field[hidden] { display:none !important; }
    body[data-theme="dark"] .move-option,
    body[data-theme="dark"] .move-meta-item,
    body[data-theme="dark"] .move-scan-status { background:var(--surface); }
    body[data-theme="dark"] .move-scanner {
        background:
            radial-gradient(circle at 50% 44%, rgba(214,173,115,.18), transparent 30%),
            radial-gradient(circle at 12% 8%, rgba(255,255,255,.06), transparent 24%),
            linear-gradient(145deg, #17130f 0%, #080706 74%);
    }
    #moveScannerOverlay[hidden],
    #moveScannerPlaceholder[hidden] { display:none !important; }
    @media (max-width: 640px) {
        .move-page {
            gap: .75rem;
        }

        .move-page .ui-hero {
            min-height: 0;
            padding: 1rem;
            border-radius: 16px;
        }

        .move-page .ui-hero h3 {
            font-size: 1.35rem;
            margin-bottom: .35rem;
        }

        .move-page .ui-hero p {
            font-size: .86rem;
            line-height: 1.35;
            max-width: 100%;
        }

        .move-page .ui-card {
            border-radius: 14px;
        }

        .move-page .ui-card-head,
        .move-page .ui-card-body {
            padding: .8rem .9rem;
        }

        .move-page .ui-card-head {
            min-height: auto;
            gap: .5rem;
        }

        .move-page .ui-card-head strong {
            font-size: .95rem;
        }

        .move-grid,
        .move-student-grid,
        .move-meta {
            gap: .55rem;
        }

        .move-student-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .move-meta {
            grid-template-columns: repeat(3, minmax(0, 1fr));
            margin-top: .7rem;
        }

        .move-meta-item {
            min-width: 0;
            padding: .62rem;
            border-radius: 10px;
        }

        .move-meta-item span {
            font-size: .6rem;
            line-height: 1.2;
            letter-spacing: .035em;
        }

        .move-meta-item b {
            display: block;
            font-size: .76rem;
            line-height: 1.25;
            overflow-wrap: anywhere;
        }

        .move-status {
            gap: .6rem;
        }

        .move-status strong {
            font-size: 1.22rem;
        }

        .move-note {
            font-size: .86rem;
            line-height: 1.4;
        }

        .move-status-badge,
        .move-live-chip {
            padding: .32rem .58rem;
            font-size: .66rem;
        }

        .move-page .ui-table th,
        .move-page .ui-table td {
            padding: .62rem .5rem;
            font-size: .69rem;
            line-height: 1.25;
        }

        .move-page .ui-table th:nth-child(6),
        .move-page .ui-table td:nth-child(6) {
            display: none;
        }

        .move-history-scroll {
            max-height: 42vh;
            overflow: auto;
            -webkit-overflow-scrolling: touch;
        }

        .move-history-scroll .ui-table thead th {
            position: sticky;
            top: 0;
            z-index: 1;
            background: rgba(255, 252, 247, .96);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        body[data-theme="dark"] .move-history-scroll .ui-table thead th {
            background: rgba(23, 20, 18, .96);
        }

        .move-scan-head { gap:.85rem; }
        .move-scan-actions { margin-left:auto; }
        .move-scan-actions .ui-btn { flex:1 1 0; justify-content:center; }
        .move-scanner {
            min-height:250px;
            height:clamp(250px, 78vw, 360px);
        }
        .move-scan-placeholder { padding:5rem 1rem 6.2rem; }
        .move-scan-placeholder-copy {
            padding:1rem .9rem;
            border-radius:16px;
        }
        .move-scan-placeholder strong { font-size:.96rem; }
        .move-scan-placeholder span,
        .move-scan-status { font-size:.78rem; }
        .move-scanner-toolbar {
            top:.6rem;
            left:.6rem;
            right:.6rem;
            padding:.45rem;
        }
        .move-scanner-toolbar .ui-btn {
            min-height:36px;
            padding:.42rem .55rem;
            font-size:.7rem;
        }
        .move-scanner-feedback {
            left:.6rem;
            right:.6rem;
            bottom:.6rem;
        }
        .ui-table th,
        .ui-table td { white-space:normal; }
    }
    @media (prefers-reduced-motion: reduce) {
        .move-scanner-state::before { animation:none !important; }
    }
</style>
@endpush

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
            {{ $records->onEachSide(1)->links('vendor.pagination.studentedge') }}
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
