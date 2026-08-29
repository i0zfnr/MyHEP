<?php

namespace Tests\Unit;

use App\Services\CertificateTemplateCleaner;
use setasign\Fpdi\Fpdi;
use Tests\TestCase;

class CertificateTemplateCleanerTest extends TestCase
{
    public function test_it_creates_a_readable_single_page_clean_master(): void
    {
        $output = sys_get_temp_dir().DIRECTORY_SEPARATOR.'studentedge-clean-master-'.uniqid().'.pdf';

        try {
            app(CertificateTemplateCleaner::class)->clean(
                resource_path('certificates/batik-run.pdf'),
                $output,
                1,
                [
                    ['x_mm' => 143, 'y_mm' => 69.5, 'width_mm' => 28, 'height_mm' => 11.5, 'color' => '#f4ebd6'],
                    ['x_mm' => 113, 'y_mm' => 78.2, 'width_mm' => 88, 'height_mm' => 11.7, 'color' => '#f4ebd6'],
                ]
            );

            $this->assertFileExists($output);
            $this->assertGreaterThan(100_000, filesize($output));

            $pdf = new Fpdi();
            $this->assertSame(1, $pdf->setSourceFile($output));
            $page = $pdf->importPage(1);
            $size = $pdf->getTemplateSize($page);
            $this->assertEqualsWithDelta(297, (float) $size['width'], 0.2);
            $this->assertEqualsWithDelta(210, (float) $size['height'], 0.2);
        } finally {
            if (is_file($output)) {
                unlink($output);
            }
        }
    }
}
