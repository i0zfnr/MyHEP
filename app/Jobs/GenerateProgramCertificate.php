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

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $pdf = new Dompdf($options);
        $pdf->setPaper('A4', 'landscape');
        $templateView = match ($certificate->template_key ?? 'standard_placeholder') {
            'standard_placeholder' => 'admin.programs.certificate_pdf',
            default => throw new \RuntimeException('Unsupported certificate template.'),
        };
        $pdf->loadHtml(view($templateView, compact('certificate', 'program'))->render());
        $pdf->render();
        Storage::disk('local')->put($path, $pdf->output());

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
}
