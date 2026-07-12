<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <title>Saman #{{ $offense->id }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #1f2937; margin: 18px; }
        h1, h2, h3, p { margin: 0; }
        .paper {
            border: 1px solid #d1d5db;
            padding: 16px;
        }
        .header {
            display: table;
            width: 100%;
            margin-bottom: 14px;
        }
        .header-cell {
            display: table-cell;
            vertical-align: middle;
        }
        .header-left { width: 130px; }
        .header-mid { text-align: center; }
        .header-right { width: 120px; text-align: right; font-size: 11px; color: #4b5563; }
        .logo {
            width: 116px;
            height: 66px;
            object-fit: contain;
            object-position: left center;
            display: block;
        }
        .logo-fallback {
            width: 116px;
            height: 66px;
            border: 1px solid #d1d5db;
            font-size: 10px;
            color: #6b7280;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 4px;
        }
        .title-top { font-size: 19px; font-weight: 700; letter-spacing: .03em; }
        .title-sub { font-size: 17px; font-weight: 700; margin-top: 2px; }
        .title-meta { margin-top: 4px; font-size: 11px; color: #4b5563; }

        .card { border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; margin-bottom: 10px; }
        .card h3 { font-size: 14px; margin-bottom: 8px; }
        .grid { width: 100%; border-collapse: collapse; }
        .grid th, .grid td { border: 1px solid #d1d5db; padding: 6px; font-size: 12px; text-align: left; vertical-align: top; }
        .grid th { background: #f3f4f6; }

        .statement {
            font-size: 12px;
            margin-bottom: 10px;
        }

        .sign-wrap {
            margin-top: 16px;
            display: table;
            width: 100%;
            table-layout: fixed;
            gap: 12px;
        }
        .sign-box {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            padding-right: 10px;
        }
        .sign-label {
            font-size: 12px;
            margin-bottom: 48px;
        }
        .sign-line {
            border-top: 1px solid #111827;
            width: 92%;
            margin-bottom: 4px;
        }
        .sign-meta {
            font-size: 12px;
            line-height: 1.45;
        }
        .actions { margin-bottom: 12px; }
        .btn {
            display: inline-block;
            border: 1px solid #9ca3af;
            border-radius: 6px;
            padding: 6px 10px;
            text-decoration: none;
            color: #111827;
            font-size: 12px;
            margin-right: 6px;
        }
        .app-footer {
            margin-top: 10px;
            text-align: center;
            font-size: 11px;
            color: #6b7280;
        }
        @media print {
            .actions { display: none; }
            .app-footer { display: none; }
            body { margin: 0; }
            .paper { border: none; padding: 0; }
        }
    </style>
</head>
<body>
    @php
        $logoCandidates = [
            public_path('images/newlogo.png'),
            public_path('images/logohep.png'),
            public_path('images/politeknik-besut.png'),
            public_path('images/politeknik-besut-terengganu.png'),
            public_path('images/polibesut-logo.png'),
            public_path('images/logo-polibesut.png'),
            public_path('images/logo.png'),
        ];

        $logoPath = null;
        foreach ($logoCandidates as $candidate) {
            if (file_exists($candidate)) {
                $logoPath = $candidate;
                break;
            }
        }

        if ($logoPath === null) {
            $globbed = array_merge(
                glob(public_path('images/*politeknik*.*')) ?: [],
                glob(public_path('images/*polibesut*.*')) ?: [],
                glob(public_path('images/*logo*.*')) ?: []
            );
            if (!empty($globbed)) {
                $logoPath = $globbed[0];
            }
        }

        $logoDataUri = null;
        if ($logoPath && is_readable($logoPath)) {
            $mime = mime_content_type($logoPath) ?: 'image/png';
            $raw = file_get_contents($logoPath);
            if ($raw !== false) {
                $logoDataUri = 'data:' . $mime . ';base64,' . base64_encode($raw);
            }
        }
    @endphp

    @if(empty($isPdf))
        <div class="actions">
            <button class="btn" onclick="window.print()">Cetak Saman</button>
            @if(!empty($pdfRoute))
                <a class="btn" href="{{ $pdfRoute }}">Muat Turun PDF</a>
            @endif
            <a class="btn" href="{{ $backRoute ?? url()->previous() }}">Kembali</a>
        </div>
    @endif

    <div class="paper">
        <div class="header">
            <div class="header-cell header-left">
                @if($logoDataUri)
                    <img src="{{ $logoDataUri }}" alt="Logo Politeknik Besut Terengganu" class="logo">
                @else
                    <div class="logo-fallback">StudentEdge</div>
                @endif
            </div>
            <div class="header-cell header-mid">
                <div class="title-top">POLITEKNIK BESUT TERENGGANU</div>
                <div class="title-sub">NOTIS DENDA KESALAHAN TATATERTIB</div>
                <div class="title-meta">No. Saman: {{ $offense->id }} | Tarikh Cetakan: {{ now()->format('Y-m-d H:i') }}</div>
            </div>
            <div class="header-cell header-right">
                <div>Status: {{ strtoupper(__($offense->status)) }}</div>
            </div>
        </div>

        <p class="statement">
            Bahawasanya saya mempunyai sebab-sebab yang munasabah untuk mempercayai bahawa pelajar berikut telah melakukan kesalahan tatatertib seperti dinyatakan di bawah.
        </p>

        <div class="card">
            <h3>Maklumat Pelajar</h3>
            <table class="grid">
                <tr><th style="width:180px;">Nama Pelajar</th><td>{{ $offense->student_name }}</td></tr>
                <tr><th>No. Pendaftaran</th><td>{{ $offense->matric_no }}</td></tr>
                <tr><th>No. KP</th><td>{{ $offense->ic_no }}</td></tr>
                <tr><th>Program</th><td>{{ $offense->program }}</td></tr>
            </table>
        </div>

        <div class="card">
            <h3>Maklumat Kesalahan</h3>
            <table class="grid">
                <tr><th style="width:180px;">Tarikh Kesalahan</th><td>{{ $offense->offense_date }}</td></tr>
                <tr><th>Masa</th><td>{{ $offense->offense_time }}</td></tr>
                <tr><th>Tempat</th><td>{{ $offense->place }}</td></tr>
                <tr><th>Jumlah Denda (RM)</th><td>{{ number_format((float) $offense->fine_amount, 2) }}</td></tr>
                <tr><th>Dikeluarkan Oleh</th><td>{{ $offense->issued_by ?? '-' }}</td></tr>
            </table>
        </div>

        <div class="card">
            <h3>Peraturan Dilanggar</h3>
            <table class="grid">
                <thead>
                    <tr>
                        <th style="width:120px;">Rujukan</th>
                        <th>Perincian</th>
                        <th style="width:220px;">Nota</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                        <tr>
                            <td>{{ __($item->rule_reference) }}</td>
                            <td>{{ __($item->description) }}</td>
                            <td>{{ $item->note ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" style="text-align:center;">Tiada item kesalahan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="sign-wrap">
            <div class="sign-box">
                <div class="sign-label">Dikeluarkan oleh:</div>
                <div class="sign-line"></div>
                <div class="sign-meta">
                    (T/Tangan Pegawai)<br>
                    Nama: <strong>{{ $offense->issued_by ?? '-' }}</strong>
                </div>
            </div>
            <div class="sign-box">
                <div class="sign-label">Saya mengaku / tidak mengaku bersalah*</div>
                <div class="sign-line"></div>
                <div class="sign-meta">
                    (T/Tangan Pelajar)<br>
                    Nama: <strong>{{ $offense->student_name }}</strong><br>
                    No. Pendaftaran: <strong>{{ $offense->matric_no }}</strong>
                </div>
            </div>
        </div>
    </div>

    @if(empty($isPdf))
        @include('partials.app_footer')
    @endif
</body>
</html>
