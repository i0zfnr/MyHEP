<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Element\Cell;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Title;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

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
        $temporaryDocx = $format === 'pdf';

        $this->writeDocx($program, $data, $report, $docxAbsolutePath, $imagePaths);
        if (! $temporaryDocx) $paths['docx_path'] = $docxPath;

        if (in_array($format, ['pdf', 'both'], true)) {
            $paths['pdf_path'] = $directory.'/laporan-program-'.$program->id.'-'.$stamp.'.pdf';
            $this->convertDocxToPdf($docxAbsolutePath, Storage::disk('local')->path($paths['pdf_path']));
        }
        if ($temporaryDocx) @unlink($docxAbsolutePath);

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
            Settings::setPdfRenderer(Settings::PDF_RENDERER_DOMPDF, base_path('vendor/dompdf/dompdf'));
            $word = IOFactory::load($sourcePath);
            IOFactory::createWriter($word, 'PDF')->save(Storage::disk('local')->path($paths['pdf_path']));
        }

        return $paths;
    }

    private function writeDocx(object $program, array $data, array $report, string $destination, array $imagePaths): void
    {
        $previousEscapingSetting = Settings::isOutputEscapingEnabled();
        Settings::setOutputEscapingEnabled(true);

        try {
            $template = resource_path('report-templates/FORMAT LAPORAN POLIBESUT 2025.docx');
            $word = IOFactory::load($template);
            $date = ($program->starts_at ?? null) ? date('d.m.Y', strtotime($program->starts_at)) : 'Tidak direkodkan';
            $longDate = ($program->starts_at ?? null)
                ? mb_strtoupper(Carbon::parse($program->starts_at)->locale('ms')->translatedFormat('d F Y'))
                : 'TIDAK DIREKODKAN';
            $objectives = array_values($report['objectives'] ?? []);
            $objectives = array_pad($objectives, 3, '');
            $impactLines = [
                implode(' ', $report['achievements'] ?? []),
                'Isu: '.implode(' ', $report['issues'] ?? []),
                'Cadangan penambahbaikan: '.implode(' ', $report['improvements'] ?? []).' Kesimpulan: '.($report['conclusion'] ?? ''),
            ];
            $replacements = [
                '[NAMA KURSUS/PROGRAM]' => mb_strtoupper($program->title),
                '[05.02.2025 (RABU)]' => $date,
                '[BILIK SEMINAR ULPL]' => ($program->venue ?? null) ?: 'Tidak direkodkan',
                '[JABATAN /UNIT]' => $data['organizer'] ?? 'Tidak direkodkan',
                '[NAMA PELAJAR/PEGAWAI' => mb_strtoupper($data['prepared_by'] ?? 'Tidak direkodkan'),
                '(JAWATAN/JABATAN/UNIT)]d' => ($data['prepared_by_position'] ?? 'Pengarah Program').' / '.($data['organizer'] ?? 'Tidak direkodkan'),
                'NAMA PROGRAM: [KURSUS PEMANTAPAN SPMP]' => 'NAMA PROGRAM: '.mb_strtoupper($program->title),
                'TEMPAT : [MAKMAL CSDL JTMK POLIBESUT]' => 'TEMPAT : '.(($program->venue ?? null) ?: 'Tidak direkodkan'),
                'TARIKH : [17 FEBRUARI 2025]' => 'TARIKH : '.$longDate,
                'ANJURAN : [UNIT LATIHAN DAN PENDIDIKAN LANJUTAN]' => 'ANJURAN : '.mb_strtoupper($data['organizer'] ?? 'Tidak direkodkan'),
                'KUMPULAN SASARAN: [PENSYARAH]' => 'KUMPULAN SASARAN: '.(($program->target_participants ?? null) ?: 'Tidak direkodkan'),
                'BILANGAN PESERTA: [20 ORANG- senarai seperti di lampiran]' => 'BILANGAN PESERTA: '.$data['attendance_total'].' ORANG (senarai kehadiran direkodkan dalam StudentEdge)',
                '[Sistem Pengurusan Maklumat Politeknik (SPMP) merupakan satu platform penting dalam pengurusan data akademik dan pentadbiran di politeknik. Bagi memastikan keberkesanan penggunaan sistem ini, satu kursus pemantapan akan diadakan bagi meningkatkan pemahaman serta kemahiran pengguna dalam mengendalikan sistem ini dengan lebih cekap dan berkesan.]' => $report['executive_summary'] ?? 'Tidak direkodkan',
                'Memberikan pendedahan kepada pensyarah  yang baharu melapor diri ke PoliBesut tentang modul-modul-modul yang digunapakai dalam SPMP.' => $objectives[0],
                'Memantapkan kefahaman sediaada pensyarah terhadap penggunaan modul-modul dalam SPMP.' => $objectives[1],
                'Mengelakkan teguran berulang oleh pihak juruaudit dalaman terhadap pelaksanaan sistem SPMP.' => implode(' ', array_slice($objectives, 2)),
                'HASIL KAJI SELIDIK/MAKLUM BALAS PESERTA PROGRAM:' => 'HASIL KAJI SELIDIK/MAKLUM BALAS PESERTA PROGRAM: '.($report['survey_summary'] ?? 'Tidak direkodkan'),
                'Peningkatan kefahaman tentang fungsi modul dalam SPMP.' => $impactLines[0],
                'Peningkatan kecekapan dan produktiviti dalam pengurusan data akademik.' => $impactLines[1],
                'Pemantapan sistem pengurusan akademik dan pelajar melalui penggunaan SPMP yang lebih berkesan.' => $impactLines[2],
            ];

            foreach ($word->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    $this->replaceInElement($element, $replacements);
                }
            }

            $this->fillTemplateTables($word->getSections()[0], $program, $data);
            IOFactory::createWriter($word, 'Word2007')->save($destination);
            $this->replaceTemplateActivityImage($destination, $imagePaths);
        } finally {
            Settings::setOutputEscapingEnabled($previousEscapingSetting);
        }
    }

    private function replaceInElement(object $element, array $replacements): void
    {
        if ($element instanceof Title) {
            $this->replaceInTitle($element, $replacements);
            return;
        }
        if ($element instanceof TextRun && $this->replaceAcrossTextRun($element, $replacements)) {
            return;
        }
        if (method_exists($element, 'getElements')) {
            foreach ($element->getElements() as $child) $this->replaceInElement($child, $replacements);
        }
        if (method_exists($element, 'getRows')) {
            foreach ($element->getRows() as $row) foreach ($row->getCells() as $cell) $this->replaceInElement($cell, $replacements);
        }
        if (method_exists($element, 'getText') && method_exists($element, 'setText')) {
            $text = $element->getText();
            foreach ($replacements as $search => $replace) $text = str_replace($search, $replace, $text);
            $element->setText($text);
        }
    }

    private function replaceInTitle(Title $title, array $replacements): void
    {
        $text = $title->getText();
        if ($text instanceof TextRun) {
            $this->replaceAcrossTextRun($text, $replacements);
            return;
        }

        $updated = $text;
        foreach ($replacements as $search => $replace) $updated = str_replace($search, $replace, $updated);
        if ($updated === $text) return;

        $property = new \ReflectionProperty(Title::class, 'text');
        $property->setValue($title, $updated);
    }

    private function replaceAcrossTextRun(TextRun $run, array $replacements): bool
    {
        $nodes = array_values(array_filter($run->getElements(), fn ($element): bool => $element instanceof Text));
        if ($nodes === []) {
            return false;
        }

        $original = implode('', array_map(fn (Text $text): string => $text->getText(), $nodes));
        $updated = $original;
        foreach ($replacements as $search => $replace) $updated = str_replace($search, $replace, $updated);
        if ($updated === $original) {
            return false;
        }

        $nodes[0]->setText($updated);
        foreach (array_slice($nodes, 1) as $node) $node->setText('');

        return true;
    }

    private function fillTemplateTables(object $section, object $program, array $data): void
    {
        $tables = array_values(array_filter($section->getElements(), fn ($element): bool => $element instanceof Table));

        if (isset($tables[1])) {
            foreach ($tables[1]->getRows() as $index => $row) {
                $name = $index === 3 ? ($data['prepared_by'] ?? 'Tidak direkodkan') : 'Tidak direkodkan';
                $this->setCellText($row->getCells()[1], ': '.$name);
            }
        }

        if (isset($tables[2])) {
            $rows = $tables[2]->getRows();
            $date = ($program->starts_at ?? null) ? date('d.m.Y', strtotime($program->starts_at)) : 'Tidak direkodkan';
            $time = ($program->starts_at ?? null) ? date('h:i A', strtotime($program->starts_at)) : 'Tidak direkodkan';
            if ($program->ends_at ?? null) $time .= ' - '.date('h:i A', strtotime($program->ends_at));
            if (isset($rows[1])) {
                $this->setCellText($rows[1]->getCells()[0], $date);
                $this->setCellText($rows[1]->getCells()[1], $time);
                $this->setCellText($rows[1]->getCells()[2], 'Pelaksanaan '.$program->title);
            }
            foreach (array_slice($rows, 2) as $row) {
                foreach ($row->getCells() as $cell) $this->setCellText($cell, '');
            }
        }

        if (isset($tables[3])) {
            foreach (array_slice($tables[3]->getRows(), 2) as $row) {
                foreach ($row->getCells() as $cell) $this->setCellText($cell, '');
            }
        }
    }

    private function setCellText(Cell $cell, string $value): void
    {
        $texts = [];
        foreach ($cell->getElements() as $element) {
            if ($element instanceof Text) $texts[] = $element;
            if ($element instanceof TextRun) {
                foreach ($element->getElements() as $child) if ($child instanceof Text) $texts[] = $child;
            }
        }
        if ($texts === []) {
            $cell->addText($value);
            return;
        }
        $texts[0]->setText($value);
        foreach (array_slice($texts, 1) as $text) $text->setText('');
    }

    private function replaceTemplateActivityImage(string $docxPath, array $imagePaths): void
    {
        $imagePaths = array_values(array_filter($imagePaths, 'is_file'));
        if ($imagePaths === [] || ! class_exists(\ZipArchive::class)) {
            return;
        }

        $sheet = $this->createPhotoSheet(array_slice($imagePaths, 0, 8));
        if ($sheet === null) {
            return;
        }

        $zip = new \ZipArchive();
        if ($zip->open($docxPath) !== true) {
            return;
        }
        $target = null;
        $largestArea = 0;
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $name = $zip->getNameIndex($index);
            if (! is_string($name) || ! str_starts_with($name, 'word/media/')) continue;
            $size = @getimagesizefromstring((string) $zip->getFromName($name));
            $area = is_array($size) ? ((int) $size[0] * (int) $size[1]) : 0;
            if ($area > $largestArea) {
                $largestArea = $area;
                $target = $name;
            }
        }
        if ($target !== null) $zip->addFromString($target, $sheet);
        $zip->close();
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

    private function plainText(array $report): string
    {
        return implode("\n\n", [
            $report['executive_summary'] ?? '',
            implode("\n", $report['objectives'] ?? []),
            $report['survey_summary'] ?? '',
            implode("\n", $report['achievements'] ?? []),
            implode("\n", $report['issues'] ?? []),
            implode("\n", $report['improvements'] ?? []),
            $report['conclusion'] ?? '',
        ]);
    }

    private function convertDocxToPdf(string $docxPath, string $pdfPath): void
    {
        Settings::setPdfRenderer(Settings::PDF_RENDERER_DOMPDF, base_path('vendor/dompdf/dompdf'));
        $word = IOFactory::load($docxPath);
        IOFactory::createWriter($word, 'PDF')->save($pdfPath);
    }
}
