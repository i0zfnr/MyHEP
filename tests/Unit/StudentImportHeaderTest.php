<?php

namespace Tests\Unit;

use App\Http\Controllers\Admin\StudentController;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class StudentImportHeaderTest extends TestCase
{
    public function test_official_student_list_headers_are_detected_and_mapped(): void
    {
        $controller = new StudentController();
        $rowsWithHeaders = new ReflectionMethod($controller, 'rowsWithHeaders');

        $rows = [
            ['Senarai Nama Keseluruhan Pelajar'],
            [],
            ['Bil', 'Nama', 'No KP', 'No Pend', 'Kod Kursus', 'Kelas', 'Sesi Semasa'],
            ['1', 'PELAJAR CONTOH', '080316031157', '34DIT26F1001', 'DF008', 'DIT1A', 'I : 2026/2027'],
        ];

        $mapped = $rowsWithHeaders->invoke($controller, $rows);

        $this->assertCount(1, $mapped);
        $this->assertSame('PELAJAR CONTOH', $mapped[0]['nama']);
        $this->assertSame('080316031157', $mapped[0]['no kp']);
        $this->assertSame('34DIT26F1001', $mapped[0]['no pend']);
        $this->assertSame('DF008', $mapped[0]['kod kursus']);
        $this->assertSame('I : 2026/2027', $mapped[0]['sesi semasa']);
    }

    public function test_official_headers_resolve_to_student_fields(): void
    {
        $controller = new StudentController();
        $rowValue = new ReflectionMethod($controller, 'rowValue');
        $row = [
            'nama' => 'PELAJAR CONTOH',
            'no kp' => '080316031157',
            'no pend' => '34DIT26F1001',
            'kod kursus' => 'DF008',
            'sesi semasa' => 'I : 2026/2027',
        ];

        $this->assertSame('080316031157', $rowValue->invoke($controller, $row, ['no ic', 'no kp']));
        $this->assertSame('34DIT26F1001', $rowValue->invoke($controller, $row, ['no matrik', 'no pend']));
        $this->assertSame('DF008', $rowValue->invoke($controller, $row, ['program', 'kod kursus']));
        $this->assertSame('I : 2026/2027', $rowValue->invoke($controller, $row, ['sesi akademik', 'sesi semasa']));
    }
}
