<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        if (!$student) {
            return redirect()->route('student.dashboard')
                ->withErrors(['student' => __('Rekod pelajar tidak dijumpai.')]);
        }

        $submission = DB::table('student_scholarship_status_forms')
            ->where('student_id', $studentId)
            ->first();

        return view('student.scholarship_status.form', compact('student', 'submission'));
    }

    public function update(Request $request): RedirectResponse
    {
        $studentId = (int) session('auth_user.id');
        $validated = $request->validate([
            'has_scholarship' => ['required', Rule::in(['yes', 'no'])],
            'sponsor_name' => ['nullable', 'string', 'max:150', 'required_if:has_scholarship,yes'],
            'monthly_amount' => ['nullable', 'numeric', 'min:0', 'required_if:has_scholarship,yes'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $payload = [
            'has_scholarship' => $validated['has_scholarship'],
            'sponsor_name' => $validated['has_scholarship'] === 'yes' ? trim((string) ($validated['sponsor_name'] ?? '')) : null,
            'monthly_amount' => $validated['has_scholarship'] === 'yes' ? $validated['monthly_amount'] : null,
            'notes' => !empty($validated['notes']) ? trim($validated['notes']) : null,
            'submitted_at' => now(),
            'updated_at' => now(),
        ];

        DB::transaction(function () use ($studentId, $payload, $validated) {
            $existing = DB::table('student_scholarship_status_forms')
                ->where('student_id', $studentId)
                ->first();

            if ($existing) {
                DB::table('student_scholarship_status_forms')
                    ->where('student_id', $studentId)
                    ->update($payload);
            } else {
                DB::table('student_scholarship_status_forms')
                    ->insert(array_merge($payload, [
                        'student_id' => $studentId,
                        'created_at' => now(),
                    ]));
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

        return redirect()->route('student.scholarships.index')
            ->with('success', __('Status biasiswa anda berjaya dihantar dan direkodkan.'));
    }
}
