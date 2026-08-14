<?php

namespace Tests\Unit;

use App\Support\ProgramApprovalRouting;
use PHPUnit\Framework\TestCase;

class ProgramApprovalRoutingTest extends TestCase
{
    public function test_academic_departments_route_to_tpa(): void
    {
        $this->assertSame('tpa', ProgramApprovalRouting::inferBranch('jtmk', 'Pensyarah'));
        $this->assertSame('tpa', ProgramApprovalRouting::inferBranch('jrkv', 'Ketua Jabatan'));
    }

    public function test_official_unit_position_overrides_flat_spreadsheet_section(): void
    {
        $this->assertSame('tpsp', ProgramApprovalRouting::inferBranch('jtmk', 'KU CISEC / KU UKK'));
        $this->assertSame('tpsp', ProgramApprovalRouting::inferBranch('pejabat_pengarah', 'KU UPLI'));
        $this->assertSame('tpsa', ProgramApprovalRouting::inferBranch('jtmk', 'KU USTM'));
        $this->assertSame('tpsa', ProgramApprovalRouting::inferBranch('jrkv', 'KU ASET'));
    }

    public function test_deputy_positions_identify_their_own_branch(): void
    {
        $this->assertSame('tpa', ProgramApprovalRouting::inferBranch('pejabat_pengarah', 'TIMBALAN PENGARAH'));
        $this->assertSame('tpsa', ProgramApprovalRouting::inferBranch('jtmk', 'TIMBALAN PENGARAH SOKONGAN AKADEMIK (TPSA)'));
        $this->assertSame('tpsp', ProgramApprovalRouting::inferBranch('jtmk', 'TIMBALAN PENGARAH STRATEGIK & PRESTASI (TPSP)'));
    }
}
