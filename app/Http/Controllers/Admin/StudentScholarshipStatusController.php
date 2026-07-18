<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentScholarshipStatusController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'has_scholarship' => ['nullable', Rule::in(['yes', 'no', 'all'])],
        ]);

        $query = DB::table('students')
            ->leftJoin('student_scholarship_status_forms as forms', 'forms.student_id', '=', 'students.id')
            ->select(
                'students.id as student_id',
                'students.full_name',
                'students.matric_no',
                'students.program',
                'forms.has_scholarship',
                'forms.sponsor_name',
                'forms.monthly_amount',
                'forms.notes',
                'forms.submitted_at'
            );

        if (!empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($sub) use ($q) {
                $sub->where('students.full_name', 'like', "%{$q}%")
                    ->orWhere('students.matric_no', 'like', "%{$q}%")
                    ->orWhere('students.program', 'like', "%{$q}%");
            });
        }

        $statusFilter = $filters['has_scholarship'] ?? 'all';
        if ($statusFilter !== 'all') {
            $query->where('forms.has_scholarship', $statusFilter);
        }

        $records = $query
            ->orderByDesc(DB::raw('forms.submitted_at IS NOT NULL'))
            ->orderBy('students.full_name')
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'total_students' => DB::table('students')->count(),
            'submitted' => DB::table('student_scholarship_status_forms')->count(),
            'has_scholarship' => DB::table('student_scholarship_status_forms')->where('has_scholarship', 'yes')->count(),
            'no_scholarship' => DB::table('student_scholarship_status_forms')->where('has_scholarship', 'no')->count(),
        ];

        return view('admin.student_scholarship_status.index', compact('records', 'filters', 'summary'));
    }
}
