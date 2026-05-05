<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $student = DB::table('students')
            ->where('id', session('auth_user.id'))
            ->first();

        if (!$student) {
            return redirect()->route('login');
        }

        return view('student.profile', compact('student'));
    }

    public function update(Request $request): RedirectResponse
    {
        $studentId = (int) session('auth_user.id');
        $student = DB::table('students')->where('id', $studentId)->first();
        if (!$student) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'email' => ['nullable', 'email', 'max:150', 'unique:students,email,' . $studentId],
            'semester' => ['nullable', 'string', 'max:20'],
            'academic_session' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'religion' => ['nullable', 'string', 'max:50'],
            'parliament' => ['nullable', 'string', 'max:120'],
            'dun' => ['nullable', 'string', 'max:120'],
            'race' => ['nullable', 'string', 'max:80'],
            'date_of_birth' => ['nullable', 'date'],
            'guardian_name' => ['nullable', 'string', 'max:150'],
            'guardian_ic_no' => ['nullable', 'string', 'max:20'],
            'guardian_address' => ['nullable', 'string'],
            'guardian_phone' => ['nullable', 'string', 'max:20'],
            'mother_ic_no' => ['nullable', 'string', 'max:20'],
            'guardian_occupation' => ['nullable', 'string', 'max:120'],
            'family_income' => ['nullable', 'numeric', 'min:0'],
            'study_address' => ['nullable', 'string'],
        ]);

        $columnValueMap = [
            'email' => $validated['email'] ?? null,
            'semester' => $validated['semester'] ?? null,
            'academic_session' => $validated['academic_session'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'religion' => $validated['religion'] ?? null,
            'parliament' => $validated['parliament'] ?? null,
            'dun' => $validated['dun'] ?? null,
            'race' => $validated['race'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'guardian_name' => $validated['guardian_name'] ?? null,
            'guardian_ic_no' => $validated['guardian_ic_no'] ?? null,
            'guardian_address' => $validated['guardian_address'] ?? null,
            'guardian_phone' => $validated['guardian_phone'] ?? null,
            'mother_ic_no' => $validated['mother_ic_no'] ?? null,
            'guardian_occupation' => $validated['guardian_occupation'] ?? null,
            'family_income' => array_key_exists('family_income', $validated) && $validated['family_income'] !== null && $validated['family_income'] !== ''
                ? (float) $validated['family_income']
                : null,
            'study_address' => $validated['study_address'] ?? null,
        ];

        $updateData = [];
        foreach ($columnValueMap as $column => $value) {
            if (Schema::hasColumn('students', $column)) {
                $updateData[$column] = $value;
            }
        }
        $updateData['updated_at'] = now();

        DB::table('students')
            ->where('id', $studentId)
            ->update($updateData);

        return redirect()->route('student.profile')
            ->with('success', __('Profil berjaya dikemaskini.'));
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $studentId = (int) session('auth_user.id');
        $student = DB::table('students')->where('id', $studentId)->first();
        if (!$student) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $currentValid = !empty($student->password)
            ? Hash::check($validated['current_password'], $student->password)
            : $validated['current_password'] === $student->ic_no;

        if (!$currentValid) {
            return redirect()->route('student.profile')
                ->withErrors(['current_password' => __('Kata laluan semasa tidak sah.')])
                ->withInput();
        }

        DB::table('students')
            ->where('id', $studentId)
            ->update([
                'password' => Hash::make($validated['new_password']),
                'updated_at' => now(),
            ]);

        return redirect()->route('student.profile')
            ->with('success', __('Kata laluan berjaya dikemaskini.'));
    }
}
