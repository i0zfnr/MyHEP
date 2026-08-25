<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>{{ $input['title'] ?? 'Kertas Kerja Program' }}</title>
    <style>
        @page {
            margin: 25mm 20mm 20mm 20mm;
            size: A4 portrait;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 11pt;
            line-height: 1.45;
            color: #000;
        }
        .header-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .meta-table td {
            padding: 5px 8px;
            font-size: 10.5pt;
            vertical-align: top;
        }
        .meta-label {
            font-weight: bold;
            width: 32%;
        }
        .section-title {
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 18px;
            margin-bottom: 6px;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }
        p, ul, ol {
            margin: 6px 0;
            padding-left: 20px;
        }
        p { padding-left: 0; text-align: justify; }
        li { margin-bottom: 4px; }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 10pt;
            vertical-align: top;
        }
        .data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .sign-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        .sign-table td {
            width: 33.33%;
            vertical-align: top;
            padding: 5px;
            font-size: 9.5pt;
        }
        .sign-space {
            height: 60px;
        }
    </style>
</head>
<body>
    <div class="header-title">
        KERTAS KERJA<br>
        {{ mb_strtoupper($input['title'] ?? 'PROGRAM POLITEKNIK BESUT') }}
    </div>

    <table class="meta-table">
        <tr>
            <td class="meta-label">TARIKH</td>
            <td>: {{ $input['date_text'] ?? date('d.m.Y') }}</td>
        </tr>
        <tr>
            <td class="meta-label">TEMPAT</td>
            <td>: {{ $input['venue'] ?? 'Politeknik Besut Terengganu' }}</td>
        </tr>
        <tr>
            <td class="meta-label">ANJURAN</td>
            <td>: {{ $input['organizer'] ?? 'Politeknik Besut Terengganu' }}</td>
        </tr>
        <tr>
            <td class="meta-label">KUMPULAN SASARAN</td>
            <td>: {{ $input['target_group'] ?? 'Pelajar Politeknik Besut' }}</td>
        </tr>
        <tr>
            <td class="meta-label">BILANGAN PESERTA</td>
            <td>: {{ $input['participant_count'] ?? '30 Orang' }}</td>
        </tr>
        <tr>
            <td class="meta-label">PERINGKAT PROGRAM</td>
            <td>: {{ $content['peringkat'] ?? 'Politeknik / Institusi' }}</td>
        </tr>
        <tr>
            <td class="meta-label">KLUSTER KPI</td>
            <td>: {{ $content['kluster_kpi'] ?? 'Kemahiran dan Inovasi' }}</td>
        </tr>
    </table>

    <div class="section-title">1. RINGKASAN PROGRAM</div>
    <p>{{ $content['ringkasan_program'] ?? '' }}</p>

    <div class="section-title">2. OBJEKTIF PROGRAM</div>
    <ol>
        @foreach($content['objektif'] ?? [] as $obj)
            <li>{{ $obj }}</li>
        @endforeach
    </ol>

    <div class="section-title">3. HASIL / IMPAK PROGRAM</div>
    <ol>
        @foreach($content['impak_program'] ?? [] as $imp)
            <li>{{ $imp }}</li>
        @endforeach
    </ol>

    <div class="section-title">4. JAWATANKUASA PROGRAM</div>
    <table class="meta-table" style="margin-bottom:10px;">
        <tr>
            <td style="width:28%;font-weight:bold;">PENAUNG</td>
            <td>: {{ $content['jawatankuasa']['penaung'] ?? 'Udom A/L Ewon (Pengarah)' }}</td>
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
            <td style="font-weight:bold;">AJK PELAKSANA</td>
            <td>: {{ $content['jawatankuasa']['ajk'] ?? 'Jawatankuasa Pelaksana' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold;">URUSETIA</td>
            <td>: {{ $content['jawatankuasa']['urusetia'] ?? 'Urusetia Program' }}</td>
        </tr>
    </table>

    <div class="section-title">5. BUTIRAN PENCERAMAH / PEGAWAI TERLIBAT</div>
    <table class="meta-table" style="margin-bottom:10px;">
        <tr>
            <td style="width:28%;font-weight:bold;">Nama Pegawai</td>
            <td>: {{ $content['penceramah']['nama'] ?? 'Pegawai Terlibat' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold;">Jawatan / Gred</td>
            <td>: {{ $content['penceramah']['jawatan'] ?? 'Pegawai Pendidikan' }} ({{ $content['penceramah']['gred'] ?? 'DH48/DH52' }})</td>
        </tr>
        <tr>
            <td style="font-weight:bold;">Jabatan / Institusi</td>
            <td>: {{ $content['penceramah']['institusi'] ?? 'Politeknik Besut Terengganu' }}</td>
        </tr>
    </table>

    <div class="section-title">6. ATURCARA PROGRAM</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:22%;">Tarikh</th>
                <th style="width:28%;">Masa</th>
                <th>Aktiviti / Pengisian</th>
            </tr>
        </thead>
        <tbody>
            @foreach($content['aturcara'] ?? [] as $row)
                <tr>
                    <td>{{ $row['tarikh'] ?? $input['date_text'] }}</td>
                    <td>{{ $row['masa'] ?? '' }}</td>
                    <td>{{ $row['aktiviti'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">7. ANGGARAN PERBELANJAAN</div>
    <p><strong>Sumber Kewangan:</strong> {{ $content['sumber_kewangan'] ?? 'Kerajaan / Akaun Amanah' }}</p>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width:30px;">Bil</th>
                <th>Perkara</th>
                <th style="width:90px;">Harga (RM)</th>
                <th style="width:80px;">Kuantiti</th>
                <th style="width:95px;">Jumlah (RM)</th>
                <th style="width:80px;">Sumber</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($content['anggaran_belanja'] ?? [] as $idx => $exp)
                @php 
                    $amt = (float)($exp['jumlah'] ?? 0); 
                    $total += $amt;
                @endphp
                <tr>
                    <td style="text-align:center;">{{ $idx + 1 }}</td>
                    <td>{{ $exp['perkara'] ?? '' }}</td>
                    <td style="text-align:right;">{{ number_format((float)($exp['harga_seunit'] ?? 0), 2) }}</td>
                    <td style="text-align:center;">{{ $exp['kuantiti'] ?? 1 }}</td>
                    <td style="text-align:right;">{{ number_format($amt, 2) }}</td>
                    <td style="text-align:center;">{{ $exp['sumber'] ?? 'OS29000' }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4" style="text-align:right;font-weight:bold;">JUMLAH KESELURUHAN:</td>
                <td style="text-align:right;font-weight:bold;">RM {{ number_format($total, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">8. PENUTUP</div>
    <p>{{ $content['penutup'] ?? 'Diharapkan program ini dapat mencapai objektif yang telah digariskan serta mendapat kelulusan pihak pengurusan.' }}</p>

    <div class="section-title">9. KELULUSAN KERTAS KERJA</div>
    <table class="sign-table">
        <tr>
            <td>
                <strong>Disediakan Oleh:</strong>
                <div class="sign-space"></div>
                ___________________________<br>
                ( {{ mb_strtoupper($content['jawatankuasa']['pengarah_program'] ?? session('auth_user.name')) }} )<br>
                Pengarah Program<br>
                Tarikh: {{ date('d.m.Y') }}
            </td>
            <td>
                <strong>Disemak Oleh:</strong>
                <div class="sign-space"></div>
                ___________________________<br>
                ( {{ mb_strtoupper($content['jawatankuasa']['penasihat1'] ?? 'KETUA JABATAN') }} )<br>
                KJ / KU / TPSA / TPGS<br>
                Tarikh:
            </td>
            <td>
                <strong>Diluluskan Oleh:</strong>
                <div class="sign-space"></div>
                ___________________________<br>
                ( UDOM A/L EWON )<br>
                Pengarah<br>
                Politeknik Besut Terengganu<br>
                Tarikh:
            </td>
        </tr>
    </table>
</body>
</html>
