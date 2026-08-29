<?php

namespace App\Services;

use setasign\Fpdi\Fpdi;

class CertificateTemplateCleaner
{
    public function clean(string $inputPath, string $outputPath, int $page, array $regions): void
    {
        if (count($regions) !== 2) {
            throw new \InvalidArgumentException('Exactly two approved cleaning regions are required.');
        }

        $directory = dirname($outputPath);
        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException('The clean certificate master directory could not be created.');
        }

        $previousReporting = error_reporting(error_reporting() & ~E_DEPRECATED);

        try {
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($inputPath);
            if ($page < 1 || $page > $pageCount) {
                throw new \InvalidArgumentException('Selected certificate page does not exist.');
            }

            $template = $pdf->importPage($page);
            $size = $pdf->getTemplateSize($template);
            $width = (float) $size['width'];
            $height = (float) $size['height'];
            $pdf->AddPage($width > $height ? 'L' : 'P', [$width, $height]);
            $pdf->useTemplate($template, 0, 0, $width, $height);

            foreach ($regions as $region) {
                $x = (float) ($region['x_mm'] ?? -1);
                $y = (float) ($region['y_mm'] ?? -1);
                $regionWidth = (float) ($region['width_mm'] ?? 0);
                $regionHeight = (float) ($region['height_mm'] ?? 0);
                if ($x < 0 || $y < 0 || $regionWidth <= 0 || $regionHeight <= 0
                    || $x + $regionWidth > $width + 0.1 || $y + $regionHeight > $height + 0.1) {
                    throw new \InvalidArgumentException('A certificate cleaning region is outside the selected page.');
                }

                $pdf->SetFillColor(...$this->hexToRgb((string) ($region['color'] ?? '#f4ebd6')));
                $pdf->Rect($x, $y, $regionWidth, $regionHeight, 'F');
            }

            $pdf->Output('F', $outputPath);
        } finally {
            error_reporting($previousReporting);
        }

        if (! is_file($outputPath) || filesize($outputPath) === 0) {
            @unlink($outputPath);
            throw new \RuntimeException('The clean certificate master could not be created.');
        }
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) !== 6 || ! ctype_xdigit($hex)) {
            return [244, 235, 214];
        }

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
