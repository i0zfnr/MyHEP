<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScholarshipStatusController extends Controller
{
    public function edit(): View|RedirectResponse
    {
        $studentId = (int) session('auth_user.id');
        $student = DB::table('students')
            ->where('id', $studentId)
            ->first();

        if (! $student) {
            return redirect()->route('student.dashboard')
                ->withErrors(['student' => __('Rekod pelajar tidak dijumpai.')]);
        }

        $submission = DB::table('student_scholarship_status_forms')
            ->where('student_id', $studentId)
            ->first();

        $document = $submission
            ? DB::table('student_documents')
                ->where('source_type', 'scholarship_status')
                ->where('source_id', $submission->id)
                ->first()
            : null;

        return view('student.scholarship_status.form', compact('student', 'submission', 'document'));
    }

    public function update(Request $request): RedirectResponse
    {
        $studentId = (int) session('auth_user.id');
        $existingSubmission = DB::table('student_scholarship_status_forms')->where('student_id', $studentId)->first();
        $existingDoc = $existingSubmission
            ? DB::table('student_documents')->where('source_type', 'scholarship_status')->where('source_id', $existingSubmission->id)->first()
            : null;

        $type = $request->input('application_type');
        if (blank($type)) {
            $type = $request->input('has_scholarship') === 'yes' ? 'scholarship' : 'none';
        }

        $validated = $request->validate([
            'application_type' => ['required', Rule::in(['scholarship', 'welfare', 'none'])],
            
            // Scholarship fields
            'sponsor_name' => ['nullable', 'string', 'max:150', Rule::requiredIf(fn () => $type === 'scholarship')],
            'monthly_amount' => ['nullable', 'numeric', 'min:0', Rule::requiredIf(fn () => $type === 'scholarship')],
            'offer_letter' => [$type === 'scholarship' && ! $existingDoc ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            
            // Welfare fields
            'guardian_name' => ['nullable', 'string', 'max:150', Rule::requiredIf(fn () => $type === 'welfare')],
            'guardian_ic_no' => ['nullable', 'string', 'max:30', Rule::requiredIf(fn () => $type === 'welfare')],
            'guardian_relationship' => ['nullable', 'string', 'max:60', Rule::requiredIf(fn () => $type === 'welfare')],
            'guardian_phone' => ['nullable', 'string', 'max:30', Rule::requiredIf(fn () => $type === 'welfare')],
            'guardian_occupation' => ['nullable', 'string', 'max:150', Rule::requiredIf(fn () => $type === 'welfare')],
            'family_income' => ['nullable', 'numeric', 'min:0', Rule::requiredIf(fn () => $type === 'welfare')],
            'dependents_count' => ['nullable', 'integer', 'min:0', 'max:30'],
            'welfare_category' => ['nullable', 'string', 'max:100', Rule::requiredIf(fn () => $type === 'welfare')],
            'welfare_description' => ['nullable', 'string', 'max:2000', Rule::requiredIf(fn () => $type === 'welfare')],
            'welfare_amount' => ['nullable', 'numeric', 'min:0'],
            'welfare_proof' => [$type === 'welfare' && ! $existingDoc ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            
            // Common
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $uploadedFile = $request->file('offer_letter') ?? $request->file('welfare_proof');
        $newPath = null;
        if ($uploadedFile) {
            $newPath = $studentId.'/'.Str::uuid().'.'.strtolower($uploadedFile->extension());
            Storage::disk('student_documents')->putFileAs('', $uploadedFile, $newPath);
        }

        $payload = [
            'application_type' => $validated['application_type'],
            'has_scholarship' => $validated['application_type'] === 'scholarship' ? 'yes' : 'no',
            'sponsor_name' => $validated['application_type'] === 'scholarship' ? trim((string) ($validated['sponsor_name'] ?? '')) : null,
            'monthly_amount' => $validated['application_type'] === 'scholarship' ? $validated['monthly_amount'] : null,
            
            'welfare_category' => $validated['application_type'] === 'welfare' ? trim((string) ($validated['welfare_category'] ?? '')) : null,
            'welfare_description' => $validated['application_type'] === 'welfare' ? trim((string) ($validated['welfare_description'] ?? '')) : null,
            'welfare_amount' => $validated['application_type'] === 'welfare' ? ($validated['welfare_amount'] ?? null) : null,
            
            'guardian_name' => $validated['application_type'] === 'welfare' ? trim((string) ($validated['guardian_name'] ?? '')) : null,
            'guardian_ic_no' => $validated['application_type'] === 'welfare' ? trim((string) ($validated['guardian_ic_no'] ?? '')) : null,
            'guardian_relationship' => $validated['application_type'] === 'welfare' ? trim((string) ($validated['guardian_relationship'] ?? '')) : null,
            'guardian_phone' => $validated['application_type'] === 'welfare' ? trim((string) ($validated['guardian_phone'] ?? '')) : null,
            'guardian_occupation' => $validated['application_type'] === 'welfare' ? trim((string) ($validated['guardian_occupation'] ?? '')) : null,
            'family_income' => $validated['application_type'] === 'welfare' ? $validated['family_income'] : null,
            'dependents_count' => $validated['application_type'] === 'welfare' ? ($validated['dependents_count'] ?? null) : null,
            
            'notes' => filled($validated['notes'] ?? null) ? trim($validated['notes']) : null,
            'submitted_at' => now(),
            'updated_at' => now(),
        ];

        try {
            DB::transaction(function () use ($studentId, $payload, $validated, $newPath, $existingDoc, $uploadedFile) {
                $existing = DB::table('student_scholarship_status_forms')
                    ->where('student_id', $studentId)
                    ->first();

                if ($existing) {
                    DB::table('student_scholarship_status_forms')
                        ->where('student_id', $studentId)
                        ->update($payload);
                    $submissionId = (int) $existing->id;
                } else {
                    $submissionId = DB::table('student_scholarship_status_forms')
                        ->insertGetId(array_merge($payload, [
                            'student_id' => $studentId,
                            'created_at' => now(),
                        ]));
                }

                // If welfare, sync guardian info back to students table profile if columns exist
                if ($validated['application_type'] === 'welfare') {
                    $studentProfileSync = [];
                    if (filled($validated['guardian_name'] ?? null)) $studentProfileSync['guardian_name'] = trim($validated['guardian_name']);
                    if (filled($validated['guardian_ic_no'] ?? null)) $studentProfileSync['guardian_ic_no'] = trim($validated['guardian_ic_no']);
                    if (filled($validated['guardian_phone'] ?? null)) $studentProfileSync['guardian_phone'] = trim($validated['guardian_phone']);
                    if (filled($validated['guardian_occupation'] ?? null)) $studentProfileSync['guardian_occupation'] = trim($validated['guardian_occupation']);
                    if (array_key_exists('family_income', $validated) && $validated['family_income'] !== null) $studentProfileSync['family_income'] = (float) $validated['family_income'];
                    if (!empty($studentProfileSync)) {
                        DB::table('students')->where('id', $studentId)->update($studentProfileSync);
                    }
                }

                // Manage Document
                if (in_array($validated['application_type'], ['scholarship', 'welfare'], true) && $newPath && $uploadedFile) {
                    $docTitle = $validated['application_type'] === 'welfare'
                        ? 'Dokumen Bukti Permohonan Kebajikan'
                        : 'Surat Tawaran Biasiswa';
                    $docCategory = $validated['application_type'] === 'welfare'
                        ? 'welfare'
                        : 'scholarship';

                    $documentPayload = [
                        'student_id' => $studentId,
                        'source_type' => 'scholarship_status',
                        'source_id' => $submissionId,
                        'title' => $docTitle,
                        'category' => $docCategory,
                        'disk' => 'student_documents',
                        'path' => $newPath,
                        'original_name' => Str::limit(basename($uploadedFile->getClientOriginalName()), 240, ''),
                        'mime_type' => (string) $uploadedFile->getMimeType(),
                        'size_bytes' => (int) $uploadedFile->getSize(),
                        'status' => 'pending',
                        'reviewed_by' => null,
                        'review_note' => null,
                        'reviewed_at' => null,
                        'updated_at' => now(),
                    ];
                    if ($existingDoc) {
                        DB::table('student_documents')->where('id', $existingDoc->id)->update($documentPayload);
                    } else {
                        DB::table('student_documents')->insert(array_merge($documentPayload, ['created_at' => now()]));
                    }
                } elseif ($validated['application_type'] === 'none' && $existingDoc) {
                    DB::table('student_documents')->where('id', $existingDoc->id)->delete();
                }

                // Sync to scholarships table
                $scholarshipPayload = [
                    'student_id' => $studentId,
                    'type' => $validated['application_type'],
                    'provider_name' => match ($validated['application_type']) {
                        'scholarship' => trim((string) ($validated['sponsor_name'] ?? '')),
                        'welfare' => 'Bantuan Kebajikan JHEP' . (filled($validated['welfare_category'] ?? null) ? ' ('.$validated['welfare_category'].')' : ''),
                        default => null,
                    },
                    'amount' => match ($validated['application_type']) {
                        'scholarship' => $validated['monthly_amount'] ?? null,
                        'welfare' => $validated['welfare_amount'] ?? null,
                        default => null,
                    },
                    'status' => $validated['application_type'] === 'none' ? 'confirmed' : 'pending',
                    'proof_file' => 'student_status_form',
                    'updated_at' => now(),
                ];

                $managedScholarship = DB::table('scholarships')
                    ->where('student_id', $studentId)
                    ->where('proof_file', 'student_status_form')
                    ->first();

                if ($managedScholarship) {
                    DB::table('scholarships')
                        ->where('id', $managedScholarship->id)
                        ->update($scholarshipPayload);
                } else {
                    DB::table('scholarships')->insert(array_merge($scholarshipPayload, [
                        'created_at' => now(),
                    ]));
                }
            });
        } catch (\Throwable $exception) {
            if ($newPath) {
                Storage::disk('student_documents')->delete($newPath);
            }
            throw $exception;
        }

        if ($existingDoc && ($newPath || $validated['application_type'] === 'none')) {
            Storage::disk('student_documents')->delete($existingDoc->path);
        }

        myhepSendPushToAdminsByScope('scholarship', [
            'category' => $validated['application_type'] === 'welfare' ? 'welfare' : 'scholarship',
            'title' => $validated['application_type'] === 'welfare' ? 'Welfare assistance application submitted' : 'Scholarship status submitted',
            'body' => 'A student submitted or updated financial assistance/welfare information for review.',
            'url' => route('admin.student-scholarship-status.index'),
            'tag' => 'scholarship-status-' . $studentId,
        ]);

        return redirect()->route('student.scholarships.index')
            ->with('success', $validated['application_type'] === 'welfare'
                ? __('Permohonan bantuan kebajikan anda berjaya dihantar untuk semakan pihak JHEP.')
                : __('Maklumat biasiswa/bantuan anda berjaya dikemaskini.'));
    }
}

