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

    public function test_it_extracts_the_program_from_a_class_name(): void
    {
        $this->assertSame('DIT', ProgramIdentifier::from(null, 'DIT3B'));
        $this->assertSame('DDT', ProgramIdentifier::from(null, 'DDT2A'));
    }

    public function test_it_provides_the_official_programme_names(): void
    {
        $this->assertSame('Diploma Teknologi Maklumat', ProgramIdentifier::label('DIT'));
        $this->assertSame('Diploma Teknologi Maklumat (Teknologi Digital)', ProgramIdentifier::label('DDT'));
        $this->assertSame('Diploma Rekabentuk Kraf', ProgramIdentifier::label('DDC'));
        $this->assertSame('Diploma Rekabentuk Fesyen Batik', ProgramIdentifier::label('DBF'));
    }

    public function test_it_normalizes_full_programme_names_to_their_distinct_codes(): void
    {
        $this->assertSame('DIT', ProgramIdentifier::from(null, 'DIPLOMA TEKNOLOGI MAKLUMAT'));
        $this->assertSame('DDT', ProgramIdentifier::from(null, 'DIPLOMA TEKNOLOGI MAKLUMAT (TEKNOLOGI DIGITAL)'));
        $this->assertSame('DDC', ProgramIdentifier::from(null, 'DIPLOMA REKABENTUK KRAF'));
        $this->assertSame('DBF', ProgramIdentifier::from(null, 'DIPLOMA REKABENTUK FESYEN BATIK'));
    }
}
