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
            'type' => ['nullable', Rule::in(['scholarship', 'welfare', 'none', 'all', 'unsubmitted'])],
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
                'forms.application_type',
                'forms.has_scholarship',
                'forms.sponsor_name',
                'forms.monthly_amount',
                'forms.welfare_category',
                'forms.welfare_description',
                'forms.welfare_amount',
                'forms.guardian_name',
                'forms.guardian_phone',
                'forms.guardian_relationship',
                'forms.family_income',
                'forms.dependents_count',
                'forms.notes',
                'forms.submitted_at',
                'offer_docs.id as doc_id',
                'offer_docs.original_name as doc_name',
                'offer_docs.category as doc_category'
            );

        if (! empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($sub) use ($q) {
                $sub->where('students.full_name', 'like', "%{$q}%")
                    ->orWhere('students.matric_no', 'like', "%{$q}%")
                    ->orWhere('students.program', 'like', "%{$q}%")
                    ->orWhere('forms.sponsor_name', 'like', "%{$q}%")
                    ->orWhere('forms.welfare_category', 'like', "%{$q}%");
            });
        }

        $typeFilter = $filters['type'] ?? 'all';
        if ($typeFilter === 'scholarship') {
            $query->where(function ($sub) {
                $sub->where('forms.application_type', 'scholarship')
                    ->orWhere(fn ($q) => $q->whereNull('forms.application_type')->where('forms.has_scholarship', 'yes'));
            });
        } elseif ($typeFilter === 'welfare') {
            $query->where('forms.application_type', 'welfare');
        } elseif ($typeFilter === 'none') {
            $query->where(function ($sub) {
                $sub->where('forms.application_type', 'none')
                    ->orWhere(fn ($q) => $q->whereNull('forms.application_type')->where('forms.has_scholarship', 'no'));
            });
        } elseif ($typeFilter === 'unsubmitted') {
            $query->whereNull('forms.submitted_at');
        }

        $records = $query
            ->orderByDesc(DB::raw('forms.submitted_at IS NOT NULL'))
            ->orderBy('students.full_name')
            ->paginate(20)
            ->withQueryString();

        $summary = [
            'total_students' => DB::table('students')->count(),
            'submitted' => DB::table('student_scholarship_status_forms')->whereNotNull('submitted_at')->count(),
            'scholarship' => DB::table('student_scholarship_status_forms')->where(function ($q) {
                $q->where('application_type', 'scholarship')
                  ->orWhere(fn ($sub) => $sub->whereNull('application_type')->where('has_scholarship', 'yes'));
            })->count(),
            'welfare' => DB::table('student_scholarship_status_forms')->where('application_type', 'welfare')->count(),
            'none' => DB::table('student_scholarship_status_forms')->where(function ($q) {
                $q->where('application_type', 'none')
                  ->orWhere(fn ($sub) => $sub->whereNull('application_type')->where('has_scholarship', 'no'));
            })->count(),
        ];

        return view('admin.student_scholarship_status.index', compact('records', 'filters', 'summary'));
    }

    public function downloadOfferLetter(int $id): StreamedResponse
    {
        $document = DB::table('student_documents')
            ->where('id', $id)
            ->where('source_type', 'scholarship_status')
            ->first();
        abort_unless($document && $document->disk === 'student_documents', 404);
        abort_unless(Storage::disk('student_documents')->exists($document->path), 404);

        auditLog('scholarship.document_download', 'student_documents', $id, 'Scholarship/welfare document downloaded');

        return Storage::disk('student_documents')->download($document->path, $document->original_name, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}

