<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    private const ADMIN_ROLES = ['guard', 'scholarship_admin', 'discipline_admin', 'system_admin'];

    public function index()
    {
        $admins = DB::table('admins')
            ->select('id', 'full_name', 'ic_no', 'role', 'created_at')
            ->orderBy('full_name')
            ->paginate(15);

        return view('admin.admin_users.index', compact('admins'));
    }

    public function create()
    {
        return view('admin.admin_users.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'ic_no' => ['required', 'string', 'max:20', 'unique:admins,ic_no'],
            'email' => ['nullable', 'email', 'max:150', 'unique:admins,email'],
            'role' => ['required', Rule::in(self::ADMIN_ROLES)],
            'password' => ['required', 'string', 'min:8'],
        ]);

        DB::table('admins')->insert([
            'full_name' => $validated['full_name'],
            'ic_no' => $validated['ic_no'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'photo' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.admin-users.index')
            ->with('success', __('Admin baharu berjaya ditambah.'));
    }

    public function edit(int $id)
    {
        $adminUser = DB::table('admins')->where('id', $id)->first();
        if (!$adminUser) {
            return redirect()->route('admin.admin-users.index')
                ->withErrors(['admin' => __('Rekod admin tidak dijumpai.')]);
        }

        return view('admin.admin_users.edit', compact('adminUser'));
    }

    public function update(Request $request, int $id)
    {
        $adminUser = DB::table('admins')->where('id', $id)->first();
        if (!$adminUser) {
            return redirect()->route('admin.admin-users.index')
                ->withErrors(['admin' => __('Rekod admin tidak dijumpai.')]);
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'ic_no' => ['required', 'string', 'max:20', Rule::unique('admins', 'ic_no')->ignore($id)],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('admins', 'email')->ignore($id)],
            'role' => ['required', Rule::in(self::ADMIN_ROLES)],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $payload = [
            'full_name' => $validated['full_name'],
            'ic_no' => $validated['ic_no'],
            'email' => $validated['email'] ?? null,
            'role' => $validated['role'],
            'updated_at' => now(),
        ];

        if (!empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }

        DB::table('admins')->where('id', $id)->update($payload);

        return redirect()->route('admin.admin-users.index')
            ->with('success', __('Maklumat admin berjaya dikemaskini.'));
    }

    public function resetPassword(int $id)
    {
        $adminUser = DB::table('admins')->where('id', $id)->first();
        if (!$adminUser) {
            return redirect()->route('admin.admin-users.index')
                ->withErrors(['admin' => __('Rekod admin tidak dijumpai.')]);
        }

        DB::table('admins')
            ->where('id', $id)
            ->update([
                'password' => Hash::make('Admin@12345'),
                'updated_at' => now(),
            ]);
        auditLog('admin_users.reset_password', 'admins', $id, 'Reset kata laluan admin kepada default');

        return redirect()->route('admin.admin-users.index')
            ->with('success', __('Kata laluan admin telah direset kepada Admin@12345.'));
    }

    public function destroy(int $id)
    {
        if ((int) session('auth_user.id') === $id) {
            return redirect()->route('admin.admin-users.index')
                ->withErrors(['admin' => __('Anda tidak boleh padam akaun sendiri.')]);
        }

        $deleted = DB::table('admins')->where('id', $id)->delete();
        if (!$deleted) {
            return redirect()->route('admin.admin-users.index')
                ->withErrors(['admin' => __('Rekod admin tidak dijumpai.')]);
        }
        auditLog('admin_users.delete', 'admins', $id, 'Padam rekod admin');

        return redirect()->route('admin.admin-users.index')
            ->with('success', __('Rekod admin berjaya dipadam.'));
    }
}
