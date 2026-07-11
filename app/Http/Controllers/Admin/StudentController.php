<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $studentStats = [
            'total' => DB::table('students')->count(),
            'default_ic' => DB::table('students')->whereNull('password')->count(),
            'custom_password' => DB::table('students')->whereNotNull('password')->count(),
        ];

        $filters = $this->validateFilters($request);
        $students = $this->filteredStudentsQuery($filters)
            ->select('id', 'full_name', 'matric_no', 'ic_no', 'program', 'phone', 'created_at')
            ->selectRaw('CASE WHEN password IS NULL THEN 0 ELSE 1 END as has_custom_password')
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.students.index', compact('students', 'filters', 'studentStats'));
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        if ($q === '') {
            return response()->json(['data' => []]);
        }

        $students = DB::table('students')
            ->select('id', 'full_name', 'matric_no')
            ->where(function ($sub) use ($q) {
                $sub->where('full_name', 'like', "%{$q}%")
                    ->orWhere('matric_no', 'like', "%{$q}%");
            })
            ->orderBy('full_name')
            ->limit(20)
            ->get();

        return response()->json(['data' => $students]);
    }

    public function export(Request $request)
    {
        $filters = $this->validateFilters($request);
        $rows = $this->filteredStudentsQuery($filters)
            ->select('id', 'full_name', 'matric_no', 'ic_no', 'program', 'phone', 'created_at')
            ->selectRaw('CASE WHEN password IS NULL THEN "default_ic" ELSE "custom_password" END as password_status')
            ->orderBy('full_name')
            ->get()
            ->map(fn ($student) => [
                $student->id,
                $student->full_name,
                $student->matric_no,
                $student->ic_no,
                $student->program,
                $student->phone ?? '',
                $student->password_status,
                $student->created_at,
            ]);

        return downloadCsv(
            'students_' . now()->format('Ymd_His') . '.csv',
            ['ID', 'Nama', 'No Matrik', 'No IC', 'Program', 'Telefon', 'Status Kata Laluan', 'Tarikh Daftar'],
            $rows
        );
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateStudent($request);

        DB::table('students')->insert([
            'full_name' => $validated['full_name'],
            'matric_no' => $validated['matric_no'],
            'ic_no' => $validated['ic_no'],
            'email' => $validated['email'] ?? null,
            'program' => $validated['program'],
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'photo' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.students.index')
            ->with('success', __('Pelajar berjaya ditambah.'));
    }

    public function edit(int $id)
    {
        $student = DB::table('students')->where('id', $id)->first();
        if (!$student) {
            return $this->studentNotFoundRedirect();
        }

        return view('admin.students.edit', compact('student'));
    }

    public function update(Request $request, int $id)
    {
        $student = DB::table('students')->where('id', $id)->first();
        if (!$student) {
            return $this->studentNotFoundRedirect();
        }

        $validated = $this->validateStudent($request, $id);

        DB::table('students')
            ->where('id', $id)
            ->update([
                'full_name' => $validated['full_name'],
                'matric_no' => $validated['matric_no'],
                'ic_no' => $validated['ic_no'],
                'email' => $validated['email'] ?? null,
                'program' => $validated['program'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.students.index')
            ->with('success', __('Maklumat pelajar berjaya dikemaskini.'));
    }

    public function destroy(int $id)
    {
        $deleted = DB::table('students')->where('id', $id)->delete();
        if (!$deleted) {
            return $this->studentNotFoundRedirect();
        }
        auditLog('students.delete', 'students', $id, 'Padam rekod pelajar');

        return redirect()->route('admin.students.index')
            ->with('success', __('Rekod pelajar berjaya dipadam.'));
    }

    public function resetPassword(int $id)
    {
        $student = DB::table('students')->where('id', $id)->first();
        if (!$student) {
            return $this->studentNotFoundRedirect();
        }

        DB::table('students')
            ->where('id', $id)
            ->update([
                // Null password means fallback login to IC is enabled.
                'password' => null,
                'updated_at' => now(),
            ]);
        auditLog('students.reset_password', 'students', $id, 'Reset kata laluan pelajar kepada IC');

        return redirect()->route('admin.students.index')
            ->with('success', __('Kata laluan pelajar telah direset kepada No. IC.'));
    }

    private function validateFilters(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'program' => ['nullable', 'string', 'max:100'],
            'password_status' => ['nullable', Rule::in(['default', 'custom'])],
        ]);
    }

    private function filteredStudentsQuery(array $filters)
    {
        $query = DB::table('students');

        if (!empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($sub) use ($q) {
                $sub->where('full_name', 'like', "%{$q}%")
                    ->orWhere('matric_no', 'like', "%{$q}%")
                    ->orWhere('ic_no', 'like', "%{$q}%");
            });
        }

        if (!empty($filters['program'])) {
            $query->where('program', 'like', '%' . trim($filters['program']) . '%');
        }

        if (!empty($filters['password_status'])) {
            if ($filters['password_status'] === 'custom') {
                $query->whereNotNull('password');
            } else {
                $query->whereNull('password');
            }
        }

        return $query;
    }

    private function validateStudent(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'matric_no' => ['required', 'string', 'max:20', Rule::unique('students', 'matric_no')->ignore($id)],
            'ic_no' => ['required', 'string', 'max:20', Rule::unique('students', 'ic_no')->ignore($id)],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('students', 'email')->ignore($id)],
            'program' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ]);
    }

    private function studentNotFoundRedirect()
    {
        return redirect()->route('admin.students.index')
            ->withErrors(['student' => __('Rekod pelajar tidak dijumpai.')]);
    }
}
