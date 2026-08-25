@extends('layouts.app')

@section('title', __('Scan QR'))

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ __('Scan QR') }}</h2>
@endsection



@section('content')
<div class="scan-page">
    <video class="scan-video" id="scanVideo" playsinline muted autoplay aria-label="{{ __('QR scanner camera preview') }}"></video>
    <canvas class="scan-canvas" id="scanCanvas"></canvas>
    <div class="scan-vignette" aria-hidden="true"></div>

    <div class="scan-topbar">
        <a href="{{ route('student.movements.index') }}" class="scan-icon-btn" aria-label="{{ __('Close scanner') }}">
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
            {{ __('Opening camera. Point it at the guard house or program QR code.') }}
        </div>
        <div class="scan-mode" aria-label="{{ __('Scan mode') }}">
            <span class="active">
                <svg viewBox="0 0 24 24"><path d="M4 4h6v6H4z"/><path d="M14 4h6v6h-6z"/><path d="M4 14h6v6H4z"/><path d="M14 14h2"/><path d="M18 14h2"/><path d="M14 18h6"/></svg>
                {{ __('Auto Scan QR') }}
            </span>
            <span>{{ __('Movement & Program Attendance') }}</span>
        </div>
    </div>
</div>

<!-- DONE Pop-up Modal -->
<div class="done-modal-backdrop" id="doneModal" style="display: none;" aria-hidden="true">
    <div class="done-modal-card done-modal-minimal">
        <div class="done-badge-icon">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </div>

        <h3 class="done-title" id="doneTitle">{{ __('DONE') }}</h3>

        <div class="done-actions" style="margin-top: 1.25rem;">
            <button type="button" class="btn-done-secondary" id="btnDoneClose">{{ __('Selesai / Imbas Lagi') }}</button>
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
    const doneModal = document.getElementById('doneModal');
    const doneTitle = document.getElementById('doneTitle');
    const doneProgramTitle = document.getElementById('doneProgramTitle');
    const doneStudentName = document.getElementById('doneStudentName');
    const doneStudentMeta = document.getElementById('doneStudentMeta');
    const donePointsText = document.getElementById('donePointsText');
    const btnDoneSurvey = document.getElementById('btnDoneSurvey');
    const btnDoneClose = document.getElementById('btnDoneClose');

    const jsQr = window.jsQR || null;
    const canvasContext = canvas ? canvas.getContext('2d', { willReadFrequently: true }) : null;
    const targetUrl = new URL(@json(route('student.movements.scan')), window.location.origin);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    let stream = null;
    let detector = null;
    let scanTimer = null;
    let isScanning = false;
    let isProcessing = false;

    const decodeSignedTokenPayload = (token) => {
        const value = String(token || '').trim();
        if (!value || !value.includes('.')) return null;

        const payload = value.split('.', 1)[0];
        try {
            const padded = payload.replace(/-/g, '+').replace(/_/g, '/') + '='.repeat((4 - (payload.length % 4)) % 4);
            const decoded = atob(padded);
            const json = JSON.parse(decoded);
            return json && typeof json === 'object' ? json : null;
        } catch (error) {
            return null;
        }
    };

    const parseTokenByPayload = (token) => {
        const payload = decodeSignedTokenPayload(token);
        if (!payload) return null;

        const programId = Number.parseInt(payload.pid, 10);
        if (Number.isInteger(programId) && programId > 0) {
            return { type: 'program', programId, token };
        }

        const checkpointId = Number.parseInt(payload.cid, 10);
        if (Number.isInteger(checkpointId) && checkpointId > 0) {
            return { type: 'movement', token };
        }

        return null;
    };

    const setStatus = (message, tone = '') => {
        if (!statusNode) return;
        statusNode.textContent = message;
        statusNode.className = 'scan-status' + (tone ? ' ' + tone : '');
    };

    const updateClock = () => {
        if (!clockNode) return;
        clockNode.textContent = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
    };

    const parseScannedValue = (rawValue) => {
        const value = String(rawValue || '').trim();
        if (!value) return null;

        try {
            const parsed = new URL(value, window.location.origin);
            const path = parsed.pathname;
            
            // Match /programs/{id}/qr-checkin or /student/programs/{id}
            const programMatch = path.match(/\/(?:student\/)?programs\/(\d+)/i);
            if (programMatch) {
                return {
                    type: 'program',
                    programId: parseInt(programMatch[1], 10),
                    token: parsed.searchParams.get('t') || parsed.searchParams.get('token') || parsed.searchParams.get('qr_token') || ''
                };
            }

            // Match Food Bank claim URL
            if (path.includes('/foodbank') || value.includes('foodbank')) {
                return {
                    type: 'foodbank'
                };
            }

            const token = parsed.searchParams.get('token');
            if (token) {
                return parseTokenByPayload(token) || { type: 'movement', token: token };
            }
        } catch (error) {}

        if (value.toLowerCase().includes('foodbank')) {
            return { type: 'foodbank' };
        }

        const parsedToken = parseTokenByPayload(value);
        if (parsedToken) {
            return parsedToken;
        }

        if (value.length >= 12) {
            return { type: 'movement', token: value };
        }

        return null;
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

    let doneAutoDismissTimer = null;

    const hideDoneModal = () => {
        if (doneAutoDismissTimer) {
            clearTimeout(doneAutoDismissTimer);
            doneAutoDismissTimer = null;
        }
        doneModal.classList.remove('show');
        doneModal.setAttribute('aria-hidden', 'true');
        setTimeout(() => {
            if (!doneModal.classList.contains('show')) {
                doneModal.style.display = 'none';
            }
        }, 300);
        isProcessing = false;
        startScanner();
    };

    const showDoneModal = (data) => {
        doneTitle.textContent = 'DONE';

        if (navigator.vibrate) {
            try { navigator.vibrate([100, 50, 100]); } catch (e) {}
        }

        doneModal.style.display = 'flex';
        doneModal.removeAttribute('aria-hidden');
        requestAnimationFrame(() => {
            doneModal.classList.add('show');
        });

        if (doneAutoDismissTimer) clearTimeout(doneAutoDismissTimer);
        doneAutoDismissTimer = setTimeout(() => {
            hideDoneModal();
        }, 2200);
    };

    const handleFoodBankClaim = async () => {
        setStatus(@json(__('QR Food Bank Dikesan. Merekodkan penebusan...')), 'ok');
        stopScanner();

        try {
            const res = await fetch(@json(route('student.foodbank.quick_scan')), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await res.json();
            if (res.ok && data.success) {
                showDoneModal(data);
            } else {
                setStatus(data.message || @json(__('Ralat semasa merekod penebusan Food Bank.')), 'danger');
                setTimeout(() => {
                    isProcessing = false;
                    startScanner();
                }, 2800);
            }
        } catch (err) {
            setStatus(@json(__('Ralat rangkaian. Sila cuba lagi.')), 'danger');
            setTimeout(() => {
                isProcessing = false;
                startScanner();
            }, 2500);
        }
    };

    const handleProgramAttendance = async (programId, token) => {
        setStatus(@json(__('QR Program Dikesan. Merekodkan kehadiran...')), 'ok');
        stopScanner();

        let coords = { latitude: null, longitude: null, accuracy: null, capturedAt: null };
        if (navigator.geolocation) {
            try {
                const pos = await new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, {
                        enableHighAccuracy: true,
                        timeout: 5000,
                        maximumAge: 0
                    });
                });
                coords = {
                    latitude: pos.coords.latitude,
                    longitude: pos.coords.longitude,
                    accuracy: pos.coords.accuracy,
                    capturedAt: new Date(pos.timestamp).toISOString()
                };
            } catch (e) {}
        }

        try {
            const res = await fetch(`/student/programs/${programId}/quick-scan`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    qr_token: token,
                    latitude: coords.latitude,
                    longitude: coords.longitude,
                    location_accuracy_m: coords.accuracy,
                    location_captured_at: coords.capturedAt
                })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                showDoneModal(data);
            } else {
                setStatus(data.message || @json(__('Ralat semasa merekod kehadiran.')), 'danger');
                setTimeout(() => {
                    isProcessing = false;
                    startScanner();
                }, 2800);
            }
        } catch (err) {
            setStatus(@json(__('Ralat rangkaian. Sila cuba lagi.')), 'danger');
            setTimeout(() => {
                isProcessing = false;
                startScanner();
            }, 2500);
        }
    };

    const handleDetectedValue = (rawValue) => {
        if (isProcessing) return;
        const parsed = parseScannedValue(rawValue);

        if (!parsed) {
            setStatus(@json(__('QR code detected, but it does not contain a valid token.')), 'danger');
            return;
        }

        isProcessing = true;

        if (parsed.type === 'foodbank') {
            handleFoodBankClaim();
            return;
        }

        if (parsed.type === 'program') {
            handleProgramAttendance(parsed.programId, parsed.token);
            return;
        }

        setStatus(@json(__('QR detected. Verifying movement pass...')), 'ok');
        stopScanner();
        targetUrl.searchParams.set('token', parsed.token);
        window.location.assign(targetUrl.toString());
    };

    const scanFrame = async () => {
        if (!isScanning || !video || video.readyState < 2 || isProcessing) return;

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
            isProcessing = false;
            setStatus(@json(__('Camera is ready. Point it at the guard house or program QR code.')), 'ok');
            scanTimer = window.setInterval(scanFrame, 350);
        } catch (error) {
            setStatus(@json(__('Camera access failed. Allow camera permission and reopen Scan QR.')), 'danger');
        }
    };

    btnDoneClose?.addEventListener('click', hideDoneModal);
    doneModal?.addEventListener('click', (e) => {
        if (e.target === doneModal) hideDoneModal();
    });

    updateClock();
    window.setInterval(updateClock, 1000);
    window.addEventListener('beforeunload', stopScanner);

    const initialToken = new URLSearchParams(window.location.search).get('token')
        || new URLSearchParams(window.location.search).get('t')
        || new URLSearchParams(window.location.search).get('qr_token');
    if (initialToken) {
        handleDetectedValue(initialToken);
    } else {
        startScanner();
    }
})();
</script>
@endpush
