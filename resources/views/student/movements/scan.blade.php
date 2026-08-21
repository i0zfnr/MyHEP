@extends('layouts.app')

@section('title', __('Scan QR'))

@section('header')
    <h2 style="margin:0;font-size:1rem;font-weight:700;">{{ __('Scan QR') }}</h2>
@endsection

@push('styles')
<style>
    body.student-scan-mode :is(
        .topbar,
        .page-header,
        .sidebar,
        .sb-overlay,
        .app-footer
    ),
    body.student-scan-mode > :is(
        .mobile-bottom-nav,
        .mobile-more-sheet,
        .mobile-more-backdrop,
        .header-user-menu--mobile,
        .header-user-backdrop
    ) {
        display: none !important;
    }

    body.student-scan-mode .page-body {
        padding: 0 !important;
        background: #0c0907 !important;
    }

    body.student-scan-mode .main-wrap {
        overflow: hidden !important;
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
        top: calc(1rem + env(safe-area-inset-top, 0px));
        left: calc(1rem + env(safe-area-inset-left, 0px));
        right: calc(1rem + env(safe-area-inset-right, 0px));
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
        left: calc(1rem + env(safe-area-inset-left, 0px));
        right: calc(1rem + env(safe-area-inset-right, 0px));
        bottom: calc(1rem + env(safe-area-inset-bottom, 0px));
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

    body.student-scan-mode .page-body .scan-status.ok {
        background: rgba(12, 9, 7, .48) !important;
        border-color: rgba(110, 231, 164, .45) !important;
        color: #cffbdd !important;
    }
    body.student-scan-mode .page-body .scan-status.danger {
        background: rgba(12, 9, 7, .48) !important;
        border-color: rgba(252, 165, 165, .48) !important;
        color: #fecaca !important;
    }

    .scan-mode {
        width: min(100%, 430px);
        justify-self: center;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: .35rem;
        padding: .35rem;
        border-radius: 999px;
        background: rgba(12, 9, 7, .52);
        box-shadow: 0 18px 44px rgba(0,0,0,.25), inset 0 1px 0 rgba(255,255,255,.14);
        backdrop-filter: blur(24px) saturate(155%);
        -webkit-backdrop-filter: blur(24px) saturate(155%);
    }

    .scan-mode span {
        min-width: 0;
        min-height: 42px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        border-radius: 999px;
        color: rgba(255,255,255,.72);
        font-size: .84rem;
        font-weight: 850;
        text-align: center;
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

    /* DONE KEY IN Modal */
    .done-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 1000;
        background: rgba(12, 9, 7, 0.88);
        backdrop-filter: blur(24px) saturate(180%);
        -webkit-backdrop-filter: blur(24px) saturate(180%);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    .done-modal-backdrop.show {
        opacity: 1;
        pointer-events: auto;
    }

    .done-modal-card {
        background: linear-gradient(165deg, rgba(38, 32, 26, 0.95), rgba(20, 17, 14, 0.98));
        border: 1px solid rgba(214, 178, 110, 0.35);
        border-radius: 28px;
        max-width: 420px;
        width: 100%;
        padding: 2rem 1.5rem 1.75rem;
        text-align: center;
        box-shadow: 0 25px 60px rgba(0,0,0,0.7), 0 0 50px rgba(212, 175, 55, 0.18);
        transform: scale(0.9) translateY(20px);
        transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .done-modal-backdrop.show .done-modal-card {
        transform: scale(1) translateY(0);
    }

    .done-badge-icon {
        width: 76px;
        height: 76px;
        border-radius: 50%;
        background: linear-gradient(135deg, #10b981, #059669);
        display: grid;
        place-items: center;
        margin: 0 auto 1.25rem;
        box-shadow: 0 0 35px rgba(16, 185, 129, 0.45);
        color: #fff;
        animation: popCheck 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    @keyframes popCheck {
        0% { transform: scale(0); }
        80% { transform: scale(1.15); }
        100% { transform: scale(1); }
    }

    .done-title {
        font-size: 1.75rem;
        font-weight: 900;
        letter-spacing: -0.02em;
        color: #fef08a;
        text-shadow: 0 2px 14px rgba(234, 179, 8, 0.35);
        margin-bottom: 0.35rem;
        text-transform: uppercase;
    }

    .done-subtitle {
        font-size: 0.85rem;
        font-weight: 700;
        color: #a8a29e;
        margin-bottom: 1.25rem;
    }

    .done-student-box {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 18px;
        padding: 1rem 1.15rem;
        text-align: left;
        margin-bottom: 1rem;
    }

    .done-student-name {
        font-size: 1.05rem;
        font-weight: 800;
        color: #fff;
        margin-bottom: 0.2rem;
    }

    .done-student-meta {
        font-size: 0.82rem;
        color: #d6b26e;
        font-weight: 600;
    }

    .done-points-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.9rem;
        border-radius: 999px;
        background: rgba(234, 179, 8, 0.14);
        border: 1px solid rgba(234, 179, 8, 0.35);
        color: #fde047;
        font-size: 0.82rem;
        font-weight: 800;
        margin-bottom: 1.5rem;
    }

    .done-actions {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .btn-done-primary {
        background: linear-gradient(135deg, #d4af37, #926b1d);
        color: #1c1917;
        font-weight: 800;
        font-size: 0.92rem;
        padding: 0.85rem 1.25rem;
        border-radius: 14px;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        box-shadow: 0 4px 18px rgba(212, 175, 55, 0.3);
    }

    .btn-done-secondary {
        background: rgba(255, 255, 255, 0.08);
        color: #fafaf9;
        font-weight: 700;
        font-size: 0.88rem;
        padding: 0.75rem 1.25rem;
        border-radius: 14px;
        border: 1px solid rgba(255, 255, 255, 0.12);
        cursor: pointer;
    }
</style>
@endpush

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

<!-- DONE KEY IN Pop-up Modal -->
<div class="done-modal-backdrop" id="doneModal">
    <div class="done-modal-card">
        <div class="done-badge-icon">
            <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        </div>

        <h3 class="done-title" id="doneTitle">{{ __('DONE KEY IN') }}</h3>
        <p class="done-subtitle" id="doneProgramTitle">{{ __('Kehadiran Berjaya Direkodkan!') }}</p>

        <div class="done-student-box">
            <div class="done-student-name" id="doneStudentName">--</div>
            <div class="done-student-meta" id="doneStudentMeta">--</div>
        </div>

        <div class="done-points-pill" id="donePointsPill">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            <span id="donePointsText">+10 Mata Merit Diperoleh</span>
        </div>

        <div class="done-actions">
            <a href="#" class="btn-done-primary" id="btnDoneSurvey" style="display: none;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.5L19 7.5V19a2 2 0 0 1-2 2z"/></svg>
                <span>{{ __('Jawab Soal Selidik Sekarang') }}</span>
            </a>
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

            const token = parsed.searchParams.get('token');
            if (token) {
                return { type: 'movement', token: token };
            }
        } catch (error) {}

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

    const showDoneModal = (data) => {
        doneTitle.textContent = data.message || 'DONE KEY IN';
        doneProgramTitle.textContent = data.program?.title || @json(__('Kehadiran Berjaya Direkodkan!'));
        doneStudentName.textContent = data.student?.full_name || '';
        doneStudentMeta.textContent = (data.student?.matric_no || '') + ' • ' + (data.student?.program || '');
        
        const points = data.program?.points || 0;
        donePointsText.textContent = points > 0 ? `+${points} {{ __('Mata Merit Diperoleh') }}` : `{{ __('Kehadiran Direkodkan') }}`;

        if (data.program?.has_survey && data.program?.survey_url) {
            btnDoneSurvey.href = data.program.survey_url;
            btnDoneSurvey.style.display = 'flex';
        } else {
            btnDoneSurvey.style.display = 'none';
        }

        if (navigator.vibrate) {
            try { navigator.vibrate([100, 50, 100]); } catch (e) {}
        }

        doneModal.classList.add('show');
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

    btnDoneClose?.addEventListener('click', () => {
        doneModal.classList.remove('show');
        isProcessing = false;
        startScanner();
    });

    updateClock();
    window.setInterval(updateClock, 1000);
    window.addEventListener('beforeunload', stopScanner);
    startScanner();
})();
</script>
@endpush
