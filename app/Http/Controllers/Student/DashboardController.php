<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $authUser = session('auth_user');
        $studentId = (int) session('auth_user.id');

        $studentColumns = ['id', 'full_name', 'matric_no', 'ic_no', 'program'];
        if (Schema::hasColumn('students', 'semester')) {
            $studentColumns[] = 'semester';
        }
        if (Schema::hasColumn('students', 'academic_session')) {
            $studentColumns[] = 'academic_session';
        }

        $studentProfile = DB::table('students')
            ->select($studentColumns)
            ->where('id', $studentId)
            ->first();

        $totalOffenses = DB::table('offenses')
            ->where('student_id', $studentId)
            ->count();
        $unpaidOffenses = DB::table('offenses')
            ->where('student_id', $studentId)
            ->where('status', 'unpaid')
            ->count();
        $activeScholarships = DB::table('scholarships')
            ->where('student_id', $studentId)
            ->where('status', 'confirmed')
            ->whereIn('type', ['scholarship', 'welfare', 'sponsorship'])
            ->count();
        $pendingFineApplications = DB::table('fine_payment_applications')
            ->where('student_id', $studentId)
            ->where('status', 'pending')
            ->count();
        $latestSticker = DB::table('vehicle_sticker_applications')
            ->where('student_id', $studentId)
            ->orderByDesc('created_at')
            ->first();
        $latestScholarshipStatusForm = DB::table('student_scholarship_status_forms')
            ->where('student_id', $studentId)
            ->first();
        $currentMovement = Schema::hasTable('student_movements')
            ? DB::table('student_movements')
                ->join('movement_types', 'movement_types.id', '=', 'student_movements.movement_type_id')
                ->where('student_movements.student_id', $studentId)
                ->whereNull('student_movements.return_at')
                ->select('student_movements.checkout_at', 'student_movements.expected_return_at', 'movement_types.name as movement_type_name')
                ->orderByDesc('student_movements.checkout_at')
                ->first()
            : null;

        $stickerStatusLabel = $latestSticker->status ?? 'none';
        $needsScholarshipStatusSubmission = $latestScholarshipStatusForm === null;
        $showPaymentAlert = $unpaidOffenses > 0;
        $movementStatusLabel = $currentMovement ? 'Outside Campus' : 'Inside Campus';

        return view('dashboard.student', compact(
            'authUser',
            'studentProfile',
            'totalOffenses',
            'unpaidOffenses',
            'activeScholarships',
            'pendingFineApplications',
            'stickerStatusLabel',
            'needsScholarshipStatusSubmission',
            'showPaymentAlert',
            'currentMovement',
            'movementStatusLabel'
        ));
    }
}
