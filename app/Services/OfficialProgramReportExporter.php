<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

class OfficialProgramReportExporter
{
    public function export(object $program, array $data, string $content, string $format, array $imagePaths = []): array
    {
        $directory = 'program-reports/'.$program->id;
        Storage::disk('local')->makeDirectory($directory);
        $stamp = now()->format('Ymd_His');
        $paths = ['docx_path' => null, 'pdf_path' => null];

        if (in_array($format, ['docx', 'both'], true)) {
            $paths['docx_path'] = $directory.'/laporan-program-'.$program->id.'-'.$stamp.'.docx';
            $this->writeDocx($program, $data, $content, Storage::disk('local')->path($paths['docx_path']), $imagePaths);
        }

        if (in_array($format, ['pdf', 'both'], true)) {
            $paths['pdf_path'] = $directory.'/laporan-program-'.$program->id.'-'.$stamp.'.pdf';
            $options = new Options();
            $options->set('isRemoteEnabled', false);
            $pdf = new Dompdf($options);
            $pdf->setPaper('A4');
            $pdf->loadHtml(view('admin.programs.report_pdf', compact('program', 'data', 'content'))->render());
            $pdf->render();
            Storage::disk('local')->put($paths['pdf_path'], $pdf->output());
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
            Settings::setPdfRenderer(Settings::PDF_RENDERER_DOMPDF, base_path('vendor/dompdf/dompdf'));
            $word = IOFactory::load($sourcePath);
            IOFactory::createWriter($word, 'PDF')->save(Storage::disk('local')->path($paths['pdf_path']));
        }

        return $paths;
    }

    private function writeDocx(object $program, array $data, string $content, string $destination, array $imagePaths): void
    {
        $template = resource_path('report-templates/FORMAT LAPORAN POLIBESUT 2025.docx');
        $word = IOFactory::load($template);
        $date = ($program->starts_at ?? null) ? date('d.m.Y', strtotime($program->starts_at)) : 'Tidak direkodkan';
        $replacements = [
            '[NAMA KURSUS/PROGRAM]' => mb_strtoupper($program->title),
            '[05.02.2025 (RABU)]' => $date,
            '[BILIK SEMINAR ULPL]' => ($program->venue ?? null) ?: 'Tidak direkodkan',
            'NAMA PROGRAM: [KURSUS PEMANTAPAN SPMP]' => 'NAMA PROGRAM: '.mb_strtoupper($program->title),
            'TEMPAT : [MAKMAL CSDL JTMK POLIBESUT]' => 'TEMPAT : '.(($program->venue ?? null) ?: 'Tidak direkodkan'),
            'TARIKH : [17 FEBRUARI 2025]' => 'TARIKH : '.$date,
            'KUMPULAN SASARAN: [PENSYARAH]' => 'KUMPULAN SASARAN: '.(($program->target_participants ?? null) ?: 'Tidak direkodkan'),
            'BILANGAN PESERTA: [20 ORANG- senarai seperti di lampiran]' => 'BILANGAN PESERTA: '.$data['attendance_total'].' ORANG (senarai kehadiran direkodkan dalam StudentEdge)',
            '[Sistem Pengurusan Maklumat Politeknik (SPMP) merupakan satu platform penting dalam pengurusan data akademik dan pentadbiran di politeknik. Bagi memastikan keberkesanan penggunaan sistem ini, satu kursus pemantapan akan diadakan bagi meningkatkan pemahaman serta kemahiran pengguna dalam mengendalikan sistem ini dengan lebih cekap dan berkesan.]' => ($program->background ?? null) ?: $content,
        ];

        foreach ($word->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                $this->replaceInElement($element, $replacements);
            }
        }

        $section = $word->getSections()[0];
        $section->addPageBreak();
        $section->addTitle('LAMPIRAN: RINGKASAN LAPORAN DIJANA AI', 1);
        foreach (preg_split('/\R{2,}/', trim($content)) ?: [] as $paragraph) {
            $section->addText($paragraph, ['name' => 'Arial', 'size' => 10]);
        }
        foreach (array_slice($imagePaths, 0, 8) as $imagePath) {
            if (is_file($imagePath)) {
                $section->addImage($imagePath, ['width' => 440, 'alignment' => 'center']);
            }
        }
        IOFactory::createWriter($word, 'Word2007')->save($destination);
    }

    private function replaceInElement(object $element, array $replacements): void
    {
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
}
