<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
            ->leftJoin('student_documents as offer_docs', function ($join): void {
                $join->on('offer_docs.source_id', '=', 'forms.id')
                    ->where('offer_docs.source_type', '=', 'scholarship_status');
            })
            ->select(
                'students.id as student_id',
                'students.full_name',
                'students.matric_no',
                'students.program',
                'forms.has_scholarship',
                'forms.sponsor_name',
                'forms.monthly_amount',
                'forms.notes',
                'forms.submitted_at',
                'offer_docs.id as offer_letter_id',
                'offer_docs.original_name as offer_letter_name'
            );

        if (! empty($filters['q'])) {
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

    public function downloadOfferLetter(int $id): StreamedResponse
    {
        $document = DB::table('student_documents')
            ->where('id', $id)
            ->where('source_type', 'scholarship_status')
            ->where('category', 'scholarship')
            ->first();
        abort_unless($document && $document->disk === 'student_documents', 404);
        abort_unless(Storage::disk('student_documents')->exists($document->path), 404);

        auditLog('scholarship.offer_letter_download', 'student_documents', $id, 'Scholarship offer letter downloaded');

        return Storage::disk('student_documents')->download($document->path, $document->original_name, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
