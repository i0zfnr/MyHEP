<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScholarshipController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->validateFilters($request);
        $records = $this->filteredQuery($filters)
            ->select(
                'scholarships.id',
                'scholarships.type',
                'scholarships.provider_name',
                'scholarships.amount',
                'scholarships.status',
                'scholarships.proof_file',
                'scholarships.created_at',
                'students.full_name as student_name',
                'students.matric_no'
            )
            ->orderByDesc('scholarships.created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.scholarships.index', compact('records', 'filters'));
    }

    public function export(Request $request)
    {
        $filters = $this->validateFilters($request);
        $rows = $this->filteredQuery($filters)
            ->select(
                'scholarships.id',
                'students.full_name as student_name',
                'students.matric_no',
                'scholarships.type',
                'scholarships.provider_name',
                'scholarships.amount',
                'scholarships.status',
                'scholarships.created_at'
            )
            ->orderByDesc('scholarships.created_at')
            ->get()
            ->map(fn ($record) => [
                $record->id,
                $record->student_name,
                $record->matric_no,
                $record->type,
                $record->provider_name ?? '',
                $record->amount !== null ? number_format((float) $record->amount, 2, '.', '') : '',
                $record->status,
                $record->created_at,
            ]);

        return downloadCsv(
            'scholarships_' . now()->format('Ymd_His') . '.csv',
            ['ID', 'Pelajar', 'No Matrik', 'Jenis', 'Penyedia', 'Jumlah (RM)', 'Status', 'Tarikh Rekod'],
            $rows
        );
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.scholarships.index')
            ->withErrors(['scholarship' => 'Add Record is unavailable for this module.']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateScholarship($request);

        DB::table('scholarships')->insert([
            'student_id' => $validated['student_id'],
            'type' => $validated['type'],
            'provider_name' => $validated['provider_name'] ?? null,
            'amount' => $validated['amount'] ?? null,
            'status' => $validated['status'],
            'proof_file' => $validated['proof_file'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.scholarships.index')
            ->with('success', __('Rekod scholarship berjaya ditambah.'));
    }

    public function edit(int $id): View|RedirectResponse
    {
        $record = DB::table('scholarships')->where('id', $id)->first();
        if (!$record) {
            return $this->notFoundRedirect();
        }

        $selectedStudent = DB::table('students')
            ->select('id', 'full_name', 'matric_no')
            ->where('id', (int) old('student_id', $record->student_id))
            ->first();

        return view('admin.scholarships.edit', compact('record', 'selectedStudent'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $record = DB::table('scholarships')->where('id', $id)->first();
        if (!$record) {
            return $this->notFoundRedirect();
        }

        $validated = $this->validateScholarship($request);

        DB::table('scholarships')
            ->where('id', $id)
            ->update([
                'student_id' => $validated['student_id'],
                'type' => $validated['type'],
                'provider_name' => $validated['provider_name'] ?? null,
                'amount' => $validated['amount'] ?? null,
                'status' => $validated['status'],
                'proof_file' => $validated['proof_file'] ?? null,
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.scholarships.index')
            ->with('success', __('Rekod scholarship berjaya dikemaskini.'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $deleted = DB::table('scholarships')->where('id', $id)->delete();
        if (!$deleted) {
            return $this->notFoundRedirect();
        }

        return redirect()->route('admin.scholarships.index')
            ->with('success', __('Rekod scholarship berjaya dipadam.'));
    }

    private function validateFilters(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'type' => ['nullable', Rule::in(['scholarship', 'welfare', 'sponsorship', 'none'])],
            'status' => ['nullable', Rule::in(['pending', 'confirmed', 'rejected'])],
        ]);
    }

    private function filteredQuery(array $filters)
    {
        $query = DB::table('scholarships')
            ->join('students', 'students.id', '=', 'scholarships.student_id');

        if (!empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($sub) use ($q) {
                $sub->where('students.full_name', 'like', "%{$q}%")
                    ->orWhere('students.matric_no', 'like', "%{$q}%")
                    ->orWhere('scholarships.provider_name', 'like', "%{$q}%");
            });
        }

        if (!empty($filters['type'])) {
            $query->where('scholarships.type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('scholarships.status', $filters['status']);
        }

        return $query;
    }

    private function validateScholarship(Request $request): array
    {
        return $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'type' => ['required', Rule::in(['scholarship', 'welfare', 'sponsorship', 'none'])],
            'provider_name' => ['nullable', 'string', 'max:150'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['pending', 'confirmed', 'rejected'])],
            'proof_file' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function notFoundRedirect(): RedirectResponse
    {
        return redirect()->route('admin.scholarships.index')
            ->withErrors(['scholarship' => 'Rekod scholarship tidak dijumpai.']);
    }
}
