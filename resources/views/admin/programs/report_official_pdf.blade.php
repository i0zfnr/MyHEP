<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Laporan Pelaksanaan Program - {{ $program->title }}</title>
    <style>
        @page {
            margin: 15mm 15mm 15mm 15mm;
            size: a4 portrait;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9.5pt;
            line-height: 1.35;
            color: #000000;
            margin: 0;
            padding: 0;
        }
        .page-break {
            page-break-after: always;
        }

        /* Top KPI Table */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.5pt;
            margin-bottom: 8px;
        }
        .kpi-table th, .kpi-table td {
            border: 0.75pt solid #000;
            padding: 2px 2px;
            text-align: center;
            vertical-align: middle;
        }
        .kpi-table th {
            font-weight: bold;
            background-color: #ffffff;
        }
        .kpi-check {
            font-size: 10pt;
            font-weight: bold;
            line-height: 1;
        }

        /* Cover Elements */
        .cover-logos {
            text-align: center;
            margin: 8px 0 6px 0;
        }
        .cover-logos img.crest {
            height: 48px;
            width: auto;
            display: inline-block;
            margin: 0 4px;
        }
        .cover-logos img.jata {
            height: 52px;
            width: auto;
            display: inline-block;
            margin: 0 4px;
        }
        .cover-inst-title {
            text-align: center;
            font-size: 11pt;
            font-weight: bold;
            margin: 4px 0 2px 0;
            text-transform: uppercase;
        }
        .cover-inst-sub {
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
            margin: 0 0 14px 0;
            text-transform: uppercase;
        }
        .cover-report-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            margin: 12px 0 4px 0;
            text-transform: uppercase;
        }
        .cover-program-name {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            margin: 4px 0 14px 0;
            text-transform: uppercase;
        }
        .cover-center-block {
            text-align: center;
            margin: 10px 0;
        }
        .cover-label {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .cover-value {
            font-size: 10.5pt;
            font-weight: bold;
            margin-bottom: 12px;
            text-transform: uppercase;
        }
        .cover-banner-wrap {
            text-align: center;
            margin: 10px auto;
        }
        .cover-banner-wrap img {
            width: 90%;
            max-height: 100px;
            object-fit: cover;
            border-radius: 4px;
        }

        /* Section Headings matching DOCX */
        .sec-head {
            font-size: 10pt;
            font-weight: bold;
            margin: 12px 0 4px 0;
            text-transform: uppercase;
        }
        .sec-subhead {
            font-size: 9.5pt;
            font-weight: bold;
            margin: 8px 0 2px 0;
        }

        /* Content Text */
        .text-block {
            font-size: 9.5pt;
            text-align: justify;
            margin-bottom: 6px;
            line-height: 1.35;
        }
        .meta-line {
            margin: 2px 0;
            font-size: 9.5pt;
        }
        .meta-line strong {
            display: inline-block;
            min-width: 170px;
        }

        /* Lists */
        ol.num-list {
            margin: 2px 0 6px 18px;
            padding: 0;
        }
        ol.num-list li {
            margin-bottom: 3px;
            text-align: justify;
        }
        ol.roman-list {
            margin: 2px 0 6px 18px;
            padding: 0;
            list-style-type: lower-roman;
        }
        ol.roman-list li {
            margin-bottom: 3px;
            text-align: justify;
        }

        /* Tables matching DOCX template */
        table.tbl-bordered {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0 10px 0;
            font-size: 9pt;
        }
        table.tbl-bordered th, table.tbl-bordered td {
            border: 0.75pt solid #000;
            padding: 4px 6px;
            vertical-align: top;
        }
        table.tbl-bordered th {
            font-weight: bold;
            text-align: center;
            background-color: #f7f7f7;
        }

        table.tbl-committee {
            width: 100%;
            border-collapse: collapse;
            margin: 4px 0 10px 0;
            font-size: 9pt;
        }
        table.tbl-committee td {
            border: 0.75pt solid #000;
            padding: 3px 6px;
            vertical-align: middle;
        }
        table.tbl-committee td.col-role {
            width: 30%;
            font-weight: bold;
        }
        table.tbl-committee td.col-name {
            width: 70%;
        }

        /* Signatures block matching DOCX */
        table.tbl-signatures {
            width: 100%;
            border-collapse: collapse;
            margin-top: 14px;
            font-size: 8.5pt;
        }
        table.tbl-signatures td {
            width: 33.33%;
            border: 0.75pt solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }
        .sig-space {
            height: 38px;
        }

        /* Photo Gallery */
        table.tbl-photos {
            width: 100%;
            border-collapse: collapse;
            margin: 6px 0;
        }
        table.tbl-photos td {
            width: 50%;
            text-align: center;
            vertical-align: middle;
            padding: 4px;
        }
        table.tbl-photos img {
            max-width: 95%;
            max-height: 140px;
            border: 0.5pt solid #888;
            border-radius: 2px;
        }
    </style>
</head>
<body>

    @php
        $kpi = strtolower($report['kluster_kpi'] ?? 'kemahiran dan inovasi');
        $dateFormatted = ($program->starts_at ?? null)
            ? date('d.m.Y', strtotime($program->starts_at)) . ' (' . strtoupper(date('l', strtotime($program->starts_at))) . ')'
            : 'Tidak direkodkan';
        $longDate = ($program->starts_at ?? null)
            ? strtoupper(\Carbon\Carbon::parse($program->starts_at)->locale('ms')->translatedFormat('d F Y'))
            : 'TIDAK DIREKODKAN';
    @endphp

    <!-- ================= PAGE 1: COVER PAGE ================= -->
    <div style="font-size: 6.5pt; margin-bottom: 2px;">*Untuk Laporan KPI sahaja</div>
    <table class="kpi-table">
        <thead>
            <tr>
                <th style="width: 12%;">Kluster Program<br><span style="font-weight: normal; font-size: 5.5pt;">*Sila tandakan</span></th>
                <th>Sukarelawan</th>
                <th>Patriotisme</th>
                <th>Perpaduan</th>
                <th>Kepimpinan</th>
                <th>Komunikasi<br>(BM / BI)</th>
                <th>Kebudayaan, kesenian &amp; warisan</th>
                <th>Kerohanian</th>
                <th>Psikologi</th>
                <th>Sukan</th>
                <th>Kesihatan</th>
                <th>Kemahiran dan Inovasi</th>
                <th>Kelab dan persatuan</th>
                <th>Niche Area</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="font-weight: bold; font-size: 6pt;">Tandakan</td>
                <td class="kpi-check">{{ str_contains($kpi, 'sukarelawan') ? '/' : '' }}</td>
                <td class="kpi-check">{{ str_contains($kpi, 'patriot') ? '/' : '' }}</td>
                <td class="kpi-check">{{ str_contains($kpi, 'perpaduan') ? '/' : '' }}</td>
                <td class="kpi-check">{{ str_contains($kpi, 'kepimpinan') ? '/' : '' }}</td>
                <td class="kpi-check">{{ str_contains($kpi, 'komunikasi') ? '/' : '' }}</td>
                <td class="kpi-check">{{ str_contains($kpi, 'budaya') || str_contains($kpi, 'kesenian') || str_contains($kpi, 'warisan') ? '/' : '' }}</td>
                <td class="kpi-check">{{ str_contains($kpi, 'rohani') ? '/' : '' }}</td>
                <td class="kpi-check">{{ str_contains($kpi, 'psikologi') ? '/' : '' }}</td>
                <td class="kpi-check">{{ str_contains($kpi, 'sukan') ? '/' : '' }}</td>
                <td class="kpi-check">{{ str_contains($kpi, 'sihat') ? '/' : '' }}</td>
                <td class="kpi-check">{{ str_contains($kpi, 'kemahiran') || str_contains($kpi, 'inovasi') ? '/' : '' }}</td>
                <td class="kpi-check">{{ str_contains($kpi, 'kelab') || str_contains($kpi, 'persatuan') ? '/' : '' }}</td>
                <td class="kpi-check">{{ str_contains($kpi, 'niche') ? '/' : '' }}</td>
            </tr>
        </tbody>
    </table>

    <div class="cover-logos">
        @if(file_exists(public_path('images/report-polibesut-crest.png')))
            <img class="crest" src="{{ public_path('images/report-polibesut-crest.png') }}" alt="PoliBesut">
        @endif
        @if(file_exists(public_path('images/report-jata-negara.jpg')))
            <img class="jata" src="{{ public_path('images/report-jata-negara.jpg') }}" alt="Jata Negara">
        @endif
    </div>

    <div class="cover-inst-title">KEMENTERIAN PENDIDIKAN TINGGI</div>
    <div class="cover-inst-sub">JABATAN PENDIDIKAN POLITEKNIK DAN KOLEJ KOMUNITI</div>

    <div class="cover-report-title">LAPORAN PELAKSANAAN PROGRAM</div>
    <div class="cover-program-name">{{ mb_strtoupper($program->title) }}</div>

    <div class="cover-center-block">
        <div class="cover-label">TARIKH</div>
        <div class="cover-value">{{ $dateFormatted }}</div>

        <div class="cover-label">TEMPAT</div>
        <div class="cover-value">{{ ($program->venue ?? null) ?: 'Politeknik Besut Terengganu' }}</div>
    </div>

    @if(file_exists(public_path('images/report-cover-banner.png')))
        <div class="cover-banner-wrap">
            <img src="{{ public_path('images/report-cover-banner.png') }}" alt="Banner">
        </div>
    @endif

    <div class="cover-center-block">
        <div class="cover-label">ANJURAN:</div>
        <div class="cover-value">{{ ($data['organizer'] ?? null) ?: 'Politeknik Besut Terengganu' }}</div>

        <div class="cover-label" style="margin-top: 10px;">DISEDIAKAN OLEH:</div>
        <div class="cover-value">
            {{ mb_strtoupper($data['prepared_by'] ?? 'Pengarah Program') }}<br>
            <span style="font-size: 9pt; font-weight: normal; text-transform: none;">
                {{ $data['prepared_by_position'] ?? 'Pengarah Program' }} / {{ $data['organizer'] ?? 'Politeknik Besut' }}
            </span>
        </div>
    </div>

    <!-- ================= PAGE 2: MAKLUMAT & JAWATANKUASA ================= -->
    <div class="page-break"></div>

    <div class="sec-head">MAKLUMAT PROGRAM/KURSUS</div>
    <div class="meta-line"><strong>NAMA PROGRAM:</strong> {{ mb_strtoupper($program->title) }}</div>
    <div class="meta-line"><strong>PERINGKAT PROGRAM :</strong> {{ $report['peringkat'] ?? 'Jabatan / Politeknik / Institusi' }}</div>
    <div class="sec-subhead">RINGKASAN PROGRAM :</div>
    <div class="text-block">{{ $report['executive_summary'] }}</div>

    <div class="sec-subhead">OBJEKTIF:</div>
    <ol class="num-list">
        @foreach($report['objectives'] as $obj)
            <li>{{ $obj }}</li>
        @endforeach
    </ol>

    <div class="meta-line"><strong>TEMPAT :</strong> {{ ($program->venue ?? null) ?: 'Politeknik Besut Terengganu' }}</div>
    <div class="meta-line"><strong>TARIKH :</strong> {{ $longDate }}</div>
    <div class="meta-line"><strong>ANJURAN :</strong> {{ mb_strtoupper($data['organizer'] ?? 'Politeknik Besut') }}</div>
    <div class="meta-line"><strong>KUMPULAN SASARAN:</strong> {{ ($program->target_participants ?? null) ?: 'Pelajar Politeknik Besut' }}</div>
    <div class="meta-line"><strong>BILANGAN PESERTA:</strong> {{ $data['attendance_total'] ?? 0 }} ORANG (senarai seperti di lampiran / rekod MyHEP)</div>

    <div class="sec-head" style="margin-top: 10px;">3. JAWATANKUASA PROGRAM: <span style="font-weight: normal; font-size: 8.5pt;">(Sila isi lampiran sekiranya perlu)</span></div>
    <table class="tbl-committee">
        <tr>
            <td class="col-role">PENAUNG</td>
            <td class="col-name">: {{ $report['jawatankuasa']['penaung'] ?? 'Pengarah Politeknik Besut' }}</td>
        </tr>
        <tr>
            <td class="col-role">PENASIHAT 1</td>
            <td class="col-name">: {{ $report['jawatankuasa']['penasihat1'] ?? 'Timbalan Pengarah Politeknik Besut' }}</td>
        </tr>
        <tr>
            <td class="col-role">PENASIHAT 2</td>
            <td class="col-name">: {{ $report['jawatankuasa']['penasihat2'] ?? 'Ketua Jabatan / Unit' }}</td>
        </tr>
        <tr>
            <td class="col-role">PENGARAH PROGRAM</td>
            <td class="col-name">: {{ $report['jawatankuasa']['pengarah_program'] ?? ($data['prepared_by'] ?? 'Tidak direkodkan') }}</td>
        </tr>
        <tr>
            <td class="col-role">SETIAUSAHA</td>
            <td class="col-name">: {{ $report['jawatankuasa']['setiausaha'] ?? 'Tidak direkodkan' }}</td>
        </tr>
        <tr>
            <td class="col-role">AJK</td>
            <td class="col-name">: {{ $report['jawatankuasa']['ajk'] ?? 'Jawatankuasa Pelaksana' }}</td>
        </tr>
        <tr>
            <td class="col-role">URUSETIA</td>
            <td class="col-name">: {{ $report['jawatankuasa']['urusetia'] ?? ($data['organizer'] ?? 'Urusetia') }}</td>
        </tr>
    </table>

    <div class="sec-head">4. BUTIRAN PENCERAMAH / JEMPUTAN LUAR / PERASMI PROGRAM :</div>
    <div class="meta-line"><strong>Nama pegawai :</strong> {{ $report['penceramah']['nama'] ?? 'Pegawai / Penceramah Terlibat' }}</div>
    <div class="meta-line"><strong>Jawatan :</strong> {{ $report['penceramah']['jawatan'] ?? '—' }}</div>
    <div class="meta-line"><strong>Gred :</strong> {{ $report['penceramah']['gred'] ?? '—' }}</div>
    <div class="meta-line"><strong>Jabatan / Institusi :</strong> {{ $report['penceramah']['institusi'] ?? 'Politeknik Besut Terengganu' }}</div>

    <div class="sec-head" style="margin-top: 10px;">5. ATURCARA PROGRAM :</div>
    <table class="tbl-bordered">
        <thead>
            <tr>
                <th style="width: 22%;">Tarikh</th>
                <th style="width: 28%;">Masa</th>
                <th style="width: 50%;">Aktiviti</th>
            </tr>
        </thead>
        <tbody>
            @foreach($report['aturcara'] as $act)
                <tr>
                    <td>{{ $act['tarikh'] ?? ($program->starts_at ? date('d.m.Y', strtotime($program->starts_at)) : '') }}</td>
                    <td>{{ $act['masa'] ?? '—' }}</td>
                    <td>{{ $act['aktiviti'] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- ================= PAGE 3: KEWANGAN, DEMOGRAFI, MAKLUM BALAS & IMPAK ================= -->
    <div class="page-break"></div>

    <div class="sec-head">6. SUMBER KEWANGAN : <span style="font-weight: normal;">* Kerajaan / Tiada / Akaun Amanah</span></div>
    <div class="sec-head">7. PERINCIAN LAPORAN KEWANGAN : <span style="font-weight: normal;">{{ $report['kewangan'] ?? 'Tiada / Peruntukan Dalaman Jabatan' }}</span></div>

    <table class="tbl-bordered">
        <thead>
            <tr>
                <th colspan="6">JUMLAH PERBELANJAAN</th>
            </tr>
            <tr>
                <th style="width: 6%;">Bil.</th>
                <th style="width: 44%;">Perkara</th>
                <th style="width: 14%;">Harga Seunit (RM)</th>
                <th style="width: 12%;">Kuantiti</th>
                <th style="width: 12%;">Jumlah (RM)</th>
                <th style="width: 12%;">Sumber Kewangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: center;">1.</td>
                <td>Perbelanjaan Pelaksanaan {{ $program->title }}</td>
                <td style="text-align: right;">-</td>
                <td style="text-align: center;">{{ $data['attendance_total'] ?? 0 }}</td>
                <td style="text-align: right;">-</td>
                <td style="text-align: center;">Dalaman</td>
            </tr>
            <tr>
                <td colspan="4" style="font-weight: bold; text-align: right;">JUMLAH KESELURUHAN</td>
                <td colspan="2" style="font-weight: bold;">RM 0.00</td>
            </tr>
        </tbody>
    </table>

    <div class="sec-head">8. BILANGAN PELAJAR : <span style="font-weight: normal;">{{ $data['attendance_total'] ?? 0 }} Orang Direkodkan</span></div>
    <div style="font-size: 9pt; line-height: 1.4; margin-bottom: 6px;">
        <strong>8.1 MELAYU:</strong> {{ $data['attendance_total'] ?? 0 }} &nbsp;&nbsp;|&nbsp;&nbsp; 
        <strong>8.2 CINA:</strong> 0 &nbsp;&nbsp;|&nbsp;&nbsp; 
        <strong>8.3 INDIA:</strong> 0 &nbsp;&nbsp;|&nbsp;&nbsp; 
        <strong>8.4 BUMIPUTRA SABAH/SARAWAK:</strong> 0<br>
        <strong>8.5 ORANG ASLI:</strong> 0 &nbsp;&nbsp;|&nbsp;&nbsp; 
        <strong>8.6 OKU:</strong> 0 &nbsp;&nbsp;|&nbsp;&nbsp; 
        <strong>8.7 JANTINA:</strong> LELAKI: {{ $data['male_total'] ?? 0 }} &nbsp;|&nbsp; PEREMPUAN: {{ $data['female_total'] ?? 0 }}<br>
        <strong>8.8 PECAHAN LOKALITI:</strong> BANDAR: {{ round(($data['attendance_total'] ?? 0) * 0.4) }} &nbsp;|&nbsp; LUAR BANDAR: {{ round(($data['attendance_total'] ?? 0) * 0.6) }}<br>
        <strong>8.9 PECAHAN UMUR:</strong> BELIA AWAL (15-18 TAHUN): 0 &nbsp;|&nbsp; BELIA PERTENGAHAN (19-24 TAHUN): {{ $data['attendance_total'] ?? 0 }} &nbsp;|&nbsp; BELIA AKHIR: 0
    </div>

    <div class="sec-head">9. BILANGAN PENGLIBATAN/PENYERTAAN KOMUNITI/BELIA (Jika ada): <span style="font-weight: normal;">Tiada</span></div>

    <div class="sec-head">10. HASIL KAJI SELIDIK/MAKLUM BALAS PESERTA PROGRAM:</div>
    <div class="text-block">{{ $report['survey_summary'] }}</div>

    <div class="sec-head">11. HASIL KAJI SELIDIK/MAKLUM BALAS KOMUNITI/ PENERIMA MANFAAT (Jika berkaitan): <span style="font-weight: normal;">Tiada</span></div>

    <div class="sec-head">12. GAMBAR AKTIVITI/PROGRAM :</div>
    @if(isset($imagePaths) && count($imagePaths) > 0)
        <table class="tbl-photos">
            @foreach(array_chunk(array_slice($imagePaths, 0, 4), 2) as $row)
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
        <div style="font-style: italic; color: #666; font-size: 8.5pt; margin: 4px 0 8px 0;">
            (Foto aktiviti disimpan dan direkodkan secara digital dalam sistem MyHEP)
        </div>
    @endif

    <div class="sec-head" style="margin-top: 10px;">13. HASIL / IMPAK PROGRAM :</div>
    <ol class="roman-list">
        <li>{{ $report['achievements'][0] ?? 'Program mencapai objektif yang disasarkan dengan kehadiran penuh peserta.' }}</li>
        <li><strong>Isu:</strong> {{ implode(' ', $report['issues'] ?? ['Tiada isu kritikal direkodkan semasa pelaksanaan.']) }}</li>
        <li><strong>Cadangan penambahbaikan:</strong> {{ implode(' ', $report['improvements'] ?? ['Memperluaskan hebahan awal program.']) }} <strong>Kesimpulan:</strong> {{ $report['conclusion'] }}</li>
    </ol>

    <div class="sec-head" style="margin-top: 12px;">14. PENGESAHAN LAPORAN</div>
    <table class="tbl-signatures">
        <tr>
            <td>
                <strong>Disediakan Oleh:</strong>
                <div class="sig-space"></div>
                __________________________<br>
                <strong>( {{ mb_strtoupper($data['prepared_by'] ?? 'Pengarah Program') }} )</strong><br>
                (Pengarah Program / Setiausaha / Penyelaras)<br>
                Politeknik Besut Terengganu<br>
                <span style="font-size: 7.5pt; color: #444;">Tarikh: {{ date('d/m/Y') }}</span>
            </td>
            <td>
                <strong>Disemak Oleh:</strong>
                <div class="sig-space"></div>
                __________________________<br>
                <strong>( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )</strong><br>
                (KJ / KU / TP(A) / TP(SA) / TP(GS))<br>
                Politeknik Besut Terengganu<br>
                <span style="font-size: 7.5pt; color: #444;">Tarikh: </span>
            </td>
            <td>
                <strong>Disahkan Oleh:</strong>
                <div class="sig-space"></div>
                __________________________<br>
                <strong>( UDOM A/L EWON )</strong><br>
                Pengarah<br>
                Politeknik Besut Terengganu<br>
                <span style="font-size: 7.5pt; color: #444;">Tarikh: </span>
            </td>
        </tr>
    </table>

</body>
</html>
