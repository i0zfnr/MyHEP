<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;

class CertificateTemplateCleaner
{
    public function clean(string $inputPath, string $outputPath, int $page, array $regions): void
    {
        $python = (string) config('services.certificate_cleaner.python', 'python');
        $script = base_path('scripts/clean_certificate_template.py');
        $dpi = (int) config('services.certificate_cleaner.dpi', 300);
        $regionsJson = json_encode($regions, JSON_THROW_ON_ERROR);

        $result = Process::timeout(120)->run([
            $python,
            $script,
            $inputPath,
            $outputPath,
            (string) $page,
            $regionsJson,
            '--dpi',
            (string) $dpi,
        ]);

        if ($result->failed() || ! is_file($outputPath) || filesize($outputPath) === 0) {
            @unlink($outputPath);
            throw new \RuntimeException('The clean certificate master could not be created. '.trim($result->errorOutput() ?: $result->output()));
        }
    }
}
