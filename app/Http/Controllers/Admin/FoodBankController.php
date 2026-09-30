<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class FoodBankController extends Controller
{
    public function index(Request $request): View
    {
        if (! adminCan('foodbank')) {
            abort(403, __('Anda tidak mempunyai akses ke modul Food Bank.'));
        }

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'program' => ['nullable', 'string', 'max:50'],
            'semester' => ['nullable', 'integer', 'min:1', 'max:8'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'month' => ['nullable', 'date_format:Y-m'],
            'per_page' => ['nullable', Rule::in(['15', '25', '50', '100'])],
        ]);

        $query = DB::table('student_food_bank_claims')
            ->leftJoin('students', 'students.id', '=', 'student_food_bank_claims.student_id')
            ->select(
                'student_food_bank_claims.id',
                'student_food_bank_claims.student_id',
                'student_food_bank_claims.claimed_at',
                'student_food_bank_claims.academic_session',
                'student_food_bank_claims.semester',
                'student_food_bank_claims.meal_type',
                'student_food_bank_claims.notes',
                'student_food_bank_claims.location',
                'student_food_bank_claims.item_count',
                'student_food_bank_claims.is_b40',
                DB::raw('COALESCE(student_food_bank_claims.student_name, students.full_name) as student_name'),
                DB::raw('COALESCE(student_food_bank_claims.matric_no, students.matric_no) as matric_no'),
                'students.ic_no',
                'students.program',
                'students.phone',
                'students.family_income',
                'students.photo'
            );

        if (! empty($filters['q'])) {
            $search = '%' . trim($filters['q']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('students.full_name', 'like', $search)
                    ->orWhere('students.matric_no', 'like', $search)
                    ->orWhere('student_food_bank_claims.student_name', 'like', $search)
                    ->orWhere('student_food_bank_claims.matric_no', 'like', $search)
                    ->orWhere('students.ic_no', 'like', $search);
            });
        }

        if (! empty($filters['program'])) {
            $query->where('students.program', $filters['program']);
        }

        if (! empty($filters['semester'])) {
            $query->where('student_food_bank_claims.semester', (int) $filters['semester']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('student_food_bank_claims.claimed_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('student_food_bank_claims.claimed_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['month'])) {
            $parts = explode('-', $filters['month']);
            $query->whereYear('student_food_bank_claims.claimed_at', (int) $parts[0])
                ->whereMonth('student_food_bank_claims.claimed_at', (int) $parts[1]);
        }

        $perPage = (int) ($filters['per_page'] ?? 25);
        $records = $query->orderByDesc('student_food_bank_claims.claimed_at')
            ->paginate($perPage)
            ->withQueryString();

        // Overall KPIs (unfiltered for master analytics)
        $now = now();
        $totalClaims = DB::table('student_food_bank_claims')->count();
        $claimsToday = DB::table('student_food_bank_claims')->whereDate('claimed_at', $now->toDateString())->count();
        $claimsThisMonth = DB::table('student_food_bank_claims')
            ->whereYear('claimed_at', $now->year)
            ->whereMonth('claimed_at', $now->month)
            ->count();
        $uniqueStudents = $this->uniqueStudentCount();

        // Distribution stats
        $programStats = DB::table('student_food_bank_claims')
            ->join('students', 'students.id', '=', 'student_food_bank_claims.student_id')
            ->select('students.program', DB::raw('count(*) as total'))
            ->whereNotNull('students.program')
            ->where('students.program', '!=', '')
            ->groupBy('students.program')
            ->orderByDesc('total')
            ->take(6)
            ->get();

        $availablePrograms = DB::table('students')
            ->whereNotNull('program')
            ->where('program', '!=', '')
            ->distinct()
            ->orderBy('program')
            ->pluck('program');

        $staticQrUrl = route('student.foodbank.claim');

        return view('admin.foodbank.index', compact(
            'records',
            'filters',
            'totalClaims',
            'claimsToday',
            'claimsThisMonth',
            'uniqueStudents',
            'programStats',
            'availablePrograms',
            'staticQrUrl'
        ));
    }

    public function export(Request $request)
    {
        if (! adminCan('foodbank')) {
            abort(403, __('Anda tidak mempunyai akses ke modul Food Bank.'));
        }

        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'program' => ['nullable', 'string', 'max:50'],
            'semester' => ['nullable', 'integer', 'min:1', 'max:8'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'month' => ['nullable', 'date_format:Y-m'],
        ]);

        $query = DB::table('student_food_bank_claims')
            ->leftJoin('students', 'students.id', '=', 'student_food_bank_claims.student_id')
            ->select(
            'student_food_bank_claims.claimed_at',
            'student_food_bank_claims.item_count',
            'student_food_bank_claims.is_b40',
            DB::raw('COALESCE(student_food_bank_claims.student_name, students.full_name) as student_name'),
            DB::raw('COALESCE(student_food_bank_claims.matric_no, students.matric_no) as matric_no')
        );

        if (! empty($filters['q'])) {
            $search = '%' . trim($filters['q']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('students.full_name', 'like', $search)
                    ->orWhere('students.matric_no', 'like', $search)
                    ->orWhere('student_food_bank_claims.student_name', 'like', $search)
                    ->orWhere('student_food_bank_claims.matric_no', 'like', $search)
                    ->orWhere('students.ic_no', 'like', $search);
            });
        }

        if (! empty($filters['program'])) {
            $query->where('students.program', $filters['program']);
        }

        if (! empty($filters['semester'])) {
            $query->where('student_food_bank_claims.semester', (int) $filters['semester']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('student_food_bank_claims.claimed_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('student_food_bank_claims.claimed_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['month'])) {
            $parts = explode('-', $filters['month']);
            $query->whereYear('student_food_bank_claims.claimed_at', (int) $parts[0])
                ->whereMonth('student_food_bank_claims.claimed_at', (int) $parts[1]);
        }

        $records = $query->orderBy('student_food_bank_claims.claimed_at')->get();
        $filePath = app(\App\Services\FoodBankReportTemplateExporter::class)->generate($records);
        $filename = 'Laporan_HQ_FoodBank_PoliBesut_' . now()->format('Ymd_His') . '.xlsx';

        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function qr(): View
    {
        if (! adminCan('foodbank')) {
            abort(403, __('Anda tidak mempunyai akses ke modul Food Bank.'));
        }

        $staticQrUrl = route('student.foodbank.claim');

        return view('admin.foodbank.qr', [
            'staticQrUrl' => $staticQrUrl,
            'totalClaims' => DB::table('student_food_bank_claims')->count(),
            'uniqueStudents' => $this->uniqueStudentCount(),
        ]);
    }

    public function destroy(int $id): RedirectResponse
    {
        if (! adminCan('foodbank')) {
            abort(403, __('Anda tidak mempunyai akses ke modul Food Bank.'));
        }

        $record = DB::table('student_food_bank_claims')->where('id', $id)->first();
        if ($record) {
            DB::table('student_food_bank_claims')->where('id', $id)->delete();
            auditLog('foodbank_claim.delete', 'student_food_bank_claims', $id, 'Deleted food bank claim record');
        }

        return redirect()->route('admin.foodbank.index')->with('success', __('Rekod Food Bank berjaya dipadam.'));
    }

    private function uniqueStudentCount(): int
    {
        return (int) DB::table('student_food_bank_claims')
            ->leftJoin('students', 'students.id', '=', 'student_food_bank_claims.student_id')
            ->selectRaw('COUNT(DISTINCT COALESCE(student_food_bank_claims.matric_no, students.matric_no)) as total')
            ->first()->total;
    }
}
