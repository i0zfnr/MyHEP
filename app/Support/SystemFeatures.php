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
            'description' => 'Allow students to use text guidance based only on their own records. File upload and broad administrative research remain unavailable.',
        ],
        'lecturer_ai_helper' => [
            'label' => 'AI Helper for Staff',
            'description' => 'Allow lecturers to use AI guidance with lecturer-appropriate context and attach PDF or image report sources.',
        ],
        'admin_ai_helper' => [
            'label' => 'AI Helper for Administrators',
            'description' => 'Allow regular authorized administrators to use the AI Helper. System administrators always retain access.',
        ],
        'admin_liquid_design' => [
            'label' => 'Liquid Design for Administrators',
            'description' => 'Use liquid glass effects for non-system administrators. Turn this off for solid, higher-contrast panels with clearer borders and reduced visual effects.',
        ],
        'enforce_student_profile_photo' => [
            'label' => 'Mandatory Student Profile Photo (Beta)',
            'description' => 'Require students to upload a verified formal profile photo before accessing other portal features, with automated face alignment and client-side face detection.',
        ],
        'student_browser_bottom_nav' => [
            'label' => 'Student Bottom Navigation in Browser',
            'description' => 'Show the student mobile bottom navigation in normal mobile browsers. Installed PWA mode always keeps the bottom navigation available.',
        ],
    ];

    public function exists(string $key): bool
    {
        return array_key_exists($key, self::FEATURES);
    }

    public function enabled(string $key): bool
    {
        if (! $this->exists($key) || ! Schema::hasTable('system_features')) {
            return false;
        }

        $value = DB::table('system_features')->where('feature_key', $key)->value('enabled');

        if ($value === null) {
            return $key === 'enforce_student_profile_photo' ? false : true;
        }

        return (bool) $value;
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
