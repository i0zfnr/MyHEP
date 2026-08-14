<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProgramApprovalRouting
{
    public const BRANCHES = [
        'tpa' => 'TPA (Deputy Director Academic)',
        'tpsa' => 'TPSA (Deputy Director Academic Support)',
        'tpsp' => 'TPSP (Deputy Director Strategic & Performance)',
    ];

    public static function inferBranch(?string $department, ?string $position = null): ?string
    {
        $position = Str::lower(Str::ascii((string) $position));
        if (str_contains($position, 'tpsp') || str_contains($position, 'strategik') || str_contains($position, 'prestasi')) {
            return 'tpsp';
        }
        if (str_contains($position, 'tpsa') || str_contains($position, 'sokongan akademik')) {
            return 'tpsa';
        }
        if (preg_match('/\btpa\b|timbalan pengarah akademik|^timbalan pengarah$/', $position)) {
            return 'tpa';
        }
        if (preg_match('/\b(?:ulpl|upli|upiks|ujk|cisec|ukk)\b|latihan|penyelidikan|inovasi|jaminan kualiti|audit/', $position)) {
            return 'tpsp';
        }
        if (preg_match('/\b(?:ustm|uidm|aset)\b|pengurus projek|sistem teknologi|multimedia/', $position)) {
            return 'tpsa';
        }

        return match ($department) {
            'jtmk', 'jpa', 'jrkv', 'jmsk' => 'tpa',
            'unit_khidmat_pengurusan', 'unit_pengurusan_kewangan' => 'tpsa',
            default => null,
        };
    }

    public static function deputyFor(string $branch): ?object
    {
        return DB::table('admins')->where('is_active', true)->where('reporting_branch', $branch)
            ->orderByRaw("CASE WHEN LOWER(position) LIKE 'timbalan pengarah%' THEN 0 ELSE 1 END")
            ->get()->first(function (object $staff) use ($branch): bool {
                $position = Str::lower(Str::ascii((string) $staff->position));

                return match ($branch) {
                    'tpa' => preg_match('/\btpa\b|timbalan pengarah.*akademik|^timbalan pengarah$/', $position) === 1,
                    'tpsa' => str_contains($position, 'tpsa') || str_contains($position, 'sokongan akademik'),
                    'tpsp' => str_contains($position, 'tpsp') || (str_contains($position, 'strategik') && str_contains($position, 'prestasi')),
                    default => false,
                };
            });
    }

    public static function polytechnicDirector(): ?object
    {
        return DB::table('admins')->where('is_active', true)
            ->whereRaw("LOWER(TRIM(position)) IN ('pengarah', 'pengarah politeknik')")
            ->first();
    }

    public static function kjHep(): ?object
    {
        return DB::table('admins')->where('is_active', true)
            ->where(function ($query): void {
                $query->where('role', 'student_affairs_head')
                    ->orWhereRaw("LOWER(TRIM(position)) IN ('kj hep', 'ketua jabatan hep', 'ketua hal ehwal pelajar')");
            })->first();
    }
}
