<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            ->select('id', 'full_name', 'matric_no', 'program')
            ->where('id', $studentId)
            ->first();

        if (! $student) {
            return redirect()->route('student.dashboard')
                ->withErrors(['student' => __('Rekod pelajar tidak dijumpai.')]);
        }

        $submission = DB::table('student_scholarship_status_forms')
            ->where('student_id', $studentId)
            ->first();
        $offerLetter = $submission
            ? DB::table('student_documents')->where('source_type', 'scholarship_status')->where('source_id', $submission->id)->first()
            : null;

        return view('student.scholarship_status.form', compact('student', 'submission', 'offerLetter'));
    }

    public function update(Request $request): RedirectResponse
    {
        $studentId = (int) session('auth_user.id');
        $existingSubmission = DB::table('student_scholarship_status_forms')->where('student_id', $studentId)->first();
        $existingOffer = $existingSubmission
            ? DB::table('student_documents')->where('source_type', 'scholarship_status')->where('source_id', $existingSubmission->id)->first()
            : null;
        $validated = $request->validate([
            'has_scholarship' => ['required', Rule::in(['yes', 'no'])],
            'sponsor_name' => ['nullable', 'string', 'max:150', 'required_if:has_scholarship,yes'],
            'monthly_amount' => ['nullable', 'numeric', 'min:0', 'required_if:has_scholarship,yes'],
            'notes' => ['nullable', 'string', 'max:500'],
            'offer_letter' => [$request->input('has_scholarship') === 'yes' && ! $existingOffer ? 'required' : 'nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ]);
        $newPath = null;
        if ($request->hasFile('offer_letter')) {
            $file = $request->file('offer_letter');
            $newPath = $studentId.'/'.Str::uuid().'.'.strtolower($file->extension());
            Storage::disk('student_documents')->putFileAs('', $file, $newPath);
        }

        $payload = [
            'has_scholarship' => $validated['has_scholarship'],
            'sponsor_name' => $validated['has_scholarship'] === 'yes' ? trim((string) ($validated['sponsor_name'] ?? '')) : null,
            'monthly_amount' => $validated['has_scholarship'] === 'yes' ? $validated['monthly_amount'] : null,
            'notes' => ! empty($validated['notes']) ? trim($validated['notes']) : null,
            'submitted_at' => now(),
            'updated_at' => now(),
        ];

        try {
            DB::transaction(function () use ($studentId, $payload, $validated, $newPath, $existingOffer, $request) {
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

                if ($validated['has_scholarship'] === 'yes' && $newPath) {
                    $file = $request->file('offer_letter');
                    $documentPayload = [
                        'student_id' => $studentId,
                        'source_type' => 'scholarship_status',
                        'source_id' => $submissionId,
                        'title' => 'Scholarship Offer Letter',
                        'category' => 'scholarship',
                        'disk' => 'student_documents',
                        'path' => $newPath,
                        'original_name' => Str::limit(basename($file->getClientOriginalName()), 240, ''),
                        'mime_type' => (string) $file->getMimeType(),
                        'size_bytes' => (int) $file->getSize(),
                        'status' => 'pending',
                        'reviewed_by' => null,
                        'review_note' => null,
                        'reviewed_at' => null,
                        'updated_at' => now(),
                    ];
                    if ($existingOffer) {
                        DB::table('student_documents')->where('id', $existingOffer->id)->update($documentPayload);
                    } else {
                        DB::table('student_documents')->insert(array_merge($documentPayload, ['created_at' => now()]));
                    }
                } elseif ($validated['has_scholarship'] === 'no' && $existingOffer) {
                    DB::table('student_documents')->where('id', $existingOffer->id)->delete();
                }

                $scholarshipPayload = [
                    'student_id' => $studentId,
                    'type' => $validated['has_scholarship'] === 'yes' ? 'scholarship' : 'none',
                    'provider_name' => $validated['has_scholarship'] === 'yes'
                        ? trim((string) ($validated['sponsor_name'] ?? ''))
                        : null,
                    'amount' => $validated['has_scholarship'] === 'yes'
                        ? $validated['monthly_amount']
                        : null,
                    'status' => $validated['has_scholarship'] === 'yes' ? 'pending' : 'confirmed',
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

        if ($existingOffer && ($newPath || $validated['has_scholarship'] === 'no')) {
            Storage::disk('student_documents')->delete($existingOffer->path);
        }

        return redirect()->route('student.scholarships.index')
            ->with('success', __('Status biasiswa anda berjaya dihantar dan direkodkan.'));
    }
}
