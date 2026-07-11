<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Print QR') }}</title>
    <style>
        :root {
            color-scheme: light;
            --ink:#111111;
            --muted:#5f5a54;
            --line:#d9d2cb;
            --panel:#f7f2ed;
        }
        * { box-sizing:border-box; }
        html, body { margin:0; padding:0; background:#f3f3f3; color:var(--ink); font-family:Arial, Helvetica, sans-serif; }
        .sheet {
            width:min(210mm, calc(100vw - 32px));
            min-height:297mm;
            margin:16px auto;
            padding:16mm 14mm;
            background:#ffffff;
            box-shadow:0 10px 30px rgba(0,0,0,.10);
        }
        .header {
            text-align:center;
            margin-bottom:8mm;
        }
        .header h1 {
            margin:0;
            font-size:22px;
            letter-spacing:.01em;
        }
        .header p {
            margin:6px 0 0;
            color:var(--muted);
            font-size:13px;
            line-height:1.5;
        }
        .card {
            border:1px solid var(--line);
            border-radius:18px;
            padding:12mm 10mm;
            background:linear-gradient(180deg, #fff, var(--panel));
        }
        .checkpoint {
            text-align:center;
            font-size:15px;
            font-weight:700;
            margin-bottom:8mm;
        }
        .qr-box {
            display:grid;
            place-items:center;
            border:1px solid var(--line);
            border-radius:16px;
            padding:8mm;
            background:#ffffff;
        }
        .qr-box img {
            width:120mm;
            max-width:100%;
            height:auto;
            display:block;
        }
        .help {
            margin:8mm 0 4mm;
            color:var(--muted);
            text-align:center;
            font-size:12px;
            line-height:1.5;
        }
        .warning {
            margin:0 0 6mm;
            padding:4mm 5mm;
            border:1px solid #f1c27d;
            border-radius:12px;
            background:#fff4e5;
            color:#8a4b08;
            font-size:12px;
            line-height:1.5;
            text-align:center;
        }
        .url {
            margin:0;
            padding:4mm;
            border:1px solid var(--line);
            border-radius:12px;
            background:#fff;
            font-size:11px;
            line-height:1.5;
            word-break:break-all;
        }
        .actions {
            display:flex;
            justify-content:center;
            gap:12px;
            margin-top:8mm;
        }
        .btn {
            border:1px solid #b8aa9d;
            border-radius:999px;
            padding:10px 18px;
            background:#ead6c2;
            color:#2b2119;
            font-size:14px;
            font-weight:700;
            text-decoration:none;
            cursor:pointer;
        }
        .btn.secondary {
            background:#fff;
        }
        @media print {
            @page { size:A4 portrait; margin:10mm; }
            html, body { background:#fff; }
            .sheet {
                width:auto;
                min-height:auto;
                margin:0;
                padding:0;
                box-shadow:none;
            }
            .actions { display:none; }
        }
    </style>
</head>
<body>
    <main class="sheet">
        <div class="header">
            <h1>{{ __('Guard House QR') }}</h1>
            <p>{{ __('Manage the live guard house QR so students scan a valid checkpoint before recording any movement.') }}</p>
        </div>

        <section class="card">
            <div class="checkpoint">{{ $checkpoint?->name ?? __('Checkpoint') }}</div>
            <div class="warning">{{ __('This QR rotates after each successful scan. A printed copy can expire after the first use and should only be used as a temporary backup.') }}</div>

            @if($scanUrl)
                <div class="qr-box">
                    <img alt="{{ __('Movement QR Code') }}" src="https://api.qrserver.com/v1/create-qr-code/?size=720x720&data={{ urlencode($scanUrl) }}">
                </div>
                <p class="help">{{ __('Students can scan this code at the checkpoint or open the URL manually if camera scanning is unavailable.') }}</p>
                <p class="url">{{ $scanUrl }}</p>
            @else
                <p class="help">{{ __('Checkpoint is not available.') }}</p>
            @endif
        </section>

        <div class="actions">
            <button class="btn" type="button" onclick="window.print()">{{ __('Print QR') }}</button>
            <button class="btn secondary" type="button" onclick="window.close()">{{ __('Close') }}</button>
        </div>
    </main>
</body>
</html>
