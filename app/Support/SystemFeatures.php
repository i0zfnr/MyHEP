<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemFeatures
{
    private const ADMIN_LIQUID_DESIGN_ROLES = [
        'system_admin',
        'student_affairs_head',
        'scholarship_admin',
        'discipline_admin',
        'lecturer',
        'guard',
    ];

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
            'label' => 'Liquid Glass for Staff and Admin',
            'description' => 'Allow Liquid Glass styling for System Admin and let other staff/admin roles opt in from Settings. Turn this off to force the solid, higher-contrast interface for all admin roles. Student styling is unchanged.',
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
        if (! $this->exists($key)) {
            return false;
        }

        if (! Schema::hasTable('system_features')) {
            return $key !== 'enforce_student_profile_photo';
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

    public function adminLiquidDesignAvailable(?string $adminRole): bool
    {
        return in_array($adminRole, self::ADMIN_LIQUID_DESIGN_ROLES, true)
            && $this->enabled('admin_liquid_design');
    }

    public function adminLiquidDesignEnabled(?string $adminRole, bool $userOptIn = false): bool
    {
        return $this->adminLiquidDesignAvailable($adminRole)
            && ($adminRole === 'system_admin' || $userOptIn);
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
