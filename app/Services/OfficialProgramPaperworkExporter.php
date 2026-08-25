<?php

namespace App\Services;

use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use ZipArchive;

class OfficialProgramPaperworkExporter
{
    public function export(array $input, array $content, string $format, int $adminId): array
    {
        $directory = 'program-paperworks/' . $adminId;
        Storage::disk('local')->makeDirectory($directory);
        $stamp = now()->format('Ymd_His') . '_' . Str::random(5);
        $paths = ['docx_path' => null, 'pdf_path' => null];

        $docxPath = $directory . '/kertas-kerja-' . $stamp . '.docx';
        $docxAbsolutePath = Storage::disk('local')->path($docxPath);

        // 1. Write DOCX from official template
        $this->writeDocxDirect($input, $content, $docxAbsolutePath);
        if (in_array($format, ['docx', 'both'], true)) {
            $paths['docx_path'] = $docxPath;
        }

        // 2. Render PDF
        if (in_array($format, ['pdf', 'both'], true)) {
            $pdfPath = $directory . '/kertas-kerja-' . $stamp . '.pdf';
            $paths['pdf_path'] = $pdfPath;
            $this->renderPdf($input, $content, Storage::disk('local')->path($pdfPath));
        }

        if ($format === 'pdf' && file_exists($docxAbsolutePath)) {
            @unlink($docxAbsolutePath);
        }

        return $paths;
    }

    private function writeDocxDirect(array $input, array $content, string $destination): void
    {
        $template = resource_path('report-templates/FORMAT KERTAS KERJA POLIBESUT 2025.docx');
        copy($template, $destination);

        $zip = new ZipArchive();
        if ($zip->open($destination) !== true) {
            return;
        }

        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) {
            $zip->close();
            return;
        }

        // 1. Convert all red text styling to professional black
        $xml = preg_replace('/<w:color w:val="[fF]{2}0000"\/>/u', '<w:color w:val="000000"/>', $xml);

        $title = mb_strtoupper($input['title'] ?? 'PROGRAM POLITEKNIK BESUT');
        $date = $input['date_text'] ?? date('d.m.Y');
        $venue = mb_strtoupper($input['venue'] ?? 'POLITEKNIK BESUT TERENGGANU');
        $organizer = mb_strtoupper($input['organizer'] ?? 'POLITEKNIK BESUT TERENGGANU');
        $preparedBy = mb_strtoupper(session('auth_user.name') ?: ($content['jawatankuasa']['pengarah_program'] ?? 'PENGARAH PROGRAM'));
        $targetGroup = mb_strtoupper($input['target_group'] ?? 'PELAJAR POLITEKNIK BESUT');
        $participantCount = mb_strtoupper($input['participant_count'] ?? '30 ORANG');

        $objectives = array_values($content['objektif'] ?? []);
        $obj1 = $objectives[0] ?? 'Memberikan pendedahan komprehensif mengenai pelaksanaan program.';
        $obj2 = $objectives[1] ?? 'Memantapkan kemahiran dan kefahaman peserta dalam bidang yang diceburi.';
        $obj3 = $objectives[2] ?? (implode(' ', array_slice($objectives, 2)) ?: 'Memupuk semangat kerjasama dan komitmen tinggi warga Politeknik Besut.');

        $impacts = array_values($content['impak_program'] ?? []);
        $imp1 = $impacts[0] ?? 'Peningkatan kefahaman tentang fungsi dan modul program.';
        $imp2 = $impacts[1] ?? 'Peningkatan kecekapan dan produktiviti pelaksanaan aktiviti.';
        $imp3 = $impacts[2] ?? (implode(' ', array_slice($impacts, 2)) ?: 'Pemantapan sistem pengurusan dan sahsiah pelajar.');

        $jk = $content['jawatankuasa'] ?? [];
        $penceramah = $content['penceramah'] ?? [];

        // Exact replacements matching FORMAT KERTAS KERJA POLIBESUT 2025.docx
        $replacements = [
            '[05.02.2025 (RABU)]' => $date,
            '[BILIK SEMINAR ULPL]' => $venue,
            'ANJURAN: [JABATAN /UNIT]' => 'ANJURAN: ' . $organizer,
            'DISEDIAKAN OLEH:[NAMA PELAJAR/PEGAWAI(JAWATAN/JABATAN/UNIT)]' => 'DISEDIAKAN OLEH: ' . $preparedBy,
            'NAMA PROGRAM: [KURSUS PEMANTAPAN SPMP]' => 'NAMA PROGRAM: ' . $title,
            'PERINGKAT PROGRAM : Jabatan/ Politeknik/ Institusi/ Komuniti/ Negeri/ Kebangsaan/ Antarabangsa' => 'PERINGKAT PROGRAM : ' . ($content['peringkat'] ?? 'Politeknik / Institusi'),
            'RINGKASAN PROGRAM : [Sistem Pengurusan Maklumat Politeknik (SPMP) merupakan satu platform penting dalam pengurusan data akademik dan pentadbiran di politeknik. Bagi memastikan keberkesanan penggunaan sistem ini, satu kursus pemantapan akan diadakan bagi meningkatkan pemahaman serta kemahiran pengguna dalam mengendalikan sistem ini dengan lebih cekap dan berkesan.]' => 'RINGKASAN PROGRAM : ' . ($content['ringkasan_program'] ?? 'Program ini dirangka khusus untuk meningkatkan kemahiran dan pengetahuan peserta.'),
            'Memberikan pendedahan kepada pensyarah  yang baharu melapor diri ke PoliBesut tentang modul-modul-modul yang digunapakai dalam SPMP.' => $obj1,
            'Memantapkan kefahaman sediaada pensyarah terhadap penggunaan modul-modul dalam SPMP.' => $obj2,
            'Mengelakkan teguran berulang oleh pihak juruaudit dalaman terhadap pelaksanaan sistem SPMP.' => $obj3,
            'TEMPAT : [MAKMAL CSDL JTMK POLIBESUT]' => 'TEMPAT : ' . $venue,
            'TARIKH : [17 FEBRUARI 2025]' => 'TARIKH : ' . $date,
            'ANJURAN : [UNIT LATIHAN DAN PENDIDIKAN LANJUTAN]' => 'ANJURAN : ' . $organizer,
            'KUMPULAN SASARAN: [PENSYARAH]' => 'KUMPULAN SASARAN: ' . $targetGroup,
            'BILANGAN PESERTA: [20 ORANG- senarai seperti di lampiran]' => 'BILANGAN PESERTA: ' . $participantCount,
            'Peningkatan kefahaman tentang fungsi modul dalam SPMP.' => $imp1,
            'Peningkatan kecekapan dan produktiviti dalam pengurusan data akademik.' => $imp2,
            'Pemantapan sistem pengurusan akademik dan pelajar melalui penggunaan SPMP yang lebih berkesan.' => $imp3,
            ': Udom A/L Ewon' => ': ' . ($jk['penaung'] ?? 'Udom A/L Ewon (Pengarah)'),
            ': Saifuddin Bin Semail' => ': ' . ($jk['penasihat1'] ?? 'Saifuddin Bin Semail'),
            ': Ts. Elisnorazmaliza Bt Ab. Hamid' => ': ' . ($jk['penasihat2'] ?? 'Ts. Elisnorazmaliza Bt Ab. Hamid'),
            ': Norakmar Binti Mohd Nadzari' => ': ' . ($jk['pengarah_program'] ?? $preparedBy),
            ': Wan Zamilah Binti Wan Ibrahim' => ': ' . ($jk['setiausaha'] ?? 'Setiausaha Program'),
            ': Endon Binti Che Mat' => ': ' . ($jk['ajk'] ?? 'Jawatankuasa Pelaksana'),
            ': Nik Hayati Binti Nik Abdullah' => ': ' . ($jk['urusetia'] ?? 'Urusetia Program'),
            'Nama pegawai      : Ts. Elisnorazmaliza Bt Ab. Hamid (TPGS)' => 'Nama pegawai      : ' . ($penceramah['nama'] ?? 'Pegawai / Penceramah Jemputan'),
            'Jawatan                 : Pegawai Pendidikan Pengajian Tinggi' => 'Jawatan                 : ' . ($penceramah['jawatan'] ?? 'Pegawai Pendidikan'),
            'Gred                   : DH52' => 'Gred                   : ' . ($penceramah['gred'] ?? '—'),
            'Jabatan / Institusi   : Politeknik Besut Terengganu' => 'Jabatan / Institusi   : ' . ($penceramah['institusi'] ?? 'Politeknik Besut Terengganu'),
        ];

        foreach ($replacements as $search => $replace) {
            $escaped = htmlspecialchars((string) $replace, ENT_XML1 | ENT_COMPAT, 'UTF-8');
            $xml = str_replace($search, $escaped, $xml);
        }

        // Ticking the KPI cluster box in the template header
        $kpiKey = strtolower($content['kluster_kpi'] ?? 'kemahiran');
        $paraIdMap = [
            'sukarelawan' => '00000012',
            'patriot' => '00000013',
            'perpaduan' => '00000014',
            'kepimpinan' => '00000015',
            'komunikasi' => '00000016',
            'kebudayaan' => '00000017',
            'kerohanian' => '00000018',
            'psikologi' => '00000019',
            'sukan' => '0000001A',
            'kesihatan' => '0000001B',
            'kemahiran' => '0000001C',
            'inovasi' => '0000001C',
            'kelab' => '0000001D',
            'niche' => '0000001E',
        ];
        $targetParaId = '0000001C';
        foreach ($paraIdMap as $keyword => $pId) {
            if (str_contains($kpiKey, $keyword)) {
                $targetParaId = $pId;
                break;
            }
        }
        $checkSearch = '<w:p w14:paraId="' . $targetParaId . '"';
        $checkReplace = '<w:p w14:paraId="' . $targetParaId . '"><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="Arial" w:cs="Arial" w:eastAsia="Arial" w:hAnsi="Arial"/><w:b w:val="1"/><w:color w:val="000000"/><w:sz w:val="22"/><w:szCs w:val="22"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:cs="Arial" w:eastAsia="Arial" w:hAnsi="Arial"/><w:b w:val="1"/><w:color w:val="000000"/><w:sz w:val="22"/><w:szCs w:val="22"/></w:rPr><w:t>/</w:t></w:r></w:p>';
        if (str_contains($xml, $checkSearch)) {
            $xml = preg_replace('/<w:p [^>]*w14:paraId="' . $targetParaId . '".*?<\/w:p>/s', $checkReplace, $xml, 1);
        }

        $zip->addFromString('word/document.xml', $xml);
        $zip->close();
    }

    private function renderPdf(array $input, array $content, string $destination): void
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);

        $html = view('admin.ai_helper.pdf_kertas_kerja', [
            'input' => $input,
            'content' => $content,
        ])->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        file_put_contents($destination, $dompdf->output());
    }
}
