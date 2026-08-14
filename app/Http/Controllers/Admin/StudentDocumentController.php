<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\StudentDocumentOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StudentDocumentController extends Controller
{
    public function index(Request $request): View
    {
        $adminRole = (string) session('auth_user.admin_role');
        $categories = StudentDocumentOptions::categoriesForAdmin($adminRole);
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'category' => ['nullable', Rule::in(array_keys($categories))],
            'status' => ['nullable', Rule::in(array_keys(StudentDocumentOptions::STATUSES))],
            'expiry' => ['nullable', Rule::in(['no_expiry', 'valid', 'expiring_30', 'expired'])],
        ]);
        $query = DB::table('student_documents')
            ->join('students', 'students.id', '=', 'student_documents.student_id')
            ->leftJoin('admins', 'admins.id', '=', 'student_documents.reviewed_by')
            ->select('student_documents.*', 'students.full_name as student_name', 'students.matric_no', 'admins.full_name as reviewer_name');
        $this->scopeVisibleDocuments($query, $adminRole);

        if (! empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($builder) use ($q): void {
                $builder->where('student_documents.title', 'like', "%{$q}%")
                    ->orWhere('student_documents.original_name', 'like', "%{$q}%")
                    ->orWhere('students.full_name', 'like', "%{$q}%")
                    ->orWhere('students.matric_no', 'like', "%{$q}%");
            });
        }
        if (! empty($filters['category'])) {
            $query->where('student_documents.category', $filters['category']);
        }
        if (! empty($filters['status'])) {
            $query->where('student_documents.status', $filters['status']);
        }
        match ($filters['expiry'] ?? null) {
            'no_expiry' => $query->whereNull('student_documents.expiry_date'),
            'valid' => $query->whereDate('student_documents.expiry_date', '>', now()->addDays(30)),
            'expiring_30' => $query->whereBetween('student_documents.expiry_date', [today(), today()->addDays(30)]),
            'expired' => $query->whereDate('student_documents.expiry_date', '<', today()),
            default => null,
        };

        $documents = $query
            ->orderByRaw("CASE WHEN student_documents.status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('student_documents.created_at')
            ->paginate(20)
            ->withQueryString();
        $countsQuery = DB::table('student_documents');
        $this->scopeVisibleDocuments($countsQuery, $adminRole);
        $counts = $countsQuery
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved")
            ->selectRaw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected")
            ->first();

        return view('admin.documents.index', [
            'documents' => $documents,
            'counts' => $counts,
            'filters' => $filters,
            'categories' => $categories,
            'limitedToInsurancePayments' => $adminRole === 'discipline_admin',
        ]);
    }

    public function download(int $id): StreamedResponse
    {
        $document = DB::table('student_documents')->where('id', $id)->first();
        abort_unless($document && $this->canAccessCategory((string) $document->category), 404);
        abort_unless($document && $document->disk === 'student_documents', 404);
        abort_unless(Storage::disk('student_documents')->exists($document->path), 404);

        auditLog('admin.student_documents.download', 'student_documents', $id, 'Administrator downloaded student document');

        return Storage::disk('student_documents')->download($document->path, $document->original_name, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function review(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'review_note' => ['nullable', 'string', 'max:1000', 'required_if:status,rejected'],
        ]);

        $document = DB::transaction(function () use ($id, $validated) {
            $document = DB::table('student_documents')->where('id', $id)->lockForUpdate()->first();
            if (! $document || ! $this->canAccessCategory((string) $document->category) || $document->status !== 'pending') {
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
            return redirect()->route('admin.documents.index')->withErrors([
                'document' => __('This document has already been reviewed or no longer exists.'),
            ]);
        }

        auditLog('student_documents.review', 'student_documents', $id, 'Document review: '.$validated['status']);
        myhepSendPushNotification('student', (int) $document->student_id, [
            'category' => 'documents',
            'title' => 'Document reviewed',
            'body' => 'One of your documents was '.$validated['status'].'.',
            'url' => route('student.documents.index'),
            'tag' => 'student-document-'.$id,
        ]);

        return redirect()->route('admin.documents.index')->with('success', __('Document review saved.'));
    }

    private function scopeVisibleDocuments($query, string $adminRole): void
    {
        if ($adminRole === 'discipline_admin') {
            $query->where('student_documents.category', 'insurance_payment');
        }
    }

    private function canAccessCategory(string $category): bool
    {
        return StudentDocumentOptions::adminCanAccessCategory(
            (string) session('auth_user.admin_role'),
            $category,
        );
    }
}
