<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Support\StudentDocumentOptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function index(): View
    {
        $studentId = (int) session('auth_user.id');
        $student = DB::table('students')->where('id', $studentId)->first();
        $isSem3Or5 = in_array((string) ($student->semester ?? ''), ['3', '5'], true);
        $insuranceDoc = $isSem3Or5
            ? DB::table('student_documents')
                ->where('student_id', $studentId)
                ->where('category', 'insurance_payment')
                ->latest('created_at')
                ->first()
            : null;

        $documents = DB::table('student_documents')
            ->leftJoin('admins', 'admins.id', '=', 'student_documents.reviewed_by')
            ->where('student_documents.student_id', $studentId)
            ->select('student_documents.*', 'admins.full_name as reviewer_name')
            ->orderByDesc('student_documents.created_at')
            ->paginate(15);
        $counts = DB::table('student_documents')
            ->where('student_id', $studentId)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved")
            ->selectRaw("SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected")
            ->first();

        return view('student.documents.index', [
            'documents' => $documents,
            'counts' => $counts,
            'categories' => StudentDocumentOptions::CATEGORIES,
            'student' => $student,
            'isSem3Or5' => $isSem3Or5,
            'insuranceDoc' => $insuranceDoc,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'category' => ['required', Rule::in(array_keys(StudentDocumentOptions::CATEGORIES))],
            'expiry_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'document' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);

        $studentId = (int) session('auth_user.id');
        $file = $validated['document'];
        $extension = strtolower($file->extension() ?: 'bin');
        $path = $studentId.'/'.Str::uuid().'.'.$extension;
        Storage::disk('student_documents')->putFileAs('', $file, $path);

        try {
            $id = DB::table('student_documents')->insertGetId([
                'student_id' => $studentId,
                'title' => trim($validated['title']),
                'category' => $validated['category'],
                'disk' => 'student_documents',
                'path' => $path,
                'original_name' => $this->safeFilename($file->getClientOriginalName()),
                'mime_type' => (string) $file->getMimeType(),
                'size_bytes' => (int) $file->getSize(),
                'expiry_date' => $validated['expiry_date'] ?? null,
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            Storage::disk('student_documents')->delete($path);
            throw $exception;
        }

        auditLog('student_documents.create', 'student_documents', $id, 'Student document uploaded');
        myhepSendPushToAdminsByScope('documents', [
            'category' => 'documents',
            'title' => 'Student document pending',
            'body' => 'A student document is waiting for review.',
            'url' => route('admin.documents.index', ['status' => 'pending']),
            'tag' => 'student-document-'.$id,
        ]);

        return redirect()->route('student.documents.index')->with('success', __('Document uploaded for review.'));
    }

    public function download(int $id): StreamedResponse
    {
        $document = $this->ownedDocument($id);
        abort_unless($document && $document->disk === 'student_documents', 404);
        abort_unless(Storage::disk('student_documents')->exists($document->path), 404);

        auditLog('student_documents.download', 'student_documents', $id, 'Student downloaded own document');

        return Storage::disk('student_documents')->download($document->path, $document->original_name, $this->downloadHeaders());
    }

    public function destroy(int $id): RedirectResponse
    {
        $document = $this->ownedDocument($id);
        abort_unless($document && $document->disk === 'student_documents', 404);

        if (Storage::disk('student_documents')->exists($document->path)) {
            Storage::disk('student_documents')->delete($document->path);
        }
        DB::table('student_documents')->where('id', $id)->where('student_id', session('auth_user.id'))->delete();
        auditLog('student_documents.delete', 'student_documents', $id, 'Student document deleted');

        return redirect()->route('student.documents.index')->with('success', __('Document deleted.'));
    }

    private function ownedDocument(int $id): ?object
    {
        return DB::table('student_documents')
            ->where('id', $id)
            ->where('student_id', session('auth_user.id'))
            ->first();
    }

    private function safeFilename(string $filename): string
    {
        $filename = preg_replace('~[\x00-\x1F\x7F/\\\\]+~', '-', basename($filename)) ?: 'document';

        return Str::limit($filename, 240, '');
    }

    private function downloadHeaders(): array
    {
        return [
            'Cache-Control' => 'private, no-store, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ];
    }
}
