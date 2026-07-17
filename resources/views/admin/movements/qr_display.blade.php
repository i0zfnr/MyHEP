<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Guard House QR') }}</title>
    <style>
        :root {
            color-scheme: dark;
            --bg:#0f0d0b;
            --panel:#181411;
            --panel-2:#221c18;
            --line:rgba(214, 188, 164, .18);
            --text:#f7efe8;
            --muted:#c6b6a7;
            --accent:#e6c7a8;
            --accent-2:#9ce3be;
        }
        * { box-sizing:border-box; }
        html, body {
            margin:0;
            min-height:100%;
            background:
                radial-gradient(circle at top right, rgba(214,188,164,.10), transparent 28%),
                linear-gradient(180deg, #171310 0%, var(--bg) 100%);
            color:var(--text);
            font-family:"Plus Jakarta Sans", Arial, sans-serif;
        }
        body {
            display:grid;
            place-items:center;
            padding:24px;
        }
        .display {
            width:min(100%, 920px);
            display:grid;
            gap:20px;
        }
        .panel {
            border:1px solid var(--line);
            border-radius:28px;
            background:linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.02));
            box-shadow:0 24px 64px rgba(0,0,0,.28);
            overflow:hidden;
        }
        .head {
            display:flex;
            align-items:center;
            justify-content:space-between;
            gap:16px;
            padding:22px 24px 18px;
            border-bottom:1px solid var(--line);
        }
        .title {
            display:grid;
            gap:6px;
        }
        .title h1 {
            margin:0;
            font-size:clamp(1.5rem, 2.6vw, 2.2rem);
            line-height:1.05;
        }
        .title p {
            margin:0;
            color:var(--muted);
            font-size:.96rem;
        }
        .status {
            display:inline-flex;
            align-items:center;
            gap:.5rem;
            padding:.72rem 1rem;
            border-radius:999px;
            border:1px solid rgba(156, 227, 190, .38);
            background:rgba(156, 227, 190, .12);
            color:var(--accent-2);
            font-size:.82rem;
            font-weight:800;
            text-transform:uppercase;
            letter-spacing:.08em;
            white-space:nowrap;
        }
        .status.off {
            border-color:rgba(248, 113, 113, .32);
            background:rgba(127, 29, 29, .18);
            color:#fca5a5;
        }
        .body {
            padding:28px 24px 24px;
            display:grid;
            gap:18px;
        }
        .checkpoint {
            text-align:center;
            font-size:1rem;
            letter-spacing:.14em;
            text-transform:uppercase;
            color:var(--muted);
        }
        .qr-box {
            display:grid;
            place-items:center;
            min-height:420px;
            padding:24px;
            border-radius:24px;
            border:1px solid var(--line);
            background:
                radial-gradient(circle at top, rgba(230,199,168,.08), transparent 40%),
                linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.01));
        }
        .qr-box img {
            width:min(68vw, 420px);
            max-width:100%;
            height:auto;
            border-radius:24px;
            background:#fff;
            padding:18px;
            box-shadow:0 18px 46px rgba(0,0,0,.22);
        }
        .help, .stamp, .url {
            text-align:center;
        }
        .help {
            margin:0;
            color:var(--muted);
            font-size:.95rem;
            line-height:1.6;
        }
        .stamp {
            margin:0;
            color:var(--accent);
            font-size:.82rem;
        }
        .url {
            margin:0;
            padding:12px 14px;
            border-radius:14px;
            border:1px solid var(--line);
            background:rgba(255,255,255,.03);
            color:var(--muted);
            font-size:.78rem;
            word-break:break-all;
        }
        .empty {
            text-align:center;
            color:var(--muted);
            padding:48px 24px;
        }
        @media (max-width: 640px) {
            body { padding:14px; }
            .head, .body { padding-left:16px; padding-right:16px; }
            .head { align-items:flex-start; flex-direction:column; }
            .qr-box { min-height:320px; padding:16px; }
            .qr-box img { width:min(78vw, 320px); padding:14px; }
        }
    </style>
</head>
<body>
@php
    $isValid = $checkpoint && $checkpoint->is_active;
@endphp
    <main class="display">
        <section class="panel">
            <div class="head">
                <div class="title">
                    <h1>{{ __('Guard House QR') }}</h1>
                    <p>{{ __('Students can scan this code at the checkpoint or open the URL manually if camera scanning is unavailable.') }}</p>
                </div>
                <div class="status {{ $isValid ? '' : 'off' }}" id="qrCheckpointStatus">
                    {{ $isValid ? __('Active') : __('Inactive') }}
                </div>
            </div>
            <div class="body">
                <div class="checkpoint" id="qrCheckpointName">{{ $checkpoint?->name ?? __('Checkpoint') }}</div>

                @if($scanUrl)
                    <div class="qr-box">
                        <img id="qrImage" alt="{{ __('Movement QR Code') }}" src="https://api.qrserver.com/v1/create-qr-code/?size=720x720&data={{ urlencode($scanUrl) }}">
                    </div>
                    <p class="help">{{ __('This QR remains active while the checkpoint is active and regenerates after every successful student scan.') }}</p>
                    <p class="stamp" id="qrLastUpdated">{{ __('Waiting for latest QR sync...') }}</p>
                    <p class="url" id="qrUrl">{{ $scanUrl }}</p>
                @else
                    <div class="empty">{{ __('Checkpoint is not available.') }}</div>
                @endif
            </div>
        </section>
    </main>

    <script>
    (() => {
        const refreshMs = 2000;
        const statusUrl = @json(route('admin.movements.qr.status'));
        const qrImage = document.getElementById('qrImage');
        const qrUrl = document.getElementById('qrUrl');
        const qrStatus = document.getElementById('qrCheckpointStatus');
        const qrName = document.getElementById('qrCheckpointName');
        const qrLastUpdated = document.getElementById('qrLastUpdated');
        let lastScanUrl = qrUrl ? qrUrl.textContent.trim() : '';

        const updateQrPanel = (payload) => {
            const checkpoint = payload?.checkpoint;
            const scanUrl = payload?.scan_url || '';

            if (!checkpoint || !qrStatus || !qrName) {
                return;
            }

            qrName.textContent = checkpoint.name || @json(__('Checkpoint'));
            qrStatus.textContent = checkpoint.is_valid ? @json(__('Active')) : @json(__('Inactive'));
            qrStatus.className = 'status ' + (checkpoint.is_valid ? '' : 'off');

            if (scanUrl && qrImage && qrUrl && scanUrl !== lastScanUrl) {
                qrImage.src = 'https://api.qrserver.com/v1/create-qr-code/?size=720x720&data=' + encodeURIComponent(scanUrl);
                qrUrl.textContent = scanUrl;
                lastScanUrl = scanUrl;
            }

            if (qrLastUpdated) {
                qrLastUpdated.textContent = @json(__('Latest QR sync: ')) + new Date().toLocaleTimeString();
            }
        };

        const pollQrStatus = async () => {
            if (document.visibilityState !== 'visible') {
                return;
            }

            try {
                const response = await fetch(statusUrl, {
                    headers: { Accept: 'application/json' },
                    cache: 'no-store',
                });

                if (!response.ok) {
                    return;
                }

                updateQrPanel(await response.json());
            } catch (error) {
                if (qrLastUpdated) {
                    qrLastUpdated.textContent = @json(__('Latest QR sync failed. Retrying...'));
                }
            }
        };

        pollQrStatus();
        window.setInterval(pollQrStatus, refreshMs);
    })();
    </script>
</body>
</html>
