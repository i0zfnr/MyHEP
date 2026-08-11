<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemFeatures
{
    public const FEATURES = [
        'document_centre' => [
            'label' => 'Document Centre',
            'description' => 'Student document archive and private document downloads.',
        ],
        'student_ai_helper' => [
            'label' => 'AI Helper for Students',
            'description' => 'Allow students to use AI guidance based only on their own records.',
        ],
        'admin_ai_helper' => [
            'label' => 'AI Helper for Administrators',
            'description' => 'Allow regular authorized administrators to use the AI Helper. System administrators always retain access.',
        ],
        'admin_liquid_design' => [
            'label' => 'Liquid Design for Administrators',
            'description' => 'Use liquid glass effects for non-system administrators. Turn this off for solid, higher-contrast panels with clearer borders and reduced visual effects.',
        ],
    ];

    public function exists(string $key): bool
    {
        return array_key_exists($key, self::FEATURES);
    }

    public function enabled(string $key): bool
    {
        if (! $this->exists($key) || ! Schema::hasTable('system_features')) {
            return $this->exists($key);
        }

        $value = DB::table('system_features')->where('feature_key', $key)->value('enabled');

        return $value === null ? true : (bool) $value;
    }

    public function all(): array
    {
        return collect(self::FEATURES)->map(function (array $feature, string $key): array {
            return array_merge($feature, ['key' => $key, 'enabled' => $this->enabled($key)]);
        })->values()->all();
    }

    public function adminLiquidDesignEnabled(?string $adminRole): bool
    {
        return $adminRole === 'system_admin' || $this->enabled('admin_liquid_design');
    }

    public function set(string $key, bool $enabled, int $adminId): void
    {
        abort_unless($this->exists($key), 404);

        DB::table('system_features')->updateOrInsert(
            ['feature_key' => $key],
            ['enabled' => $enabled, 'updated_by' => $adminId, 'created_at' => now(), 'updated_at' => now()]
        );
    }
}
