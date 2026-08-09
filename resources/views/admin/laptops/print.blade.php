<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>JHEP Laptop QR Labels</title>
    <style>
        @page{size:A4 portrait;margin:10mm}*{box-sizing:border-box}body{margin:0;font-family:Arial,sans-serif;color:#241b15}.sheet{display:grid;grid-template-columns:repeat(2,1fr);gap:8mm}.label{min-height:133mm;padding:8mm;border:1px dashed #a79586;display:grid;align-content:center;justify-items:center;text-align:center;break-inside:avoid}.label h1{margin:0;font-size:18pt}.label p{margin:3mm 0 5mm;color:#68594e;font-size:10pt}.label img{width:67mm;height:67mm;image-rendering:auto}.label small{margin-top:4mm;font-size:8.5pt;color:#68594e;word-break:break-all}.actions{position:fixed;right:14px;top:14px}@media print{.actions{display:none}.sheet{gap:8mm}}
    </style>
</head>
<body>
    <div class="actions"><button type="button" onclick="window.print()">Print QR labels</button></div>
    <main class="sheet">
        @foreach($laptops as $laptop)
            @php($borrowUrl = route('laptops.borrow', $laptop->qr_token))
            <article class="label">
                <h1>{{ $laptop->name }}</h1>
                <p>{{ $laptop->asset_code }}</p>
                <img alt="QR code for {{ $laptop->name }}" src="https://api.qrserver.com/v1/create-qr-code/?size=600x600&data={{ urlencode($borrowUrl) }}">
                <small>Scan to borrow this JHEP laptop</small>
            </article>
        @endforeach
    </main>
</body>
</html>
