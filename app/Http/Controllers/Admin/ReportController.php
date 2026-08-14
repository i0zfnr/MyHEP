<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function monthly(Request $request): View
    {
        $hasDisciplineAccess = canAccessDisciplineAdmin()
            || (session('auth_user.admin_role') ?? null) === 'lecturer';
        $hasScholarshipAccess = canAccessScholarshipAdmin();

        $monthInput = (string) $request->query('month', '');
        $month = preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $monthInput) ? $monthInput : now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m-d', "{$month}-01")->startOfDay();
        $end = $start->copy()->endOfMonth()->endOfDay();

        if ((session('auth_user.admin_role') ?? null) === 'lecturer') {
            $staffId = (int) session('auth_user.id');
            $programSummary = $this->staffProgramSummary($staffId, $start, $end);

            return view('admin.reports.program_monthly', compact('month', 'start', 'end', 'programSummary'));
        }

        $disciplineSummary = null;
        $scholarshipSummary = null;

        if ($hasDisciplineAccess) {
            $fineStatuses = $this->statusCounts('fine_payment_applications', $start, $end);
            $stickerStatuses = $this->statusCounts('vehicle_sticker_applications', $start, $end);
            $disciplineSummary = [
                'new_offenses' => DB::table('offenses')->whereBetween('created_at', [$start, $end])->count(),
                'paid_offenses' => DB::table('offenses')->where('status', 'paid')->whereBetween('updated_at', [$start, $end])->count(),
                'fine_total' => array_sum($fineStatuses),
                'fine_pending' => $fineStatuses['pending'] ?? 0,
                'fine_status_approved' => $fineStatuses['approved'] ?? 0,
                'fine_status_rejected' => $fineStatuses['rejected'] ?? 0,
                'fine_approved' => DB::table('fine_payment_applications')->where('status', 'approved')->whereBetween('updated_at', [$start, $end])->count(),
                'fine_rejected' => DB::table('fine_payment_applications')->where('status', 'rejected')->whereBetween('updated_at', [$start, $end])->count(),
                'sticker_total' => array_sum($stickerStatuses),
                'sticker_pending' => $stickerStatuses['pending'] ?? 0,
                'sticker_approved' => DB::table('vehicle_sticker_applications')->where('status', 'approved')->whereBetween('updated_at', [$start, $end])->count(),
                'sticker_rejected' => $stickerStatuses['rejected'] ?? 0,
                'current_unpaid' => DB::table('offenses')->where('status', 'unpaid')->count(),
                'current_fine_backlog' => DB::table('fine_payment_applications')->where('status', 'pending')->count(),
            ];
            $disciplineSummary['fine_approval_rate'] = $this->percentage(
                $disciplineSummary['fine_approved'],
                $disciplineSummary['fine_approved'] + $disciplineSummary['fine_rejected']
            );
            $disciplineSummary['sticker_approval_rate'] = $this->percentage(
                $disciplineSummary['sticker_approved'],
                $disciplineSummary['sticker_approved'] + $disciplineSummary['sticker_rejected']
            );
            $disciplineSummary['trend'] = $this->sixMonthTrend(
                $start,
                fn (Carbon $periodStart, Carbon $periodEnd): int => DB::table('offenses')->whereBetween('created_at', [$periodStart, $periodEnd])->count(),
                fn (Carbon $periodStart, Carbon $periodEnd): int => DB::table('fine_payment_applications')->where('status', 'approved')->whereBetween('updated_at', [$periodStart, $periodEnd])->count()
            );
        }

        if ($hasScholarshipAccess) {
            $scholarshipStatuses = $this->statusCounts('scholarships', $start, $end);
            $scholarshipSummary = [
                'new_records' => array_sum($scholarshipStatuses),
                'confirmed' => DB::table('scholarships')->where('status', 'confirmed')->whereBetween('updated_at', [$start, $end])->count(),
                'pending' => $scholarshipStatuses['pending'] ?? 0,
                'rejected' => $scholarshipStatuses['rejected'] ?? 0,
                'status_confirmed' => $scholarshipStatuses['confirmed'] ?? 0,
                'rejected_decisions' => DB::table('scholarships')->where('status', 'rejected')->whereBetween('updated_at', [$start, $end])->count(),
                'announcements' => DB::table('scholarship_announcements')->whereBetween('created_at', [$start, $end])->count(),
                'current_pending' => DB::table('scholarships')->where('status', 'pending')->count(),
                'confirmed_amount' => (float) DB::table('scholarships')
                    ->where('status', 'confirmed')
                    ->whereBetween('updated_at', [$start, $end])
                    ->sum('amount'),
            ];
            $scholarshipSummary['confirmation_rate'] = $this->percentage(
                $scholarshipSummary['confirmed'],
                $scholarshipSummary['confirmed'] + $scholarshipSummary['rejected_decisions']
            );
            $scholarshipSummary['trend'] = $this->sixMonthTrend(
                $start,
                fn (Carbon $periodStart, Carbon $periodEnd): int => DB::table('scholarships')->whereBetween('created_at', [$periodStart, $periodEnd])->count(),
                fn (Carbon $periodStart, Carbon $periodEnd): int => DB::table('scholarships')->where('status', 'confirmed')->whereBetween('updated_at', [$periodStart, $periodEnd])->count()
            );
        }

        return view('admin.reports.monthly', compact(
            'month',
            'start',
            'end',
            'hasDisciplineAccess',
            'hasScholarshipAccess',
            'disciplineSummary',
            'scholarshipSummary'
        ));
    }

    private function statusCounts(string $table, Carbon $start, Carbon $end): array
    {
        return DB::table($table)
            ->whereBetween('created_at', [$start, $end])
            ->select('status', DB::raw('COUNT(*) as aggregate'))
            ->groupBy('status')
            ->pluck('aggregate', 'status')
            ->map(fn ($count): int => (int) $count)
            ->all();
    }

    private function percentage(int $value, int $total): float
    {
        return $total > 0 ? round(($value / $total) * 100, 1) : 0.0;
    }

    private function staffProgramSummary(int $staffId, Carbon $start, Carbon $end): array
    {
        if (! Schema::hasTable('programs')) {
            return ['counts' => [], 'statuses' => [], 'trend' => [], 'recent' => collect(), 'approval_rate' => 0.0];
        }

        $owned = DB::table('programs')->where('created_by', $staffId);
        $createdThisMonth = (clone $owned)->whereBetween('created_at', [$start, $end])->count();
        $approvedThisMonth = (clone $owned)->where('status', 'approved')->whereBetween('director_reviewed_at', [$start, $end])->count();
        $rejectedThisMonth = (clone $owned)->where('status', 'rejected')->whereBetween('updated_at', [$start, $end])->count();
        $statuses = collect(['draft', 'pending_deputy', 'pending_director', 'approved', 'in_progress', 'completed', 'rejected'])
            ->map(fn (string $status): array => ['status' => $status, 'value' => (clone $owned)->where('status', $status)->count()])
            ->all();

        $trend = $this->sixMonthTrend(
            $start,
            fn (Carbon $periodStart, Carbon $periodEnd): int => (clone $owned)->whereBetween('created_at', [$periodStart, $periodEnd])->count(),
            fn (Carbon $periodStart, Carbon $periodEnd): int => (clone $owned)->where('status', 'approved')->whereBetween('director_reviewed_at', [$periodStart, $periodEnd])->count()
        );

        return [
            'counts' => [
                'created' => $createdThisMonth,
                'submitted' => (clone $owned)->whereIn('status', ['pending_deputy', 'pending_director'])->whereBetween('updated_at', [$start, $end])->count(),
                'approved' => $approvedThisMonth,
                'completed' => (clone $owned)->where('status', 'completed')->whereBetween('updated_at', [$start, $end])->count(),
                'current_total' => (clone $owned)->count(),
                'review_tasks' => DB::table('programs')->where(function ($query) use ($staffId): void {
                    $query->where(fn ($q) => $q->where('status', 'pending_deputy')->where('deputy_reviewer_id', $staffId))
                        ->orWhere(fn ($q) => $q->where('status', 'pending_director')->where('director_reviewer_id', $staffId));
                })->count(),
            ],
            'approval_rate' => $this->percentage($approvedThisMonth, $approvedThisMonth + $rejectedThisMonth),
            'statuses' => $statuses,
            'trend' => $trend,
            'recent' => (clone $owned)->whereBetween('updated_at', [$start, $end])->select('id', 'title', 'status', 'starts_at', 'approval_branch')->orderByDesc('updated_at')->limit(10)->get(),
        ];
    }

    private function sixMonthTrend(Carbon $selectedMonth, callable $primaryQuery, callable $secondaryQuery): array
    {
        $trend = [];

        for ($offset = 5; $offset >= 0; $offset--) {
            $periodStart = $selectedMonth->copy()->subMonthsNoOverflow($offset)->startOfMonth()->startOfDay();
            $periodEnd = $periodStart->copy()->endOfMonth()->endOfDay();
            $trend[] = [
                'label' => $periodStart->format('M'),
                'year' => $periodStart->format('Y'),
                'primary' => $primaryQuery($periodStart, $periodEnd),
                'secondary' => $secondaryQuery($periodStart, $periodEnd),
            ];
        }

        $maximum = max(1, ...array_map(
            fn (array $period): int => max($period['primary'], $period['secondary']),
            $trend
        ));

        return array_map(function (array $period) use ($maximum): array {
            $period['primary_height'] = round(($period['primary'] / $maximum) * 100, 2);
            $period['secondary_height'] = round(($period['secondary'] / $maximum) * 100, 2);

            return $period;
        }, $trend);
    }
}
