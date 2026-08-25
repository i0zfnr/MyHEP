<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FoodBankController extends Controller
{
    public function index(): View
    {
        $studentId = (int) session('auth_user.id');
        $student = DB::table('students')->where('id', $studentId)->first();

        $claims = DB::table('student_food_bank_claims')
            ->where('student_id', $studentId)
            ->orderByDesc('claimed_at')
            ->paginate(10);

        $totalClaims = DB::table('student_food_bank_claims')
            ->where('student_id', $studentId)
            ->count();

        $claimsThisMonth = DB::table('student_food_bank_claims')
            ->where('student_id', $studentId)
            ->whereYear('claimed_at', now()->year)
            ->whereMonth('claimed_at', now()->month)
            ->count();

        $lastClaim = DB::table('student_food_bank_claims')
            ->where('student_id', $studentId)
            ->orderByDesc('claimed_at')
            ->first();

        $hasClaimedToday = DB::table('student_food_bank_claims')
            ->where('student_id', $studentId)
            ->whereDate('claimed_at', now()->toDateString())
            ->exists();

        return view('student.foodbank.index', compact(
            'student',
            'claims',
            'totalClaims',
            'claimsThisMonth',
            'lastClaim',
            'hasClaimedToday'
        ));
    }

    public function claimView(Request $request): View|RedirectResponse
    {
        $studentId = (int) session('auth_user.id');
        if (! $studentId) {
            return redirect()->route('login')->with('warning', __('Sila log masuk terlebih dahulu untuk menebus Food Bank.'));
        }

        $student = DB::table('students')->where('id', $studentId)->first();
        if (! $student) {
            abort(404, __('Maklumat pelajar tidak dijumpai.'));
        }

        // Check if student claimed within the last 5 minutes (avoid duplicate instant double-scan)
        $recentClaim = DB::table('student_food_bank_claims')
            ->where('student_id', $studentId)
            ->where('claimed_at', '>=', now()->subMinutes(5))
            ->orderByDesc('claimed_at')
            ->first();

        if ($recentClaim) {
            $claim = $recentClaim;
            $isNew = false;
        } else {
            $claimId = DB::table('student_food_bank_claims')->insertGetId([
                'student_id' => $student->id,
                'claimed_at' => now(),
                'academic_session' => $student->academic_session,
                'semester' => $student->semester,
                'meal_type' => 'makanan_percuma',
                'notes' => 'Penebusan melalui Imbasan QR Rasmi Food Bank',
                'location' => 'Food Bank Siswa Politeknik Besut',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $claim = DB::table('student_food_bank_claims')->where('id', $claimId)->first();
            $isNew = true;

            auditLog('foodbank.claim', 'student_food_bank_claims', $claimId, 'Student claimed food from Food Bank');
        }

        $totalStudentClaims = DB::table('student_food_bank_claims')
            ->where('student_id', $studentId)
            ->count();

        return view('student.foodbank.claim', compact(
            'student',
            'claim',
            'isNew',
            'totalStudentClaims'
        ));
    }

    public function quickScan(Request $request): JsonResponse
    {
        $studentId = (int) session('auth_user.id');
        if (! $studentId) {
            return response()->json([
                'success' => false,
                'message' => __('Sesi tamat. Sila log masuk semula.'),
            ], 401);
        }

        $student = DB::table('students')->where('id', $studentId)->first();
        if (! $student) {
            return response()->json([
                'success' => false,
                'message' => __('Profil pelajar tidak dijumpai.'),
            ], 404);
        }

        // Avoid double-submit within 3 minutes
        $recentClaim = DB::table('student_food_bank_claims')
            ->where('student_id', $studentId)
            ->where('claimed_at', '>=', now()->subMinutes(3))
            ->first();

        if (! $recentClaim) {
            $claimId = DB::table('student_food_bank_claims')->insertGetId([
                'student_id' => $student->id,
                'claimed_at' => now(),
                'academic_session' => $student->academic_session,
                'semester' => $student->semester,
                'meal_type' => 'makanan_percuma',
                'notes' => 'Imbasan QR dalam aplikasi MyHEP',
                'location' => 'Food Bank Siswa Politeknik Besut',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            auditLog('foodbank.quick_scan', 'student_food_bank_claims', $claimId, 'Student quick-scanned food bank QR');
        }

        $totalClaims = DB::table('student_food_bank_claims')->where('student_id', $studentId)->count();

        return response()->json([
            'success' => true,
            'already_claimed' => (bool) $recentClaim,
            'message' => $recentClaim ? __('Penebusan anda telah direkodkan sebentar tadi.') : __('Makanan Food Bank Berjaya Ditebus!'),
            'student' => [
                'full_name' => $student->full_name,
                'matric_no' => $student->matric_no,
                'program' => $student->program ?: 'Politeknik Besut',
                'semester' => $student->semester ? 'Semester ' . $student->semester : '',
            ],
            'claim' => [
                'claimed_at' => now()->format('d/m/Y h:i A'),
                'total_claims' => $totalClaims,
                'location' => 'Food Bank Siswa Politeknik Besut',
            ],
        ]);
    }
}
