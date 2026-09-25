<?php

namespace App\Services;

use App\Support\ProgramIdentifier;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\SimpleType\TblWidth;

class StudentStudyVerificationLetter
{
    public function identity(object $student): array
    {
        return [
            'student_name' => mb_strtoupper(trim((string) ($student->full_name ?? ''))),
            'ic_no' => $this->formatIdentityNumber((string) ($student->ic_no ?? '')),
            'matric_no' => mb_strtoupper(trim((string) ($student->matric_no ?? ''))),
            'program_name' => mb_strtoupper(ProgramIdentifier::label((string) ($student->program ?? ''))),
        ];
    }

    public function defaults(object $student): array
    {
        $semester = (int) preg_replace('/\D+/', '', (string) ($student->semester ?? ''));

        return [
            'study_start_session' => (string) ($student->academic_session ?? ''),
            'study_end_session' => '',
            'study_duration' => '3 TAHUN',
            'current_study_year' => now()->format('Y'),
            'current_semester' => $semester >= 1 && $semester <= 12 ? $semester : '',
            'sponsorship_details' => 'TIADA',
        ];
    }

    public function documentData(object $student, array $input): array
    {
        $identity = $this->identity($student);
        $timestamp = now();

        return array_merge($identity, [
            'reference_number' => rtrim((string) config('study-verification-letter.reference_prefix')).' (M'.$timestamp->format('ymd').'/'.str_pad((string) $student->id, 5, '0', STR_PAD_LEFT).'/'.$timestamp->format('His').')',
            'letter_date' => $timestamp->format('j').' '.$this->malayMonth((int) $timestamp->format('n')).' '.$timestamp->format('Y'),
            'generated_at' => $timestamp->format('d/m/Y H:i'),
            'study_start_session' => mb_strtoupper(trim($input['study_start_session'])),
            'study_end_session' => mb_strtoupper(trim($input['study_end_session'])),
            'study_duration' => mb_strtoupper(trim($input['study_duration'])),
            'current_study_year' => mb_strtoupper(trim($input['current_study_year'])),
            'current_semester' => (int) $input['current_semester'],
            'sponsorship_details' => mb_strtoupper(trim($input['sponsorship_details'])),
            'institution_name' => (string) config('study-verification-letter.institution_name'),
            'department_name' => (string) config('study-verification-letter.department_name'),
            'institution_address' => (string) config('study-verification-letter.institution_address'),
            'officer_name' => mb_strtoupper((string) config('study-verification-letter.officer_name')),
            'officer_position' => (string) config('study-verification-letter.officer_position'),
            'officer_authority' => (string) config('study-verification-letter.officer_authority'),
            'mottos' => (array) config('study-verification-letter.mottos', []),
            'logo_data_uri' => $this->logoDataUri(),
        ]);
    }

    public function docx(array $document): string
    {
        $phpWord = new PhpWord();
        $phpWord->getDocInfo()
            ->setCreator('MyHEP')
            ->setTitle('Surat Pengesahan Pengajian Pelajar')
            ->setSubject('Pengesahan pengajian pelajar');
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        $section = $phpWord->addSection([
            'pageSizeW' => 11906,
            'pageSizeH' => 16838,
            'marginLeft' => 1304,
            'marginRight' => 1021,
            'marginTop' => 737,
            'marginBottom' => 680,
            'headerHeight' => 340,
            'footerHeight' => 340,
        ]);

        $phpWord->addTableStyle('Letterhead', [
            'width' => 5000,
            'unit' => TblWidth::PERCENT,
            'alignment' => JcTable::CENTER,
            'cellMarginTop' => 0,
            'cellMarginRight' => 0,
            'cellMarginBottom' => 80,
            'cellMarginLeft' => 0,
            'borderBottomSize' => 8,
            'borderBottomColor' => '9A6C37',
        ]);
        $phpWord->addTableStyle('Metadata', [
            'width' => 5000,
            'unit' => TblWidth::PERCENT,
            'cellMarginTop' => 0,
            'cellMarginRight' => 0,
            'cellMarginBottom' => 0,
            'cellMarginLeft' => 0,
        ]);
        $phpWord->addTableStyle('StudentDetails', [
            'width' => 5000,
            'unit' => TblWidth::PERCENT,
            'cellMarginTop' => 20,
            'cellMarginRight' => 50,
            'cellMarginBottom' => 20,
            'cellMarginLeft' => 0,
        ]);

        $letterhead = $section->addTable('Letterhead');
        $letterhead->addRow();
        $logoCell = $letterhead->addCell(2300, ['valign' => 'center']);
        $logoPath = public_path('images/logo-politeknik-besut.png');
        if (is_file($logoPath)) {
            $logoCell->addImage($logoPath, ['width' => 128, 'height' => 57]);
        } else {
            $logoCell->addText($document['institution_name'], ['bold' => true, 'size' => 11]);
        }
        $addressCell = $letterhead->addCell(6700, ['valign' => 'center']);
        $addressCell->addText($document['institution_name'], ['bold' => true, 'size' => 10], ['alignment' => Jc::END, 'spaceAfter' => 20]);
        $addressCell->addText($document['department_name'], ['bold' => true, 'size' => 9], ['alignment' => Jc::END, 'spaceAfter' => 20]);
        foreach (preg_split('/\r\n|\r|\n/', $document['institution_address']) ?: [] as $addressLine) {
            $addressCell->addText(trim($addressLine), ['size' => 8], ['alignment' => Jc::END, 'spaceAfter' => 0]);
        }

        $section->addText('', [], ['spaceAfter' => 40]);
        $metadata = $section->addTable('Metadata');
        foreach ([
            ['Ruj. Kami', $document['reference_number']],
            ['Tarikh', $document['letter_date']],
        ] as [$label, $value]) {
            $metadata->addRow();
            $metadata->addCell(3000)->addText('');
            $metadata->addCell(1050)->addText($label, ['size' => 9], ['spaceAfter' => 0]);
            $metadata->addCell(200)->addText(':', ['size' => 9], ['spaceAfter' => 0]);
            $metadata->addCell(4900)->addText($value, ['size' => 9], ['spaceAfter' => 0]);
        }

        $section->addText('Kepada', ['size' => 10], ['spaceBefore' => 120, 'spaceAfter' => 0]);
        $section->addText('PIHAK YANG BERKENAAN', ['bold' => true, 'size' => 10], ['spaceAfter' => 100]);
        $section->addText('Tuan,', ['size' => 10], ['spaceAfter' => 100]);
        $section->addText(
            'PENGESAHAN PENGAJIAN PELAJAR',
            ['bold' => true, 'underline' => 'single', 'size' => 10],
            ['spaceAfter' => 120]
        );
        $section->addText(
            'Dengan segala hormatnya saya merujuk kepada perkara di atas.',
            ['size' => 10],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 100]
        );
        $section->addText(
            '2.    Pihak kami dengan ini mengesahkan bahawa pelajar dengan butiran seperti di bawah telah didaftarkan sebagai pelajar di institusi kami:',
            ['size' => 10],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 100]
        );

        $section->addText('2.1 BUTIRAN PENGAJIAN PELAJAR', ['bold' => true, 'size' => 10], ['spaceBefore' => 80, 'spaceAfter' => 40, 'indentation' => ['left' => 260]]);
        $details = $section->addTable('StudentDetails');
        foreach ([
            ['Nama pelajar', $document['student_name']],
            ['No. KP pelajar', $document['ic_no']],
            ['No. pendaftaran pelajar', $document['matric_no']],
            ['Program pengajian', $document['program_name']],
            ['Sesi pengajian bermula', $document['study_start_session']],
            ['Sesi pengajian tamat', $document['study_end_session']],
            ['Tempoh pengajian', $document['study_duration']],
            ['Tahun pengajian semasa', $document['current_study_year']],
            ['Semester pengajian semasa', 'SEMESTER '.$document['current_semester']],
        ] as [$label, $value]) {
            $details->addRow();
            $details->addCell(3600)->addText($label, ['size' => 9], ['spaceAfter' => 0]);
            $details->addCell(250)->addText(':', ['size' => 9], ['spaceAfter' => 0]);
            $details->addCell(5300)->addText($value, ['size' => 9], ['spaceAfter' => 0]);
        }

        $section->addText(
            '2.2 BUTIRAN TAJAAN / BIASISWA / PINJAMAN / PEMBIAYAAN (SEDIA ADA)',
            ['bold' => true, 'size' => 10],
            ['spaceBefore' => 80, 'spaceAfter' => 45, 'indentation' => ['left' => 260]]
        );
        $section->addText(
            $document['sponsorship_details'],
            ['size' => 10],
            ['spaceAfter' => 100, 'indentation' => ['left' => 520]]
        );
        $section->addText('Sekian, terima kasih.', ['size' => 10], ['spaceAfter' => 80]);
        foreach ($document['mottos'] as $motto) {
            $section->addText('“'.mb_strtoupper((string) $motto).'”', ['bold' => true, 'size' => 9], ['spaceAfter' => 15]);
        }
        $section->addText('', [], ['spaceAfter' => 220]);
        $section->addText('('.$document['officer_name'].')', ['bold' => true, 'size' => 10], ['spaceAfter' => 0]);
        $section->addText($document['officer_position'], ['size' => 9], ['spaceAfter' => 0]);
        $section->addText($document['officer_authority'], ['size' => 9], ['spaceAfter' => 0]);
        $section->addText($document['institution_name'], ['size' => 9], ['spaceAfter' => 100]);
        $section->addText('s.k    Fail Pelajar', ['size' => 9], ['spaceAfter' => 0]);

        $footer = $section->addFooter();
        $footer->addText(
            'Dokumen dijana melalui MyHEP pada '.$document['generated_at'].' • '.$document['reference_number'],
            ['italic' => true, 'color' => '666666', 'size' => 7.5],
            ['alignment' => Jc::CENTER]
        );

        $temporaryPath = tempnam(sys_get_temp_dir(), 'myhep-letter-');
        if ($temporaryPath === false) {
            throw new \RuntimeException('Unable to create the temporary DOCX file.');
        }

        try {
            IOFactory::createWriter($phpWord, 'Word2007')->save($temporaryPath);
            $contents = file_get_contents($temporaryPath);
            if ($contents === false) {
                throw new \RuntimeException('Unable to read the generated DOCX file.');
            }

            return $contents;
        } finally {
            @unlink($temporaryPath);
        }
    }

    public function filename(object $student, string $format = 'pdf'): string
    {
        $matric = preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) ($student->matric_no ?? 'pelajar')) ?: 'pelajar';
        $extension = $format === 'docx' ? 'docx' : 'pdf';

        return 'Surat-Pengesahan-Pengajian-'.$matric.'.'.$extension;
    }

    private function formatIdentityNumber(string $identityNumber): string
    {
        $digits = preg_replace('/\D+/', '', $identityNumber) ?: '';

        if (strlen($digits) === 12) {
            return substr($digits, 0, 6).'-'.substr($digits, 6, 2).'-'.substr($digits, 8, 4);
        }

        return mb_strtoupper(trim($identityNumber));
    }

    private function malayMonth(int $month): string
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Mac', 4 => 'April',
            5 => 'Mei', 6 => 'Jun', 7 => 'Julai', 8 => 'Ogos',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Disember',
        ][$month];
    }

    private function logoDataUri(): ?string
    {
        $path = public_path('images/logo-politeknik-besut.png');
        if (! is_file($path)) {
            return null;
        }

        return 'data:image/png;base64,'.base64_encode((string) file_get_contents($path));
    }
}
