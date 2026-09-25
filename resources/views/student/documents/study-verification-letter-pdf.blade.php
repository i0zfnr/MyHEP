<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Surat Pengesahan Pengajian Pelajar</title>
    <style>
        @page { size: A4 portrait; margin: 15mm 18mm 13mm 23mm; }
        * { box-sizing: border-box; }
        body { margin: 0; color: #000; font-family: Arial, Helvetica, sans-serif; font-size: 10.5pt; line-height: 1.4; }
        .letterhead { display: table; width: 100%; border-bottom: 1.2pt solid #111; padding-bottom: 8px; margin-bottom: 14px; }
        .letterhead-logo, .letterhead-copy { display: table-cell; vertical-align: middle; }
        .letterhead-logo { width: 68px; }
        .letterhead-logo img { width: 56px; max-height: 64px; object-fit: contain; }
        .letterhead-copy { text-align: right; }
        .letterhead-copy strong { display: block; font-size: 13pt; text-transform: uppercase; letter-spacing: .2pt; }
        .letterhead-copy span { display: block; font-size: 9pt; margin-top: 2px; }
        .meta { margin-left: auto; width: 78%; border-collapse: collapse; margin-bottom: 16px; font-size: 9.5pt; }
        .meta td { padding: 1px 0; vertical-align: top; }
        .meta td:first-child { width: 24%; }
        .recipient { margin: 0 0 13px; }
        .recipient strong { display: block; }
        p { margin: 0 0 10px; text-align: justify; }
        .subject { font-weight: 700; text-decoration: underline; margin: 0 0 10px; }
        .details-title { font-weight: 700; margin: 9px 0 5px 18px; }
        .details { width: calc(100% - 18px); margin-left: 18px; border-collapse: collapse; }
        .details td { padding: 1.5px 0; vertical-align: top; }
        .details .label { width: 42%; }
        .details .colon { width: 4%; text-align: center; }
        .sponsor { margin: 3px 0 12px 30px; white-space: pre-line; }
        .closing { margin-top: 12px; }
        .motto { font-weight: 700; margin: 1px 0; }
        .signature { margin-top: 13px; }
        .signature-space { height: 28px; }
        .signature p { margin: 0; text-align: left; }
        .copy { margin-top: 12px; }
        .system-note { position: fixed; bottom: -4mm; left: 0; right: 0; color: #555; font-size: 7.5pt; text-align: center; border-top: .5pt solid #bbb; padding-top: 4px; }
    </style>
</head>
<body>
    <header class="letterhead">
        <div class="letterhead-logo">@if($logo_data_uri)<img src="{{ $logo_data_uri }}" alt="">@endif</div>
        <div class="letterhead-copy">
            <strong>{{ $institution_name }}</strong>
            <span>{{ $department_name }}</span>
            <span>{{ $institution_address }}</span>
        </div>
    </header>

    <table class="meta">
        <tr><td>Ruj. Kami</td><td>: {{ $reference_number }}</td></tr>
        <tr><td>Tarikh</td><td>: {{ $letter_date }}</td></tr>
    </table>

    <div class="recipient">Kepada<br><strong>PIHAK YANG BERKENAAN</strong></div>
    <p>Tuan,</p>
    <div class="subject">PENGESAHAN PENGAJIAN PELAJAR</div>
    <p>Dengan segala hormatnya saya merujuk kepada perkara di atas.</p>
    <p>2.&nbsp;&nbsp;&nbsp;&nbsp;Pihak kami dengan ini mengesahkan bahawa pelajar dengan butiran seperti di bawah telah didaftarkan sebagai pelajar di institusi kami:</p>

    <div class="details-title">2.1 BUTIRAN PENGAJIAN PELAJAR</div>
    <table class="details">
        <tr><td class="label">Nama pelajar</td><td class="colon">:</td><td>{{ $student_name }}</td></tr>
        <tr><td class="label">No. KP pelajar</td><td class="colon">:</td><td>{{ $ic_no }}</td></tr>
        <tr><td class="label">No. pendaftaran pelajar</td><td class="colon">:</td><td>{{ $matric_no }}</td></tr>
        <tr><td class="label">Program pengajian</td><td class="colon">:</td><td>{{ $program_name }}</td></tr>
        <tr><td class="label">Sesi pengajian bermula</td><td class="colon">:</td><td>{{ $study_start_session }}</td></tr>
        <tr><td class="label">Sesi pengajian tamat</td><td class="colon">:</td><td>{{ $study_end_session }}</td></tr>
        <tr><td class="label">Tempoh pengajian</td><td class="colon">:</td><td>{{ $study_duration }}</td></tr>
        <tr><td class="label">Tahun pengajian semasa</td><td class="colon">:</td><td>{{ $current_study_year }}</td></tr>
        <tr><td class="label">Semester pengajian semasa</td><td class="colon">:</td><td>SEMESTER {{ $current_semester }}</td></tr>
    </table>

    <div class="details-title">2.2 BUTIRAN TAJAAN / BIASISWA / PINJAMAN / PEMBIAYAAN (SEDIA ADA)</div>
    <div class="sponsor">{{ $sponsorship_details }}</div>

    <p class="closing">Sekian, terima kasih.</p>
    @foreach($mottos as $motto)<p class="motto">&ldquo;{{ $motto }}&rdquo;</p>@endforeach

    <div class="signature">
        <p>Saya yang menjalankan amanah,</p>
        <div class="signature-space"></div>
        <p><strong>({{ $officer_name }})</strong></p>
        <p>{{ $officer_position }}</p>
        <p>{{ $officer_authority }}</p>
        <p>{{ $institution_name }}</p>
    </div>

    <p class="copy">s.k&nbsp;&nbsp;&nbsp;&nbsp;Fail Pelajar</p>
    <div class="system-note">Dokumen dijana melalui MyHEP pada {{ $generated_at }} &bull; {{ $reference_number }}</div>
</body>
</html>
