<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Poster QR Kod Food Bank Siswa - Politeknik Besut') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #e2e8f0;
            color: #0f172a;
            display: grid;
            place-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .no-print-bar {
            width: min(100%, 210mm);
            margin-bottom: 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 12px 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #1e40af;
            color: #fff;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 750;
            font-size: 0.9rem;
            cursor: pointer;
            border: none;
            text-decoration: none;
            transition: all 0.2s;
        }
        .print-btn:hover {
            background: #1d4ed8;
        }
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #475569;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
        }

        /* Official A4 Poster */
        .poster-sheet {
            width: 210mm;
            min-height: 297mm;
            background: #ffffff;
            padding: 16mm 18mm;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
            position: relative;
            overflow: hidden;
            border: 6px solid #1e40af;
        }

        .poster-sheet::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 12px;
            background: linear-gradient(90deg, #1e40af, #3b82f6, #60a5fa, #1e40af);
        }

        .poster-header {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 12px;
        }
        .institution-title {
            font-size: 13pt;
            font-weight: 800;
            letter-spacing: 0.08em;
            color: #1e293b;
            text-transform: uppercase;
        }
        .unit-title {
            font-size: 9.5pt;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-top: 2px;
        }

        .main-headline {
            margin-top: 14px;
        }
        .badge-pill {
            display: inline-block;
            background: #eff6ff;
            color: #1e40af;
            border: 1.5px solid #bfdbfe;
            padding: 4px 18px;
            border-radius: 999px;
            font-size: 10pt;
            font-weight: 800;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .hero-title {
            font-size: 32pt;
            font-weight: 900;
            line-height: 1.1;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: -0.02em;
        }
        .hero-subtitle {
            font-size: 12pt;
            font-weight: 700;
            color: #059669;
            margin-top: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* QR Frame */
        .qr-card {
            background: #ffffff;
            border: 3px solid #1e40af;
            border-radius: 24px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            margin: 14px 0;
            box-shadow: 0 12px 28px rgba(30, 64, 175, 0.1);
            position: relative;
        }
        .qr-image-wrapper {
            background: #fff;
            padding: 10px;
            border-radius: 16px;
        }
        .qr-image-wrapper img {
            width: 82mm;
            height: 82mm;
            display: block;
        }
        .scan-instruction-callout {
            margin-top: 8px;
            font-size: 11pt;
            font-weight: 800;
            color: #1e40af;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        /* Steps Section */
        .steps-container {
            width: 100%;
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            padding: 12px 18px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            text-align: left;
        }
        .step-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .step-number {
            width: 28px;
            height: 28px;
            background: #1e40af;
            color: #ffffff;
            border-radius: 50%;
            display: grid;
            place-items: center;
            font-size: 10pt;
            font-weight: 800;
            flex-shrink: 0;
        }
        .step-text {
            font-size: 8.5pt;
            line-height: 1.35;
            color: #334155;
        }
        .step-text strong {
            color: #0f172a;
            display: block;
            font-size: 9pt;
            margin-bottom: 2px;
        }

        /* Footer */
        .poster-footer {
            width: 100%;
            border-top: 1.5px solid #e2e8f0;
            padding-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 8pt;
            color: #64748b;
        }
        .slogan {
            font-weight: 700;
            color: #1e40af;
            font-size: 8.5pt;
        }

        @media print {
            body {
                background: none;
                padding: 0;
            }
            .no-print-bar {
                display: none !important;
            }
            .poster-sheet {
                width: 100%;
                min-height: 100vh;
                box-shadow: none;
                border-radius: 0;
                padding: 12mm 15mm;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <a href="{{ route('admin.foodbank.index') }}" class="back-btn">
            <svg style="width:16px;height:16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            {{ __('Kembali ke Dashboard Food Bank') }}
        </a>
        <button type="button" class="print-btn" onclick="window.print();">
            <svg style="width:18px;height:18px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            {{ __('Cetak Poster Ini (A4)') }}
        </button>
    </div>

    <div class="poster-sheet">
        <!-- Header -->
        <div class="poster-header">
            <div class="institution-title">{{ __('POLITEKNIK BESUT TERENGGANU') }}</div>
            <div class="unit-title">{{ __('HAL EHWAL PELAJAR &middot; UNIT BIASISWA & KEBAJIKAN') }}</div>
        </div>

        <!-- Main Headline -->
        <div class="main-headline">
            <span class="badge-pill">🍃 {{ __('Inisiatif Kebajikan Siswa') }}</span>
            <h1 class="hero-title">{{ __('FOOD BANK SISWA') }}</h1>
            <p class="hero-subtitle">✨ {{ __('Percuma Untuk Pelajar Politeknik Besut') }} ✨</p>
        </div>

        <!-- QR Code Card -->
        <div class="qr-card">
            <div class="qr-image-wrapper">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=450x450&data={{ urlencode($staticQrUrl) }}" alt="Food Bank QR Code">
            </div>
            <div class="scan-instruction-callout">
                📲 {{ __('IMBAS QR KOD INI SEBELUM MENGAMBIL MAKANAN') }}
            </div>
        </div>

        <!-- 3-Step Guide -->
        <div class="steps-container">
            <div class="step-item">
                <div class="step-number">1</div>
                <div class="step-text">
                    <strong>{{ __('Buka Kamera / App') }}</strong>
                    {{ __('Buka kamera telefon anda atau gunakan pengimbas QR di portal MyHEP.') }}
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <div class="step-text">
                    <strong>{{ __('Automatik Disahkan') }}</strong>
                    {{ __('Sistem mengesan profil & sesi login pelajar anda secara serta-merta.') }}
                </div>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <div class="step-text">
                    <strong>{{ __('Ambil Makanan') }}</strong>
                    {{ __('Ambil makanan percuma yang disediakan mengikut keperluan anda.') }}
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="poster-footer">
            <span class="slogan">{{ __('Rezeki Dikongsi, Kasih Diabadi') }}</span>
            <span>{{ __('Sistem Pengurusan HEP &middot; MyHEP Politeknik Besut') }}</span>
        </div>
    </div>

</body>
</html>
