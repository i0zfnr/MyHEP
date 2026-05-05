<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function monthly(Request $request): View
    {
        $hasDisciplineAccess = canAccessDisciplineAdmin();
        $hasScholarshipAccess = canAccessScholarshipAdmin();

        $monthInput = $request->query('month');
        $month = preg_match('/^\d{4}-\d{2}$/', (string) $monthInput) ? $monthInput : now()->format('Y-m');
        $start = Carbon::createFromFormat('Y-m-d', "{$month}-01")->startOfDay();
        $end = $start->copy()->endOfMonth()->endOfDay();

        $disciplineSummary = null;
        $scholarshipSummary = null;

        if ($hasDisciplineAccess) {
            $disciplineSummary = [
                'new_offenses' => DB::table('offenses')->whereBetween('created_at', [$start, $end])->count(),
                'paid_offenses' => DB::table('offenses')->where('status', 'paid')->whereBetween('updated_at', [$start, $end])->count(),
                'fine_pending' => DB::table('fine_payment_applications')->where('status', 'pending')->whereBetween('created_at', [$start, $end])->count(),
                'fine_approved' => DB::table('fine_payment_applications')->where('status', 'approved')->whereBetween('updated_at', [$start, $end])->count(),
                'sticker_pending' => DB::table('vehicle_sticker_applications')->where('status', 'pending')->whereBetween('created_at', [$start, $end])->count(),
                'sticker_approved' => DB::table('vehicle_sticker_applications')->where('status', 'approved')->whereBetween('updated_at', [$start, $end])->count(),
            ];
        }

        if ($hasScholarshipAccess) {
            $scholarshipSummary = [
                'new_records' => DB::table('scholarships')->whereBetween('created_at', [$start, $end])->count(),
                'confirmed' => DB::table('scholarships')->where('status', 'confirmed')->whereBetween('updated_at', [$start, $end])->count(),
                'pending' => DB::table('scholarships')->where('status', 'pending')->whereBetween('created_at', [$start, $end])->count(),
                'announcements' => DB::table('scholarship_announcements')->whereBetween('created_at', [$start, $end])->count(),
            ];
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
}
