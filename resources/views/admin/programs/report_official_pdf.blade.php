<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Laporan Pelaksanaan Program - {{ $program->title }}</title>
    <style>
        @page {
            margin: 18mm 16mm 18mm 16mm;
            size: a4 portrait;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10.5pt;
            line-height: 1.45;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-after: always;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 7.5pt;
        }
        .header-table td, .header-table th {
            border: 1px solid #444;
            padding: 3px 4px;
            text-align: center;
        }
        .header-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .kpi-mark {
            font-weight: bold;
            font-size: 11pt;
            color: #000;
        }
        .top-banner {
            text-align: center;
            margin: 12px 0 16px 0;
        }
        .top-banner img.jata {
            width: 85px;
            height: auto;
            margin-bottom: 6px;
        }
        .top-banner h2 {
            margin: 2px 0;
            font-size: 14pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            color: #000;
        }
        .top-banner h3 {
            margin: 2px 0;
            font-size: 12pt;
            font-weight: bold;
            color: #222;
        }
        .cover-box {
            border: 1.5px solid #222;
            padding: 16px 20px;
            margin-top: 15px;
            background-color: #fff;
            border-radius: 4px;
        }
        .cover-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 12px;
            color: #000;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
        }
        .cover-meta {
            width: 100%;
            margin-top: 10px;
        }
        .cover-meta td {
            padding: 5px 4px;
            vertical-align: top;
            font-size: 10.5pt;
        }
        .cover-meta td.label {
            width: 25%;
            font-weight: bold;
            color: #333;
        }
        .cover-meta td.colon {
            width: 3%;
            text-align: center;
            font-weight: bold;
        }
        .cover-meta td.value {
            width: 72%;
            color: #111;
        }

        /* Section Headings */
        h4.section-title {
            font-size: 11pt;
            font-weight: bold;
            margin: 16px 0 8px 0;
            padding: 4px 6px;
            background-color: #e9ecef;
            border-left: 4px solid #4a5568;
            text-transform: uppercase;
        }
        .content-box {
            font-size: 10.5pt;
            text-align: justify;
            margin-bottom: 10px;
            line-height: 1.45;
        }
        ul.custom-list {
            margin: 4px 0 10px 18px;
            padding: 0;
        }
        ul.custom-list li {
            margin-bottom: 4px;
            text-align: justify;
        }

        /* Standard Tables */
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0 14px 0;
            font-size: 9.5pt;
        }
        table.data-table th, table.data-table td {
            border: 1px solid #555;
            padding: 5px 8px;
            text-align: left;
            vertical-align: top;
        }
        table.data-table th {
            background-color: #eaeaea;
            font-weight: bold;
            text-align: center;
        }

        /* Signature block */
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 9.5pt;
        }
        .signature-table td {
            width: 33.33%;
            vertical-align: top;
            padding: 8px 10px;
            border: 1px solid #777;
        }
        .sig-space {
            height: 45px;
        }

        /* Photo Gallery */
        .photo-gallery {
            width: 100%;
            margin: 10px 0;
            border-collapse: collapse;
        }
        .photo-gallery td {
            width: 50%;
            padding: 4px;
            text-align: center;
            vertical-align: middle;
        }
        .photo-gallery img {
            max-width: 95%;
            max-height: 140px;
            border: 1px solid #ccc;
            border-radius: 4px;
            object-fit: cover;
        }
    </style>
</head>
<body>

    <!-- KPI Cluster Header -->
    <div style="font-size: 7pt; color: #555; margin-bottom: 2px;">*Untuk Laporan KPI sahaja</div>
    <table class="header-table">
        <thead>
            <tr>
                <th style="width: 13%;">Kluster Program<br><span style="font-weight: normal; font-size: 6.5pt;">*Sila tandakan</span></th>
                <th>Sukarelawan</th>
                <th>Patriotisme</th>
                <th>Perpaduan</th>
                <th>Kepimpinan</th>
                <th>Komunikasi<br>(BM / BI)</th>
                <th>Kebudayaan &amp; Warisan</th>
                <th>Kerohanian</th>
                <th>Psikologi</th>
                <th>Sukan</th>
                <th>Kesihatan</th>
                <th>Kemahiran &amp; Inovasi</th>
                <th>Kelab &amp; Persatuan</th>
                <th>Niche Area</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-size: 7pt; font-weight: bold;">Tandakan ( / )</td>
                @php
                    $kpi = strtolower($report['kluster_kpi'] ?? 'kemahiran dan inovasi');
                @endphp
                <td class="kpi-mark">{{ str_contains($kpi, 'sukarelawan') ? '✓' : '' }}</td>
                <td class="kpi-mark">{{ str_contains($kpi, 'patriot') ? '✓' : '' }}</td>
                <td class="kpi-mark">{{ str_contains($kpi, 'perpaduan') ? '✓' : '' }}</td>
                <td class="kpi-mark">{{ str_contains($kpi, 'kepimpinan') ? '✓' : '' }}</td>
                <td class="kpi-mark">{{ str_contains($kpi, 'komunikasi') ? '✓' : '' }}</td>
                <td class="kpi-mark">{{ str_contains($kpi, 'budaya') || str_contains($kpi, 'kesenian') || str_contains($kpi, 'warisan') ? '✓' : '' }}</td>
                <td class="kpi-mark">{{ str_contains($kpi, 'rohani') ? '✓' : '' }}</td>
                <td class="kpi-mark">{{ str_contains($kpi, 'psikologi') ? '✓' : '' }}</td>
                <td class="kpi-mark">{{ str_contains($kpi, 'sukan') ? '✓' : '' }}</td>
                <td class="kpi-mark">{{ str_contains($kpi, 'sihat') ? '✓' : '' }}</td>
                <td class="kpi-mark">{{ str_contains($kpi, 'kemahiran') || str_contains($kpi, 'inovasi') ? '✓' : '' }}</td>
                <td class="kpi-mark">{{ str_contains($kpi, 'kelab') || str_contains($kpi, 'persatuan') ? '✓' : '' }}</td>
                <td class="kpi-mark">{{ str_contains($kpi, 'niche') ? '✓' : '' }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Top Banner & Logo -->
    <div class="top-banner">
        @if(file_exists(public_path('images/report-jata-negara.jpg')))
            <img class="jata" src="{{ public_path('images/report-jata-negara.jpg') }}" alt="Jata Negara">
        @endif
        <h2>KEMENTERIAN PENDIDIKAN TINGGI</h2>
        <h3>JABATAN PENDIDIKAN POLITEKNIK DAN KOLEJ KOMUNITI</h3>
        <div style="font-size: 11pt; font-weight: bold; margin-top: 4px; color: #333;">POLITEKNIK BESUT TERENGGANU</div>
    </div>

    <!-- Cover / Title Box -->
    <div class="cover-box">
        <div class="cover-title">LAPORAN PELAKSANAAN PROGRAM</div>
        <table class="cover-meta">
            <tr>
                <td class="label">NAMA PROGRAM</td>
                <td class="colon">:</td>
                <td class="value"><strong>{{ mb_strtoupper($program->title) }}</strong></td>
            </tr>
            <tr>
                <td class="label">TARIKH</td>
                <td class="colon">:</td>
                <td class="value">{{ $program->starts_at ? date('d.m.Y', strtotime($program->starts_at)) . ' (' . strtoupper(date('l', strtotime($program->starts_at))) . ')' : 'Tidak direkodkan' }}</td>
            </tr>
            <tr>
                <td class="label">TEMPAT</td>
                <td class="colon">:</td>
                <td class="value">{{ ($program->venue ?? null) ?: 'Politeknik Besut Terengganu' }}</td>
            </tr>
            <tr>
                <td class="label">ANJURAN</td>
                <td class="colon">:</td>
                <td class="value">{{ ($data['organizer'] ?? null) ?: 'Jabatan Hal Ehwal Pelajar (JHEP)' }}</td>
            </tr>
            <tr>
                <td class="label">DISEDIAKAN OLEH</td>
                <td class="colon">:</td>
                <td class="value">
                    <strong>{{ mb_strtoupper($data['prepared_by'] ?? 'Pengarah Program') }}</strong><br>
                    <span style="font-size: 9.5pt; color: #555;">{{ $data['prepared_by_position'] ?? 'Pengarah Program' }} &middot; {{ $data['organizer'] ?? 'Politeknik Besut' }}</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Section 1 & 2: Maklumat, Ringkasan & Objektif -->
    <h4 class="section-title">1. MAKLUMAT &amp; RINGKASAN PROGRAM</h4>
    <div class="content-box">
        <strong>Peringkat Program:</strong> {{ $report['peringkat'] ?? 'Politeknik / Institusi' }}<br>
        <strong>Kumpulan Sasaran:</strong> {{ ($program->target_participants ?? null) ?: 'Pelajar Politeknik Besut' }}<br>
        <strong>Bilangan Peserta:</strong> {{ $data['attendance_total'] ?? 0 }} Orang (disahkan melalui rekod StudentEdge)<br><br>
        <strong>Ringkasan Program:</strong><br>
        {{ $report['executive_summary'] }}
    </div>

    <h4 class="section-title">2. OBJEKTIF PROGRAM</h4>
    <div class="content-box">
        <ul class="custom-list">
            @foreach($report['objectives'] as $obj)
                <li>{{ $obj }}</li>
            @endforeach
        </ul>
    </div>

    <!-- Page Break for Clean 2nd Page -->
    <div class="page-break"></div>

    <!-- Section 3: Jawatankuasa Program -->
    <h4 class="section-title">3. JAWATANKUASA PROGRAM</h4>
    <table class="data-table">
        <tr>
            <th style="width: 32%;">Peranan</th>
            <th style="width: 68%;">Nama Pegawai / Pelajar</th>
        </tr>
        <tr>
            <td><strong>Penaung</strong></td>
            <td>{{ $report['jawatankuasa']['penaung'] ?? 'Pengarah Politeknik Besut' }}</td>
        </tr>
        <tr>
            <td><strong>Penasihat 1</strong></td>
            <td>{{ $report['jawatankuasa']['penasihat1'] ?? 'Timbalan Pengarah Politeknik Besut' }}</td>
        </tr>
        <tr>
            <td><strong>Penasihat 2</strong></td>
            <td>{{ $report['jawatankuasa']['penasihat2'] ?? 'Ketua Jabatan / Unit' }}</td>
        </tr>
        <tr>
            <td><strong>Pengarah Program</strong></td>
            <td><strong>{{ $report['jawatankuasa']['pengarah_program'] ?? ($data['prepared_by'] ?? 'Tidak direkodkan') }}</strong></td>
        </tr>
        <tr>
            <td><strong>Setiausaha</strong></td>
            <td>{{ $report['jawatankuasa']['setiausaha'] ?? 'Tidak direkodkan' }}</td>
        </tr>
        <tr>
            <td><strong>AJK Pelaksana</strong></td>
            <td>{{ $report['jawatankuasa']['ajk'] ?? 'Jawatankuasa Pelaksana' }}</td>
        </tr>
        <tr>
            <td><strong>Urusetia</strong></td>
            <td>{{ $report['jawatankuasa']['urusetia'] ?? ($data['organizer'] ?? 'Urusetia Program') }}</td>
        </tr>
    </table>

    <!-- Section 4 & 5: Butiran Penceramah & Aturcara -->
    <h4 class="section-title">4. BUTIRAN PENCERAMAH / PERASMI / JEMPUTAN</h4>
    <div class="content-box" style="margin-left: 6px;">
        <strong>Nama:</strong> {{ $report['penceramah']['nama'] ?? 'Pegawai / Penceramah Terlibat' }}<br>
        <strong>Jawatan &amp; Gred:</strong> {{ $report['penceramah']['jawatan'] ?? '—' }} ({{ $report['penceramah']['gred'] ?? '—' }})<br>
        <strong>Jabatan / Institusi:</strong> {{ $report['penceramah']['institusi'] ?? 'Politeknik Besut Terengganu' }}
    </div>

    <h4 class="section-title">5. ATURCARA PROGRAM</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 20%;">Tarikh</th>
                <th style="width: 25%;">Masa</th>
                <th style="width: 55%;">Aktiviti</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['aturcara'] as $row)
                <tr>
                    <td>{{ $row['tarikh'] ?? ($program->starts_at ? date('d.m.Y', strtotime($program->starts_at)) : '') }}</td>
                    <td>{{ $row['masa'] ?? '—' }}</td>
                    <td>{{ $row['aktiviti'] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Section 6 & 7: Kewangan -->
    <h4 class="section-title">6. SUMBER KEWANGAN &amp; PERBELANJAAN</h4>
    <div class="content-box" style="margin-left: 6px;">
        <strong>Sumber Kewangan:</strong> {{ $report['kewangan'] ?? 'Tiada / Peruntukan Dalaman Jabatan' }}
    </div>

    <!-- Section 8 & 9: Hasil Kaji Selidik -->
    <h4 class="section-title">7. HASIL KAJI SELIDIK &amp; MAKLUM BALAS PESERTA</h4>
    <div class="content-box">
        {{ $report['survey_summary'] }}
    </div>

    <!-- Page Break for Clean 3rd Page -->
    <div class="page-break"></div>

    <!-- Section 10: Gambar Aktiviti -->
    <h4 class="section-title">8. GAMBAR AKTIVITI / PROGRAM</h4>
    @if(isset($imagePaths) && count($imagePaths) > 0)
        <table class="photo-gallery">
            @foreach(array_chunk(array_slice($imagePaths, 0, 6), 2) as $row)
                <tr>
                    @foreach($row as $img)
                        <td>
                            @if(file_exists($img))
                                <img src="{{ $img }}" alt="Foto Aktiviti">
                            @endif
                        </td>
                    @endforeach
                    @if(count($row) === 1)
                        <td></td>
                    @endif
                </tr>
            @endforeach
        </table>
    @else
        <div class="content-box" style="font-style: italic; color: #777; padding: 10px;">
            (Tiada lampiran gambar aktiviti disertakan semasa penjanaan laporan.)
        </div>
    @endif

    <!-- Section 11: Hasil / Impak, Isu & Cadangan -->
    <h4 class="section-title">9. HASIL / IMPAK PROGRAM</h4>
    <div class="content-box">
        <strong>Pencapaian &amp; Hasil Utama:</strong>
        <ul class="custom-list">
            @foreach($report['achievements'] as $ach)
                <li>{{ $ach }}</li>
            @endforeach
        </ul>

        <strong>Isu &amp; Kekangan:</strong>
        <ul class="custom-list">
            @foreach($report['issues'] as $iss)
                <li>{{ $iss }}</li>
            @endforeach
        </ul>

        <strong>Cadangan Penambahbaikan:</strong>
        <ul class="custom-list">
            @foreach($report['improvements'] as $imp)
                <li>{{ $imp }}</li>
            @endforeach
        </ul>

        <strong>Kesimpulan:</strong><br>
        {{ $report['conclusion'] }}
    </div>

    <!-- Section 12: Pengesahan Laporan -->
    <h4 class="section-title">10. PENGESAHAN LAPORAN</h4>
    <table class="signature-table">
        <tr>
            <td>
                <strong>Disediakan Oleh:</strong>
                <div class="sig-space"></div>
                __________________________<br>
                <strong>( {{ mb_strtoupper($data['prepared_by'] ?? 'Pengarah Program') }} )</strong><br>
                {{ $data['prepared_by_position'] ?? 'Pengarah Program' }}<br>
                Politeknik Besut Terengganu<br>
                <span style="font-size: 8pt; color: #555;">Tarikh: {{ date('d/m/Y') }}</span>
            </td>
            <td>
                <strong>Disemak Oleh:</strong>
                <div class="sig-space"></div>
                __________________________<br>
                <strong>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</strong><br>
                KJ / KU / TPA / TPSA<br>
                Politeknik Besut Terengganu<br>
                <span style="font-size: 8pt; color: #555;">Tarikh: </span>
            </td>
            <td>
                <strong>Disahkan Oleh:</strong>
                <div class="sig-space"></div>
                __________________________<br>
                <strong>( UDOM A/L EWON )</strong><br>
                Pengarah<br>
                Politeknik Besut Terengganu<br>
                <span style="font-size: 8pt; color: #555;">Tarikh: </span>
            </td>
        </tr>
    </table>

</body>
</html>
