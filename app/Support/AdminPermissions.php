<?php

namespace App\Support;

class AdminPermissions
{
    private const ABILITY_ROLES = [
        'scholarship' => ['scholarship_admin', 'student_affairs_head', 'system_admin'],
        'discipline' => ['discipline_admin', 'student_affairs_head', 'system_admin'],
        'students' => ['scholarship_admin', 'discipline_admin', 'student_affairs_head', 'guard', 'system_admin'],
        'movement' => ['guard', 'discipline_admin', 'student_affairs_head', 'system_admin'],
        'backoffice' => ['scholarship_admin', 'discipline_admin', 'student_affairs_head', 'system_admin'],
        'system' => ['system_admin'],
        'students.list' => ['scholarship_admin', 'discipline_admin', 'student_affairs_head', 'guard', 'system_admin'],
        'students.sensitive' => ['discipline_admin', 'student_affairs_head', 'system_admin'],
        'students.export' => ['discipline_admin', 'student_affairs_head', 'system_admin'],
        'students.manage' => ['discipline_admin', 'student_affairs_head', 'system_admin'],
        'documents' => ['student_affairs_head', 'system_admin'],
    ];

    public function allowsRole(?string $role, string $ability): bool
    {
        return in_array($role, self::ABILITY_ROLES[$ability] ?? [], true);
    }

    public function allowsSession(string $ability): bool
    {
        $authUser = session('auth_user');

        return is_array($authUser)
            && ($authUser['role'] ?? null) === 'admin'
            && $this->allowsRole($authUser['admin_role'] ?? null, $ability);
    }
}
