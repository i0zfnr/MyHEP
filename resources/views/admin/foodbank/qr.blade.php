<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Poster QR Kod Food Bank Siswa - Politeknik Besut') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#302218; --muted:#765f4d; --gold:#a8783e; --line:#e5d8c7; --paper:#fffdf8; --soft:#f7f0e6; }
        * { box-sizing:border-box; }
        body { display:flex; flex-direction:column; align-items:center; gap:16px; min-height:100vh; margin:0; padding:24px; background:#eae5dc; color:var(--ink); font-family:'Plus Jakarta Sans',Arial,sans-serif; }
        .no-print-bar { display:flex; align-items:center; justify-content:space-between; gap:16px; width:min(100%,210mm); padding:12px 16px; border:1px solid var(--line); border-radius:12px; background:#fff; box-shadow:0 8px 24px rgba(48,34,24,.08); }
        .no-print-bar a,.no-print-bar button { display:inline-flex; align-items:center; justify-content:center; gap:8px; min-height:42px; font:inherit; font-size:.82rem; font-weight:700; text-decoration:none; }
        .no-print-bar svg { width:18px; height:18px; flex:none; }
        .back-btn { color:var(--ink); }
        .back-btn:hover { color:var(--gold); }
        .print-btn { padding:0 16px; border:1px solid #493220; border-radius:9px; background:#493220; color:#fff; cursor:pointer; }
        .print-btn:hover { background:#684729; }
        .poster-upload-btn,.poster-download-btn { display:inline-flex; align-items:center; justify-content:center; min-height:42px; padding:0 14px; border:1px solid var(--line); border-radius:9px; background:#fff; color:var(--ink); font:inherit; font-size:.82rem; font-weight:750; cursor:pointer; }
        .poster-download-btn { border-color:#28684b; background:#28684b; color:#fff; }
        .poster-download-btn:disabled { opacity:.45; cursor:not-allowed; }
        .no-print-bar { flex-wrap:wrap; }
        .poster-upload-input { position:absolute; width:1px; height:1px; overflow:hidden; clip:rect(0,0,0,0); white-space:nowrap; }
        .poster-upload-status { flex:1 1 100%; color:var(--muted); font-size:.76rem; }
        .poster-upload-status[data-state="error"] { color:#a12424; }
        .poster-upload-status[data-state="success"] { color:#17643d; }
        .poster-edit-panel { display:none; width:min(100%,210mm); padding:14px 16px; border:1px solid var(--line); border-radius:12px; background:#fff; box-shadow:0 8px 24px rgba(48,34,24,.08); }
        .poster-edit-panel h2 { margin:0 0 4px; font-size:.95rem; }
        .poster-edit-panel p { margin:0 0 12px; color:var(--muted); font-size:.75rem; }
        .poster-adjustments { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:12px; }
        .poster-adjustments label { display:grid; gap:4px; color:var(--muted); font-size:.7rem; font-weight:750; }
        .poster-adjustments input { width:100%; accent-color:#28684b; }
        .uploaded-poster-sheet { display:none; position:relative; width:min(100%,210mm); overflow:hidden; background:#fff; box-shadow:0 20px 60px rgba(48,34,24,.16); }
        .uploaded-poster-sheet canvas { display:block; width:100%; height:auto; }
        .uploaded-poster-sheet .qr-overlay { position:absolute; z-index:2; width:24%; aspect-ratio:1; max-width:none; object-fit:contain; cursor:move; touch-action:none; user-select:none; -webkit-user-drag:none; }
        body.custom-poster-active .poster-sheet,body.custom-poster-active .poster-edit-panel,body.custom-poster-active .uploaded-poster-sheet { display:block; }
        body.custom-poster-active .poster-sheet { display:none; }
        body.poster-landscape .uploaded-poster-sheet { width:min(100%,297mm); }
        .no-print-bar :focus-visible { outline:3px solid var(--gold); outline-offset:3px; }

        .poster-sheet { position:relative; display:flex; flex-direction:column; width:210mm; min-height:297mm; overflow:hidden; padding:16mm 18mm 14mm; border:1px solid #d6c2aa; border-radius:8px; background:radial-gradient(circle at 100% 0,rgba(204,161,102,.12),transparent 31%),var(--paper); box-shadow:0 20px 60px rgba(48,34,24,.16); }
        .poster-sheet::before { position:absolute; inset:0 auto 0 0; width:4mm; background:linear-gradient(180deg,#694428,#bd8a4c 48%,#e6c68c); content:''; }
        .poster-header { display:flex; align-items:center; justify-content:space-between; gap:10mm; padding-bottom:5mm; border-bottom:1px solid var(--line); }
        .institution { display:flex; align-items:center; gap:3mm; min-width:0; }
        .institution img { display:block; width:16mm; height:16mm; object-fit:contain; }
        .institution-title { display:block; font-size:11.5pt; font-weight:800; line-height:1.25; letter-spacing:.035em; text-transform:uppercase; }
        .unit-title { display:block; margin-top:1.5mm; color:var(--muted); font-size:7.5pt; font-weight:700; line-height:1.35; text-transform:uppercase; }
        .poster-type { flex:none; padding:2mm 3mm; border:1px solid var(--line); border-radius:3mm; color:var(--muted); font-size:7.5pt; font-weight:800; letter-spacing:.11em; text-transform:uppercase; }

        .poster-hero { margin-top:16mm; text-align:center; }
        .eyebrow { display:inline-block; padding:2mm 4mm; border-radius:999px; background:var(--soft); color:#795126; font-size:8pt; font-weight:800; letter-spacing:.11em; text-transform:uppercase; }
        .hero-title { margin:5mm 0 0; font-size:36pt; font-weight:800; line-height:1.02; letter-spacing:-.055em; text-transform:uppercase; }
        .hero-title::after { display:block; width:18mm; height:1mm; margin:5mm auto 0; background:var(--gold); content:''; }
        .hero-subtitle { max-width:135mm; margin:5mm auto 0; color:var(--muted); font-size:11pt; font-weight:600; line-height:1.45; }

        .qr-section { display:flex; flex-direction:column; align-items:center; margin-top:12mm; text-align:center; }
        .qr-frame { padding:5mm; border:2px solid var(--ink); border-radius:5mm; background:#fff; box-shadow:0 8px 20px rgba(48,34,24,.08); }
        .qr-frame img { display:block; width:82mm; height:82mm; object-fit:contain; }
        .scan-instruction { width:min(100%,136mm); margin:7mm 0 0; padding:3.5mm 5mm; border-radius:3mm; background:#493220; color:#fff; font-size:10pt; font-weight:800; line-height:1.4; letter-spacing:.015em; text-transform:uppercase; }

        .steps-section { width:100%; margin-top:auto; padding-top:8mm; }
        .steps-heading { margin:0 0 4mm; color:var(--muted); font-size:8pt; font-weight:800; letter-spacing:.12em; text-align:center; text-transform:uppercase; }
        .steps-container { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:3mm; }
        .step-item { min-width:0; padding:4mm; border:1px solid var(--line); border-radius:3mm; background:rgba(247,240,230,.62); }
        .step-number { display:grid; place-items:center; width:7mm; height:7mm; margin-bottom:3mm; border-radius:50%; background:var(--gold); color:#fff; font-size:8pt; font-weight:800; }
        .step-text { margin:0; color:var(--muted); font-size:8pt; line-height:1.45; }
        .step-text strong { display:block; margin-bottom:1mm; color:var(--ink); font-size:8.5pt; line-height:1.25; }
        .poster-footer { display:flex; justify-content:space-between; align-items:baseline; gap:8mm; width:100%; margin-top:9mm; padding-top:4mm; border-top:1px solid var(--line); color:var(--muted); font-size:7.5pt; }
        .poster-footer strong { color:var(--ink); font-size:8pt; }

        @media (max-width:700px) {
            body { padding:12px; }
            .no-print-bar { flex-wrap:wrap; }
            .no-print-bar a,.no-print-bar button,.poster-upload-btn,.poster-download-btn { width:100%; }
            .poster-adjustments { grid-template-columns:1fr; gap:6px; }
            .poster-sheet { width:100%; min-height:0; padding:26px 22px 30px 26px; }
            .poster-sheet::before { width:5px; }
            .poster-header { align-items:flex-start; gap:8px; }
            .institution img { width:42px; height:42px; }
            .institution-title { font-size:.75rem; }
            .unit-title { font-size:.6rem; }
            .poster-type { font-size:.54rem; }
            .poster-hero { margin-top:45px; }
            .hero-title { font-size:clamp(2.15rem,9vw,3rem); }
            .hero-subtitle { font-size:.9rem; }
            .qr-section { margin-top:34px; }
            .qr-frame { padding:12px; }
            .qr-frame img { width:min(62vw,320px); height:min(62vw,320px); }
            .scan-instruction { margin-top:20px; font-size:.8rem; }
            .steps-section { margin-top:40px; }
            .steps-container { grid-template-columns:1fr; gap:8px; }
            .step-item { display:flex; align-items:center; gap:12px; padding:12px; }
            .step-number { flex:none; width:30px; height:30px; margin:0; }
            .poster-footer { flex-wrap:wrap; margin-top:30px; font-size:.65rem; }
        }

        @page { size:A4; margin:0; }
        @page poster-landscape { size:A4 landscape; margin:0; }
        @media print {
            html,body { width:210mm; height:297mm; }
            html:has(body.poster-landscape),body.poster-landscape { width:297mm; height:210mm; }
            body { display:block; min-height:0; padding:0; background:#fff; }
            .no-print-bar { display:none !important; }
            .poster-edit-panel { display:none !important; }
            body.custom-poster-active .poster-sheet { display:none !important; }
            body.custom-poster-active .uploaded-poster-sheet { display:block !important; width:auto; height:auto; max-width:100%; max-height:285mm; margin:0 auto; box-shadow:none; break-inside:avoid; }
            body.custom-poster-active .uploaded-poster-sheet canvas { width:auto; max-width:100%; max-height:285mm; margin:0 auto; }
            body.custom-poster-active .uploaded-poster-sheet .qr-overlay { width:var(--qr-size-print); }
            body.poster-landscape.custom-poster-active #uploadedPosterSheet { page:poster-landscape; }
            .poster-sheet { width:210mm; height:297mm; min-height:0; padding:16mm 18mm 14mm; border-radius:0; box-shadow:none; break-inside:avoid; }
            .poster-sheet::before { width:4mm; }
            .poster-header { align-items:center; gap:10mm; }
            .institution img { width:16mm; height:16mm; }
            .institution-title { font-size:11.5pt; }
            .unit-title,.poster-type { font-size:7.5pt; }
            .poster-hero { margin-top:16mm; }
            .hero-title { font-size:36pt; }
            .hero-subtitle { font-size:11pt; }
            .qr-section { margin-top:12mm; }
            .qr-frame { padding:5mm; }
            .qr-frame img { width:82mm; height:82mm; }
            .scan-instruction { margin-top:7mm; font-size:10pt; }
            .steps-section { margin-top:auto; }
            .steps-container { grid-template-columns:repeat(3,minmax(0,1fr)); gap:3mm; }
            .step-item { display:block; padding:4mm; }
            .step-number { width:7mm; height:7mm; margin:0 0 3mm; }
            .poster-footer { flex-wrap:nowrap; margin-top:9mm; font-size:7.5pt; }
            .poster-sheet,.poster-sheet * { print-color-adjust:exact; -webkit-print-color-adjust:exact; }
        }
    </style>
</head>
<body data-qr-target="{{ $staticQrUrl }}">
    <div class="no-print-bar">
        <a href="{{ route('admin.foodbank.index') }}" class="back-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            {{ __('Kembali ke Dashboard Food Bank') }}
        </a>
        <button type="button" class="print-btn" onclick="window.print()">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            {{ __('Cetak Poster Ini (A4)') }}
        </button>
        <label for="posterUpload" class="poster-upload-btn">{{ __('Muat Naik Poster Pelanggan') }}</label>
        <input id="posterUpload" class="poster-upload-input" type="file" accept="image/png,image/jpeg,image/webp,application/pdf,.pdf">
        <button id="downloadComposedPoster" type="button" class="poster-download-btn" disabled>{{ __('Muat Turun Poster Dengan QR') }}</button>
        <div id="posterUploadStatus" class="poster-upload-status" role="status" aria-live="polite">{{ __('Muat naik poster PNG, JPG, WebP atau PDF. Sistem akan mencari ruang kosong untuk QR.') }} {{ __('Fail diproses dalam pelayar dan tidak disimpan.') }}</div>
    </div>

    <section id="posterEditPanel" class="poster-edit-panel" aria-label="{{ __('Laraskan kedudukan QR') }}">
        <h2>{{ __('Semak kedudukan QR') }}</h2>
        <p>{{ __('QR dijana untuk borang Food Bank. Seret QR ke lokasi yang sesuai atau gunakan pelaras di bawah sebelum memuat turun atau mencetak.') }}</p>
        <div class="poster-adjustments">
            <label>{{ __('Kedudukan mendatar') }} <input id="qrPositionX" type="range" min="0" max="100" value="38"></label>
            <label>{{ __('Kedudukan menegak') }} <input id="qrPositionY" type="range" min="0" max="100" value="45"></label>
            <label>{{ __('Saiz QR') }} <input id="qrSize" type="range" min="10" max="60" value="24"></label>
        </div>
    </section>

    <section id="uploadedPosterSheet" class="uploaded-poster-sheet" aria-label="{{ __('Poster pelanggan dengan kod QR') }}">
        <canvas id="posterSourceCanvas"></canvas>
        <img id="qrOverlay" class="qr-overlay" alt="{{ __('QR Food Bank') }}" draggable="false">
    </section>

    <main class="poster-sheet">
        <header class="poster-header">
            <div class="institution">
                <img src="{{ asset('images/myhep-mark.png') }}" alt="" aria-hidden="true">
                <div>
                    <span class="institution-title">{{ __('POLITEKNIK BESUT TERENGGANU') }}</span>
                    <span class="unit-title">{{ __('HAL EHWAL PELAJAR · UNIT BIASISWA & KEBAJIKAN') }}</span>
                </div>
            </div>
            <span class="poster-type">MyHEP</span>
        </header>

        <section class="poster-hero" aria-labelledby="posterTitle">
            <span class="eyebrow">{{ __('Inisiatif Kebajikan Siswa') }}</span>
            <h1 id="posterTitle" class="hero-title">{{ __('FOOD BANK SISWA') }}</h1>
            <p class="hero-subtitle">{{ __('Percuma Untuk Pelajar Politeknik Besut') }}</p>
        </section>

        <section class="qr-section" aria-label="{{ __('IMBAS QR KOD INI SEBELUM MENGAMBIL MAKANAN') }}">
            <div class="qr-frame">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=450x450&amp;data={{ urlencode($staticQrUrl) }}" alt="{{ __('QR Kod Food Bank Siswa') }}" width="450" height="450" loading="eager" decoding="sync">
            </div>
            <p class="scan-instruction">{{ __('IMBAS QR KOD INI SEBELUM MENGAMBIL MAKANAN') }}</p>
        </section>

        <section class="steps-section" aria-label="{{ __('Cara Menebus Makanan Percuma') }}">
            <h2 class="steps-heading">{{ __('Cara Menebus Makanan Percuma') }}</h2>
            <div class="steps-container">
                <div class="step-item">
                    <span class="step-number" aria-hidden="true">1</span>
                    <p class="step-text"><strong>{{ __('Buka Kamera / App') }}</strong>{{ __('Buka kamera telefon anda atau gunakan pengimbas QR di portal MyHEP.') }}</p>
                </div>
                <div class="step-item">
                    <span class="step-number" aria-hidden="true">2</span>
                    <p class="step-text"><strong>{{ __('Isi Borang') }}</strong>{{ __('Masukkan nama, nombor matrik, jumlah item dan status B40.') }}</p>
                </div>
                <div class="step-item">
                    <span class="step-number" aria-hidden="true">3</span>
                    <p class="step-text"><strong>{{ __('Hantar & Tunjuk') }}</strong>{{ __('Hantar borang dan tunjukkan pengesahan kepada petugas.') }}</p>
                </div>
            </div>
        </section>

        <footer class="poster-footer">
            <strong>{{ __('Rezeki Dikongsi, Kasih Diabadi') }}</strong>
            <span>MyHEP <span aria-hidden="true">·</span> {{ __('POLITEKNIK BESUT TERENGGANU') }}</span>
        </footer>
    </main>
<script>
    window.foodBankPosterText = {
        qrError: @json(__('QR tidak dapat dijana. Sila muat semula halaman.')),
        fileSizeError: @json(__('Fail poster melebihi had 20 MB.')),
        reading: @json(__('Sedang membaca poster dan mencari ruang QR...')),
        fileTypeError: @json(__('Pilih fail PNG, JPG, WebP atau PDF.')),
        detected: @json(__('Ruang kosong yang sesuai dikesan. Seret QR jika kedudukan perlu dilaraskan.')),
        fallback: @json(__('Ruang QR tidak dapat dikenal pasti dengan yakin. QR diletakkan di tengah; seret atau laraskan kedudukannya.')),
        readError: @json(__('Poster tidak dapat dibaca. Cuba fail lain.')),
        downloadError: @json(__('Poster tidak dapat dimuat turun.')),
    };
</script>
@vite(['resources/js/foodbank-poster.js'])
</body>
</html>
