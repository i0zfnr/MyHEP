@extends('layouts.app')

@section('title', 'Scan Laptop QR')
@section('header')<h2 style="margin:0;font-size:1rem;font-weight:700;">Scan Laptop QR</h2>@endsection

@push('styles')
<style>
    body.student-scan-mode :is(.topbar,.page-header,.sidebar,.sb-overlay,.app-footer),
    body.student-scan-mode > :is(.mobile-bottom-nav,.mobile-more-sheet,.mobile-more-backdrop,.header-user-menu--mobile,.header-user-backdrop) { display:none!important; }
    body.student-scan-mode .page-body { padding:0!important; background:#0c0907!important; }
    body.student-scan-mode .main-wrap { overflow:hidden!important; }
    .staff-scan-page{position:fixed;inset:0;z-index:900;overflow:hidden;background:#0c0907;color:#fffaf5}
    .staff-scan-video{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;background:#0c0907}
    .staff-scan-vignette{position:absolute;inset:0;pointer-events:none;background:linear-gradient(180deg,rgba(0,0,0,.5),transparent 22%,transparent 62%,rgba(0,0,0,.72)),radial-gradient(circle at center,transparent 32%,rgba(0,0,0,.4) 100%)}
    .staff-scan-topbar{position:absolute;top:calc(1rem + env(safe-area-inset-top,0px));left:calc(1rem + env(safe-area-inset-left,0px));right:calc(1rem + env(safe-area-inset-right,0px));z-index:4;display:flex;align-items:center;justify-content:space-between;gap:1rem}
    .staff-scan-icon{width:44px;height:44px;display:grid;place-items:center;border:1px solid rgba(255,255,255,.14);border-radius:999px;background:rgba(0,0,0,.28);color:#fff;text-decoration:none;box-shadow:inset 0 1px 0 rgba(255,255,255,.16);backdrop-filter:blur(18px) saturate(150%);-webkit-backdrop-filter:blur(18px) saturate(150%)}
    .staff-scan-icon svg{width:22px;height:22px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}
    .staff-scan-clock{font-size:.82rem;font-weight:800;text-shadow:0 2px 10px rgba(0,0,0,.36)}
    .staff-scan-frame{position:absolute;left:50%;top:44%;z-index:3;width:min(64vw,320px);aspect-ratio:1;transform:translate(-50%,-50%);pointer-events:none}
    .staff-scan-frame span{position:absolute;width:56px;height:56px;border-color:rgba(255,255,255,.96)}
    .staff-scan-frame span:nth-child(1){top:0;left:0;border-top:5px solid;border-left:5px solid;border-radius:18px 0 0}
    .staff-scan-frame span:nth-child(2){top:0;right:0;border-top:5px solid;border-right:5px solid;border-radius:0 18px 0 0}
    .staff-scan-frame span:nth-child(3){right:0;bottom:0;border-right:5px solid;border-bottom:5px solid;border-radius:0 0 18px}
    .staff-scan-frame span:nth-child(4){bottom:0;left:0;border-bottom:5px solid;border-left:5px solid;border-radius:0 0 0 18px}
    .staff-scan-bottom{position:absolute;left:calc(1rem + env(safe-area-inset-left,0px));right:calc(1rem + env(safe-area-inset-right,0px));bottom:calc(1rem + env(safe-area-inset-bottom,0px));z-index:4;display:grid;gap:.7rem}
    .staff-scan-status{justify-self:center;width:min(100%,430px);padding:.8rem 1rem;border:1px solid rgba(255,255,255,.18);border-radius:18px;background:rgba(12,9,7,.54);color:#fff3df;font-size:.82rem;font-weight:700;line-height:1.45;text-align:center;box-shadow:0 18px 44px rgba(0,0,0,.24),inset 0 1px 0 rgba(255,255,255,.12);backdrop-filter:blur(22px) saturate(150%);-webkit-backdrop-filter:blur(22px) saturate(150%)}
    .staff-scan-status.success{border-color:rgba(110,231,164,.48);color:#cffbdd}.staff-scan-status.error{border-color:rgba(252,165,165,.5);color:#fecaca}
    .staff-scan-mode{width:min(100%,430px);justify-self:center;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.35rem;padding:.35rem;border-radius:999px;background:rgba(12,9,7,.56);box-shadow:0 18px 44px rgba(0,0,0,.25),inset 0 1px 0 rgba(255,255,255,.14);backdrop-filter:blur(24px) saturate(155%);-webkit-backdrop-filter:blur(24px) saturate(155%)}
    .staff-scan-mode span{min-height:42px;display:inline-flex;align-items:center;justify-content:center;gap:.4rem;border-radius:999px;color:rgba(255,255,255,.72);font-size:.8rem;font-weight:850;text-align:center}.staff-scan-mode .active{background:#fffaf5;color:#7d582f}
    .staff-token-confirm{width:min(100%,430px);min-height:46px;justify-self:center;border:1px solid rgba(255,255,255,.3);border-radius:14px;background:#fffaf5;color:#5b3e22;font:inherit;font-weight:850;cursor:pointer}.staff-token-confirm[hidden]{display:none}
    .staff-scan-canvas{display:none}
</style>
@endpush

@section('content')
<div class="staff-scan-page">
    <video class="staff-scan-video" id="laptopScannerVideo" playsinline muted autoplay aria-label="Laptop QR scanner camera preview"></video>
    <canvas class="staff-scan-canvas" id="laptopScannerCanvas"></canvas>
    <div class="staff-scan-vignette" aria-hidden="true"></div>

    <div class="staff-scan-topbar">
        <a href="{{ route('admin.dashboard') }}" class="staff-scan-icon" aria-label="Close scanner">
            <svg viewBox="0 0 24 24"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </a>
        <div class="staff-scan-clock" id="staffScanClock">--:--</div>
        <span class="staff-scan-icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M13 2 3 14h8l-1 8 11-14h-8z"/></svg></span>
    </div>

    <div class="staff-scan-frame" aria-hidden="true"><span></span><span></span><span></span><span></span></div>

    <div class="staff-scan-bottom">
        <div class="staff-scan-status" id="laptopScanResult" role="status" aria-live="polite">Opening camera. Point it at a JHEP laptop QR code.</div>
        <button type="button" class="staff-token-confirm" id="confirmLaptopToken" {{ $initialToken === '' ? 'hidden' : '' }}>Confirm Laptop Scan</button>
        <div class="staff-scan-mode" aria-label="Scan mode">
            <span class="active">Scan QR</span>
            <span>JHEP Staff · {{ $currentLoans->count() }} active</span>
        </div>
    </div>
</div>
<span class="sr-only">Borrow or return a laptop</span>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
(() => {
    const video = document.getElementById('laptopScannerVideo');
    const canvas = document.getElementById('laptopScannerCanvas');
    const context = canvas?.getContext('2d', { willReadFrequently: true });
    const result = document.getElementById('laptopScanResult');
    const confirmButton = document.getElementById('confirmLaptopToken');
    const clock = document.getElementById('staffScanClock');
    const endpoint = @json(route('admin.laptops.scan.process'));
    const initialToken = @json($initialToken);
    let stream = null;
    let detector = null;
    let timer = null;
    let busy = false;

    const show = (message, tone = '') => {
        result.textContent = message;
        result.className = `staff-scan-status${tone ? ` ${tone}` : ''}`;
    };
    const updateClock = () => { clock.textContent = new Date().toLocaleTimeString([], { hour:'numeric', minute:'2-digit' }); };
    const extractToken = (value) => {
        const raw = String(value || '').trim();
        try {
            const url = new URL(raw, window.location.origin);
            return url.searchParams.get('token') || url.pathname.match(/[0-9a-f]{8}-(?:[0-9a-f]{4}-){3}[0-9a-f]{12}/i)?.[0] || raw;
        } catch (_) { return raw; }
    };
    const stop = () => {
        if (timer) window.clearInterval(timer);
        stream?.getTracks().forEach(track => track.stop());
        timer = null; stream = null;
        if (video) video.srcObject = null;
    };
    const submit = async (value) => {
        if (busy) return;
        busy = true; stop(); confirmButton.hidden = true;
        show('Recording laptop activity…', 'success');
        try {
            const response = await fetch(endpoint, { method:'POST', credentials:'same-origin', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())}, body:JSON.stringify({token:extractToken(value)}) });
            const data = await response.json();
            show(data.message || 'Unable to process this QR code.', response.ok ? 'success' : 'error');
            if (response.ok) window.setTimeout(() => window.location.reload(), 1400);
            else window.setTimeout(start, 1800);
        } catch (_) {
            show('The scan could not be saved. Check your connection and try again.', 'error');
            window.setTimeout(start, 1800);
        } finally { busy = false; }
    };
    const scanFrame = async () => {
        if (!stream || busy || video.readyState < 2) return;
        try {
            if (detector) {
                const codes = await detector.detect(video);
                if (codes[0]?.rawValue) await submit(codes[0].rawValue);
                return;
            }
            if (!window.jsQR || !context) return;
            const width = video.videoWidth; const height = video.videoHeight;
            if (!width || !height) return;
            canvas.width = width; canvas.height = height;
            context.drawImage(video, 0, 0, width, height);
            const image = context.getImageData(0, 0, width, height);
            const code = window.jsQR(image.data, image.width, image.height, { inversionAttempts:'dontInvert' });
            if (code?.data) await submit(code.data);
        } catch (_) { show('Keep the QR code steady inside the frame.', 'error'); }
    };
    const requestCamera = async () => {
        const options = [
            {video:{facingMode:{exact:'environment'}},audio:false},
            {video:{facingMode:{ideal:'environment'}},audio:false},
            {video:true,audio:false},
        ];
        let lastError;
        for (const constraints of options) { try { return await navigator.mediaDevices.getUserMedia(constraints); } catch (error) { lastError = error; } }
        throw lastError;
    };
    const start = async () => {
        if (!navigator.mediaDevices?.getUserMedia) { show('Camera scanning requires HTTPS or the installed PWA.', 'error'); return; }
        try {
            if ('BarcodeDetector' in window) detector = new BarcodeDetector({ formats:['qr_code'] });
            stream = await requestCamera();
            video.srcObject = stream; await video.play();
            show('Camera is ready. Point it at a JHEP laptop QR code.', 'success');
            timer = window.setInterval(scanFrame, 350);
        } catch (_) { show('Camera access failed. Allow camera permission and reopen Scan QR.', 'error'); }
    };

    confirmButton?.addEventListener('click', () => submit(initialToken));
    updateClock(); window.setInterval(updateClock, 1000);
    window.addEventListener('pagehide', stop);
    document.addEventListener('visibilitychange', () => { if (document.hidden) stop(); });
    start();
})();
</script>
@endpush
