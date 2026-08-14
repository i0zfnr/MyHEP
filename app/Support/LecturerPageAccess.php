<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LecturerPageAccess
{
    public const PAGES = [
        'offense_list' => [
            'label' => 'Offense List',
            'description' => 'Allow lecturer to view offense records and print offense documents.',
            'default' => false,
        ],
        'offense_register' => [
            'label' => 'Register Offense',
            'description' => 'Allow lecturer to register a new offense for a student.',
            'default' => false,
        ],
        'guard_management' => [
            'label' => 'Guard Management',
            'description' => 'Allow this lecturer to add, update, reset, and remove guard accounts.',
            'default' => false,
        ],
    ];

    public function exists(string $page): bool
    {
        return array_key_exists($page, self::PAGES);
    }

    public function enabled(int $adminId, string $page): bool
    {
        if (! $this->exists($page) || ! Schema::hasTable('lecturer_page_access')) {
            return $this->exists($page) && (bool) (self::PAGES[$page]['default'] ?? false);
        }

        $value = DB::table('lecturer_page_access')
            ->where('admin_id', $adminId)
            ->where('page_key', $page)
            ->value('enabled');

        return $value === null ? (bool) (self::PAGES[$page]['default'] ?? false) : (bool) $value;
    }

    public function allFor(int $adminId): array
    {
        return collect(self::PAGES)
            ->map(fn (array $page, string $key): array => $page + [
                'key' => $key,
                'enabled' => $this->enabled($adminId, $key),
            ])
            ->values()
            ->all();
    }

    public function sync(int $adminId, array $enabledPages, int $updatedBy): void
    {
        foreach (array_keys(self::PAGES) as $page) {
            DB::table('lecturer_page_access')->updateOrInsert(
                ['admin_id' => $adminId, 'page_key' => $page],
                [
                    'enabled' => in_array($page, $enabledPages, true),
                    'updated_by' => $updatedBy,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function forget(int $adminId): void
    {
        if (Schema::hasTable('lecturer_page_access')) {
            DB::table('lecturer_page_access')->where('admin_id', $adminId)->delete();
        }
    }
}
