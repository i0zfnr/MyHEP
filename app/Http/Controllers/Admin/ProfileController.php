<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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
}
