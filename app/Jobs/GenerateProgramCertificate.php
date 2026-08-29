<?php

namespace App\Jobs;

use App\Services\CertificateTemplateCleaner;
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

        $recipientFields = $fields->keyBy('field_key');

        $cleanedPath = $template->cleaned_file_path ?? null;
        if (empty($cleanedPath) || ! Storage::disk($disk)->exists($cleanedPath)) {
            $cleanedPath = $this->createCleanedMasterForLegacyTemplate($template, $fields, $disk);
        }
        $usesCleanedMaster = ! empty($cleanedPath)
            && Storage::disk($disk)->exists($cleanedPath);
        $templatePath = Storage::disk($disk)->path(
            $usesCleanedMaster ? $cleanedPath : $template->file_path
        );
        $previousReporting = error_reporting(error_reporting() & ~E_DEPRECATED);

        try {
            $pdf = new Fpdi('L', 'mm', 'A4');
            $this->registerCertificateFonts($pdf);
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
                    $recipientKey = match ((string) $field->field_key) {
                        'background_cover_name' => 'student_name',
                        'background_cover_ic' => 'ic_no',
                        default => null,
                    };
                    $recipientField = $recipientKey ? $recipientFields->get($recipientKey) : null;

                    if ($recipientField && $recipientKey) {
                        $this->drawRecipientSafetyCover($pdf, $field, $recipientKey, $width);
                    } else {
                        $this->drawCover($pdf, $field);
                    }
                    continue;
                }

                if (! $usesCleanedMaster && (bool) $field->cover_background) {
                    $this->drawCover($pdf, $field);
                }

                $value = $this->certificateFieldValue($field->field_key, $certificate, $program);
                if ($value === '') {
                    continue;
                }

                $style = in_array((string) $field->field_key, ['student_name', 'ic_no'], true)
                    ? 'B'
                    : ((string) $field->font_weight === 'bold' ? 'B' : '');
                $fontFamily = in_array((string) $field->field_key, ['student_name', 'ic_no'], true)
                    ? 'Poppins'
                    : 'Arial';
                $pdf->SetTextColor(...$this->hexToRgb($field->text_color ?: '#1f1a16'));
                $fontRange = $this->recipientFontRange((string) $field->field_key, (int) $field->font_size);
                $fontSize = $this->fitFontSize(
                    $pdf,
                    $value,
                    (float) $field->width_mm,
                    $fontRange['start'],
                    $fontRange['minimum'],
                    $fontFamily,
                    $style
                );
                $pdf->SetFont($fontFamily, $style, $fontSize);
                $recipientCover = match ((string) $field->field_key) {
                    'student_name' => $recipientFields->get('background_cover_name'),
                    'ic_no' => $recipientFields->get('background_cover_ic'),
                    default => null,
                };
                $fieldY = $recipientCover
                    ? max(0, (float) $recipientCover->y_mm + (((float) $recipientCover->height_mm - (float) $field->height_mm) / 2))
                    : (float) $field->y_mm;
                $pdf->SetXY((float) $field->x_mm, $fieldY);
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

    private function drawRecipientSafetyCover(Fpdi $pdf, object $cover, string $recipientKey, float $pageWidth): void
    {
        $box = $this->recipientSafetyCoverBox($cover, $recipientKey, $pageWidth);
        $pdf->SetFillColor(...$this->hexToRgb($cover->cover_color ?: '#f4ebd6'));
        $pdf->Rect($box['x'], $box['y'], $box['width'], $box['height'], 'F');
    }

    private function recipientSafetyCoverBox(object $cover, string $recipientKey, float $pageWidth): array
    {
        $minimumWidth = $recipientKey === 'ic_no' ? 90.0 : 55.0;
        $width = min($pageWidth, max((float) $cover->width_mm, $minimumWidth));
        $center = (float) $cover->x_mm + ((float) $cover->width_mm / 2);
        $x = max(0, min($pageWidth - $width, $center - ($width / 2)));

        return [
            'x' => $x,
            'y' => max(0, (float) $cover->y_mm),
            'width' => $width,
            'height' => max(8.0, (float) $cover->height_mm),
        ];
    }

    private function createCleanedMasterForLegacyTemplate(object $template, $fields, string $disk): ?string
    {
        $recipientFields = $fields->keyBy('field_key');
        $regions = [];

        foreach (['name' => 'student_name', 'ic' => 'ic_no'] as $suffix => $recipientKey) {
            $cover = $recipientFields->get('background_cover_'.$suffix);
            $recipient = $recipientFields->get($recipientKey);
            // Templates saved in placement-only mode deliberately have no
            // removal covers. Their original artwork must remain untouched.
            if (! $recipient || ! $cover) {
                return null;
            }

            if ($cover && $this->coverOverlapsRecipientField($cover, $recipient)) {
                $regions[] = [
                    'x_mm' => (float) $cover->x_mm,
                    'y_mm' => (float) $cover->y_mm,
                    'width_mm' => (float) $cover->width_mm,
                    'height_mm' => (float) $cover->height_mm,
                    'color' => (string) ($cover->cover_color ?: '#f4ebd6'),
                ];
                continue;
            }

            $coverHeight = max(8.0, (float) ($cover->height_mm ?? $recipient->height_mm));
            $regions[] = [
                'x_mm' => (float) $recipient->x_mm,
                'y_mm' => max(0, (float) $recipient->y_mm - $coverHeight),
                'width_mm' => (float) $recipient->width_mm,
                'height_mm' => (float) $recipient->height_mm + $coverHeight,
                'color' => (string) ($cover->cover_color ?? '#f4ebd6'),
            ];
        }

        $cleanedPath = 'certificate-templates/'.((string) $template->slug).'-cleaned.pdf';
        app(CertificateTemplateCleaner::class)->clean(
            Storage::disk($disk)->path($template->file_path),
            Storage::disk($disk)->path($cleanedPath),
            (int) ($template->source_page ?: 1),
            $regions
        );
        DB::table('certificate_templates')->where('id', $template->id)->update([
            'cleaned_file_path' => $cleanedPath,
            'updated_at' => now(),
        ]);

        return $cleanedPath;
    }

    private function coverOverlapsRecipientField(object $cover, object $recipient): bool
    {
        $horizontalOverlap = min(
            (float) $cover->x_mm + (float) $cover->width_mm,
            (float) $recipient->x_mm + (float) $recipient->width_mm
        ) - max((float) $cover->x_mm, (float) $recipient->x_mm);
        $verticalOverlap = min(
            (float) $cover->y_mm + (float) $cover->height_mm,
            (float) $recipient->y_mm + (float) $recipient->height_mm
        ) - max((float) $cover->y_mm, (float) $recipient->y_mm);

        return $horizontalOverlap > 0 && $verticalOverlap > 0;
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

    private function fitFontSize(
        Fpdi $pdf,
        string $text,
        float $maxWidth,
        int $start,
        int $minimum,
        string $fontFamily = 'Arial',
        string $fontStyle = 'B'
    ): int
    {
        $fontSize = max($start, $minimum);
        do {
            $pdf->SetFont($fontFamily, $fontStyle, $fontSize);
            if ($pdf->GetStringWidth($this->pdfText($text)) <= $maxWidth || $fontSize <= $minimum) {
                return $fontSize;
            }
            $fontSize--;
        } while ($fontSize >= $minimum);

        return $minimum;
    }

    private function registerCertificateFonts(Fpdi $pdf): void
    {
        $fontDirectory = resource_path('fonts/certificate');
        $fontDefinition = $fontDirectory.DIRECTORY_SEPARATOR.'Poppins-Bold.php';
        $fontData = $fontDirectory.DIRECTORY_SEPARATOR.'Poppins-Bold.z';

        if (! is_file($fontDefinition) || ! is_file($fontData)) {
            throw new \RuntimeException('Poppins certificate font assets are missing.');
        }

        $pdf->AddFont('Poppins', 'B', 'Poppins-Bold.php', $fontDirectory);
    }

    private function recipientFontRange(string $fieldKey, int $configuredSize): array
    {
        return match ($fieldKey) {
            'student_name' => ['start' => max(18, $configuredSize), 'minimum' => 12],
            'ic_no' => ['start' => max(16, $configuredSize), 'minimum' => 12],
            default => ['start' => max(10, $configuredSize), 'minimum' => 8],
        };
    }

    private function pdfText(?string $text): string
    {
        $converted = iconv('UTF-8', 'windows-1252//TRANSLIT//IGNORE', (string) $text);

        return $converted === false ? (string) $text : $converted;
    }
}
