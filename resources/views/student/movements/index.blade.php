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
    .move-scan-actions { display:flex; gap:.6rem; flex-wrap:wrap; }
    .move-scanner {
        position:relative;
        border:1px dashed var(--border);
        border-radius:18px;
        overflow:hidden;
        min-height:320px;
        height:clamp(320px, 54vw, 420px);
        background:
            radial-gradient(circle at 50% 50%, rgba(164,141,120,.08), transparent 58%),
            rgba(255,255,255,.62);
    }
    .move-scanner video {
        position:absolute;
        inset:0;
        width:100%;
        height:100%;
        object-fit:cover;
        background:#000;
        opacity:0;
        transition:opacity .18s ease;
        z-index:1;
    }
    .move-scanner.is-live video { opacity:1; }
    .move-scanner-overlay {
        position:absolute;
        inset:0;
        display:flex;
        align-items:center;
        justify-content:center;
        padding:1.25rem;
        pointer-events:none;
        z-index:3;
    }
    .move-scan-frame {
        width:min(72vw, 280px);
        max-width:78%;
        aspect-ratio:1;
        border:2px solid rgba(255,255,255,.92);
        border-radius:22px;
        box-shadow:0 0 0 999px rgba(0,0,0,.28);
        position:relative;
    }
    .move-scan-frame::before,
    .move-scan-frame::after {
        content:'';
        position:absolute;
        inset:14px;
        border:2px solid rgba(255,255,255,.14);
        border-radius:16px;
    }
    .move-scan-placeholder {
        position:absolute;
        inset:0;
        display:flex;
        flex-direction:column;
        align-items:center;
        justify-content:center;
        padding:1.5rem;
        text-align:center;
        color:var(--text-muted);
        z-index:2;
    }
    .move-scan-placeholder-copy {
        display:flex;
        flex-direction:column;
        align-items:center;
        gap:.7rem;
        width:min(100%, 420px);
        padding:1.25rem;
        border-radius:20px;
        background:rgba(20,17,14,.42);
        backdrop-filter:blur(8px);
    }
    .move-scan-placeholder strong { color:var(--text); font-size:1rem; }
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
    body[data-theme="dark"] .move-option,
    body[data-theme="dark"] .move-meta-item,
    body[data-theme="dark"] .move-scan-status { background:var(--surface); }
    body[data-theme="dark"] .move-scan-placeholder-copy { background:rgba(14,12,10,.62); }
    body[data-theme="dark"] .move-scanner {
        background:
            radial-gradient(circle at 50% 50%, rgba(215,191,168,.08), transparent 58%),
            rgba(24,21,18,.72);
    }
    #moveScannerOverlay[hidden],
    #moveScannerPlaceholder[hidden] { display:none !important; }
    @media (max-width: 640px) {
        .move-scan-head { gap:.85rem; }
        .move-scan-actions { width:100%; }
        .move-scan-actions .ui-btn { flex:1 1 0; justify-content:center; }
        .move-scanner {
            min-height:250px;
            height:clamp(250px, 78vw, 360px);
        }
        .move-scan-frame {
            width:min(62vw, 220px);
            border-radius:18px;
        }
        .move-scan-frame::before,
        .move-scan-frame::after {
            inset:11px;
            border-radius:12px;
        }
        .move-scan-placeholder { padding:1rem; }
        .move-scan-placeholder-copy {
            padding:1rem .9rem;
            border-radius:16px;
        }
        .move-scan-placeholder strong { font-size:.96rem; }
        .move-scan-placeholder span,
        .move-scan-status { font-size:.78rem; }
        .ui-table th,
        .ui-table td {
            font-size:.74rem;
            white-space:normal;
        }
    }
</style>
@endpush

@section('content')
@php
    $insideCampus = !$currentMovement;
    $checkpointValid = $hasScannedToken
        && $checkpoint
        && $checkpoint->is_active
        && (!$checkpoint->valid_from || \Illuminate\Support\Carbon::parse($checkpoint->valid_from)->lte(now()))
        && (!$checkpoint->valid_until || \Illuminate\Support\Carbon::parse($checkpoint->valid_until)->gte(now()));
@endphp

<div class="ui-shell">
    @if(session('success'))
        <div class="move-alert">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="move-alert danger">{{ $errors->first() }}</div>
    @endif

    <div class="ui-hero">
        <h3>{{ __('Campus Movement') }}</h3>
        <p>{{ __('Students must scan the guard house QR first. After a valid scan, the system unlocks movement options for check-out or return.') }}</p>
    </div>

    <div class="move-grid">
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

                <div class="move-meta">
                    <div class="move-meta-item">
                        <span>{{ __('Last Check-Out') }}</span>
                        <b>{{ $currentMovement?->checkout_at ? \Illuminate\Support\Carbon::parse($currentMovement->checkout_at)->format('d M Y, h:i A') : '-' }}</b>
                    </div>
                    <div class="move-meta-item">
                        <span>{{ __('Expected Return') }}</span>
                        <b>{{ $currentMovement?->expected_return_at ? \Illuminate\Support\Carbon::parse($currentMovement->expected_return_at)->format('d M Y, h:i A') : '-' }}</b>
                    </div>
                    <div class="move-meta-item">
                        <span>{{ __('Checkpoint') }}</span>
                        <b>{{ $currentMovement->checkpoint_name ?? ($checkpointValid ? $checkpoint->name : '-') }}</b>
                    </div>
                </div>
            </div>
        </section>

        <section class="ui-card">
            <div class="ui-card-head">
                <strong>{{ __('Scan Guard House QR') }}</strong>
                @if($checkpointValid)
                    <span class="move-live-chip">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        {{ __('QR Verified') }}
                    </span>
                @endif
            </div>
            <div class="ui-card-body move-scan-card">
                <div class="move-scan-head">
                    <div>
                        <p class="move-note" style="margin-top:0;">{{ __('Use the device camera to scan the active QR code at the guard house. The movement menu will only appear after a valid scan.') }}</p>
                    </div>
                    <div class="move-scan-actions">
                        <button type="button" class="ui-btn primary" id="scanStartBtn">{{ __('Start Scanner') }}</button>
                        <button type="button" class="ui-btn" id="scanStopBtn">{{ __('Stop Scanner') }}</button>
                    </div>
                </div>

                <div class="move-scanner" id="moveScanner">
                    <video id="moveScannerVideo" playsinline muted></video>
                    <div class="move-scan-placeholder" id="moveScannerPlaceholder">
                        <div class="move-scan-placeholder-copy">
                            <strong>{{ __('Camera scanner is idle') }}</strong>
                            <span>{{ __('Point the camera at the guard house QR code to continue.') }}</span>
                        </div>
                    </div>
                    <div class="move-scanner-overlay" id="moveScannerOverlay" hidden>
                        <div class="move-scan-frame"></div>
                    </div>
                </div>

                <div class="move-scan-status {{ $checkpointValid ? 'ok' : 'warn' }}" id="moveScanStatus">
                    {{ $checkpointValid
                        ? __('Valid QR scan detected. You can now choose a movement below.')
                        : __('Waiting for a valid QR scan from the guard house checkpoint.') }}
                </div>

                @if($checkpointValid)
                    <form method="POST" action="{{ route('student.movements.store') }}" data-confirm-message="{{ __('Confirm this movement record?') }}" data-confirm-action="{{ __('Confirm Movement') }}">
                        @csrf
                        <input type="hidden" name="checkpoint_id" value="{{ $checkpoint->id }}">
                        <input type="hidden" name="gps_latitude" id="gpsLatitude">
                        <input type="hidden" name="gps_longitude" id="gpsLongitude">

                        <div class="move-options">
                            @foreach($movementTypes as $type)
                                @php
                                    $disabled = $currentMovement
                                        ? $type->direction !== 'return'
                                        : $type->direction === 'return';
                                @endphp
                                <label class="move-option" style="{{ $disabled ? 'opacity:.48;' : '' }}">
                                    <input type="radio" name="movement_type_id" value="{{ $type->id }}" {{ $disabled ? 'disabled' : '' }} required>
                                    <span>
                                        <span class="move-option-title">{{ __($type->name) }}</span>
                                        <span class="move-option-hint">
                                            {{ $type->direction === 'return' ? __('Close your current outside-campus record.') : __('Open a new outside-campus movement record.') }}
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div class="ui-actions" style="margin-top:1rem;">
                            <button type="submit" class="ui-btn primary">{{ __('Confirm') }}</button>
                            <a href="{{ route('student.movements.index') }}" class="ui-btn">{{ __('Reset Scan') }}</a>
                        </div>
                    </form>
                @endif
            </div>
        </section>
    </div>

    <section class="ui-card">
        <div class="ui-card-head">
            <strong>{{ __('Movement History') }}</strong>
        </div>
        <div style="overflow-x:auto;">
            <table class="ui-table">
                <thead>
                    <tr>
                        <th>{{ __('Type') }}</th>
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
                            <td>{{ \Illuminate\Support\Carbon::parse($record->checkout_at)->format('d M Y, h:i A') }}</td>
                            <td>{{ $record->return_at ? \Illuminate\Support\Carbon::parse($record->return_at)->format('d M Y, h:i A') : '-' }}</td>
                            <td><span class="ui-status status-{{ $record->rule_status === 'late' ? 'rejected' : ($record->rule_status === 'pending' ? 'pending' : 'confirmed') }}">{{ __($record->rule_status) }}</span></td>
                            <td>{{ (int) $record->late_minutes }} {{ __('min') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="move-empty">{{ __('No movement records yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="ui-card-body">{{ $records->links() }}</div>
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

    const startBtn = document.getElementById('scanStartBtn');
    const stopBtn = document.getElementById('scanStopBtn');
    const video = document.getElementById('moveScannerVideo');
    const scanner = document.getElementById('moveScanner');
    const placeholder = document.getElementById('moveScannerPlaceholder');
    const overlay = document.getElementById('moveScannerOverlay');
    const statusNode = document.getElementById('moveScanStatus');
    const currentUrl = new URL(window.location.href);

    if (!startBtn || !stopBtn || !video || !scanner || !placeholder || !overlay || !statusNode) {
        return;
    }

    let stream = null;
    let detector = null;
    let scanTimer = null;
    let isScanning = false;

    const supportsDetector = 'BarcodeDetector' in window;
    if (supportsDetector) {
        detector = new window.BarcodeDetector({ formats: ['qr_code'] });
    }

    const setStatus = (message, tone) => {
        statusNode.textContent = message;
        statusNode.className = 'move-scan-status' + (tone ? ' ' + tone : '');
    };

    const extractToken = (rawValue) => {
        const value = String(rawValue || '').trim();
        if (!value) return null;

        try {
            const parsed = new URL(value, window.location.origin);
            return parsed.searchParams.get('token');
        } catch (error) {
            return value.length >= 12 ? value : null;
        }
    };

    const stopScanner = () => {
        if (scanTimer) {
            clearInterval(scanTimer);
            scanTimer = null;
        }
        if (stream) {
            stream.getTracks().forEach((track) => track.stop());
            stream = null;
        }
        video.srcObject = null;
        scanner.classList.remove('is-live');
        placeholder.hidden = false;
        overlay.hidden = true;
        isScanning = false;
    };

    const handleDetectedValue = (rawValue) => {
        const token = extractToken(rawValue);
        if (!token) {
            setStatus(@json(__('QR code detected, but it does not contain a valid movement token.')), 'danger');
            return;
        }

        stopScanner();
        currentUrl.searchParams.set('token', token);
        window.location.assign(currentUrl.toString());
    };

    const scanFrame = async () => {
        if (!detector || !isScanning || video.readyState < 2) {
            return;
        }

        try {
            const barcodes = await detector.detect(video);
            if (barcodes.length > 0) {
                handleDetectedValue(barcodes[0].rawValue);
            }
        } catch (error) {
            setStatus(@json(__('Unable to read the QR frame right now. Keep the camera steady and try again.')), 'danger');
        }
    };

    startBtn.addEventListener('click', async () => {
        if (!supportsDetector || !navigator.mediaDevices?.getUserMedia) {
            setStatus(@json(__('This browser does not support the built-in QR scanner. Use a supported mobile Chrome/Edge browser.')), 'danger');
            return;
        }

        try {
            stopScanner();
            stream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } },
                audio: false,
            });
            video.srcObject = stream;
            await video.play();
            scanner.classList.add('is-live');
            placeholder.hidden = true;
            overlay.hidden = false;
            isScanning = true;
            setStatus(@json(__('Scanner is live. Point the camera at the guard house QR code.')), 'ok');
            scanTimer = window.setInterval(scanFrame, 450);
        } catch (error) {
            setStatus(@json(__('Camera access failed. Please allow camera permission and try again.')), 'danger');
        }
    });

    stopBtn.addEventListener('click', () => {
        stopScanner();
        setStatus(@json(__('Scanner stopped. Start it again when you are ready to scan the guard house QR code.')), 'warn');
    });

    window.addEventListener('beforeunload', stopScanner);
})();
</script>
@endpush
