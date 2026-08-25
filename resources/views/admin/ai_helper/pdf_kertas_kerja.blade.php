<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>{{ $input['title'] ?? 'Kertas Kerja Program' }}</title>
    <style>
        @page {
            margin: 20mm 18mm 20mm 18mm;
            size: A4 portrait;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10pt;
            line-height: 1.35;
            color: #000;
        }
        .header-top {
            text-align: right;
            font-size: 9pt;
            font-weight: bold;
            color: #d9534f;
            margin-bottom: 12px;
        }
        .header-inst {
            color: #555;
            font-size: 9pt;
        }
        .doc-main-title {
            text-align: center;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 16px;
        }
        .sec-header {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 14px;
            margin-bottom: 6px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .grid-table th, .grid-table td {
            border: 1px solid #000;
            padding: 4px 6px;
            font-size: 9pt;
        }
        .grid-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: center;
        }
        .info-table td {
            padding: 3px 4px;
            font-size: 9.5pt;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            width: 25%;
        }
        .roman-list, .alpha-list {
            margin: 4px 0;
            padding-left: 22px;
        }
        .roman-list li, .alpha-list li {
            margin-bottom: 3px;
            text-align: justify;
        }
        .sign-grid {
            width: 100%;
            margin-top: 20px;
        }
        .sign-grid td {
            width: 33.33%;
            vertical-align: top;
            padding: 4px;
            font-size: 9pt;
        }
        .sign-space {
            height: 50px;
        }
        .checkbox-box {
            display: inline-block;
            width: 14px;
            height: 14px;
            border: 1px solid #000;
            vertical-align: middle;
            margin-right: 4px;
            text-align: center;
            font-size: 10pt;
            line-height: 12px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    <div class="header-top">
        <span class="header-inst">PoliBesut |</span> ({{ mb_strtoupper($input['organizer'] ?? 'JABATAN / UNIT') }})
    </div>

    <div class="doc-main-title">
        ({{ mb_strtoupper($input['organizer'] ?? 'JAWATAN / JABATAN / UNIT') }})
    </div>

    <!-- 1. KATEGORI KURSUS -->
    <div class="sec-header">1. KATEGORI KURSUS:</div>
    <table class="grid-table" style="margin-bottom: 12px;">
        <thead>
            <tr>
                <th style="width:30px;">BIL</th>
                <th>BIDANG KURSUS</th>
                <th style="width:25px;">/</th>
                <th style="width:30px;">BIL</th>
                <th>BIDANG KURSUS</th>
                <th style="width:25px;">/</th>
            </tr>
        </thead>
        <tbody>
            @php
                $cat = strtolower((string)($content['kategori_kursus'] ?? $content['kluster_kpi'] ?? ''));
                $is06 = str_contains($cat, 'pengajaran') || str_contains($cat, 'pembelajaran') || $cat === '06';
                $is09 = str_contains($cat, 'teknologi') || str_contains($cat, 'maklumat') || str_contains($cat, 'it') || $cat === '09';
                $is01 = str_contains($cat, 'kepimpinan') || $cat === '01';
            @endphp
            <tr>
                <td style="text-align:center;">01</td>
                <td>Kepimpinan</td>
                <td style="text-align:center;font-weight:bold;">{{ $is01 ? '/' : '' }}</td>
                <td style="text-align:center;">06</td>
                <td>Pengajaran & Pembelajaran</td>
                <td style="text-align:center;font-weight:bold;">{{ $is06 ? '/' : '' }}</td>
            </tr>
            <tr>
                <td style="text-align:center;">02</td>
                <td>Kewangan</td>
                <td></td>
                <td style="text-align:center;">07</td>
                <td>Pentadbiran/Pengurusan</td>
                <td></td>
            </tr>
            <tr>
                <td style="text-align:center;">03</td>
                <td>Lain-lain</td>
                <td></td>
                <td style="text-align:center;">08</td>
                <td>Teknikal</td>
                <td></td>
            </tr>
            <tr>
                <td style="text-align:center;">04</td>
                <td>Pembangunan & Penyelidikan</td>
                <td></td>
                <td style="text-align:center;">09</td>
                <td>Teknologi Maklumat</td>
                <td style="text-align:center;font-weight:bold;">{{ $is09 ? '/' : ($is06 ? '' : '/') }}</td>
            </tr>
            <tr>
                <td style="text-align:center;">05</td>
                <td>Pembangunan Diri</td>
                <td></td>
                <td style="text-align:center;">10</td>
                <td>Perkeranian</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <!-- 2. MAKLUMAT PROGRAM/KURSUS -->
    <div class="sec-header">2. MAKLUMAT PROGRAM/KURSUS</div>
    <table class="info-table">
        <tr>
            <td class="info-label" style="width:28%;">a. NAMA PROGRAM</td>
            <td>: [{{ mb_strtoupper($input['title'] ?? 'NAMA PROGRAM') }}]</td>
        </tr>
        <tr>
            <td class="info-label">b. PERINGKAT PROGRAM</td>
            <td>: {{ $content['peringkat'] ?? 'Jabatan / Politeknik / Institusi / Komuniti / Negeri / Kebangsaan / Antarabangsa' }}</td>
        </tr>
        <tr>
            <td class="info-label" colspan="2">c. RINGKASAN PROGRAM :</td>
        </tr>
        <tr>
            <td colspan="2" style="padding-left:15px; text-align:justify;">
                [{{ $content['ringkasan_program'] ?? 'Program ini dirangka bagi meningkatkan kemahiran dan pengetahuan peserta dalam bidang berkaitan.' }}]
            </td>
        </tr>
        <tr>
            <td class="info-label" colspan="2" style="padding-top:6px;">d. OBJEKTIF:</td>
        </tr>
        <tr>
            <td colspan="2" style="padding-left:15px;">
                <ol type="i" class="roman-list" style="margin:0; padding-left:15px;">
                    @foreach($content['objektif'] ?? [] as $obj)
                        <li>{{ $obj }}</li>
                    @endforeach
                    @if(empty($content['objektif']))
                        <li>Memberikan pendedahan secara komprehensif kepada peserta mengenai skop dan pengisian program.</li>
                        <li>Memantapkan kefahaman dan kemahiran praktikal peserta dalam mengendalikan tugasan berkaitan.</li>
                        <li>Memupuk semangat kerjasama dan komitmen tinggi dalam kalangan warga Politeknik Besut Terengganu.</li>
                    @endif
                </ol>
            </td>
        </tr>
        <tr>
            <td class="info-label">e. TEMPAT</td>
            <td>: [{{ mb_strtoupper($input['venue'] ?? 'POLITEKNIK BESUT TERENGGANU') }}]</td>
        </tr>
        <tr>
            <td class="info-label">f. TARIKH</td>
            <td>: [{{ mb_strtoupper($input['date_text'] ?? date('d F Y')) }}]</td>
        </tr>
        <tr>
            <td class="info-label">g. ANJURAN</td>
            <td>: [{{ mb_strtoupper($input['organizer'] ?? 'POLITEKNIK BESUT TERENGGANU') }}]</td>
        </tr>
        <tr>
            <td class="info-label">h. KUMPULAN SASARAN</td>
            <td>: [{{ mb_strtoupper($input['target_group'] ?? 'PELAJAR / PENSYARAH') }}]</td>
        </tr>
        <tr>
            <td class="info-label">i. BILANGAN PESERTA</td>
            <td>: [{{ mb_strtoupper($input['participant_count'] ?? '30 ORANG') }} - senarai seperti di lampiran]</td>
        </tr>
    </table>

    <div class="page-break"></div>

    <div class="header-top">
        <span class="header-inst">PoliBesut |</span> ({{ mb_strtoupper($input['organizer'] ?? 'JABATAN / UNIT') }})
    </div>

    <!-- 3. HASIL / IMPAK PROGRAM -->
    <div class="sec-header">3. HASIL / IMPAK PROGRAM :</div>
    <ol type="i" class="roman-list">
        @foreach($content['impak_program'] ?? [] as $imp)
            <li>{{ $imp }}</li>
        @endforeach
        @if(empty($content['impak_program']))
            <li>Peningkatan kefahaman dan kemahiran teknikal peserta dalam modul yang dipelajari.</li>
            <li>Peningkatan kecekapan, produktiviti dan kualiti penyampaian program di peringkat politeknik.</li>
            <li>Pengukuhan jaringan kolaborasi dan pemantapan sahsiah peserta secara menyeluruh.</li>
        @endif
    </ol>

    <!-- 4. JAWATANKUASA PROGRAM -->
    <div class="sec-header">4. JAWATANKUASA PROGRAM: (Sila isi lampiran sekiranya perlu)</div>
    <table class="info-table" style="margin-left: 15px; width: 95%;">
        <tr>
            <td style="width:28%;font-weight:bold;">PENAUNG</td>
            <td>: {{ $content['jawatankuasa']['penaung'] ?? 'Udom A/L Ewon (Pengarah Politeknik Besut)' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold;">PENASIHAT 1</td>
            <td>: {{ $content['jawatankuasa']['penasihat1'] ?? 'Saifuddin Bin Semail' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold;">PENASIHAT 2</td>
            <td>: {{ $content['jawatankuasa']['penasihat2'] ?? 'Ts. Elisnorazmaliza Bt Ab. Hamid' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold;">PENGARAH PROGRAM</td>
            <td>: {{ $content['jawatankuasa']['pengarah_program'] ?? session('auth_user.name') }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold;">SETIAUSAHA</td>
            <td>: {{ $content['jawatankuasa']['setiausaha'] ?? 'Setiausaha Program' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold;">AJK</td>
            <td>: {{ $content['jawatankuasa']['ajk'] ?? 'Jawatankuasa Pelaksana Program' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold;">URUSETIA</td>
            <td>: {{ $content['jawatankuasa']['urusetia'] ?? 'HEP' }}</td>
        </tr>
    </table>

    <!-- 5. BUTIRAN PENCERAMAH -->
    <div class="sec-header" style="margin-top:14px;">5. BUTIRAN PENCERAMAH / JEMPUTAN LUAR / PERASMI PROGRAM :</div>
    <table class="info-table" style="margin-left: 15px; width: 95%;">
        <tr>
            <td style="width:25%;">Nama pegawai</td>
            <td>: {{ $content['penceramah']['nama'] ?? 'Ts. Elisnorazmaliza Bt Ab. Hamid (TPGS)' }}</td>
        </tr>
        <tr>
            <td>Jawatan</td>
            <td>: {{ $content['penceramah']['jawatan'] ?? 'Pegawai Pendidikan Pengajian Tinggi' }}</td>
        </tr>
        <tr>
            <td>Gred</td>
            <td>: {{ $content['penceramah']['gred'] ?? 'DH52' }}</td>
        </tr>
        <tr>
            <td>Jabatan / Institusi</td>
            <td>: {{ $content['penceramah']['institusi'] ?? 'Politeknik Besut Terengganu' }}</td>
        </tr>
    </table>

    <!-- 6. ATURCARA PROGRAM -->
    <div class="sec-header" style="margin-top:14px;">6. ATURCARA PROGRAM :</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width:25%;">Tarikh</th>
                <th style="width:25%;">Masa</th>
                <th>Aktiviti</th>
            </tr>
        </thead>
        <tbody>
            @if(!empty($content['aturcara']) && is_array($content['aturcara']))
                @foreach($content['aturcara'] as $row)
                    <tr>
                        <td style="text-align:center;">{{ $row['tarikh'] ?? $input['date_text'] }}</td>
                        <td style="text-align:center;">{{ $row['masa'] ?? '' }}</td>
                        <td>{{ $row['aktiviti'] ?? '' }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td style="text-align:center; height:24px;">{{ $input['date_text'] ?? date('d.m.Y') }}</td>
                    <td style="text-align:center;">08.30 pg – 08.45 pg</td>
                    <td>Sesi 1 : Pengenalan</td>
                </tr>
                <tr>
                    <td style="text-align:center; height:24px;"></td>
                    <td style="text-align:center;">08.45 pg – 10.00 pg</td>
                    <td>Sesi 2 : Modul Praktikal / Bengkel</td>
                </tr>
                <tr>
                    <td style="text-align:center; height:24px;"></td>
                    <td style="text-align:center;">10.00 pg – 01.00 tgh</td>
                    <td>Sesi 3 : Latihan Lanjutan</td>
                </tr>
                <tr>
                    <td style="text-align:center; height:24px;"></td>
                    <td style="text-align:center;">01.00 tgh – 02.30 ptg</td>
                    <td>Makan Tengahari & Solat</td>
                </tr>
                <tr>
                    <td style="text-align:center; height:24px;"></td>
                    <td style="text-align:center;">02.30 ptg – 04.30 ptg</td>
                    <td>Sesi 4 & Penutup</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="page-break"></div>

    <div class="header-top">
        <span class="header-inst">PoliBesut |</span> ({{ mb_strtoupper($input['organizer'] ?? 'JABATAN / UNIT') }})
    </div>

    <!-- 7. SUMBER KEWANGAN -->
    <div class="sec-header">7. SUMBER KEWANGAN : &nbsp;&nbsp; * Kerajaan / Tiada / Akaun Amanah</div>
    <div style="font-size:8.5pt; font-style:italic; margin-bottom:10px;">*Potong yang tidak berkaitan</div>

    <!-- 8. PERUNTUKAN KEWANGAN -->
    <div class="sec-header">8. PERUNTUKAN KEWANGAN : &nbsp;&nbsp;
        <span class="checkbox-box">&#10003;</span> Berkaitan &nbsp;&nbsp;&nbsp;&nbsp;
        <span class="checkbox-box"></span> Tidak Berkaitan
    </div>

    <div style="text-align:center; font-weight:bold; font-size:9.5pt; margin-top:8px; margin-bottom:4px;">ANGGARAN PERBELANJAAN</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width:25px;">Bil.</th>
                <th>Perkara<br><span style="font-weight:normal; font-size:7.5pt;">(Contoh: Makanan & minuman, penginapan, pengangkutan, tuntutan, penceramah, sewaan, peralatan kursus)</span></th>
                <th style="width:75px;">Harga Seunit (RM)</th>
                <th style="width:70px;">Kuantiti<br><span style="font-weight:normal; font-size:7.5pt;">(Bil Peserta / Unit)</span></th>
                <th style="width:80px;">Jumlah (RM)</th>
                <th style="width:80px;">Sumber Kewangan<br><span style="font-weight:normal; font-size:7.5pt;">(Contoh: OS42000, OS29000, OS24000)</span></th>
            </tr>
        </thead>
        <tbody>
            @php $totExp = 0; @endphp
            @if(!empty($content['anggaran_belanja']) && is_array($content['anggaran_belanja']))
                @foreach($content['anggaran_belanja'] as $idx => $item)
                    @php $sum = (float)($item['jumlah'] ?? 0); $totExp += $sum; @endphp
                    <tr>
                        <td style="text-align:center;">{{ $idx + 1 }}.</td>
                        <td>{{ $item['perkara'] ?? '' }}</td>
                        <td style="text-align:right;">{{ number_format((float)($item['harga_seunit'] ?? 0), 2) }}</td>
                        <td style="text-align:center;">{{ $item['kuantiti'] ?? 1 }}</td>
                        <td style="text-align:right;">{{ number_format($sum, 2) }}</td>
                        <td style="text-align:center;">{{ $item['sumber'] ?? 'OS29000' }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td style="text-align:center;">1.</td>
                    <td>Makan Tengahari</td>
                    <td style="text-align:right;">RM10.00</td>
                    <td style="text-align:center;">26</td>
                    <td style="text-align:right;">260.00</td>
                    <td style="text-align:center;">OS29000</td>
                </tr>
            @endif
            <tr>
                <td colspan="4" style="text-align:right; font-weight:bold;">JUMLAH KESELURUHAN</td>
                <td style="text-align:right; font-weight:bold;">RM {{ number_format($totExp ?: 260, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div style="text-align:center; font-weight:bold; font-size:9.5pt; margin-top:12px; margin-bottom:4px;">ANGGARAN TERIMAAN (PROGRAM PSH SAHAJA)</div>
    <table class="grid-table">
        <thead>
            <tr>
                <th style="width:25px;">Bil.</th>
                <th>Perkara</th>
                <th style="width:75px;">Harga Seunit (RM)</th>
                <th style="width:70px;">Kuantiti<br><span style="font-weight:normal; font-size:7.5pt;">(Bil Peserta / Unit)</span></th>
                <th style="width:80px;">Jumlah (RM)</th>
                <th style="width:80px;">-</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align:center;">1.</td>
                <td>Yuran Kursus</td>
                <td style="text-align:center;">-</td>
                <td style="text-align:center;">-</td>
                <td style="text-align:center;">-</td>
                <td style="text-align:center;">-</td>
            </tr>
            <tr>
                <td colspan="4" style="text-align:right; font-weight:bold;">JUMLAH KESELURUHAN</td>
                <td style="text-align:center; font-weight:bold;">RM -</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <p style="margin-top:14px; font-size:9pt;">
        Disahkan bahawa baki peruntukan di bawah pecahan kepala ..................................................... adalah mencukupi bagi menampung kos aktiviti ini.
    </p>

    <div style="margin-top:20px; font-size:9pt;">
        .................................................................................<br>
        (Tandatangan Penolong Akauntan & cap rasmi)<br>
        Tarikh :
    </div>

    <!-- 9. SEMAKAN KULPL -->
    <div class="sec-header" style="margin-top:16px;">9. SEMAKAN KULPL: &nbsp;&nbsp;
        <span class="checkbox-box">&#10003;</span> Berkaitan &nbsp;&nbsp;&nbsp;&nbsp;
        <span class="checkbox-box"></span> Tidak Berkaitan
    </div>
    <div style="font-size:8.5pt; font-style:italic; margin-bottom:10px;">
        Semakan KULPL bagi program PSH atau program latihan staf seperti kursus, taklimat dan lain-lain.
    </div>

    <div style="font-size:9pt; margin-top:8px;">
        <strong>Disemak Oleh:</strong><br><br>
        .................................................................................<br>
        ( <strong>NIK HAYATI BINTI NIK ABDULLAH</strong> )<br>
        (Ketua Unit Latihan dan Pendidikan Lanjutan)<br>
        Politeknik Besut Terengganu &nbsp;&nbsp;&nbsp;&nbsp; Tarikh:
    </div>

    <div class="page-break"></div>

    <div class="header-top">
        <span class="header-inst">PoliBesut |</span> ({{ mb_strtoupper($input['organizer'] ?? 'JABATAN / UNIT') }})
    </div>

    <!-- 10. PENUTUP -->
    <div class="sec-header">10. PENUTUP</div>
    <p style="text-align:justify; margin-bottom:20px;">
        {{ $content['penutup'] ?? 'Diharapkan program / aktiviti yang akan dilaksanakan dapat mencapai objektif yang telah ditetapkan.' }}
    </p>

    <!-- 11. KELULUSAN KERTAS KERJA -->
    <div class="sec-header">11. KELULUSAN KERTAS KERJA</div>

    <table class="sign-grid">
        <tr>
            <td>
                <strong>Disediakan Oleh:</strong>
                <div class="sign-space"></div>
                __________________________________<br>
                ( {{ mb_strtoupper($content['jawatankuasa']['pengarah_program'] ?? session('auth_user.name') ?? 'PENGARAH PROGRAM') }} )<br>
                (Pengarah Program / Setiausaha / Penyelaras)<br>
                Politeknik Besut Terengganu<br>
                Tarikh: {{ $input['date_text'] ?? date('d.m.Y') }}
            </td>
            <td>
                <strong>Disemak Oleh:</strong>
                <div class="sign-space"></div>
                __________________________________<br>
                ( &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; )<br>
                (KJ / KU / TP(A) / TP(SA) / TP(GS))<br>
                Politeknik Besut Terengganu<br>
                Tarikh:
            </td>
            <td>
                <strong>Diluluskan Oleh:</strong>
                <div class="sign-space"></div>
                __________________________________<br>
                ( <strong>UDOM A/L EWON</strong> )<br>
                Pengarah<br>
                Politeknik Besut Terengganu<br>
                Tarikh:
            </td>
        </tr>
    </table>

</body>
</html>
