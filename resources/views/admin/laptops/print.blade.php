<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('JHEP Laptop QR Labels') }}</title>
    <style>
        @page{size:A4 portrait;margin:8mm}
        *{box-sizing:border-box}
        body{margin:0;background:#eee9e2;font-family:Arial,Helvetica,sans-serif;color:#201a16;-webkit-print-color-adjust:exact;print-color-adjust:exact}
        .sheet{width:194mm;height:281mm;margin:0 auto 8mm;display:grid;grid-template-columns:repeat(2,minmax(0,1fr));grid-template-rows:repeat(2,minmax(0,1fr));gap:5mm;break-after:page;page-break-after:always}
        .sheet:last-of-type{break-after:auto;page-break-after:auto}
        .label{position:relative;min-width:0;min-height:0;padding:6mm 6mm 5mm;overflow:hidden;border:1px dashed #a99783;border-radius:3mm;background:#fff;display:grid;grid-template-rows:auto minmax(0,1fr) auto;align-items:center;justify-items:center;text-align:center;break-inside:avoid;page-break-inside:avoid}
        .label::before{content:'';position:absolute;inset:0 0 auto;height:4mm;background:linear-gradient(90deg,#725229,#c7a35d,#725229)}
        .brand{width:100%;display:flex;align-items:center;justify-content:center;gap:2.5mm;padding-top:1mm;padding-bottom:2.5mm;border-bottom:.35mm solid #e6d8c6}
        .brand img{width:11mm;height:11mm;object-fit:contain}
        .brand-copy{text-align:left}.brand-copy strong{display:block;font-size:9pt;letter-spacing:.01em}.brand-copy span{display:block;margin-top:.4mm;color:#806b55;font-size:5.8pt;font-weight:700;letter-spacing:.1em;text-transform:uppercase}
        .label-main{display:grid;align-content:center;justify-items:center;padding:2mm 0 1.5mm}
        .asset-tag{display:inline-flex;margin-bottom:1.5mm;padding:1mm 2.5mm;border:.3mm solid #ddc49d;border-radius:99mm;background:#fbf4e8;color:#745224;font-size:6pt;font-weight:800;letter-spacing:.08em;text-transform:uppercase}
        .label h1{margin:0;font-size:14pt;line-height:1.05}.label p{margin:1mm 0 2mm;color:#6f5b49;font-size:7.5pt;font-weight:700;letter-spacing:.06em}
        .qr-frame{padding:2mm;border:.4mm solid #d2c0aa;border-radius:2.5mm;background:#fff;box-shadow:0 2mm 5mm rgba(54,39,25,.11)}
        .qr-frame img{display:block;width:52mm;height:52mm;image-rendering:auto}
        .label-footer{width:100%;padding-top:2mm;border-top:.35mm solid #e6d8c6}
        .instruction{display:block;color:#32271f;font-size:7.2pt;font-weight:800}.instruction-ms{display:block;margin-top:.6mm;color:#7b6857;font-size:5.8pt}
        .actions{position:fixed;z-index:3;right:16px;top:16px}.actions button{min-height:42px;padding:0 16px;border:1px solid #745224;border-radius:10px;background:#8e6937;color:#fff;font:700 14px Arial;box-shadow:0 8px 22px rgba(45,32,20,.18);cursor:pointer}
        @media print{body{background:#fff}.actions{display:none}.sheet{margin-bottom:0;gap:5mm}.label{border-radius:0}.qr-frame{box-shadow:none}}
    </style>
</head>
<body>
    <div class="actions"><button type="button" onclick="window.print()">{{ __('Print QR labels') }}</button></div>
    @foreach($laptops->chunk(4) as $sheetLaptops)
    <main class="sheet">
        @foreach($sheetLaptops as $laptop)
            @php($borrowUrl = route('laptops.borrow', $laptop->qr_token))
            <article class="label">
                <header class="brand">
                    <img alt="{{ __('Politeknik Besut logo') }}" src="{{ asset('images/logo-politeknik-besut.png') }}">
                    <div class="brand-copy"><strong>{{ __('Politeknik Besut Terengganu') }}</strong><span lang="ms" translate="no" class="notranslate">{{ __('Jabatan Hal Ehwal Pelajar') }}</span></div>
                </header>
                <div class="label-main">
                    <span class="asset-tag">{{ __('Official Equipment') }}</span>
                    <h1>{{ $laptop->name }}</h1>
                    <p>{{ $laptop->asset_code }}</p>
                    <div class="qr-frame"><img alt="QR code for {{ $laptop->name }}" src="https://api.qrserver.com/v1/create-qr-code/?size=600x600&data={{ urlencode($borrowUrl) }}"></div>
                </div>
                <footer class="label-footer"><span class="instruction">{{ __('Scan QR to borrow or return this laptop') }}</span><span class="instruction-ms">{{ __('Imbas kod QR untuk pinjam atau pulangkan komputer riba ini') }}</span></footer>
            </article>
        @endforeach
    </main>
    @endforeach
</body>
</html>
