<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Support\AccountSessionManager;
use App\Support\SystemFeatures;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(SystemFeatures $features): View|RedirectResponse
    {
        $student = DB::table('students')
            ->where('id', session('auth_user.id'))
            ->first();

        if (!$student) {
            return redirect()->route('login');
        }

        $enforceStudentProfilePhoto = $features->enabled('enforce_student_profile_photo');

        return view('student.profile', compact('student', 'enforceStudentProfilePhoto'));
    }

    public function update(Request $request): RedirectResponse
    {
        $studentId = (int) session('auth_user.id');
        $student = DB::table('students')->where('id', $studentId)->first();
        if (!$student) {
            return redirect()->route('login');
        }

        // An administrator can allow this student to use the system while
        // completing profile information at their own pace. Keep this check in
        // the write action as well as the access middleware.
        $completionBypassEnabled = (bool) ($student->profile_completion_bypass ?? false);
        $requiredProfileField = $completionBypassEnabled ? 'nullable' : 'required';

        $rawDob = trim((string) $request->input('date_of_birth', ''));
        if (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})$/', $rawDob, $matches)) {
            $normalizedDob = sprintf('%04d-%02d-%02d', (int) $matches[3], (int) $matches[2], (int) $matches[1]);
            $request->merge(['date_of_birth' => $normalizedDob]);
        }

        $validated = $request->validate([
            'profile_photo' => [blank($student->photo ?? null) && ! $completionBypassEnabled ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:51200'],
            'email' => [$requiredProfileField, 'email', 'max:150', 'unique:students,email,' . $studentId],
            'semester' => [$requiredProfileField, 'string', 'max:20'],
            'academic_session' => [$requiredProfileField, 'string', 'max:30'],
            'phone' => [$requiredProfileField, 'string', 'max:20'],
            'address' => [$requiredProfileField, 'string'],
            'residence_status' => ['nullable', 'in:inside_campus,live_out'],
            'room_number' => ['nullable', 'string', 'max:30'],
            'religion' => [$requiredProfileField, 'string', 'max:50'],
            'race' => [$requiredProfileField, 'string', 'max:80'],
            'date_of_birth' => [$requiredProfileField, 'date'],
            'guardian_name' => [$requiredProfileField, 'string', 'max:150'],
            'guardian_ic_no' => [$requiredProfileField, 'string', 'max:20'],
            'guardian_address' => [$requiredProfileField, 'string'],
            'guardian_phone' => [$requiredProfileField, 'string', 'max:20'],
            'mother_ic_no' => [$requiredProfileField, 'string', 'max:20'],
            'family_income' => [$requiredProfileField, 'numeric', 'min:0'],
            'study_address' => ['nullable', 'string'],
        ], [
            'profile_photo.uploaded' => __('The profile photo is too large. Please choose a photo below 50MB.'),
            'profile_photo.max' => __('The profile photo must be below 50MB.'),
            'profile_photo.image' => __('The profile photo must be an image file.'),
            'profile_photo.mimes' => __('The profile photo must be JPG, PNG, or WEBP.'),
        ]);

        $columnValueMap = [
            'email' => $validated['email'] ?? null,
            'semester' => $validated['semester'] ?? null,
            'academic_session' => $validated['academic_session'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'residence_status' => $validated['residence_status'] ?? ($student->residence_status ?? 'inside_campus'),
            'room_number' => ($validated['residence_status'] ?? ($student->residence_status ?? 'inside_campus')) === 'inside_campus'
                ? ($validated['room_number'] ?? null)
                : null,
            'religion' => $validated['religion'] ?? null,
            'race' => $validated['race'] ?? null,
            'date_of_birth' => $validated['date_of_birth'] ?? null,
            'guardian_name' => $validated['guardian_name'] ?? null,
            'guardian_ic_no' => $validated['guardian_ic_no'] ?? null,
            'guardian_address' => $validated['guardian_address'] ?? null,
            'guardian_phone' => $validated['guardian_phone'] ?? null,
            'mother_ic_no' => $validated['mother_ic_no'] ?? null,
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

        $oldPhotoPath = $student->photo ?? null;
        if ($request->hasFile('profile_photo') && Schema::hasColumn('students', 'photo')) {
            $updateData['photo'] = $request->file('profile_photo')->store('students/profile_photos', 'public');
            if (Schema::hasColumn('students', 'profile_photo_status')) {
                $updateData['profile_photo_status'] = 'pending';
            }
            if (Schema::hasColumn('students', 'profile_photo_reviewed_at')) {
                $updateData['profile_photo_reviewed_at'] = null;
            }
            if (Schema::hasColumn('students', 'profile_photo_reviewed_by')) {
                $updateData['profile_photo_reviewed_by'] = null;
            }
        }

        $updateData['updated_at'] = now();

        DB::table('students')
            ->where('id', $studentId)
            ->update($updateData);

        if (!empty($updateData['photo']) && $oldPhotoPath && $oldPhotoPath !== $updateData['photo']) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        return redirect()->route('student.profile')
            ->with('success', __('Profil berjaya dikemaskini.'));
    }

    public function updatePassword(Request $request, AccountSessionManager $sessions): RedirectResponse
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
        $sessions->revokeAccount(
            'student',
            $studentId,
            $request->session()->get('account_session_public_id')
        );

        myhepSendPushNotification('student', $studentId, [
            'category' => 'account',
            'title' => 'Password changed',
            'body' => 'Your MyHEP password was changed. Review your account immediately if this was not you.',
            'url' => route('settings.show'),
            'tag' => 'student-password-changed-' . $studentId,
            'requireInteraction' => true,
        ]);

        return redirect()->route('student.profile')
            ->with('success', __('Kata laluan berjaya dikemaskini.'));
    }
}
