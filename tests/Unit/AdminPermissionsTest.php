<?php

namespace Tests\Unit;

use App\Support\AdminPermissions;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AdminPermissionsTest extends TestCase
{
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
            'scholarship admin can list' => ['scholarship_admin', 'students.list', true],
            'scholarship admin cannot view sensitive profile' => ['scholarship_admin', 'students.sensitive', false],
            'scholarship admin cannot export generic student data' => ['scholarship_admin', 'students.export', false],
            'discipline admin can view sensitive profile' => ['discipline_admin', 'students.sensitive', true],
            'discipline admin can export' => ['discipline_admin', 'students.export', true],
            'discipline admin can manage' => ['discipline_admin', 'students.manage', true],
            'student affairs head can manage' => ['student_affairs_head', 'students.manage', true],
            'system admin can manage' => ['system_admin', 'students.manage', true],
            'student affairs head can review documents' => ['student_affairs_head', 'documents', true],
            'system admin can review documents' => ['system_admin', 'documents', true],
            'discipline admin cannot review documents' => ['discipline_admin', 'documents', false],
            'scholarship admin cannot review documents' => ['scholarship_admin', 'documents', false],
            'guard cannot review documents' => ['guard', 'documents', false],
            'unknown role is denied' => ['unknown', 'students.list', false],
            'unknown ability is denied' => ['system_admin', 'unknown', false],
        ];
    }
}
