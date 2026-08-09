<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

class Nric
{
    public static function normalize(?string $value): string
    {
        return preg_replace('/[^0-9]/', '', (string) $value) ?? '';
    }

    public static function isAssignedToAdmin(string $nric, ?int $ignoreId = null): bool
    {
        return DB::table('admins')
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->whereNotNull('ic_no')
            ->pluck('ic_no')
            ->contains(fn (string $existing): bool => self::normalize($existing) === $nric);
    }
}
