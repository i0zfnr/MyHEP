<?php

namespace Tests\Unit;

use App\Support\ProgramIdentifier;
use PHPUnit\Framework\TestCase;

class ProgramIdentifierTest extends TestCase
{
    public function test_it_prefers_the_matric_program_prefix(): void
    {
        $this->assertSame('DIT', ProgramIdentifier::from('34DIT26F1001', 'DF008'));
        $this->assertSame('DIT', ProgramIdentifier::from('34DIT27F1001', 'DF009'));
    }

    public function test_it_normalizes_known_course_codes_without_a_matric_number(): void
    {
        $this->assertSame('DDT', ProgramIdentifier::from(null, 'DF007'));
        $this->assertSame('DDC', ProgramIdentifier::from(null, 'DV010'));
        $this->assertSame('DBF', ProgramIdentifier::from(null, 'DV011'));
    }
}
