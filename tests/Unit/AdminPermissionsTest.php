<?php

namespace Tests\Unit;

use App\Support\AdminPermissions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AdminPermissionsTest extends TestCase
{
    public function test_lecturer_category_unlocks_only_its_operational_module(): void
    {
        $permissions = new AdminPermissions;

        $this->assertTrue($permissions->allowsAccount('lecturer', 'discipline', 'discipline'));
        $this->assertTrue($permissions->allowsAccount('lecturer', 'discipline', 'movement'));
        $this->assertFalse($permissions->allowsAccount('lecturer', 'discipline', 'scholarship'));
        $this->assertTrue($permissions->allowsAccount('lecturer', 'scholarship', 'scholarship'));
        $this->assertFalse($permissions->allowsAccount('lecturer', 'scholarship', 'discipline'));
        $this->assertFalse($permissions->allowsAccount('lecturer', 'general', 'discipline'));
        $this->assertTrue($permissions->allowsAccount('lecturer', 'general', 'laptops.use'));
        $this->assertFalse($permissions->allowsAccount('lecturer', 'general', 'laptops.manage'));
    }

    #[DataProvider('studentPermissions')]
    public function test_student_permissions_follow_the_role_matrix(string $role, string $ability, bool $expected): void
    {
        $this->assertSame($expected, (new AdminPermissions)->allowsRole($role, $ability));
    }

    public static function studentPermissions(): array
    {
        return [
            'guard can list' => ['guard', 'students.list', true],
            'guard cannot view sensitive profile' => ['guard', 'students.sensitive', false],
            'guard cannot export' => ['guard', 'students.export', false],
            'guard cannot manage' => ['guard', 'students.manage', false],
            'lecturer can register offense' => ['lecturer', 'offense.register', true],
            'lecturer can use limited student lookup' => ['lecturer', 'students.lookup', true],
            'lecturer cannot list student directory' => ['lecturer', 'students.list', false],
            'lecturer cannot view sensitive profile' => ['lecturer', 'students.sensitive', false],
            'lecturer cannot export student data' => ['lecturer', 'students.export', false],
            'lecturer cannot manage students' => ['lecturer', 'students.manage', false],
            'head can manage staff' => ['student_affairs_head', 'staff.manage', true],
            'system admin can manage staff' => ['system_admin', 'staff.manage', true],
            'head can manage laptops' => ['student_affairs_head', 'laptops.manage', true],
            'system admin can manage laptops' => ['system_admin', 'laptops.manage', true],
            'head can manage program student page access' => ['student_affairs_head', 'program_access.manage', true],
            'system admin can manage program student page access' => ['system_admin', 'program_access.manage', true],
            'discipline admin cannot manage program student page access' => ['discipline_admin', 'program_access.manage', false],
            'lecturer can use laptops' => ['lecturer', 'laptops.use', true],
            'discipline admin can use laptops' => ['discipline_admin', 'laptops.use', true],
            'scholarship admin can use laptops' => ['scholarship_admin', 'laptops.use', true],
            'guard can use laptops' => ['guard', 'laptops.use', true],
            'discipline admin can manage guards' => ['discipline_admin', 'guards.manage', true],
            'lecturer guard permission still requires page gate' => ['lecturer', 'guards.manage', true],
            'scholarship admin cannot manage guards' => ['scholarship_admin', 'guards.manage', false],
            'scholarship admin can list' => ['scholarship_admin', 'students.list', true],
            'scholarship admin can view student profile for welfare assessment' => ['scholarship_admin', 'students.sensitive', true],
            'scholarship admin cannot export generic student data' => ['scholarship_admin', 'students.export', false],
            'discipline admin can view sensitive profile' => ['discipline_admin', 'students.sensitive', true],
            'discipline admin can export' => ['discipline_admin', 'students.export', true],
            'discipline admin can manage' => ['discipline_admin', 'students.manage', true],
            'student affairs head can manage' => ['student_affairs_head', 'students.manage', true],
            'system admin can manage' => ['system_admin', 'students.manage', true],
            'student affairs head can review documents' => ['student_affairs_head', 'documents', true],
            'system admin can review documents' => ['system_admin', 'documents', true],
            'discipline admin can review documents' => ['discipline_admin', 'documents', true],
            'scholarship admin cannot review documents' => ['scholarship_admin', 'documents', false],
            'guard cannot review documents' => ['guard', 'documents', false],
            'unknown role is denied' => ['unknown', 'students.list', false],
            'unknown ability is denied' => ['system_admin', 'unknown', false],
        ];
    }
}
