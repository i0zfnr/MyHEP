<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
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

    public function claimView(): View
    {
        return view('student.foodbank.claim');
    }

    public function storeClaim(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_name' => ['required', 'string', 'max:255'],
            'matric_no' => ['required', 'string', 'max:50'],
            'item_count' => ['required', 'integer', 'min:1', 'max:999'],
            'is_b40' => ['required', 'boolean'],
        ]);

        $name = trim(preg_replace('/\s+/u', ' ', $data['student_name']));
        $matricNo = strtoupper(trim($data['matric_no']));
        if ($name === '' || $matricNo === '') {
            return back()->withInput()->withErrors(['student_name' => __('Sila lengkapkan nama dan nombor matrik.')]);
        }

        // Link matching student accounts for their own history, but allow public submissions.
        $student = DB::table('students')->where('matric_no', $matricNo)->first();
        $studentId = $student && mb_strtolower(trim($student->full_name)) === mb_strtolower($name)
            ? $student->id
            : null;
        $linkedStudent = $studentId ? $student : null;

        $recentClaim = DB::table('student_food_bank_claims')
            ->where('matric_no', $matricNo)
            ->where('claimed_at', '>=', now()->subMinutes(3))
            ->exists();
        if ($recentClaim) {
            return back()->withInput()->withErrors(['matric_no' => __('Penebusan untuk nombor matrik ini baru sahaja direkodkan. Sila semak dengan petugas Food Bank.')]);
        }

        $claimedAt = now();
        $claimId = DB::table('student_food_bank_claims')->insertGetId([
            'student_id' => $studentId,
            'student_name' => $name,
            'matric_no' => $matricNo,
            'item_count' => (int) $data['item_count'],
            'is_b40' => (bool) $data['is_b40'],
            'claimed_at' => $claimedAt,
            'academic_session' => $linkedStudent->academic_session ?? null,
            'semester' => $linkedStudent->semester ?? null,
            'meal_type' => 'makanan_percuma',
            'notes' => 'Borang awam QR Food Bank',
            'location' => 'Food Bank Siswa Politeknik Besut',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'created_at' => $claimedAt,
            'updated_at' => $claimedAt,
        ]);

        auditLog('foodbank.claim', 'student_food_bank_claims', $claimId, 'Public Food Bank form submitted');

        return redirect()->route('student.foodbank.claim')->with('foodbank_receipt', [
            'student_name' => $name,
            'matric_no' => $matricNo,
            'item_count' => (int) $data['item_count'],
            'is_b40' => (bool) $data['is_b40'],
            'claimed_at' => $claimedAt->format('d/m/Y, h:i A'),
        ]);
    }
}
