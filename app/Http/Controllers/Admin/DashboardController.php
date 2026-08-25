<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\LecturerPageAccess;
use App\Support\ProgramIdentifier;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(LecturerPageAccess $lecturerPages): View
    {
        $authUser = session('auth_user');
        $adminRole = $authUser['admin_role'] ?? null;
        $isLecturer = $adminRole === 'lecturer';
        $lecturerId = (int) ($authUser['id'] ?? 0);
        $showSystemMonitoring = $adminRole === 'system_admin';

        $canAccessDisciplineModule = canAccessDisciplineAdmin();
        $canAccessMovementModule = canAccessMovementAdmin();
        $hasDisciplineAccess = $canAccessDisciplineModule || $isLecturer;
        $hasMovementAccess = $canAccessMovementModule || $isLecturer;
        $hasScholarshipAccess = canAccessScholarshipAdmin();
        $canViewOffenseList = ! $isLecturer || $lecturerPages->enabled($lecturerId, 'offense_list');
        $canRegisterOffense = ! $isLecturer || $lecturerPages->enabled($lecturerId, 'offense_register');

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
        $programDashboard = $isLecturer ? $this->buildStaffProgramDashboard($lecturerId) : null;

        if ($hasMovementAccess) {
            $movementStats = systemCacheRemember('myhep.dashboard.movement_stats', 45, function (): array {
                $stats = [
                    'total_students' => (int) DB::table('students')->count(),
                    'outside_now' => 0,
                    'checkouts_today' => 0,
                    'late_returns' => 0,
                    'overnight_records' => 0,
                ];

                if (Schema::hasTable('student_movements')) {
                    $stats['outside_now'] = (int) DB::table('student_movements')->whereNull('return_at')->count();
                    $stats['checkouts_today'] = (int) DB::table('student_movements')->whereDate('checkout_at', now()->toDateString())->count();
                    $stats['late_returns'] = (int) DB::table('student_movements')->where('rule_status', 'late')->count();
                    $stats['overnight_records'] = (int) DB::table('student_movements')
                        ->join('movement_types', 'movement_types.id', '=', 'student_movements.movement_type_id')
                        ->where('movement_types.slug', 'overnight_stay')
                        ->count();
                }

                return $stats;
            });

            $totalStudents = (int) ($movementStats['total_students'] ?? 0);
            $outsideNow = (int) ($movementStats['outside_now'] ?? 0);
            $movementCheckoutsToday = (int) ($movementStats['checkouts_today'] ?? 0);
            $movementLateReturns = (int) ($movementStats['late_returns'] ?? 0);
            $movementOvernightRecords = (int) ($movementStats['overnight_records'] ?? 0);
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

        $systemMonitoring = $showSystemMonitoring
            ? systemCacheRemember('myhep.dashboard.system_monitoring', 10, fn () => $this->buildSystemMonitoring())
            : null;

        $canViewAnalytics = ! $isLecturer;
        $analyticsScope = implode('.', array_map(
            fn (bool $enabled) => $enabled ? '1' : '0',
            [
                $canViewAnalytics && $hasDisciplineAccess,
                $canViewAnalytics && $hasMovementAccess,
                $canViewAnalytics && $hasScholarshipAccess,
            ]
        ));
        $analytics = systemCacheRemember(
            'myhep.dashboard.analytics.payload.' . app()->getLocale() . '.' . $analyticsScope,
            300,
            fn () => $this->buildAnalytics(
                $canViewAnalytics && $hasDisciplineAccess,
                $canViewAnalytics && $hasMovementAccess,
                $canViewAnalytics && $hasScholarshipAccess,
            )
        );

        return view('dashboard.admin', compact(
            'authUser',
            'isLecturer',
            'programDashboard',
            'showSystemMonitoring',
            'systemMonitoring',
            'analytics',
            'hasDisciplineAccess',
            'hasMovementAccess',
            'hasScholarshipAccess',
            'canAccessMovementModule',
            'canViewOffenseList',
            'canRegisterOffense',
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

    private function buildStaffProgramDashboard(int $staffId): array
    {
        if (! Schema::hasTable('programs')) {
            return ['counts' => [], 'status_distribution' => [], 'trend' => [], 'recent' => collect()];
        }

        return systemCacheRemember("myhep.dashboard.staff_programs.{$staffId}", 60, function () use ($staffId): array {
            $statusCounts = DB::table('programs')
                ->where('created_by', $staffId)
                ->selectRaw('status, COUNT(*) as c')
                ->groupBy('status')
                ->pluck('c', 'status')
                ->all();

            $reviewTasksCount = DB::table('programs')->where(function ($query) use ($staffId): void {
                $query->where(function ($deputy) use ($staffId): void {
                    $deputy->where('status', 'pending_deputy')->where('deputy_reviewer_id', $staffId);
                })->orWhere(function ($director) use ($staffId): void {
                    $director->where('status', 'pending_director')->where('director_reviewer_id', $staffId);
                });
            })->count();

            $statuses = ['draft', 'pending_deputy', 'pending_director', 'approved', 'in_progress', 'completed', 'rejected'];
            $distribution = collect($statuses)->map(function (string $status) use ($statusCounts): array {
                return ['status' => $status, 'value' => (int) ($statusCounts[$status] ?? 0)];
            })->all();

            $sixMonthsAgo = now()->subMonthsNoOverflow(5)->startOfMonth();
            $createdRows = DB::table('programs')
                ->where('created_by', $staffId)
                ->where('created_at', '>=', $sixMonthsAgo)
                ->select('created_at')
                ->get();

            $approvedRows = DB::table('programs')
                ->where('created_by', $staffId)
                ->where('status', 'approved')
                ->where('director_reviewed_at', '>=', $sixMonthsAgo)
                ->select('director_reviewed_at')
                ->get();

            $trend = [];
            for ($offset = 5; $offset >= 0; $offset--) {
                $start = now()->subMonthsNoOverflow($offset)->startOfMonth();
                $monthKey = $start->format('Y-m');

                $createdCount = $createdRows->filter(fn ($r) => substr((string) $r->created_at, 0, 7) === $monthKey)->count();
                $approvedCount = $approvedRows->filter(fn ($r) => substr((string) $r->director_reviewed_at, 0, 7) === $monthKey)->count();

                $trend[] = [
                    'label' => $start->format('M'),
                    'created' => $createdCount,
                    'approved' => $approvedCount,
                ];
            }
            $trendMax = max(1, ...collect($trend)->flatMap(fn ($item) => [$item['created'], $item['approved']])->all());
            $trend = collect($trend)->map(function (array $item) use ($trendMax): array {
                $item['created_height'] = round(($item['created'] / $trendMax) * 100, 2);
                $item['approved_height'] = round(($item['approved'] / $trendMax) * 100, 2);
                return $item;
            })->all();

            $recent = DB::table('programs')
                ->where(function ($query) use ($staffId): void {
                    $query->where('created_by', $staffId)
                        ->orWhere('deputy_reviewer_id', $staffId)
                        ->orWhere('director_reviewer_id', $staffId);
                })
                ->select('id', 'title', 'status', 'starts_at', 'approval_branch', 'updated_at')
                ->orderByDesc('updated_at')
                ->limit(6)
                ->get();

            $totalOwned = array_sum($statusCounts);

            return [
                'counts' => [
                    'total_students' => (int) DB::table('students')->count(),
                    'total' => $totalOwned,
                    'draft' => (int) ($statusCounts['draft'] ?? 0),
                    'pending_deputy' => (int) ($statusCounts['pending_deputy'] ?? 0),
                    'pending_director' => (int) ($statusCounts['pending_director'] ?? 0),
                    'approved' => (int) ($statusCounts['approved'] ?? 0),
                    'in_progress' => (int) ($statusCounts['in_progress'] ?? 0),
                    'completed' => (int) ($statusCounts['completed'] ?? 0),
                    'review_tasks' => $reviewTasksCount,
                ],
                'status_distribution' => $distribution,
                'trend' => $trend,
                'recent' => $recent,
            ];
        });
    }

    public function live(): JsonResponse
    {
        $authUser = session('auth_user');
        if (($authUser['admin_role'] ?? null) !== 'system_admin') {
            abort(403);
        }

        return response()->json([
            'data' => systemCacheRemember(
                'myhep.dashboard.system_monitoring',
                10,
                fn () => $this->buildSystemMonitoring()
            ),
        ]);
    }

    private function buildAnalytics(bool $hasDisciplineAccess, bool $hasMovementAccess, bool $hasScholarshipAccess): array
    {
        $domains = [];
        if ($hasDisciplineAccess) {
            $domains[] = 'discipline';
        }
        if ($hasMovementAccess) {
            $domains[] = 'movement';
        }
        if ($hasScholarshipAccess) {
            $domains[] = 'scholarship';
        }

        $analytics = [
            'domains' => $domains,
            'kpis' => [],
            'gauges' => [],
            'donuts' => [],
            'stacked' => null,
            'treemap' => null,
            'hbar' => null,
            'grouped' => null,
            'trends' => [],
        ];

        if ($domains === []) {
            return $analytics;
        }

        $palette = ['var(--se-primary)', 'var(--se-success)', 'var(--se-warning)', 'var(--se-info)', 'var(--se-danger)', '#8c8175', '#6f8d78', '#9a6a3f'];

        $analytics['donuts'][] = $this->adminRoleDonut($palette);
        $analytics['donuts'][] = $this->studentRaceDonut($palette);
        $analytics['stacked'] = $this->programSemesterStacked($palette);

        if (in_array('discipline', $domains, true)) {
            $discipline = $this->disciplineAnalytics();
            $analytics['treemap'] = $discipline['treemap'];
            $analytics['hbar'] = $discipline['hbar'];
            $analytics['grouped'] = $discipline['grouped'];
            $analytics['trends'][] = $discipline['trend'];
            $analytics['gauges'][] = $discipline['gauge'];
            $analytics['kpis'] = array_merge($analytics['kpis'], $discipline['kpis']);
        }

        if (in_array('movement', $domains, true)) {
            $analytics['trends'][] = $this->domainTrends(
                'movement',
                __('Movement Activity'),
                __('Campus Movement'),
                'student_movements',
                'checkout_at'
            );
            $analytics['gauges'][] = $this->onTimeReturnGauge();
            $analytics['kpis'] = array_merge($analytics['kpis'], $this->movementKpis());
        }

        if (in_array('scholarship', $domains, true)) {
            $analytics['trends'][] = $this->domainTrends(
                'scholarship',
                __('Scholarship Activity'),
                __('Scholarship Records'),
                'scholarships',
                'created_at'
            );
            $analytics['gauges'][] = $this->scholarshipActiveGauge();
            $analytics['kpis'] = array_merge($analytics['kpis'], $this->scholarshipKpis());
        }

        return $analytics;
    }

    private function adminRoleDonut(array $palette): array
    {
        $rows = collect(systemCacheRemember('myhep.dashboard.analytics.roles.public.v2', 300, function () {
            return Schema::hasTable('admins')
                ? DB::table('admins')
                    ->selectRaw('role, COUNT(*) as c')
                    ->where('role', '!=', 'system_admin')
                    ->groupBy('role')
                    ->orderByDesc('c')
                    ->get()
                    ->map(fn ($row) => ['role' => (string) $row->role, 'c' => (int) $row->c])
                    ->all()
                : [];
        }));

        $total = (int) $rows->sum('c');
        $segments = $rows->values()->map(function ($row, $index) use ($palette) {
            return [
                'label' => adminRoleLabel((string) $row['role']),
                'value' => (int) $row['c'],
                'color' => $palette[$index % count($palette)],
            ];
        })->all();

        return [
            'kicker' => __('Staff'),
            'title' => __('User Roles'),
            'copy' => __('Distribution of staff accounts by role.'),
            'total' => $total,
            'segments' => $segments,
        ];
    }

    private function studentRaceDonut(array $palette): array
    {
        $rows = collect(systemCacheRemember('myhep.dashboard.analytics.races', 300, function () {
            return Schema::hasTable('students') && Schema::hasColumn('students', 'race')
                ? DB::table('students')
                    ->selectRaw('race, COUNT(*) as c')
                    ->groupBy('race')
                    ->orderByDesc('c')
                    ->get()
                    ->map(fn ($row) => ['race' => (string) ($row->race ?? ''), 'c' => (int) $row->c])
                    ->all()
                : [];
        }));

        $total = (int) $rows->sum('c');
        $segments = $rows->values()->map(function ($row, $index) use ($palette) {
            $race = trim((string) $row['race']);
            $label = $race === '' ? __('Not set') : $race;

            return [
                'label' => $label,
                'value' => (int) $row['c'],
                'color' => $palette[$index % count($palette)],
            ];
        })->all();

        return [
            'kicker' => __('Student Body'),
            'title' => __('Race Distribution'),
            'copy' => __('Student population by race group.'),
            'total' => $total,
            'segments' => $segments,
        ];
    }

    private function programSemesterStacked(array $palette): ?array
    {
        $rows = collect(systemCacheRemember('myhep.dashboard.analytics.programs', 300, function () {
            return Schema::hasTable('students')
                && Schema::hasColumn('students', 'program')
                && Schema::hasColumn('students', 'semester')
                ? DB::table('students')
                    ->selectRaw('program, semester, COUNT(*) as c')
                    ->groupBy('program', 'semester')
                    ->orderBy('program')
                    ->get()
                    ->map(fn ($row) => [
                        'program' => (string) ($row->program ?? ''),
                        'semester' => (string) ($row->semester ?? ''),
                        'c' => (int) $row->c,
                    ])
                    ->all()
                : [];
        }));

        if ($rows->isEmpty()) {
            return null;
        }

        $rows = $rows->map(function (array $row): array {
            $row['program'] = ProgramIdentifier::from(null, $row['program']);
            $row['semester'] = trim((string) $row['semester']);

            return $row;
        });

        $semesters = $rows->pluck('semester')
            ->unique()
            ->sortBy(fn ($value) => $value === '' ? '999' : str_pad((string) $value, 3, '0', STR_PAD_LEFT))
            ->values();

        $series = $rows->groupBy('program')->sortKeys()->map(function ($group) use ($semesters, $palette) {
            $segments = [];
            $total = 0;

            foreach ($semesters as $index => $semester) {
                $row = $group->firstWhere('semester', $semester);
                $count = $row ? (int) $row['c'] : 0;
                $total += $count;
                $segments[] = [
                    'label' => $semester === '' ? __('Not set') : __('Sem') . ' ' . $semester,
                    'value' => $count,
                    'color' => $palette[$index % count($palette)],
                ];
            }

            return [
                'label' => (string) $group->first()['program'],
                'total' => $total,
                'segments' => $segments,
            ];
        })->values()->all();

        return [
            'kicker' => __('Student Body'),
            'title' => __('Students by Program and Semester'),
            'copy' => __('Enrolled population split across programs and class years.'),
            'max' => max(1, ...collect($series)->pluck('total')->all()),
            'series' => $series,
        ];
    }

    private function disciplineAnalytics(): array
    {
        $cached = systemCacheRemember('myhep.dashboard.analytics.discipline', 90, function () {
            $ruleBreakdown = [];
            $topOffenseTypes = [];

            if (Schema::hasTable('offense_items') && Schema::hasTable('offense_types')) {
                $ruleBreakdown = DB::table('offense_items')
                    ->join('offense_types', 'offense_types.id', '=', 'offense_items.offense_type_id')
                    ->selectRaw('offense_types.rule_reference, COUNT(*) as c')
                    ->groupBy('offense_types.rule_reference')
                    ->orderByDesc('c')
                    ->get()
                    ->map(fn ($row) => ['label' => (string) $row->rule_reference, 'value' => (int) $row->c])
                    ->all();

                $topOffenseTypes = DB::table('offense_items')
                    ->join('offense_types', 'offense_types.id', '=', 'offense_items.offense_type_id')
                    ->selectRaw('offense_types.description, COUNT(*) as c')
                    ->groupBy('offense_types.description')
                    ->orderByDesc('c')
                    ->limit(8)
                    ->get()
                    ->map(fn ($row) => ['label' => (string) $row->description, 'value' => (int) $row->c])
                    ->all();
            }

            return [
                'rule_breakdown' => $ruleBreakdown,
                'top_offense_types' => $topOffenseTypes,
            ];
        });

        $treemap = null;
        if ($cached['rule_breakdown'] !== []) {
            $treemap = [
                'kicker' => __('Discipline'),
                'title' => __('Offenses by Rule Reference'),
                'copy' => __('Offense records categorized by the rule broken.'),
                'max' => max(1, ...collect($cached['rule_breakdown'])->pluck('value')->all()),
                'tiles' => $cached['rule_breakdown'],
                'total' => collect($cached['rule_breakdown'])->sum('value'),
            ];
        }

        $hbar = null;
        if ($cached['top_offense_types'] !== []) {
            $hbar = [
                'kicker' => __('Discipline'),
                'title' => __('Top Offense Types'),
                'copy' => __('Most recorded violated rules, ranked by volume.'),
                'max' => max(1, ...collect($cached['top_offense_types'])->pluck('value')->all()),
                'rows' => $cached['top_offense_types'],
            ];
        }

        $groupedRaw = $this->groupedByStatus('offenses', 'offense_date', ['paid', 'applied', 'unpaid']);
        $priority = array_flip(['paid', 'applied', 'unpaid']);
        $thisSeries = collect($groupedRaw['this'])
            ->sortBy(fn ($item) => $priority[$item['label']] ?? 99)
            ->values()
            ->all();
        $lastSeries = collect($groupedRaw['last'])
            ->sortBy(fn ($item) => $priority[$item['label']] ?? 99)
            ->values()
            ->all();

        $six = $this->monthlyCounts('offenses', 'offense_date', 6);
        $unpaidSix = $this->monthlyCounts('offenses', 'offense_date', 6, 'status', 'unpaid');
        $pendingSix = $this->monthlyCounts('fine_payment_applications', 'created_at', 6, 'status', 'pending');
        $studentSix = $this->monthlyCounts('students', 'created_at', 6);

        $totalOffenses = Schema::hasTable('offenses') ? (int) DB::table('offenses')->count() : 0;
        $paidOffenses = Schema::hasTable('offenses') ? (int) DB::table('offenses')->where('status', 'paid')->count() : 0;
        $unpaidOffenses = Schema::hasTable('offenses') ? (int) DB::table('offenses')->where('status', 'unpaid')->count() : 0;
        $pendingFine = Schema::hasTable('fine_payment_applications')
            ? (int) DB::table('fine_payment_applications')->where('status', 'pending')->count()
            : 0;
        $totalStudents = Schema::hasTable('students') ? (int) DB::table('students')->count() : 0;

        return [
            'treemap' => $treemap,
            'hbar' => $hbar,
            'grouped' => [
                'kicker' => __('Discipline'),
                'title' => __('Offenses This Month vs Last Month'),
                'copy' => __('Side-by-side comparison of offense statuses.'),
                'this' => $thisSeries,
                'last' => $lastSeries,
                'max' => max(1, ...array_merge(
                    collect($thisSeries)->pluck('value')->all(),
                    collect($lastSeries)->pluck('value')->all()
                )),
            ],
            'trend' => $this->domainTrends('discipline', __('Offense Activity'), __('Offenses'), 'offenses', 'offense_date'),
            'gauge' => [
                'kicker' => __('Discipline'),
                'title' => __('Offense Payment Rate'),
                'copy' => __('Share of registered offenses marked as paid.'),
                'value' => $totalOffenses > 0 ? (int) round(($paidOffenses / $totalOffenses) * 100) : 0,
                'display' => $totalOffenses > 0 ? number_format(($paidOffenses / $totalOffenses) * 100, 1) . '%' : '0%',
                'note' => $totalOffenses > 0
                    ? __(':paid of :total offenses paid', ['paid' => number_format($paidOffenses), 'total' => number_format($totalOffenses)])
                    : __('No offenses recorded yet.'),
                'tone' => 'green',
                'active' => $totalOffenses > 0,
            ],
            'kpis' => [
                $this->kpi(__('Total Offenses'), number_format($totalOffenses), __('All registered offense records'), 'slate', $six['values'], 'offense'),
                $this->kpi(__('Unpaid Offenses'), number_format($unpaidOffenses), __('Awaiting payment'), 'red', $unpaidSix['values'], 'payment'),
                $this->kpi(__('Pending Fine Applications'), number_format($pendingFine), __('Awaiting decision'), 'gold', $pendingSix['values'], 'review'),
                $this->kpi(__('Total Students'), number_format($totalStudents), __('Registered students'), 'blue', $studentSix['values'], 'students'),
            ],
        ];
    }

    private function movementKpis(): array
    {
        $outsideNow = Schema::hasTable('student_movements')
            ? (int) DB::table('student_movements')->whereNull('return_at')->count()
            : 0;
        $checkoutsToday = Schema::hasTable('student_movements')
            ? (int) DB::table('student_movements')->whereDate('checkout_at', now()->toDateString())->count()
            : 0;
        $lateReturns = Schema::hasTable('student_movements')
            ? (int) DB::table('student_movements')->where('rule_status', 'late')->count()
            : 0;
        $checkoutsSix = $this->monthlyCounts('student_movements', 'checkout_at', 6);
        $lateSix = $this->monthlyCounts('student_movements', 'checkout_at', 6, 'rule_status', 'late');

        return [
            $this->kpi(__('Outside Now'), number_format($outsideNow), __('Active checkouts without return'), 'green', $checkoutsSix['values'], 'outside'),
            $this->kpi(__('Check-Outs Today'), number_format($checkoutsToday), __('Movements started today'), 'blue', $checkoutsSix['values'], 'movement'),
            $this->kpi(__('Late Returns'), number_format($lateReturns), __('Returned past the allowance'), 'red', $lateSix['values'], 'late'),
        ];
    }

    private function scholarshipKpis(): array
    {
        $totalSix = $this->monthlyCounts('scholarships', 'created_at', 6);
        $pendingSix = $this->monthlyCounts('scholarships', 'created_at', 6, 'status', 'pending');
        $announcementsSix = $this->monthlyCounts('scholarship_announcements', 'created_at', 6);

        $total = Schema::hasTable('scholarships') ? (int) DB::table('scholarships')->count() : 0;
        $active = Schema::hasTable('scholarships')
            ? (int) DB::table('scholarships')
                ->where('status', 'confirmed')
                ->whereIn('type', ['scholarship', 'welfare', 'sponsorship'])
                ->count()
            : 0;
        $pending = Schema::hasTable('scholarships')
            ? (int) DB::table('scholarships')->where('status', 'pending')->count()
            : 0;

        return [
            $this->kpi(__('Total Scholarship Records'), number_format($total), __('All aid records'), 'slate', $totalSix['values'], 'records'),
            $this->kpi(__('Active Aid'), number_format($active), __('Confirmed scholarship, welfare, and sponsorship'), 'green', $totalSix['values'], 'aid'),
            $this->kpi(__('Pending Records'), number_format($pending), __('Awaiting decision'), 'gold', $pendingSix['values'], 'pending'),
            $this->kpi(__('Announcements'), (string) array_sum($announcementsSix['values']), __('Published in the last 6 months'), 'violet', $announcementsSix['values'], 'announcement'),
        ];
    }

    private function onTimeReturnGauge(): array
    {
        $returned = Schema::hasTable('student_movements')
            ? (int) DB::table('student_movements')->whereNotNull('return_at')->count()
            : 0;
        $late = Schema::hasTable('student_movements')
            ? (int) DB::table('student_movements')->where('rule_status', 'late')->count()
            : 0;
        $onTime = max(0, $returned - $late);

        return [
            'kicker' => __('Movement'),
            'title' => __('On-Time Return'),
            'copy' => __('Share of completed checkouts returned on time.'),
            'value' => $returned > 0 ? (int) round(($onTime / $returned) * 100) : 0,
            'display' => $returned > 0 ? number_format(($onTime / $returned) * 100, 1) . '%' : '0%',
            'note' => $returned > 0
                ? __('On time :on_time of :total returns', ['on_time' => number_format($onTime), 'total' => number_format($returned)])
                : __('No completed returns recorded yet.'),
            'tone' => 'blue',
            'active' => $returned > 0,
        ];
    }

    private function scholarshipActiveGauge(): array
    {
        $total = Schema::hasTable('scholarships') ? (int) DB::table('scholarships')->count() : 0;
        $active = Schema::hasTable('scholarships')
            ? (int) DB::table('scholarships')
                ->where('status', 'confirmed')
                ->whereIn('type', ['scholarship', 'welfare', 'sponsorship'])
                ->count()
            : 0;

        return [
            'kicker' => __('Scholarship'),
            'title' => __('Active Aid Rate'),
            'copy' => __('Share of aid records that are confirmed and active.'),
            'value' => $total > 0 ? (int) round(($active / $total) * 100) : 0,
            'display' => $total > 0 ? number_format(($active / $total) * 100, 1) . '%' : '0%',
            'note' => $total > 0
                ? __('Active :active of :total records', ['active' => number_format($active), 'total' => number_format($total)])
                : __('No scholarship records recorded yet.'),
            'tone' => 'gold',
            'active' => $total > 0,
        ];
    }

    private function domainTrends(string $domain, string $title, string $kicker, string $table, string $dateColumn): array
    {
        $six = $this->monthlyCounts($table, $dateColumn, 6);
        $twelve = $this->monthlyCounts($table, $dateColumn, 12);

        $running = 0;
        $cumulative = [];
        foreach ($six['values'] as $value) {
            $running += $value;
            $cumulative[] = $running;
        }

        return [
            'domain' => $domain,
            'title' => $title,
            'kicker' => $kicker,
            'six' => $six,
            'twelve' => $twelve,
            'area' => $cumulative,
            'heat' => $this->heatmapCounts($table, $dateColumn, 56),
            'lineTotal' => array_sum($six['values']),
        ];
    }

    private function kpi(string $label, string $value, string $sub, string $tone, array $spark, string $icon): array
    {
        return [
            'label' => $label,
            'value' => $value,
            'sub' => $sub,
            'tone' => $tone,
            'spark' => $spark,
            'icon' => $icon,
            'delta' => $this->deltaPercent($spark),
        ];
    }

    private function deltaPercent(array $values): array
    {
        $count = count($values);
        $current = $count > 0 ? (int) $values[$count - 1] : 0;
        $previous = $count > 1 ? (int) $values[$count - 2] : 0;

        if ($previous <= 0) {
            return [
                'dir' => $current > 0 ? 'up' : 'flat',
                'text' => $current > 0 ? __('New') : '—',
            ];
        }

        $percent = round((($current - $previous) / $previous) * 100, 1);

        return [
            'dir' => $percent > 0 ? 'up' : ($percent < 0 ? 'down' : 'flat'),
            'text' => ($percent > 0 ? '+' : '') . number_format($percent, 1) . '%',
        ];
    }

    private function monthlyCounts(string $table, string $dateColumn, int $months, ?string $statusColumn = null, ?string $statusValue = null): array
    {
        $labels = [];
        $keyed = [];

        foreach (range($months - 1, 0) as $offset) {
            $month = now()->subMonths($offset);
            $keyed[$month->format('Y-m')] = 0;
            $labels[] = $month->format('M y');
        }

        if (Schema::hasTable($table)
            && Schema::hasColumn($table, $dateColumn)
            && ($statusColumn === null || Schema::hasColumn($table, $statusColumn))) {
            $monthExpression = DB::getDriverName() === 'sqlite'
                ? "strftime('%Y-%m', $dateColumn)"
                : "DATE_FORMAT($dateColumn, '%Y-%m')";
            $query = DB::table($table)->where($dateColumn, '>=', now()->subMonths($months - 1)->startOfMonth());
            if ($statusColumn !== null) {
                $query->where($statusColumn, $statusValue);
            }

            foreach ($query->selectRaw("$monthExpression as ym, COUNT(*) as c")->groupBy('ym')->get() as $row) {
                if (array_key_exists($row->ym, $keyed)) {
                    $keyed[$row->ym] = (int) $row->c;
                }
            }
        }

        $values = [];
        foreach (range($months - 1, 0) as $offset) {
            $values[] = $keyed[now()->subMonths($offset)->format('Y-m')];
        }

        return ['labels' => $labels, 'values' => $values];
    }

    private function groupedByStatus(string $table, string $dateColumn, array $statuses): array
    {
        $thisMonth = now()->format('Y-m');
        $lastMonth = now()->subMonth()->format('Y-m');
        $keyed = ['this' => [], 'last' => []];

        foreach ($statuses as $status) {
            $keyed['this'][] = ['label' => $status, 'value' => 0];
            $keyed['last'][] = ['label' => $status, 'value' => 0];
        }

        if (Schema::hasTable($table)
            && Schema::hasColumn($table, $dateColumn)
            && Schema::hasColumn($table, 'status')) {
            $monthExpression = DB::getDriverName() === 'sqlite'
                ? "strftime('%Y-%m', $dateColumn)"
                : "DATE_FORMAT($dateColumn, '%Y-%m')";
            $rows = DB::table($table)
                ->selectRaw("$monthExpression as ym, status, COUNT(*) as c")
                ->where($dateColumn, '>=', now()->subMonth()->startOfMonth())
                ->groupBy('ym', 'status')
                ->get();

            foreach ($rows as $row) {
                if ($row->ym === $thisMonth || $row->ym === $lastMonth) {
                    $bucket = $row->ym === $thisMonth ? 'this' : 'last';
                    $index = array_search($row->status, $statuses, true);
                    if ($index !== false) {
                        $keyed[$bucket][$index]['value'] = (int) $row->c;
                    } else {
                        $keyed[$bucket][] = ['label' => (string) $row->status, 'value' => (int) $row->c];
                    }
                }
            }
        }

        return $keyed;
    }

    private function heatmapCounts(string $table, string $dateColumn, int $days = 56): array
    {
        $keyed = [];
        $cells = [];

        foreach (range($days - 1, 0) as $offset) {
            $day = now()->subDays($offset);
            $keyed[$day->toDateString()] = 0;
            $cells[] = [
                'date' => $day->format('d M'),
                'weekday' => $day->format('D'),
                'count' => 0,
                'level' => 0,
            ];
        }

        if (Schema::hasTable($table) && Schema::hasColumn($table, $dateColumn)) {
            $dayExpression = DB::getDriverName() === 'sqlite'
                ? "strftime('%Y-%m-%d', $dateColumn)"
                : "DATE_FORMAT($dateColumn, '%Y-%m-%d')";
            foreach (DB::table($table)
                ->where($dateColumn, '>=', now()->subDays($days - 1)->startOfDay())
                ->selectRaw("$dayExpression as d, COUNT(*) as c")
                ->groupBy('d')
                ->get() as $row) {
                if (array_key_exists($row->d, $keyed)) {
                    $keyed[$row->d] = (int) $row->c;
                }
            }
        }

        $max = max(1, ...array_values($keyed));

        foreach ($cells as $index => $cell) {
            $count = $keyed[now()->subDays($days - 1 - $index)->toDateString()];
            $cells[$index]['count'] = $count;
            $cells[$index]['level'] = $count > 0 ? max(1, (int) round(($count / $max) * 4)) : 0;
        }

        return [
            'cells' => $cells,
            'max' => $max,
            'total' => array_sum($keyed),
        ];
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
