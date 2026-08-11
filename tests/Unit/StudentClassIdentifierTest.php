<?php

namespace Tests\Unit;

use App\Support\StudentClassIdentifier;
use PHPUnit\Framework\TestCase;

class StudentClassIdentifierTest extends TestCase
{
    public function test_it_normalizes_a_class_and_extracts_its_semester(): void
    {
        $this->assertSame('DIT3B', StudentClassIdentifier::normalize(' dit 3b '));
        $this->assertSame('3', StudentClassIdentifier::semester('DIT3B'));
        $this->assertSame('2', StudentClassIdentifier::semester('DDT2A'));
    }

    public function test_it_returns_null_when_a_class_has_no_semester_number(): void
    {
        $this->assertNull(StudentClassIdentifier::semester('DIT'));
        $this->assertNull(StudentClassIdentifier::normalize(null));
    }
}
