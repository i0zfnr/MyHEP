@extends('layouts.app')

@section('title', __('Scan QR'))

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ __('Scan QR') }}</h2>
@endsection

@push('styles')
<style>
    body.has-student-bottom-nav .page-header,
    body.has-student-bottom-nav .mobile-bottom-nav,
    body.has-student-bottom-nav .app-footer {
        display: none !important;
    }

    body.has-student-bottom-nav .page-body {
        padding: 0 !important;
    }

    .scan-page {
        position: fixed;
        inset: 0;
        z-index: 900;
        overflow: hidden;
        background: #0c0907;
        color: #fffaf5;
    }

    .scan-video {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        background: #0c0907;
    }

    .scan-vignette {
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            linear-gradient(180deg, rgba(0,0,0,.48), transparent 22%, transparent 64%, rgba(0,0,0,.62)),
            radial-gradient(circle at center, transparent 34%, rgba(0,0,0,.38) 100%);
    }

    .scan-topbar {
        position: absolute;
        top: max(1rem, env(safe-area-inset-top));
        left: max(1rem, env(safe-area-inset-left));
        right: max(1rem, env(safe-area-inset-right));
        z-index: 4;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }

    .scan-icon-btn {
        width: 44px;
        height: 44px;
        display: grid;
        place-items: center;
        border: 0;
        border-radius: 999px;
        background: rgba(0, 0, 0, .24);
        color: #fff;
        text-decoration: none;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.16);
        backdrop-filter: blur(18px) saturate(150%);
        -webkit-backdrop-filter: blur(18px) saturate(150%);
    }

    .scan-icon-btn svg {
        width: 22px;
        height: 22px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .scan-clock {
        font-size: .82rem;
        font-weight: 800;
        text-shadow: 0 2px 10px rgba(0,0,0,.36);
    }

    .scan-frame {
        position: absolute;
        left: 50%;
        top: 45%;
        z-index: 3;
        width: min(64vw, 320px);
        aspect-ratio: 1;
        transform: translate(-50%, -50%);
        pointer-events: none;
    }

    .scan-frame span {
        position: absolute;
        width: 56px;
        height: 56px;
        border-color: rgba(255,255,255,.96);
    }

    .scan-frame span:nth-child(1) { top: 0; left: 0; border-top: 5px solid; border-left: 5px solid; border-radius: 18px 0 0 0; }
    .scan-frame span:nth-child(2) { top: 0; right: 0; border-top: 5px solid; border-right: 5px solid; border-radius: 0 18px 0 0; }
    .scan-frame span:nth-child(3) { bottom: 0; right: 0; border-bottom: 5px solid; border-right: 5px solid; border-radius: 0 0 18px 0; }
    .scan-frame span:nth-child(4) { bottom: 0; left: 0; border-bottom: 5px solid; border-left: 5px solid; border-radius: 0 0 0 18px; }

    .scan-bottom {
        position: absolute;
        left: max(1rem, env(safe-area-inset-left));
        right: max(1rem, env(safe-area-inset-right));
        bottom: max(1rem, env(safe-area-inset-bottom));
        z-index: 4;
        display: grid;
        gap: .85rem;
    }

    .scan-status {
        justify-self: center;
        max-width: min(100%, 430px);
        padding: .8rem 1rem;
        border: 1px solid rgba(255,255,255,.18);
        border-radius: 18px;
        background: rgba(12, 9, 7, .48);
        color: #fff3df;
        font-size: .82rem;
        font-weight: 700;
        line-height: 1.45;
        text-align: center;
        box-shadow: 0 18px 44px rgba(0,0,0,.24), inset 0 1px 0 rgba(255,255,255,.12);
        backdrop-filter: blur(22px) saturate(150%);
        -webkit-backdrop-filter: blur(22px) saturate(150%);
    }

    .scan-status.ok { border-color: rgba(110, 231, 164, .45); color: #cffbdd; }
    .scan-status.danger { border-color: rgba(252, 165, 165, .48); color: #fecaca; }

    .scan-mode {
        width: min(100%, 430px);
        justify-self: center;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: .35rem;
        padding: .35rem;
        border-radius: 999px;
        background: rgba(12, 9, 7, .52);
        box-shadow: 0 18px 44px rgba(0,0,0,.25), inset 0 1px 0 rgba(255,255,255,.14);
        backdrop-filter: blur(24px) saturate(155%);
        -webkit-backdrop-filter: blur(24px) saturate(155%);
    }

    .scan-mode span {
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        border-radius: 999px;
        color: rgba(255,255,255,.72);
        font-size: .84rem;
        font-weight: 850;
    }

    .scan-mode .active {
        background: #fffaf5;
        color: #7d582f;
    }

    .scan-mode svg {
        width: 16px;
        height: 16px;
        fill: none;
        stroke: currentColor;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .scan-canvas {
        display: none;
    }
</style>
@endpush

@section('content')
<div class="scan-page">
    <video class="scan-video" id="scanVideo" playsinline muted autoplay aria-label="{{ __('QR scanner camera preview') }}"></video>
    <canvas class="scan-canvas" id="scanCanvas"></canvas>
    <div class="scan-vignette" aria-hidden="true"></div>

    <div class="scan-topbar">
        <a href="{{ route('student.movements.index') }}" class="scan-icon-btn" aria-label="Close scanner">
            <svg viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </a>
        <div class="scan-clock" id="scanClock">--:--</div>
        <span class="scan-icon-btn" aria-hidden="true">
            <svg viewBox="0 0 24 24"><path d="M13 2 3 14h8l-1 8 11-14h-8z"/></svg>
        </span>
    </div>

    <div class="scan-frame" aria-hidden="true">
        <span></span><span></span><span></span><span></span>
    </div>

    <div class="scan-bottom">
        <div class="scan-status" id="scanStatus" role="status" aria-live="polite">
            {{ __('Opening camera. Point it at the guard house QR code.') }}
        </div>
        <div class="scan-mode" aria-label="Scan mode">
            <span class="active">
                <svg viewBox="0 0 24 24"><path d="M4 4h6v6H4z"/><path d="M14 4h6v6h-6z"/><path d="M4 14h6v6H4z"/><path d="M14 14h2"/><path d="M18 14h2"/><path d="M14 18h6"/></svg>
                Scan QR
            </span>
            <span>Student Movement</span>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
(() => {
    const video = document.getElementById('scanVideo');
    const canvas = document.getElementById('scanCanvas');
    const statusNode = document.getElementById('scanStatus');
    const clockNode = document.getElementById('scanClock');
    const jsQr = window.jsQR || null;
    const canvasContext = canvas ? canvas.getContext('2d', { willReadFrequently: true }) : null;
    const targetUrl = new URL(@json(route('student.movements.scan')), window.location.origin);

    let stream = null;
    let detector = null;
    let scanTimer = null;
    let isScanning = false;

    const setStatus = (message, tone = '') => {
        if (!statusNode) return;
        statusNode.textContent = message;
        statusNode.className = 'scan-status' + (tone ? ' ' + tone : '');
    };

    const updateClock = () => {
        if (!clockNode) return;
        clockNode.textContent = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
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
        isScanning = false;
        if (video) video.srcObject = null;
    };

    const handleDetectedValue = (rawValue) => {
        const token = extractToken(rawValue);
        if (!token) {
            setStatus(@json(__('QR code detected, but it does not contain a valid movement token.')), 'danger');
            return;
        }

        setStatus(@json(__('QR detected. Verifying movement pass...')), 'ok');
        stopScanner();
        targetUrl.searchParams.set('token', token);
        window.location.assign(targetUrl.toString());
    };

    const scanFrame = async () => {
        if (!isScanning || !video || video.readyState < 2) return;

        try {
            if (detector) {
                const barcodes = await detector.detect(video);
                if (barcodes.length > 0) handleDetectedValue(barcodes[0].rawValue);
                return;
            }

            if (!jsQr || !canvasContext) return;
            const width = video.videoWidth || 0;
            const height = video.videoHeight || 0;
            if (!width || !height) return;

            if (canvas.width !== width || canvas.height !== height) {
                canvas.width = width;
                canvas.height = height;
            }

            canvasContext.drawImage(video, 0, 0, width, height);
            const imageData = canvasContext.getImageData(0, 0, width, height);
            const qrCode = jsQr(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: 'dontInvert',
            });

            if (qrCode?.data) handleDetectedValue(qrCode.data);
        } catch (error) {
            setStatus(@json(__('Unable to read the QR frame right now. Keep the camera steady.')), 'danger');
        }
    };

    const requestCameraStream = async () => {
        const attempts = [
            { video: { facingMode: { exact: 'environment' } }, audio: false },
            { video: { facingMode: { ideal: 'environment' } }, audio: false },
            { video: true, audio: false },
        ];

        let lastError = null;
        for (const constraints of attempts) {
            try {
                return await navigator.mediaDevices.getUserMedia(constraints);
            } catch (error) {
                lastError = error;
            }
        }

        throw lastError;
    };

    const startScanner = async () => {
        if (!video || !canvas || !navigator.mediaDevices?.getUserMedia) {
            setStatus(@json(__('Camera scanning is unavailable. Open this page on HTTPS or your installed PWA and allow camera access.')), 'danger');
            return;
        }

        try {
            if ('BarcodeDetector' in window) {
                detector = new window.BarcodeDetector({ formats: ['qr_code'] });
            }
            stream = await requestCameraStream();
            video.srcObject = stream;
            video.setAttribute('playsinline', 'true');
            video.setAttribute('autoplay', 'true');
            await video.play();
            isScanning = true;
            setStatus(@json(__('Camera is ready. Point it at the guard house QR code.')), 'ok');
            scanTimer = window.setInterval(scanFrame, 350);
        } catch (error) {
            setStatus(@json(__('Camera access failed. Allow camera permission and reopen Scan QR.')), 'danger');
        }
    };

    updateClock();
    window.setInterval(updateClock, 1000);
    window.addEventListener('beforeunload', stopScanner);
    startScanner();
})();
</script>
@endpush
