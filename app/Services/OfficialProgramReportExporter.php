<?php

namespace App\Services;

use Carbon\Carbon;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;

class OfficialProgramReportExporter
{
    public function export(object $program, array $data, array $report, string $format, array $imagePaths = []): array
    {
        $directory = 'program-reports/'.$program->id;
        Storage::disk('local')->makeDirectory($directory);
        $stamp = now()->format('Ymd_His');
        $paths = ['docx_path' => null, 'pdf_path' => null];
        $docxPath = $directory.'/laporan-program-'.$program->id.'-'.$stamp.'.docx';
        $docxAbsolutePath = Storage::disk('local')->path($docxPath);

        // 1. Generate faithful 1-to-1 DOCX from official template
        $this->writeDocxDirect($program, $data, $report, $docxAbsolutePath, $imagePaths);
        if (in_array($format, ['docx', 'both'], true)) {
            $paths['docx_path'] = $docxPath;
        }

        // 2. Generate clean, high-fidelity PDF
        if (in_array($format, ['pdf', 'both'], true)) {
            $pdfPath = $directory.'/laporan-program-'.$program->id.'-'.$stamp.'.pdf';
            $paths['pdf_path'] = $pdfPath;
            $this->renderPdf($program, $data, $report, Storage::disk('local')->path($pdfPath), $imagePaths);
        }

        // Clean up temporary docx if only pdf was requested
        if ($format === 'pdf' && file_exists($docxAbsolutePath)) {
            @unlink($docxAbsolutePath);
        }

        return $paths;
    }

    public function saveEditedDocx(object $program, string $sourcePath, string $format): array
    {
        $directory = 'program-reports/'.$program->id;
        Storage::disk('local')->makeDirectory($directory);
        $stamp = now()->format('Ymd_His');
        $paths = ['docx_path' => null, 'pdf_path' => null];

        if (in_array($format, ['docx', 'both'], true)) {
            $paths['docx_path'] = $directory.'/laporan-program-disemak-'.$program->id.'-'.$stamp.'.docx';
            copy($sourcePath, Storage::disk('local')->path($paths['docx_path']));
        }
        if (in_array($format, ['pdf', 'both'], true)) {
            $paths['pdf_path'] = $directory.'/laporan-program-disemak-'.$program->id.'-'.$stamp.'.pdf';
            $pdfAbsolutePath = Storage::disk('local')->path($paths['pdf_path']);

            $data = [
                'organizer' => 'Politeknik Besut Terengganu',
                'prepared_by' => $program->director_name ?? 'Pengarah Program',
                'prepared_by_position' => 'Pengarah Program',
                'attendance_total' => 0,
            ];
            $report = (new ProgramReportContent())->fallback($program, $data);
            $this->renderPdf($program, $data, $report, $pdfAbsolutePath);
        }

        return $paths;
    }

    private function writeDocxDirect(object $program, array $data, array $report, string $destination, array $imagePaths): void
    {
        $template = resource_path('report-templates/FORMAT LAPORAN POLIBESUT 2025.docx');
        copy($template, $destination);

        $zip = new \ZipArchive();
        if ($zip->open($destination) !== true) {
            return;
        }

        $xml = $zip->getFromName('word/document.xml');
        $rels = $zip->getFromName('word/_rels/document.xml.rels');
        if ($xml === false || $rels === false) {
            $zip->close();
            return;
        }

        // 1. Convert all red text styling to professional black
        $xml = preg_replace('/<w:color w:val="[fF]{2}0000"\/>/u', '<w:color w:val="000000"/>', $xml);

        $date = ($program->starts_at ?? null)
            ? date('d.m.Y', strtotime($program->starts_at)).' ('.mb_strtoupper(date('l', strtotime($program->starts_at))).')'
            : 'Tidak direkodkan';
        $longDate = ($program->starts_at ?? null)
            ? mb_strtoupper(Carbon::parse($program->starts_at)->locale('ms')->translatedFormat('d F Y'))
            : 'TIDAK DIREKODKAN';

        $objectives = array_values($report['objectives'] ?? []);
        $obj1 = $objectives[0] ?? 'Meningkatkan pengetahuan dan pemahaman peserta mengenai pengisian program.';
        $obj2 = $objectives[1] ?? 'Memupuk kerjasama dan semangat berpasukan dalam kalangan peserta.';
        $obj3 = implode(' ', array_slice($objectives, 2)) ?: 'Memastikan penglibatan aktif dalam aktiviti pembangunan sahsiah.';

        $impactLines = [
            implode(' ', $report['achievements'] ?? []) ?: 'Program berjaya dilaksanakan dengan kehadiran penuh peserta.',
            'Isu: '.(implode(' ', $report['issues'] ?? []) ?: 'Tiada isu ketara direkodkan semasa pelaksanaan.'),
            'Cadangan penambahbaikan: '.(implode(' ', $report['improvements'] ?? []) ?: 'Memperluaskan hebahan awal program.').' Kesimpulan: '.($report['conclusion'] ?? 'Program mencapai objektif yang disasarkan.'),
        ];

        $jawatankuasa = $report['jawatankuasa'] ?? [];
        $penceramah = $report['penceramah'] ?? [];
        $studentDemographics = $data['student_demographics'] ?? [];

        // 2. Perform exact string replacements for all placeholders
        $replacements = [
            '[NAMA KURSUS/PROGRAM]' => mb_strtoupper($program->title),
            '[05.02.2025 (RABU)]' => $date,
            '[BILIK SEMINAR ULPL]' => ($program->venue ?? null) ?: 'Politeknik Besut Terengganu',
            '[JABATAN /UNIT]' => $data['organizer'] ?? 'Politeknik Besut Terengganu',
            '[NAMA PELAJAR/PEGAWAI' => mb_strtoupper($data['prepared_by'] ?? 'Pengarah Program'),
            '(JAWATAN/JABATAN/UNIT)]d' => ($data['prepared_by_position'] ?? 'Pengarah Program').' / '.($data['organizer'] ?? 'Politeknik Besut'),
            'NAMA PROGRAM: [KURSUS PEMANTAPAN SPMP]' => 'NAMA PROGRAM: '.mb_strtoupper($program->title),
            'PERINGKAT PROGRAM : Jabatan/ Politeknik/ Institusi/ Komuniti/ Negeri/ Kebangsaan/ Antarabangsa' => 'PERINGKAT PROGRAM : '.($report['peringkat'] ?? 'Politeknik / Institusi'),
            'TEMPAT : [MAKMAL CSDL JTMK POLIBESUT]' => 'TEMPAT : '.(($program->venue ?? null) ?: 'Politeknik Besut Terengganu'),
            'TARIKH : [17 FEBRUARI 2025]' => 'TARIKH : '.$longDate,
            'ANJURAN : [UNIT LATIHAN DAN PENDIDIKAN LANJUTAN]' => 'ANJURAN : '.mb_strtoupper($data['organizer'] ?? 'Politeknik Besut'),
            'KUMPULAN SASARAN: [PENSYARAH]' => 'KUMPULAN SASARAN: '.(($program->target_participants ?? null) ?: 'Pelajar Politeknik Besut'),
            'BILANGAN PESERTA: [20 ORANG- senarai seperti di lampiran]' => 'BILANGAN PESERTA: '.($data['attendance_total'] ?? 0).' ORANG (senarai kehadiran direkodkan dalam MyHEP)',
            '[Sistem Pengurusan Maklumat Politeknik (SPMP) merupakan satu platform penting dalam pengurusan data akademik dan pentadbiran di politeknik. Bagi memastikan keberkesanan penggunaan sistem ini, satu kursus pemantapan akan diadakan bagi meningkatkan pemahaman serta kemahiran pengguna dalam mengendalikan sistem ini dengan lebih cekap dan berkesan.]' => $report['executive_summary'] ?? 'Program telah dilaksanakan mengikut perancangan kertas kerja.',
            'Memberikan pendedahan kepada pensyarah  yang baharu melapor diri ke PoliBesut tentang modul-modul-modul yang digunapakai dalam SPMP.' => $obj1,
            'Memantapkan kefahaman sediaada pensyarah terhadap penggunaan modul-modul dalam SPMP.' => $obj2,
            'Mengelakkan teguran berulang oleh pihak juruaudit dalaman terhadap pelaksanaan sistem SPMP.' => $obj3,
            ': Udom A/L Ewon' => ': '.($jawatankuasa['penaung'] ?? 'Pengarah Politeknik Besut'),
            ': Saifuddin Bin Semail' => ': '.($jawatankuasa['penasihat1'] ?? 'Timbalan Pengarah Politeknik Besut'),
            ': Ts. Elisnorazmaliza Bt Ab. Hamid' => ': '.($jawatankuasa['penasihat2'] ?? 'Ketua Jabatan / Unit'),
            ': Norakmar Binti Mohd Nadzari' => ': '.($jawatankuasa['pengarah_program'] ?? ($data['prepared_by'] ?? 'Pengarah Program')),
            ': Wan Zamilah Binti Wan Ibrahim' => ': '.($jawatankuasa['setiausaha'] ?? 'Setiausaha Program'),
            ': Endon Binti Che Mat' => ': '.($jawatankuasa['ajk'] ?? 'Jawatankuasa Pelaksana'),
            ': Nik Hayati Binti Nik Abdullah' => ': '.($jawatankuasa['urusetia'] ?? ($data['organizer'] ?? 'Urusetia')),
            'Nama pegawai      : Ts. Elisnorazmaliza Bt Ab. Hamid (TPGS)' => 'Nama pegawai      : '.($penceramah['nama'] ?? ($program->director_name ?? 'Pegawai Terlibat')),
            'Jawatan                 : Pegawai Pendidikan Pengajian Tinggi' => 'Jawatan                 : '.($penceramah['jawatan'] ?? 'Pegawai Pendidikan'),
            'Gred                   : DH52' => 'Gred                   : '.($penceramah['gred'] ?? '—'),
            'Jabatan / Institusi   : Politeknik Besut Terengganu' => 'Jabatan / Institusi   : '.($penceramah['institusi'] ?? 'Politeknik Besut Terengganu'),
            'HASIL KAJI SELIDIK/MAKLUM BALAS PESERTA PROGRAM:' => 'HASIL KAJI SELIDIK/MAKLUM BALAS PESERTA PROGRAM:',
            'Peningkatan kefahaman tentang fungsi modul dalam SPMP.' => $impactLines[0],
            'Peningkatan kecekapan dan produktiviti dalam pengurusan data akademik.' => $impactLines[1],
            'Pemantapan sistem pengurusan akademik dan pelajar melalui penggunaan SPMP yang lebih berkesan.' => $impactLines[2],
        ];

        foreach ($replacements as $search => $replace) {
            $escapedReplace = htmlspecialchars((string) $replace, ENT_XML1 | ENT_COMPAT, 'UTF-8');
            $xml = str_replace($search, $escapedReplace, $xml);
        }

        $xml = $this->fillStudentDemographicSection($xml, $studentDemographics);
        $xml = $this->insertSurveySectionAfterHeading($xml, $data, $report);

        // 3. Mark KPI Cluster in Header Table
        $kpiKey = strtolower($report['kluster_kpi'] ?? 'kemahiran dan inovasi');
        $paraIdMap = [
            'sukarelawan' => '00000012',
            'patriot' => '00000013',
            'perpaduan' => '00000014',
            'kepimpinan' => '00000015',
            'komunikasi' => '00000016',
            'kebudayaan' => '00000017',
            'kesenian' => '00000017',
            'warisan' => '00000017',
            'kerohanian' => '00000018',
            'rohani' => '00000018',
            'psikologi' => '00000019',
            'sukan' => '0000001A',
            'kesihatan' => '0000001B',
            'kemahiran' => '0000001C',
            'inovasi' => '0000001C',
            'kelab' => '0000001D',
            'persatuan' => '0000001D',
            'niche' => '0000001E',
        ];
        $targetParaId = '0000001C'; // Default to Kemahiran dan Inovasi
        foreach ($paraIdMap as $keyword => $pId) {
            if (str_contains($kpiKey, $keyword)) {
                $targetParaId = $pId;
                break;
            }
        }
        $checkSearch = '<w:p w14:paraId="'.$targetParaId.'"';
        $checkReplace = '<w:p w14:paraId="'.$targetParaId.'"><w:pPr><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="Arial" w:cs="Arial" w:eastAsia="Arial" w:hAnsi="Arial"/><w:b w:val="1"/><w:color w:val="000000"/><w:sz w:val="22"/><w:szCs w:val="22"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:cs="Arial" w:eastAsia="Arial" w:hAnsi="Arial"/><w:b w:val="1"/><w:color w:val="000000"/><w:sz w:val="22"/><w:szCs w:val="22"/></w:rPr><w:t>/</w:t></w:r></w:p>';
        if (str_contains($xml, $checkSearch)) {
            $xml = preg_replace('/<w:p [^>]*w14:paraId="'.$targetParaId.'".*?<\/w:p>/s', $checkReplace, $xml, 1);
        }

        // 4. Handle Section 12: Activity Images (Gambar Aktiviti)
        $imagePaths = array_values(array_filter($imagePaths, 'is_file'));
        if ($imagePaths !== []) {
            $sheet = $this->createPhotoSheet(array_slice($imagePaths, 0, 8));
            if ($sheet !== null) {
                // Add activity_photos.png into media folder
                $zip->addFromString('word/media/activity_photos.png', $sheet);

                // Add relationship if not already added
                if (! str_contains($rels, 'rIdActivityPhotos')) {
                    $rels = str_replace('</Relationships>', '<Relationship Id="rIdActivityPhotos" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/activity_photos.png"/></Relationships>', $rels);
                    $zip->addFromString('word/_rels/document.xml.rels', $rels);
                }

                // Insert DrawingML image paragraph right after "GAMBAR AKTIVITI/PROGRAM :"
                $imageXml = '<w:p xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
                    .'<w:pPr><w:jc w:val="center"/><w:spacing w:before="140" w:after="200"/></w:pPr>'
                    .'<w:r><w:drawing>'
                    .'<wp:inline distT="0" distB="0" distL="0" distR="0">'
                    .'<wp:extent cx="5400000" cy="4000000"/>'
                    .'<wp:docPr id="9991" name="ActivityPhotos"/>'
                    .'<wp:cNvGraphicFramePr><a:graphicFrameLocks noChangeAspect="1"/></wp:cNvGraphicFramePr>'
                    .'<a:graphic>'
                    .'<a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
                    .'<pic:pic>'
                    .'<pic:nvPicPr>'
                    .'<pic:cNvPr id="9991" name="activity_photos.png"/>'
                    .'<pic:cNvPicPr/>'
                    .'</pic:nvPicPr>'
                    .'<pic:blipFill>'
                    .'<a:blip r:embed="rIdActivityPhotos"/>'
                    .'<a:stretch><a:fillRect/></a:stretch>'
                    .'</pic:blipFill>'
                    .'<pic:spPr>'
                    .'<a:xfrm><a:off x="0" y="0"/><a:ext cx="5400000" cy="4000000"/></a:xfrm>'
                    .'<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>'
                    .'</pic:spPr>'
                    .'</pic:pic>'
                    .'</a:graphicData>'
                    .'</a:graphic>'
                    .'</wp:inline>'
                    .'</w:drawing></w:r>'
                    .'</w:p>';

                $targetHeading = '<w:t xml:space="preserve"> GAMBAR AKTIVITI/PROGRAM :</w:t></w:r></w:p>';
                if (str_contains($xml, $targetHeading)) {
                    $xml = str_replace($targetHeading, $targetHeading.$imageXml, $xml);
                }
            }
        }

        $zip->addFromString('word/document.xml', $xml);
        $zip->close();
    }

    private function renderPdf(object $program, array $data, array $report, string $destination, array $imagePaths = []): void
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Arial');

        $dompdf = new Dompdf($options);
        $html = view('admin.programs.report_official_pdf', compact('program', 'data', 'report', 'imagePaths'))->render();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        file_put_contents($destination, $dompdf->output());
    }

    private function fillStudentDemographicSection(string $xml, array $demographics): string
    {
        $related = (bool) ($demographics['is_student_related'] ?? false);
        $sectionValues = [
            '8.1 MELAYU:' => $demographics['melayu'] ?? 0,
            '8.2 CINA:' => $demographics['cina'] ?? 0,
            '8.3 INDIA:' => $demographics['india'] ?? 0,
            '8.4 BUMIPUTRA SABAH/SARAWAK:' => $demographics['bumiputera_sabah_sarawak'] ?? 0,
            '8.5 ORANG ASLI:' => $demographics['orang_asli'] ?? 0,
            '8.6 OKU:' => $demographics['oku'] ?? 0,
            'LELAKI:' => $demographics['lelaki'] ?? 0,
            'PEREMPUAN:' => $demographics['perempuan'] ?? 0,
            'JUMLAH KESELURUHAN PELAJAR:' => $demographics['total'] ?? 0,
            'BANDAR:' => $demographics['bandar'] ?? 0,
            'LUAR BANDAR:' => $demographics['luar_bandar'] ?? 0,
            'BELIA AWAL (15-18 TAHUN) :' => $demographics['age_15_18'] ?? 0,
            'BELIA PERTENGAHAN (19-24 TAHUN):' => $demographics['age_19_24'] ?? 0,
            'BELIA AKHIR (25-30 TAHUN):' => $demographics['age_25_30'] ?? 0,
        ];

        $xml = $this->appendValueAfterText($xml, 'Berkaitan', $related ? 'X' : '', 1);
        $xml = $this->appendValueAfterText($xml, 'Tidak Berkaitan', $related ? '' : 'X', 1);

        foreach ($sectionValues as $label => $value) {
            $xml = $this->appendValueAfterText($xml, $label, (string) (int) $value);
        }

        return $xml;
    }

    private function insertSurveySectionAfterHeading(string $xml, array $data, array $report): string
    {
        $surveyXml = $this->buildSurveySectionXml($data, $report);
        if ($surveyXml === '') {
            return $xml;
        }

        return $this->insertXmlAfterParagraphContaining($xml, 'HASIL KAJI SELIDIK/MAKLUM BALAS PESERTA PROGRAM:', $surveyXml);
    }

    private function buildSurveySectionXml(array $data, array $report): string
    {
        $analytics = $data['questionnaire_analytics'] ?? [];
        $questionStats = array_values(array_filter(
            $analytics['question_stats'] ?? [],
            fn ($item): bool => ($item['type'] ?? null) === 'rating_4'
        ));

        $summary = trim((string) ($report['survey_summary'] ?? ''));
        if ($questionStats === []) {
            return $summary !== '' ? $this->wordParagraph($summary) : '';
        }

        $questionStats = array_slice($questionStats, 0, 10);
        $surveyResponses = (int) ($data['survey_responses'] ?? $analytics['total_responses'] ?? 0);
        $intro = 'Soal selidik telah diedarkan secara atas talian. Hasil maklum balas daripada '
            .$surveyResponses.' responden sistem MyHEP adalah seperti jadual berikut.';

        $totals = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
        $currentCategory = null;
        $rows = [
            $this->wordTableRow([
                ['text' => 'Bil', 'width' => 420, 'bold' => true, 'center' => true],
                ['text' => '1', 'width' => 260, 'bold' => true, 'center' => true],
                ['text' => 'Sangat tidak setuju', 'width' => 1020, 'bold' => true, 'center' => true],
                ['text' => '2', 'width' => 260, 'bold' => true, 'center' => true],
                ['text' => 'Tidak setuju', 'width' => 1020, 'bold' => true, 'center' => true],
                ['text' => '3', 'width' => 260, 'bold' => true, 'center' => true],
                ['text' => 'Setuju', 'width' => 1020, 'bold' => true, 'center' => true],
                ['text' => '4', 'width' => 260, 'bold' => true, 'center' => true],
                ['text' => 'Sangat Setuju', 'width' => 1020, 'bold' => true, 'center' => true],
                ['text' => 'BIL. RESPON', 'width' => 1680, 'bold' => true, 'center' => true, 'gridSpan' => 4],
            ]),
            $this->wordTableRow([
                ['text' => '', 'width' => 5540, 'gridSpan' => 9],
                ['text' => '1', 'width' => 420, 'center' => true],
                ['text' => '2', 'width' => 420, 'center' => true],
                ['text' => '3', 'width' => 420, 'center' => true],
                ['text' => '4', 'width' => 420, 'center' => true],
            ]),
        ];

        foreach ($questionStats as $index => $item) {
            $breakdown = $item['breakdown'] ?? [];
            $category = $this->surveyCategoryForQuestion((string) ($item['text'] ?? ''), $index);
            if ($category !== $currentCategory) {
                $currentCategory = $category;
                $rows[] = $this->wordTableRow([
                    ['text' => '', 'width' => 420],
                    ['text' => $category, 'width' => 6800, 'bold' => true, 'italic' => true, 'gridSpan' => 12],
                ], 'D9D9D9');
            }

            for ($score = 1; $score <= 4; $score++) {
                $totals[$score] += (int) ($breakdown[$score] ?? 0);
            }

            $rows[] = $this->wordTableRow([
                ['text' => (string) ($index + 1).'.', 'width' => 420, 'center' => true],
                ['text' => (string) ($item['text'] ?? 'Soalan'), 'width' => 5120, 'gridSpan' => 8],
                ['text' => (string) ((int) ($breakdown[1] ?? 0)), 'width' => 420, 'center' => true],
                ['text' => (string) ((int) ($breakdown[2] ?? 0)), 'width' => 420, 'center' => true],
                ['text' => (string) ((int) ($breakdown[3] ?? 0)), 'width' => 420, 'center' => true],
                ['text' => (string) ((int) ($breakdown[4] ?? 0)), 'width' => 420, 'center' => true],
            ]);
        }

        $grandTotal = max(1, array_sum($totals));
        $rows[] = $this->wordTableRow([
            ['text' => '', 'width' => 420],
            ['text' => 'Jumlah', 'width' => 5120, 'bold' => true, 'right' => true, 'gridSpan' => 8],
            ['text' => (string) $totals[1], 'width' => 420, 'center' => true],
            ['text' => (string) $totals[2], 'width' => 420, 'center' => true],
            ['text' => (string) $totals[3], 'width' => 420, 'center' => true],
            ['text' => (string) $totals[4], 'width' => 420, 'center' => true],
        ]);
        $rows[] = $this->wordTableRow([
            ['text' => '', 'width' => 420],
            ['text' => 'Peratus (%)', 'width' => 5120, 'bold' => true, 'right' => true, 'gridSpan' => 8],
            ['text' => number_format(($totals[1] / $grandTotal) * 100, 1), 'width' => 420, 'center' => true],
            ['text' => number_format(($totals[2] / $grandTotal) * 100, 1), 'width' => 420, 'center' => true],
            ['text' => number_format(($totals[3] / $grandTotal) * 100, 1), 'width' => 420, 'center' => true],
            ['text' => number_format(($totals[4] / $grandTotal) * 100, 1), 'width' => 420, 'center' => true],
        ]);

        $table = '<w:tbl>'
            .'<w:tblPr><w:tblW w:w="0" w:type="auto"/><w:tblBorders><w:top w:val="single" w:sz="6" w:space="0" w:color="000000"/><w:left w:val="single" w:sz="6" w:space="0" w:color="000000"/><w:bottom w:val="single" w:sz="6" w:space="0" w:color="000000"/><w:right w:val="single" w:sz="6" w:space="0" w:color="000000"/><w:insideH w:val="single" w:sz="6" w:space="0" w:color="000000"/><w:insideV w:val="single" w:sz="6" w:space="0" w:color="000000"/></w:tblBorders><w:tblCellMar><w:top w:w="70" w:type="dxa"/><w:left w:w="70" w:type="dxa"/><w:bottom w:w="70" w:type="dxa"/><w:right w:w="70" w:type="dxa"/></w:tblCellMar></w:tblPr>'
            .implode('', $rows)
            .'</w:tbl>';

        return $this->wordParagraph($intro).$table.($summary !== '' ? $this->wordParagraph('Ulasan Urusetia: '.$summary) : '');
    }

    private function surveyCategoryForQuestion(string $question, int $index): string
    {
        $text = mb_strtolower($question);
        $effectivenessKeywords = [
            'meningkatkan',
            'pengetahuan',
            'pemahaman',
            'berkeyakinan',
            'keyakinan',
            'mengaplikasi',
            'berjaya',
            'bermanfaat',
            'increased',
            'knowledge',
            'understanding',
            'confident',
            'successful',
            'beneficial',
        ];

        foreach ($effectivenessKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                return 'Penilaian Keberkesanan Program Terhadap Peserta';
            }
        }

        return 'Penilaian Pelaksanaan Program';
    }

    private function insertXmlAfterParagraphContaining(string $xml, string $needle, string $insertXml): string
    {
        $normalizedNeedle = preg_replace('/\s+/', ' ', trim($needle));
        $inserted = false;

        return preg_replace_callback(
            '/<w:p\b[^>]*>.*?<\/w:p>/su',
            function (array $matches) use ($normalizedNeedle, $insertXml, &$inserted): string {
                if ($inserted) {
                    return $matches[0];
                }

                $text = html_entity_decode(strip_tags($matches[0]), ENT_QUOTES | ENT_XML1, 'UTF-8');
                $normalizedText = preg_replace('/\s+/', ' ', trim($text));
                if (! str_contains($normalizedText, $normalizedNeedle)) {
                    return $matches[0];
                }

                $inserted = true;

                return $matches[0].$insertXml;
            },
            $xml
        ) ?? $xml;
    }

    private function wordParagraph(string $text): string
    {
        return '<w:p><w:pPr><w:spacing w:before="100" w:after="100"/><w:jc w:val="both"/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:cs="Arial" w:eastAsia="Arial" w:hAnsi="Arial"/><w:sz w:val="20"/><w:szCs w:val="20"/></w:rPr><w:t xml:space="preserve">'
            .htmlspecialchars($text, ENT_XML1 | ENT_COMPAT, 'UTF-8')
            .'</w:t></w:r></w:p>';
    }

    private function wordTableRow(array $cells, ?string $fill = null): string
    {
        $xml = '<w:tr>';
        foreach ($cells as $cell) {
            $xml .= $this->wordTableCell(
                (string) ($cell['text'] ?? ''),
                (int) ($cell['width'] ?? 1000),
                (bool) ($cell['bold'] ?? false),
                (bool) ($cell['center'] ?? false),
                (bool) ($cell['right'] ?? false),
                $fill,
                (int) ($cell['gridSpan'] ?? 1),
                (bool) ($cell['italic'] ?? false)
            );
        }

        return $xml.'</w:tr>';
    }

    private function wordTableCell(string $text, int $width, bool $bold = false, bool $center = false, bool $right = false, ?string $fill = null, int $gridSpan = 1, bool $italic = false): string
    {
        $jc = $center ? 'center' : ($right ? 'right' : 'left');
        $shading = $fill ? '<w:shd w:fill="'.$fill.'"/>' : '';
        $boldXml = $bold ? '<w:b/>' : '';
        $italicXml = $italic ? '<w:i/>' : '';
        $spanXml = $gridSpan > 1 ? '<w:gridSpan w:val="'.$gridSpan.'"/>' : '';

        return '<w:tc><w:tcPr><w:tcW w:w="'.$width.'" w:type="dxa"/>'.$spanXml.$shading.'</w:tcPr>'
            .'<w:p><w:pPr><w:jc w:val="'.$jc.'"/></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Arial" w:cs="Arial" w:eastAsia="Arial" w:hAnsi="Arial"/>'.$boldXml.$italicXml.'<w:sz w:val="18"/><w:szCs w:val="18"/></w:rPr><w:t xml:space="preserve">'
            .htmlspecialchars($text, ENT_XML1 | ENT_COMPAT, 'UTF-8')
            .'</w:t></w:r></w:p></w:tc>';
    }

    private function appendValueAfterText(string $xml, string $label, string $value, int $limit = 0): string
    {
        $escapedValue = htmlspecialchars((string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
        $normalizedLabel = preg_replace('/\s+/', ' ', trim($label));
        $replacements = 0;

        return preg_replace_callback(
            '/(<w:t(?:\s+[^>]*)?>)(.*?)(<\/w:t>)/su',
            function (array $matches) use ($normalizedLabel, $escapedValue, $limit, &$replacements): string {
                if ($limit > 0 && $replacements >= $limit) {
                    return $matches[0];
                }

                $text = html_entity_decode($matches[2], ENT_QUOTES | ENT_XML1, 'UTF-8');
                $normalizedText = preg_replace('/\s+/', ' ', trim($text));
                if ($normalizedText !== $normalizedLabel) {
                    return $matches[0];
                }

                $replacements++;

                return $matches[1].$matches[2].($escapedValue === '' ? '' : ' '.$escapedValue).$matches[3];
            },
            $xml
        ) ?? $xml;
    }

    private function createPhotoSheet(array $imagePaths): ?string
    {
        if (! extension_loaded('gd')) {
            return null;
        }
        $width = 1674;
        $height = 1886;
        $margin = 28;
        $columns = count($imagePaths) === 1 ? 1 : 2;
        $rows = (int) ceil(count($imagePaths) / $columns);
        $canvas = imagecreatetruecolor($width, $height);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 255, 255, 255));
        $cellWidth = (int) floor(($width - ($columns + 1) * $margin) / $columns);
        $cellHeight = (int) floor(($height - ($rows + 1) * $margin) / $rows);

        foreach ($imagePaths as $index => $path) {
            $source = @imagecreatefromstring((string) file_get_contents($path));
            if ($source === false) continue;
            $sourceWidth = imagesx($source);
            $sourceHeight = imagesy($source);
            $scale = min($cellWidth / $sourceWidth, $cellHeight / $sourceHeight);
            $drawWidth = max(1, (int) floor($sourceWidth * $scale));
            $drawHeight = max(1, (int) floor($sourceHeight * $scale));
            $column = $index % $columns;
            $row = (int) floor($index / $columns);
            $x = $margin + $column * ($cellWidth + $margin) + (int) floor(($cellWidth - $drawWidth) / 2);
            $y = $margin + $row * ($cellHeight + $margin) + (int) floor(($cellHeight - $drawHeight) / 2);
            imagecopyresampled($canvas, $source, $x, $y, 0, 0, $drawWidth, $drawHeight, $sourceWidth, $sourceHeight);
            imagedestroy($source);
        }

        ob_start();
        imagepng($canvas, null, 6);
        $output = ob_get_clean();
        imagedestroy($canvas);

        return is_string($output) ? $output : null;
    }
}
