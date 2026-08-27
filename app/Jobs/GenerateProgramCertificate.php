<?php

namespace App\Jobs;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;
use Throwable;

class GenerateProgramCertificate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $certificateId) {}

    public function handle(): void
    {
        $certificate = DB::table('program_certificates')->where('id', $this->certificateId)->first();
        if (! $certificate || $certificate->status === 'ready') return;
        $program = DB::table('programs')->where('id', $certificate->program_id)->first();
        if (! $program) throw new \RuntimeException('Program not found.');

        DB::table('program_certificates')->where('id', $certificate->id)->update(['status' => 'generating', 'updated_at' => now()]);
        $directory = 'program-certificates/'.$program->id;
        $safeMatric = preg_replace('/[^A-Za-z0-9_-]/', '-', $certificate->matric_no);
        $path = $directory.'/'.$safeMatric.'.pdf';
        Storage::disk('local')->makeDirectory($directory);

        $output = match ($certificate->template_key ?? 'standard_placeholder') {
            'standard_placeholder' => $this->renderStandardCertificate($certificate, $program),
            'batik_run_participation' => $this->renderBatikRunParticipationCertificate($certificate),
            'uploaded_pdf' => $this->renderUploadedPdfTemplate($certificate, $program),
            default => throw new \RuntimeException('Unsupported certificate template.'),
        };

        Storage::disk('local')->put($path, $output);

        DB::table('program_certificates')->where('id', $certificate->id)->update([
            'status' => 'ready', 'path' => $path, 'failure_reason' => null,
            'generated_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        DB::table('program_certificates')->where('id', $this->certificateId)->update([
            'status' => 'failed', 'failure_reason' => mb_substr($exception?->getMessage() ?: 'Generation failed.', 0, 1000), 'updated_at' => now(),
        ]);
    }

    private function renderStandardCertificate(object $certificate, object $program): string
    {
        $options = new Options();
        $options->set('isRemoteEnabled', false);

        $pdf = new Dompdf($options);
        $pdf->setPaper('A4', 'landscape');
        $pdf->loadHtml(view('admin.programs.certificate_pdf', compact('certificate', 'program'))->render());
        $pdf->render();

        return $pdf->output();
    }

    private function renderBatikRunParticipationCertificate(object $certificate): string
    {
        $templatePath = resource_path('certificates/batik-run.pdf');
        if (! is_file($templatePath)) {
            throw new \RuntimeException('Batik Run certificate template not found.');
        }

        $previousReporting = error_reporting(error_reporting() & ~E_DEPRECATED);

        try {
            $pdf = new Fpdi('L', 'mm', 'A4');
            $pdf->setSourceFile($templatePath);
            $page = $pdf->importPage(1);
            $pdf->AddPage('L', 'A4');
            $pdf->useTemplate($page, 0, 0, 297, 210);

            $pdf->SetFillColor(244, 235, 214);
            $pdf->Rect(83, 68.2, 131, 25.8, 'F');

            $pdf->SetTextColor(31, 26, 22);
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetXY(73.5, 70.2);
            $pdf->Cell(150, 4.5, 'NAMA', 0, 1, 'C');

            $pdf->SetFont('Arial', 'B', $this->fitFontSize($pdf, $certificate->student_name, 150, 14, 9));
            $pdf->SetXY(73.5, 75.3);
            $pdf->Cell(150, 6.2, $this->pdfText($certificate->student_name), 0, 1, 'C');

            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetXY(73.5, 82.0);
            $pdf->Cell(150, 4.5, 'NO. KAD PENGENALAN', 0, 1, 'C');

            $icNo = $this->studentIcNo($certificate);
            $pdf->SetFont('Arial', '', $this->fitFontSize($pdf, $icNo, 150, 10, 8));
            $pdf->SetXY(73.5, 87.1);
            $pdf->Cell(150, 5.2, $this->pdfText($icNo), 0, 1, 'C');

            return $pdf->Output('S');
        } finally {
            error_reporting($previousReporting);
        }
    }

    private function renderUploadedPdfTemplate(object $certificate, object $program): string
    {
        if (! $certificate->certificate_template_id) {
            throw new \RuntimeException('Uploaded certificate template was not selected.');
        }

        $template = DB::table('certificate_templates')
            ->where('id', $certificate->certificate_template_id)
            ->where('is_active', true)
            ->first();
        if (! $template) {
            throw new \RuntimeException('Uploaded certificate template not found.');
        }

        $disk = $template->disk ?: 'local';
        if (! Storage::disk($disk)->exists($template->file_path)) {
            throw new \RuntimeException('Uploaded certificate template file not found.');
        }

        $fields = DB::table('certificate_template_fields')
            ->where('certificate_template_id', $template->id)
            ->orderBy('id')
            ->get();
        if ($fields->isEmpty()) {
            throw new \RuntimeException('Uploaded certificate template has no mapped fields.');
        }

        $templatePath = Storage::disk($disk)->path($template->file_path);
        $previousReporting = error_reporting(error_reporting() & ~E_DEPRECATED);

        try {
            $pdf = new Fpdi('L', 'mm', 'A4');
            $pageCount = $pdf->setSourceFile($templatePath);
            $sourcePage = max(1, min((int) ($template->source_page ?: 1), $pageCount));
            $page = $pdf->importPage($sourcePage);
            $size = $pdf->getTemplateSize($page);
            $width = (float) ($template->page_width_mm ?: ($size['width'] ?? 297));
            $height = (float) ($template->page_height_mm ?: ($size['height'] ?? 210));
            $orientation = $width > $height ? 'L' : 'P';
            $pdf->AddPage($orientation, [$width, $height]);
            $pdf->useTemplate($page, 0, 0, $width, $height);

            foreach ($fields as $field) {
                if (str_starts_with((string) $field->field_key, 'background_cover')) {
                    $this->drawCover($pdf, $field);
                    continue;
                }

                if ((bool) $field->cover_background) {
                    $this->drawCover($pdf, $field);
                }

                $value = $this->certificateFieldValue($field->field_key, $certificate, $program);
                if ($value === '') {
                    continue;
                }

                $style = (string) $field->font_weight === 'bold' ? 'B' : '';
                $pdf->SetTextColor(...$this->hexToRgb($field->text_color ?: '#1f1a16'));
                $pdf->SetFont('Arial', $style, (int) $field->font_size);
                $pdf->SetXY((float) $field->x_mm, (float) $field->y_mm);
                $pdf->Cell(
                    (float) $field->width_mm,
                    (float) $field->height_mm,
                    $this->pdfText($value),
                    0,
                    0,
                    in_array($field->alignment, ['L', 'C', 'R'], true) ? $field->alignment : 'C'
                );
            }

            return $pdf->Output('S');
        } finally {
            error_reporting($previousReporting);
        }
    }

    private function drawCover(Fpdi $pdf, object $field): void
    {
        $pdf->SetFillColor(...$this->hexToRgb($field->cover_color ?: '#f4ebd6'));
        $pdf->Rect((float) $field->x_mm, (float) $field->y_mm, (float) $field->width_mm, (float) $field->height_mm, 'F');
    }

    private function certificateFieldValue(string $fieldKey, object $certificate, object $program): string
    {
        return match ($fieldKey) {
            'student_name' => (string) $certificate->student_name,
            'ic_no' => $this->studentIcNo($certificate),
            'program_title' => (string) $program->title,
            'program_date' => $program->starts_at ? date('d M Y', strtotime($program->starts_at)) : '',
            'serial_no' => (string) $certificate->serial_no,
            default => '',
        };
    }

    private function studentIcNo(object $certificate): string
    {
        return (string) (DB::table('students')->where('id', $certificate->student_id)->value('ic_no') ?? '');
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            return [31, 26, 22];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private function fitFontSize(Fpdi $pdf, string $text, float $maxWidth, int $start, int $minimum): int
    {
        $fontSize = $start;
        do {
            $pdf->SetFont('Arial', 'B', $fontSize);
            if ($pdf->GetStringWidth($this->pdfText($text)) <= $maxWidth || $fontSize <= $minimum) {
                return $fontSize;
            }
            $fontSize--;
        } while ($fontSize >= $minimum);

        return $minimum;
    }

    private function pdfText(?string $text): string
    {
        $converted = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', (string) $text);

        return $converted === false ? (string) $text : $converted;
    }
}
