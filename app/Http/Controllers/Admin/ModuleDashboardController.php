<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ModuleDashboardController extends Controller
{
    private const OVERSIGHT_ROLES = ['system_admin', 'student_affairs_head'];

    public function scholarship(): View
    {
        $this->authorizeOversight('scholarship');

        $hasRecords = Schema::hasTable('scholarships');
        $hasForms = Schema::hasTable('student_scholarship_status_forms');

        $totalRecords = $hasRecords ? (int) DB::table('scholarships')->count() : 0;
        $confirmed = $hasRecords
            ? (int) DB::table('scholarships')->where('status', 'confirmed')->whereIn('type', ['scholarship', 'welfare', 'sponsorship'])->count()
            : 0;
        $pending = $hasRecords ? (int) DB::table('scholarships')->where('status', 'pending')->count() : 0;
        $submittedStudents = $hasForms ? (int) DB::table('student_scholarship_status_forms')->distinct()->count('student_id') : 0;
        $confirmedAmount = $hasRecords
            ? (float) DB::table('scholarships')->where('status', 'confirmed')->whereIn('type', ['scholarship', 'welfare', 'sponsorship'])->sum('amount')
            : 0;

        $statusRows = $hasRecords
            ? DB::table('scholarships')->select('status')->selectRaw('COUNT(*) as total')->groupBy('status')->orderByDesc('total')->get()
            : collect();
        $typeRows = $hasRecords
            ? DB::table('scholarships')->select('type')->selectRaw('COUNT(*) as total')->groupBy('type')->orderByDesc('total')->get()
            : collect();
        $formRows = $hasForms
            ? DB::table('student_scholarship_status_forms')->select('has_scholarship')->selectRaw('COUNT(*) as total')->groupBy('has_scholarship')->orderByDesc('total')->get()
            : collect();
        $providerRows = $hasRecords
            ? DB::table('scholarships')->whereNotNull('provider_name')->where('provider_name', '<>', '')
                ->select('provider_name')->selectRaw('COUNT(*) as total')
                ->groupBy('provider_name')->orderByDesc('total')->limit(6)->get()
            : collect();

        return view('admin.dashboards.module', [
            'title' => __('Scholarship Dashboard'),
            'eyebrow' => __('Scholarship'),
            'description' => __('A visual overview of scholarship submissions, aid records, and review status.'),
            'recordsUrl' => route('admin.scholarships.index'),
            'recordsLabel' => __('Scholarship Records'),
            'cards' => [
                ['label' => __('Students submitted details'), 'value' => number_format($submittedStudents), 'note' => __('Unique students with a submitted scholarship form'), 'tone' => 'gold'],
                ['label' => __('Scholarship records'), 'value' => number_format($totalRecords), 'note' => __('All recorded aid entries'), 'tone' => 'blue'],
                ['label' => __('Confirmed aid'), 'value' => number_format($confirmed), 'note' => __('Records confirmed by the office'), 'tone' => 'green'],
                ['label' => __('Pending review'), 'value' => number_format($pending), 'note' => __('Records awaiting a decision'), 'tone' => 'orange'],
                ['label' => __('Confirmed amount'), 'value' => 'RM ' . number_format((float) $confirmedAmount, 2), 'note' => __('Sum of confirmed aid record amounts'), 'tone' => 'purple'],
            ],
            'charts' => [
                $this->monthlyChart('Scholarship records by month', 'New aid records created in the last six months.', 'scholarships', 'created_at'),
                $this->rowsChart(__('Records by status'), __('Review outcome for recorded aid entries.'), $this->chartRows($statusRows, 'status')),
                $this->rowsChart(__('Records by aid type'), __('Distribution across scholarship, welfare, and sponsorship records.'), $this->chartRows($typeRows, 'type')),
                $this->rowsChart(__('Student form responses'), __('Students who reported receiving aid or no aid.'), $this->chartRows($formRows, 'has_scholarship', ['yes' => __('Has scholarship'), 'no' => __('No scholarship')])) ,
                $this->rowsChart(__('Top aid providers'), __('Providers with the most scholarship records.'), $this->chartRows($providerRows, 'provider_name')),
            ],
        ]);
    }

    public function discipline(): View
    {
        $this->authorizeOversight('discipline');

        $hasOffenses = Schema::hasTable('offenses');
        $total = $hasOffenses ? (int) DB::table('offenses')->count() : 0;
        $unpaid = $hasOffenses ? (int) DB::table('offenses')->where('status', 'unpaid')->count() : 0;
        $paid = $hasOffenses ? (int) DB::table('offenses')->where('status', 'paid')->count() : 0;
        $applied = $hasOffenses ? (int) DB::table('offenses')->where('status', 'applied')->count() : 0;
        $pendingApplications = Schema::hasTable('fine_payment_applications')
            ? DB::table('fine_payment_applications')->where('status', 'pending')->count()
            : 0;

        $statusRows = $hasOffenses
            ? DB::table('offenses')->select('status')->selectRaw('COUNT(*) as total')->groupBy('status')->orderByDesc('total')->get()
            : collect();
        $locationRows = $hasOffenses
            ? DB::table('offenses')->whereNotNull('place')->where('place', '<>', '')
                ->select('place')->selectRaw('COUNT(*) as total')
                ->groupBy('place')->orderByDesc('total')->limit(6)->get()
            : collect();
        $ruleRows = Schema::hasTable('offense_items') && Schema::hasTable('offense_types')
            ? DB::table('offense_items')
                ->join('offense_types', 'offense_types.id', '=', 'offense_items.offense_type_id')
                ->select('offense_types.rule_reference')->selectRaw('COUNT(*) as total')
                ->groupBy('offense_types.rule_reference')->orderByDesc('total')->limit(6)->get()
            : collect();

        return view('admin.dashboards.module', [
            'title' => __('Discipline Dashboard'),
            'eyebrow' => __('Discipline'),
            'description' => __('A visual overview of student offenses, payment status, and recorded rules.'),
            'recordsUrl' => route('admin.offenses.index'),
            'recordsLabel' => __('Offense List'),
            'cards' => [
                ['label' => __('Total offenses'), 'value' => number_format($total), 'note' => __('All recorded offense cases'), 'tone' => 'blue'],
                ['label' => __('Unpaid offenses'), 'value' => number_format($unpaid), 'note' => __('Awaiting payment'), 'tone' => 'orange'],
                ['label' => __('Fine applications pending'), 'value' => number_format($pendingApplications), 'note' => __('Awaiting office review'), 'tone' => 'gold'],
                ['label' => __('Paid cases'), 'value' => number_format($paid), 'note' => $total > 0 ? number_format(($paid / $total) * 100, 1) . '% ' . __('of all cases') : __('No offense cases recorded yet'), 'tone' => 'green'],
                ['label' => __('Payment applications'), 'value' => number_format($applied), 'note' => __('Cases with a payment application'), 'tone' => 'purple'],
            ],
            'charts' => [
                $this->monthlyChart(__('Offenses by month'), __('Cases recorded in the last six months.'), 'offenses', 'offense_date'),
                $this->rowsChart(__('Cases by payment status'), __('Current status of recorded offense cases.'), $this->chartRows($statusRows, 'status')),
                $this->rowsChart(__('Most recorded rules'), __('Rule references appearing most often in offense items.'), $this->chartRows($ruleRows, 'rule_reference')),
                $this->rowsChart(__('Cases by location'), __('Locations with the most recorded cases.'), $this->chartRows($locationRows, 'place')),
            ],
        ]);
    }

    private function authorizeOversight(string $module): void
    {
        abort_unless(in_array(session('auth_user.admin_role'), self::OVERSIGHT_ROLES, true), 403);
        abort_unless($module === 'scholarship' ? canAccessScholarshipAdmin() : canAccessDisciplineAdmin(), 403);
    }

    private function monthlyChart(string $title, string $description, string $table, string $dateColumn): array
    {
        $months = [];
        foreach (range(5, 0) as $offset) {
            $month = now()->startOfMonth()->subMonths($offset);
            $start = $month->copy();
            $end = $month->copy()->endOfMonth();
            $value = Schema::hasTable($table) && Schema::hasColumn($table, $dateColumn)
                ? (int) DB::table($table)->whereBetween($dateColumn, [$start, $end])->count()
                : 0;
            $months[] = ['label' => $month->format('M'), 'value' => $value];
        }

        return ['title' => __($title), 'description' => __($description), 'items' => $months];
    }

    private function rowsChart(string $title, string $description, array $rows): array
    {
        return ['title' => $title, 'description' => $description, 'items' => $rows];
    }

    private function chartRows(iterable $rows, string $labelKey, array $labels = []): array
    {
        return collect($rows)->map(fn ($row) => [
            'label' => $labels[(string) ($row->{$labelKey} ?? '')] ?? ucfirst((string) ($row->{$labelKey} ?? __('Unknown'))),
            'value' => (int) ($row->total ?? 0),
        ])->values()->all();
    }
}
