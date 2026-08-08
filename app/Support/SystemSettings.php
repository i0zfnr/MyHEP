<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemSettings
{
    public const SESSION_LIFETIME = 'session_lifetime_minutes';

    public function sessionLifetime(): int
    {
        $fallback = (int) config('session.lifetime', 120);
        if (! Schema::hasTable('system_settings')) {
            return $fallback;
        }

        $value = DB::table('system_settings')->where('setting_key', self::SESSION_LIFETIME)->value('setting_value');

        return $this->validLifetime($value) ?? $fallback;
    }

    public function setSessionLifetime(int $minutes, int $adminId): void
    {
        DB::table('system_settings')->updateOrInsert(
            ['setting_key' => self::SESSION_LIFETIME],
            ['setting_value' => (string) $minutes, 'updated_by' => $adminId, 'created_at' => now(), 'updated_at' => now()]
        );
    }

    private function validLifetime(mixed $value): ?int
    {
        $minutes = filter_var($value, FILTER_VALIDATE_INT);

        return $minutes !== false && $minutes >= 1440 && $minutes <= 43200 ? $minutes : null;
    }
}
