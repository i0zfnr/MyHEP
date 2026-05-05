<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

if (!function_exists('isStudentAuthenticated')) {
    function isStudentAuthenticated(): bool
    {
        $authUser = session('auth_user');
        return (bool) ($authUser && ($authUser['role'] ?? null) === 'student');
    }
}

if (!function_exists('isAdminAuthenticated')) {
    function isAdminAuthenticated(): bool
    {
        $authUser = session('auth_user');
        return (bool) ($authUser && ($authUser['role'] ?? null) === 'admin');
    }
}

if (!function_exists('canAccessScholarshipAdmin')) {
    function canAccessScholarshipAdmin(): bool
    {
        if (!isAdminAuthenticated()) {
            return false;
        }

        $adminRole = session('auth_user.admin_role');
        return in_array($adminRole, ['scholarship_admin', 'system_admin'], true);
    }
}

if (!function_exists('canAccessDisciplineAdmin')) {
    function canAccessDisciplineAdmin(): bool
    {
        if (!isAdminAuthenticated()) {
            return false;
        }

        $adminRole = session('auth_user.admin_role');
        return in_array($adminRole, ['discipline_admin', 'system_admin'], true);
    }
}

if (!function_exists('baseRuleCategories')) {
    function baseRuleCategories(): array
    {
        return [
            'Kad Pelajar',
            'Pemakaian',
            'Kebersihan',
            'Bunyi Bising',
            'Perpustakaan',
            'Trafik Kampus',
            'Kemudahan Kampus',
            'Lain-lain',
        ];
    }
}

if (!function_exists('ensureRuleCategoriesSeeded')) {
    function ensureRuleCategoriesSeeded(): void
    {
        $now = now();
        $rows = collect(baseRuleCategories())
            ->map(fn ($name) => [
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        DB::table('rule_categories')->upsert($rows, ['name'], ['updated_at']);
    }
}

if (!function_exists('ruleCategoryOptions')) {
    function ruleCategoryOptions()
    {
        ensureRuleCategoriesSeeded();
        return DB::table('rule_categories')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }
}

if (!function_exists('downloadCsv')) {
    function downloadCsv(string $filename, array $headers, iterable $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

if (!function_exists('auditLog')) {
    function auditLog(string $action, ?string $targetType = null, ?int $targetId = null, ?string $description = null): void
    {
        try {
            $authUser = session('auth_user');
            DB::table('audit_logs')->insert([
                'actor_type' => $authUser['role'] ?? 'guest',
                'actor_id' => isset($authUser['id']) ? (int) $authUser['id'] : null,
                'action' => $action,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => Str::limit((string) request()->userAgent(), 255, ''),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Do not block main flow if audit logging fails.
        }
    }
}
