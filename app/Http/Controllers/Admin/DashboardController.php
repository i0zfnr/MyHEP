<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $authUser = session('auth_user');
        $adminRole = $authUser['admin_role'] ?? null;
        $showSystemMonitoring = $adminRole === 'system_admin';

        $hasDisciplineAccess = canAccessDisciplineAdmin();
        $hasMovementAccess = canAccessMovementAdmin();
        $hasScholarshipAccess = canAccessScholarshipAdmin();

        $totalStudents = 0;
        $totalOffenses = 0;
        $unpaidOffenses = 0;
        $pendingFineApplications = 0;
        $outsideNow = 0;
        $movementCheckoutsToday = 0;
        $movementLateReturns = 0;
        $movementOvernightRecords = 0;
        $recentOffenses = collect();
        $recentFineApplications = collect();
        $totalScholarshipRecords = 0;
        $activeScholarships = 0;
        $pendingScholarships = 0;
        $recentScholarshipRecords = collect();
        $recentScholarshipAnnouncements = collect();

        if ($hasMovementAccess) {
            $totalStudents = DB::table('students')->count();

            if (Schema::hasTable('student_movements')) {
                $outsideNow = DB::table('student_movements')->whereNull('return_at')->count();
                $movementCheckoutsToday = DB::table('student_movements')->whereDate('checkout_at', now()->toDateString())->count();
                $movementLateReturns = DB::table('student_movements')->where('rule_status', 'late')->count();
                $movementOvernightRecords = DB::table('student_movements')
                    ->join('movement_types', 'movement_types.id', '=', 'student_movements.movement_type_id')
                    ->where('movement_types.slug', 'overnight_stay')
                    ->count();
            }
        }

        if ($hasDisciplineAccess) {
            $disciplineStats = systemCacheRemember('myhep.dashboard.discipline_stats', 90, function () {
                return [
                    'total_students' => DB::table('students')->count(),
                    'total_offenses' => DB::table('offenses')->count(),
                    'unpaid_offenses' => DB::table('offenses')->where('status', 'unpaid')->count(),
                    'pending_fine_applications' => DB::table('fine_payment_applications')
                        ->where('status', 'pending')
                        ->count(),
                ];
            });
            $totalStudents = (int) ($disciplineStats['total_students'] ?? 0);
            $totalOffenses = (int) ($disciplineStats['total_offenses'] ?? 0);
            $unpaidOffenses = (int) ($disciplineStats['unpaid_offenses'] ?? 0);
            $pendingFineApplications = (int) ($disciplineStats['pending_fine_applications'] ?? 0);

            $recentOffenses = collect(systemCacheRemember('myhep.dashboard.recent_offenses', 45, function () {
                return DB::table('offenses')
                    ->join('students', 'students.id', '=', 'offenses.student_id')
                    ->select(
                        'offenses.id',
                        'offenses.status',
                        'offenses.created_at',
                        'students.full_name as student_name',
                        'students.matric_no'
                    )
                    ->orderByDesc('offenses.created_at')
                    ->limit(6)
                    ->get()
                    ->map(fn ($row) => (array) $row)
                    ->all();
            }))->map(fn ($row) => (object) $row);

            $recentFineApplications = collect(systemCacheRemember('myhep.dashboard.recent_fine_applications', 45, function () {
                return DB::table('fine_payment_applications')
                    ->join('students', 'students.id', '=', 'fine_payment_applications.student_id')
                    ->join('offenses', 'offenses.id', '=', 'fine_payment_applications.offense_id')
                    ->select(
                        'fine_payment_applications.id',
                        'fine_payment_applications.status',
                        'fine_payment_applications.created_at',
                        'fine_payment_applications.meeting_date',
                        'students.full_name as student_name',
                        'offenses.place'
                    )
                    ->orderByDesc('fine_payment_applications.created_at')
                    ->limit(6)
                    ->get()
                    ->map(fn ($row) => (array) $row)
                    ->all();
            }))->map(fn ($row) => (object) $row);
        }

        if ($hasScholarshipAccess) {
            $scholarshipStats = systemCacheRemember('myhep.dashboard.scholarship_stats', 90, function () {
                return [
                    'total_scholarship_records' => DB::table('scholarships')->count(),
                    'active_scholarships' => DB::table('scholarships')
                        ->where('status', 'confirmed')
                        ->whereIn('type', ['scholarship', 'welfare', 'sponsorship'])
                        ->count(),
                    'pending_scholarships' => DB::table('scholarships')
                        ->where('status', 'pending')
                        ->count(),
                ];
            });
            $totalScholarshipRecords = (int) ($scholarshipStats['total_scholarship_records'] ?? 0);
            $activeScholarships = (int) ($scholarshipStats['active_scholarships'] ?? 0);
            $pendingScholarships = (int) ($scholarshipStats['pending_scholarships'] ?? 0);

            $recentScholarshipRecords = collect(systemCacheRemember('myhep.dashboard.recent_scholarship_records', 45, function () {
                return DB::table('scholarships')
                    ->join('students', 'students.id', '=', 'scholarships.student_id')
                    ->select(
                        'scholarships.id',
                        'scholarships.type',
                        'scholarships.status',
                        'students.full_name as student_name',
                        'students.matric_no'
                    )
                    ->orderByDesc('scholarships.created_at')
                    ->limit(6)
                    ->get()
                    ->map(fn ($row) => (array) $row)
                    ->all();
            }))->map(fn ($row) => (object) $row);

            $recentScholarshipAnnouncements = collect(systemCacheRemember('myhep.dashboard.recent_scholarship_announcements', 45, function () {
                return DB::table('scholarship_announcements')
                    ->select('id', 'title', 'type', 'created_at')
                    ->orderByDesc('created_at')
                    ->limit(6)
                    ->get()
                    ->map(fn ($row) => (array) $row)
                    ->all();
            }))->map(fn ($row) => (object) $row);
        }

        $systemMonitoring = $showSystemMonitoring ? $this->buildSystemMonitoring() : null;

        return view('dashboard.admin', compact(
            'authUser',
            'showSystemMonitoring',
            'systemMonitoring',
            'hasDisciplineAccess',
            'hasMovementAccess',
            'hasScholarshipAccess',
            'totalStudents',
            'totalOffenses',
            'unpaidOffenses',
            'pendingFineApplications',
            'outsideNow',
            'movementCheckoutsToday',
            'movementLateReturns',
            'movementOvernightRecords',
            'recentOffenses',
            'recentFineApplications',
            'totalScholarshipRecords',
            'activeScholarships',
            'pendingScholarships',
            'recentScholarshipRecords',
            'recentScholarshipAnnouncements'
        ));
    }

    public function live(): JsonResponse
    {
        $authUser = session('auth_user');
        if (($authUser['admin_role'] ?? null) !== 'system_admin') {
            abort(403);
        }

        return response()->json([
            'data' => $this->buildSystemMonitoring(),
        ]);
    }

    private function buildSystemMonitoring(): array
    {
        $diskTotal = @disk_total_space(base_path());
        $diskFree = @disk_free_space(base_path());
        $diskUsed = ($diskTotal !== false && $diskFree !== false) ? ($diskTotal - $diskFree) : null;
        $diskUsagePercent = ($diskTotal && $diskUsed !== null && $diskTotal > 0)
            ? round(($diskUsed / $diskTotal) * 100, 1)
            : null;

        $memoryLimitBytes = $this->parseIniBytes(ini_get('memory_limit'));
        $memoryUsageBytes = memory_get_usage(true);
        $memoryPeakBytes = memory_get_peak_usage(true);
        $memoryUsagePercent = ($memoryLimitBytes && $memoryLimitBytes > 0)
            ? round(($memoryUsageBytes / $memoryLimitBytes) * 100, 1)
            : null;

        $cpuUsagePercent = $this->getCpuUsagePercent();
        $loadAvg = function_exists('sys_getloadavg') ? sys_getloadavg() : false;
        $load1 = is_array($loadAvg) && isset($loadAvg[0]) ? round((float) $loadAvg[0], 2) : null;

        $dbStatus = 'ok';
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $dbStatus = 'error';
        }

        $overallLoad = round(collect([$cpuUsagePercent, $memoryUsagePercent, $diskUsagePercent])
            ->filter(fn ($value) => $value !== null)
            ->avg() ?? 0, 1);
        $trendBase = $overallLoad > 0 ? $overallLoad : 42;

        return [
            'maintenance' => app()->isDownForMaintenance(),
            'cpu_percent' => $cpuUsagePercent,
            'ram_percent' => $memoryUsagePercent,
            'disk_percent' => $diskUsagePercent,
            'overall_load' => $overallLoad,
            'trend' => [
                max(8, $trendBase - 18),
                max(8, $trendBase - 10),
                max(8, $trendBase - 7),
                max(8, $trendBase + 9),
                max(8, $trendBase - 3),
                max(8, $trendBase - 14),
                max(8, $trendBase - 6),
            ],
            'ram_usage_text' => $this->formatBytes($memoryUsageBytes),
            'ram_peak_text' => $this->formatBytes($memoryPeakBytes),
            'ram_limit_text' => $memoryLimitBytes !== null ? $this->formatBytes($memoryLimitBytes) : 'Unlimited',
            'disk_used_text' => $diskUsed !== null ? $this->formatBytes((int) $diskUsed) : '-',
            'disk_total_text' => $diskTotal !== false ? $this->formatBytes((int) $diskTotal) : '-',
            'db_status' => $dbStatus,
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'os' => php_uname('s') . ' ' . php_uname('r'),
            'server_time' => now()->format('Y-m-d H:i:s'),
            'load_1m' => $load1,
        ];
    }

    private function getCpuUsagePercent(): ?float
    {
        $cpu = null;

        if (PHP_OS_FAMILY === 'Windows') {
            $output = @shell_exec('wmic cpu get loadpercentage /value 2>NUL');
            if (is_string($output) && preg_match('/LoadPercentage=(\d+)/i', $output, $match)) {
                $cpu = (float) $match[1];
            }
        } else {
            $output = @shell_exec("top -bn1 | grep 'Cpu(s)'");
            if (is_string($output) && preg_match('/(\d+(?:\.\d+)?)\s*id/', $output, $match)) {
                $idle = (float) $match[1];
                $cpu = max(0.0, min(100.0, 100.0 - $idle));
            }
        }

        return $cpu !== null ? round($cpu, 1) : null;
    }

    private function parseIniBytes($value): ?int
    {
        if ($value === false || $value === null) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '' || $value === '-1') {
            return null;
        }

        if (!preg_match('/^(\d+)([KMGTP]?)/i', $value, $matches)) {
            return null;
        }

        $bytes = (int) $matches[1];
        $unit = strtoupper($matches[2] ?? '');

        return match ($unit) {
            'P' => $bytes * 1024 * 1024 * 1024 * 1024 * 1024,
            'T' => $bytes * 1024 * 1024 * 1024 * 1024,
            'G' => $bytes * 1024 * 1024 * 1024,
            'M' => $bytes * 1024 * 1024,
            'K' => $bytes * 1024,
            default => $bytes,
        };
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $value = (float) $bytes;
        $index = 0;

        while ($value >= 1024 && $index < count($units) - 1) {
            $value /= 1024;
            $index++;
        }

        return number_format($value, $index === 0 ? 0 : 2) . ' ' . $units[$index];
    }
}
