<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentInsuranceController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'semester' => ['nullable', Rule::in(['all', '3', '5'])],
            'status' => ['nullable', Rule::in(['all', 'approved', 'pending', 'rejected', 'unpaid'])],
            'program' => ['nullable', 'string', 'max:50'],
        ]);

        $query = $this->buildBaseQuery();

        if (! empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($builder) use ($q): void {
                $builder->where('students.full_name', 'like', "%{$q}%")
                    ->orWhere('students.matric_no', 'like', "%{$q}%")
                    ->orWhere('students.ic_no', 'like', "%{$q}%")
                    ->orWhere('students.class_name', 'like', "%{$q}%")
                    ->orWhere('students.program', 'like', "%{$q}%");
            });
        }

        if (! empty($filters['semester']) && $filters['semester'] !== 'all') {
            $query->where('students.semester', $filters['semester']);
        }

        if (! empty($filters['program'])) {
            $query->where('students.program', $filters['program']);
        }

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'unpaid') {
                $query->whereNull('doc.id');
            } else {
                $query->where('doc.status', $filters['status']);
            }
        }

        $students = $query
            ->orderByRaw("
                CASE 
                    WHEN doc.status = 'pending' THEN 0
                    WHEN doc.id IS NULL THEN 1
                    WHEN doc.status = 'rejected' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('students.program')
            ->orderBy('students.semester')
            ->orderBy('students.full_name')
            ->paginate(20)
            ->withQueryString();

        $stats = $this->calculateStats();
        $programs = DB::table('students')
            ->whereIn('semester', [3, 5, '3', '5'])
            ->whereNotNull('program')
            ->where('program', '<>', '')
            ->distinct()
            ->pluck('program')
            ->sort()
            ->values();

        return view('admin.insurance.index', [
            'students' => $students,
            'stats' => $stats,
            'filters' => $filters,
            'programs' => $programs,
        ]);
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'semester' => ['nullable', Rule::in(['all', '3', '5'])],
            'status' => ['nullable', Rule::in(['all', 'approved', 'pending', 'rejected', 'unpaid'])],
            'program' => ['nullable', 'string', 'max:50'],
        ]);

        $query = $this->buildBaseQuery();

        if (! empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($builder) use ($q): void {
                $builder->where('students.full_name', 'like', "%{$q}%")
                    ->orWhere('students.matric_no', 'like', "%{$q}%")
                    ->orWhere('students.ic_no', 'like', "%{$q}%")
                    ->orWhere('students.class_name', 'like', "%{$q}%")
                    ->orWhere('students.program', 'like', "%{$q}%");
            });
        }

        if (! empty($filters['semester']) && $filters['semester'] !== 'all') {
            $query->where('students.semester', $filters['semester']);
        }

        if (! empty($filters['program'])) {
            $query->where('students.program', $filters['program']);
        }

        if (! empty($filters['status']) && $filters['status'] !== 'all') {
            if ($filters['status'] === 'unpaid') {
                $query->whereNull('doc.id');
            } else {
                $query->where('doc.status', $filters['status']);
            }
        }

        $records = $query
            ->orderBy('students.program')
            ->orderBy('students.semester')
            ->orderBy('students.full_name')
            ->get();

        $headers = [
            __('No.'),
            __('Full Name'),
            __('Matric No.'),
            __('IC No.'),
            __('Program'),
            __('Class'),
            __('Semester'),
            __('Insurance Status'),
            __('Receipt Filename'),
            __('Upload Date'),
            __('Reviewed By'),
            __('Reviewed At'),
            __('Review Note'),
        ];

        $rows = [];
        foreach ($records as $index => $row) {
            $statusLabel = match ($row->doc_status) {
                'approved' => __('Paid & Approved'),
                'pending' => __('Pending Review'),
                'rejected' => __('Rejected'),
                default => __('Unpaid / Missing Receipt'),
            };

            $rows[] = [
                $index + 1,
                $row->full_name,
                $row->matric_no,
                '="'.$row->ic_no.'"',
                $row->program ?: '-',
                $row->class_name ?: '-',
                $row->semester,
                $statusLabel,
                $row->doc_original_name ?: '-',
                $row->doc_created_at ? substr((string) $row->doc_created_at, 0, 16) : '-',
                $row->reviewer_name ?: '-',
                $row->doc_reviewed_at ? substr((string) $row->doc_reviewed_at, 0, 16) : '-',
                $row->doc_review_note ?: '-',
            ];
        }

        auditLog('admin.insurance.export', 'student_documents', null, 'Exported student insurance payment records (Sem 3 & 5)');

        $filename = 'Student_Insurance_Records_Sem3_Sem5_'.now()->format('Ymd_His').'.csv';

        return downloadCsv($filename, $headers, $rows);
    }

    public function downloadReceipt(int $id): StreamedResponse
    {
        $document = DB::table('student_documents')
            ->where('id', $id)
            ->where('category', 'insurance_payment')
            ->first();

        abort_unless($document && $document->disk === 'student_documents', 404);
        abort_unless(Storage::disk('student_documents')->exists($document->path), 404);

        auditLog('admin.insurance.download_receipt', 'student_documents', $id, 'Downloaded student insurance receipt');

        return Storage::disk('student_documents')->download($document->path, $document->original_name, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function reviewReceipt(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'review_note' => ['nullable', 'string', 'max:1000', 'required_if:status,rejected'],
        ]);

        $document = DB::transaction(function () use ($id, $validated) {
            $document = DB::table('student_documents')
                ->where('id', $id)
                ->where('category', 'insurance_payment')
                ->lockForUpdate()
                ->first();

            if (! $document || $document->status !== 'pending') {
                return null;
            }

            DB::table('student_documents')->where('id', $id)->update([
                'status' => $validated['status'],
                'review_note' => filled($validated['review_note'] ?? null) ? trim($validated['review_note']) : null,
                'reviewed_by' => (int) session('auth_user.id'),
                'reviewed_at' => now(),
                'updated_at' => now(),
            ]);

            return $document;
        });

        if (! $document) {
            return back()->withErrors([
                'document' => __('This document has already been reviewed or no longer exists.'),
            ]);
        }

        auditLog('student_documents.review', 'student_documents', $id, 'Insurance receipt review: '.$validated['status']);

        myhepSendPushNotification('student', (int) $document->student_id, [
            'category' => 'documents',
            'title' => __('Insurance Payment Status Updated'),
            'body' => $validated['status'] === 'approved'
                ? __('Your insurance payment receipt has been verified and approved.')
                : __('Your insurance payment receipt was rejected: :reason', ['reason' => $validated['review_note'] ?: __('Please re-upload a valid receipt.')]),
            'url' => route('student.documents.index'),
            'tag' => 'insurance-document-'.$id,
        ]);

        return back()->with('success', __('Insurance receipt status updated successfully.'));
    }

    public function broadcastNotice(Request $request): RedirectResponse
    {
        $studentsToNotify = DB::table('students')
            ->leftJoin('student_documents as doc', function ($join) {
                $join->on('doc.student_id', '=', 'students.id')
                    ->where('doc.category', '=', 'insurance_payment');
            })
            ->whereIn('students.semester', [3, 5, '3', '5'])
            ->where(function ($query) {
                $query->whereNull('doc.id')
                    ->orWhere('doc.status', 'rejected')
                    ->orWhere('doc.status', 'pending');
            })
            ->select('students.id', 'students.full_name')
            ->distinct()
            ->get();

        $count = 0;
        foreach ($studentsToNotify as $student) {
            myhepSendPushNotification('student', (int) $student->id, [
                'category' => 'documents',
                'title' => __('Peringatan Bayaran Insurans (Sem 3 & 5)'),
                'body' => __('Semua pelajar Semester 3 & 5 diwajibkan memuat naik resit bayaran insurans ke Pusat Dokumen segera.'),
                'url' => route('student.documents.index'),
                'tag' => 'insurance-compulsory-notice-'.now()->format('Ymd'),
            ]);
            $count++;
        }

        auditLog('admin.insurance.broadcast_notice', 'student_documents', null, "Broadcasted compulsory insurance notice to {$count} Semester 3 & 5 students");

        return back()->with('success', __(':count pelajar Semester 3 & 5 telah dihantar notifikasi peringatan bayaran insurans.', ['count' => $count]));
    }

    private function buildBaseQuery()
    {
        // Subquery for the latest insurance payment document per student
        $latestDocSubquery = DB::table('student_documents')
            ->select('student_documents.*')
            ->where('category', 'insurance_payment')
            ->whereIn('id', function ($query) {
                $query->select(DB::raw('MAX(id)'))
                    ->from('student_documents')
                    ->where('category', 'insurance_payment')
                    ->groupBy('student_id');
            });

        return DB::table('students')
            ->whereIn('students.semester', [3, 5, '3', '5'])
            ->leftJoinSub($latestDocSubquery, 'doc', function ($join) {
                $join->on('doc.student_id', '=', 'students.id');
            })
            ->leftJoin('admins', 'admins.id', '=', 'doc.reviewed_by')
            ->select(
                'students.id as student_id',
                'students.full_name',
                'students.matric_no',
                'students.ic_no',
                'students.program',
                'students.class_name',
                'students.semester',
                'students.phone',
                'doc.id as doc_id',
                'doc.title as doc_title',
                'doc.status as doc_status',
                'doc.original_name as doc_original_name',
                'doc.size_bytes as doc_size_bytes',
                'doc.created_at as doc_created_at',
                'doc.reviewed_at as doc_reviewed_at',
                'doc.review_note as doc_review_note',
                'admins.full_name as reviewer_name'
            );
    }

    private function calculateStats(): object
    {
        $base = $this->buildBaseQuery()->get();

        $total = $base->count();
        $approved = $base->where('doc_status', 'approved')->count();
        $pending = $base->where('doc_status', 'pending')->count();
        $rejected = $base->where('doc_status', 'rejected')->count();
        $unpaid = $base->whereNull('doc_status')->count();

        $sem3Total = $base->whereIn('semester', [3, '3'])->count();
        $sem3Approved = $base->whereIn('semester', [3, '3'])->where('doc_status', 'approved')->count();

        $sem5Total = $base->whereIn('semester', [5, '5'])->count();
        $sem5Approved = $base->whereIn('semester', [5, '5'])->where('doc_status', 'approved')->count();

        $rate = $total > 0 ? round(($approved / $total) * 100, 1) : 0;

        return (object) [
            'total' => $total,
            'approved' => $approved,
            'pending' => $pending,
            'rejected' => $rejected,
            'unpaid' => $unpaid,
            'rate' => $rate,
            'sem3_total' => $sem3Total,
            'sem3_approved' => $sem3Approved,
            'sem5_total' => $sem5Total,
            'sem5_approved' => $sem5Approved,
        ];
    }
}
