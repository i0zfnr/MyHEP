@extends('layouts.app')

@section('title', 'Scan Laptop QR')
@section('header')<h2 style="margin:0;font-size:1rem;font-weight:700;">Scan Laptop QR</h2>@endsection

@push('styles')
<style>
    .staff-scan-wrap{max-width:680px;margin:0 auto;display:grid;gap:1rem}.staff-scan-hero{padding:1.2rem;border-radius:18px;background:linear-gradient(135deg,#3b291d,#765237 60%,#a77950);color:#fff;box-shadow:var(--glass-shadow)}.staff-scan-hero h1{margin:.25rem 0;font-size:1.45rem}.staff-scan-hero p{margin:0;color:rgba(255,255,255,.76);line-height:1.5}.staff-scan-card{padding:1rem;border:1px solid var(--glass-line);border-radius:18px;background:var(--glass-bg-strong);box-shadow:var(--glass-shadow)}.scanner-stage{position:relative;overflow:hidden;aspect-ratio:4/3;border-radius:15px;background:#15110e;display:grid;place-items:center}.scanner-stage video{width:100%;height:100%;object-fit:cover}.scanner-frame{position:absolute;width:62%;aspect-ratio:1;border:3px solid #f2d5b5;border-radius:18px;box-shadow:0 0 0 999px rgba(0,0,0,.32);pointer-events:none}.scanner-line{position:absolute;left:9%;right:9%;top:50%;height:2px;background:#f2d5b5;box-shadow:0 0 10px #f2d5b5}.scan-actions{display:grid;grid-template-columns:1fr 1fr;gap:.65rem;margin-top:.8rem}.scan-btn{min-height:46px;border:1px solid var(--border);border-radius:11px;background:var(--surface);color:var(--text);font:inherit;font-weight:800;cursor:pointer}.scan-btn.primary{background:var(--primary-dark);border-color:var(--primary-dark);color:var(--surface)}body[data-theme="dark"] .scan-btn.primary{background:var(--primary);color:#21170f}.scan-result{display:none;margin-top:.8rem;padding:.85rem;border-radius:12px;font-weight:700;font-size:.82rem}.scan-result.show{display:block}.scan-result.success{background:#e7f4ee;color:#287352}.scan-result.error{background:var(--danger-light);color:var(--danger)}body[data-theme="dark"] .scan-result.success{background:rgba(46,160,112,.18);color:#8ee0bb}.scan-help{margin:.75rem 0 0;color:var(--text-muted);font-size:.74rem;line-height:1.5}.current-loans h2{margin:0 0 .7rem;color:var(--text);font-size:1rem}.loan-chip{display:flex;justify-content:space-between;gap:.7rem;padding:.75rem 0;border-top:1px solid var(--glass-line);color:var(--text);font-size:.78rem}.loan-chip small{color:var(--text-muted)}.manual-confirm{display:none}.manual-confirm.show{display:block}@media(max-width:480px){.staff-scan-card{padding:.75rem}.scanner-stage{aspect-ratio:3/4}.scan-actions{grid-template-columns:1fr}}
</style>
@endpush

@section('content')
<div class="staff-scan-wrap">
    <section class="staff-scan-hero"><span style="font-size:.67rem;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:#f2d5b5">JHEP asset scanner</span><h1>Borrow or return a laptop</h1><p>Scan once when taking a laptop. Scan the same QR again when returning it. No late-return penalties are applied.</p></section>
    <section class="staff-scan-card">
        <div class="scanner-stage"><video id="laptopScannerVideo" playsinline muted></video><div class="scanner-frame"><span class="scanner-line"></span></div></div>
        <canvas id="laptopScannerCanvas" hidden></canvas>
        <div class="scan-actions"><button type="button" class="scan-btn primary" id="startLaptopScanner">Start Camera</button><button type="button" class="scan-btn" id="stopLaptopScanner" disabled>Stop Camera</button></div>
        <button type="button" class="scan-btn primary manual-confirm {{ $initialToken !== '' ? 'show' : '' }}" id="confirmLaptopToken" style="width:100%;margin-top:.7rem">Confirm Laptop Scan</button>
        <div class="scan-result" id="laptopScanResult" role="status" aria-live="polite"></div>
        <p class="scan-help">Camera access requires HTTPS or localhost. The scanner stops after reading a QR to prevent duplicate transactions.</p>
    </section>
    <section class="staff-scan-card current-loans"><h2>Your current laptops</h2>@forelse($currentLoans as $loan)<div class="loan-chip"><span><strong>{{ $loan->name }}</strong><br><small>{{ $loan->asset_code }}</small></span><small>{{ \Illuminate\Support\Carbon::parse($loan->borrowed_at)->format('d M, h:i A') }}</small></div>@empty<p class="scan-help">You are not currently borrowing a JHEP laptop.</p>@endforelse</section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/jsqr@1.4.0/dist/jsQR.min.js"></script>
<script>
(() => {
    const video = document.getElementById('laptopScannerVideo');
    const canvas = document.getElementById('laptopScannerCanvas');
    const context = canvas.getContext('2d', { willReadFrequently: true });
    const startButton = document.getElementById('startLaptopScanner');
    const stopButton = document.getElementById('stopLaptopScanner');
    const confirmButton = document.getElementById('confirmLaptopToken');
    const result = document.getElementById('laptopScanResult');
    const endpoint = @json(route('admin.laptops.scan.process'));
    let token = @json($initialToken);
    let stream = null;
    let frame = null;
    let busy = false;

    function show(message, type) { result.textContent = message; result.className = `scan-result show ${type}`; }
    function extractToken(value) {
        try { const url = new URL(value, window.location.origin); return url.searchParams.get('token') || value; } catch (_) { return value; }
    }
    function stop() {
        if (frame) cancelAnimationFrame(frame);
        if (stream) stream.getTracks().forEach(track => track.stop());
        frame = null; stream = null; video.srcObject = null; startButton.disabled = false; stopButton.disabled = true;
    }
    async function submit(value) {
        if (busy) return;
        busy = true; stop(); show('Recording laptop activity…', 'success');
        try {
            const response = await fetch(endpoint, { method:'POST', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())}, body:JSON.stringify({token:extractToken(value)}) });
            const data = await response.json();
            show(data.message || 'Unable to process this QR code.', response.ok ? 'success' : 'error');
            if (response.ok) setTimeout(() => window.location.reload(), 1200);
        } catch (_) { show('The scan could not be saved. Check your connection and try again.', 'error'); }
        finally { busy = false; }
    }
    async function tick() {
        if (!stream || busy) return;
        if (video.readyState >= 2) {
            canvas.width = video.videoWidth; canvas.height = video.videoHeight;
            context.drawImage(video, 0, 0, canvas.width, canvas.height);
            const image = context.getImageData(0, 0, canvas.width, canvas.height);
            const code = window.jsQR ? window.jsQR(image.data, image.width, image.height, { inversionAttempts:'dontInvert' }) : null;
            if (code?.data) { await submit(code.data); return; }
        }
        frame = requestAnimationFrame(tick);
    }
    startButton.addEventListener('click', async () => {
        try { stream = await navigator.mediaDevices.getUserMedia({video:{facingMode:{ideal:'environment'}},audio:false}); video.srcObject = stream; await video.play(); startButton.disabled=true; stopButton.disabled=false; show('Camera ready. Point it at a laptop QR code.', 'success'); tick(); }
        catch (_) { show('Camera access was not available. Allow camera permission and try again.', 'error'); }
    });
    stopButton.addEventListener('click', stop);
    confirmButton.addEventListener('click', () => submit(token));
    window.addEventListener('pagehide', stop);
})();
</script>
@endpush
