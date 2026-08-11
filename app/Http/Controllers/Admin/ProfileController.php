<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AccountSessionManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View|RedirectResponse
    {
        $admin = DB::table('admins')
            ->select('id', 'full_name', 'ic_no', 'role', 'photo')
            ->where('id', session('auth_user.id'))
            ->first();

        if (!$admin) {
            return redirect()->route('login');
        }

        $photoUrl = null;
        if (!empty($admin->photo) && Storage::disk('public')->exists($admin->photo)) {
            $photoUrl = Storage::disk('public')->url($admin->photo);
        }

        return view('admin.profile', compact('admin', 'photoUrl'));
    }

    public function updatePhoto(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:51200'],
        ], [
            'profile_photo.uploaded' => __('The profile photo is too large. Please choose a photo below 50MB.'),
            'profile_photo.max' => __('The profile photo must be below 50MB.'),
            'profile_photo.image' => __('The profile photo must be an image file.'),
            'profile_photo.mimes' => __('The profile photo must be JPG, PNG, or WEBP.'),
        ]);

        $adminId = (int) session('auth_user.id');
        $admin = DB::table('admins')->select('id', 'photo')->where('id', $adminId)->first();
        if (!$admin) {
            return redirect()->route('login');
        }

        $photoPath = $validated['profile_photo']->store('admins/profile_photos', 'public');

        DB::table('admins')->where('id', $adminId)->update([
            'photo' => $photoPath,
            'updated_at' => now(),
        ]);

        if (!empty($admin->photo) && $admin->photo !== $photoPath) {
            Storage::disk('public')->delete($admin->photo);
        }

        auditLog('admin.profile_photo_updated', 'admins', $adminId, 'Admin profile photo updated');

        return redirect()->route('admin.profile')->with('success', __('Profile photo updated successfully.'));
    }

    public function updatePassword(Request $request, AccountSessionManager $sessions): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ]);

        $adminId = (int) session('auth_user.id');
        $admin = DB::table('admins')->select('id', 'password')->where('id', $adminId)->first();
        if (! $admin) {
            return redirect()->route('login');
        }

        if (! Hash::check($validated['current_password'], $admin->password)) {
            throw ValidationException::withMessages([
                'current_password' => __('The current password is incorrect.'),
            ]);
        }

        DB::table('admins')->where('id', $adminId)->update([
            'password' => Hash::make($validated['password']),
            'updated_at' => now(),
        ]);

        $oldSessionId = $request->session()->getId();
        $request->session()->regenerate();
        $owner = ['type' => 'admin', 'id' => $adminId];
        $sessions->rotate($request, $oldSessionId, $owner);
        $sessions->revokeOthers($owner, $request->session()->getId());

        auditLog('admin.password_updated', 'admins', $adminId, 'Admin updated own password');

        return redirect()->route('admin.profile')->with('success', __('Password updated successfully. Other sessions have been signed out.'));
    }
}
