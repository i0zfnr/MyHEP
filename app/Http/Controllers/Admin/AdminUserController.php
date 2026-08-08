<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AccountSessionManager;
use App\Support\LecturerPageAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    private const ADMIN_ROLES = ['guard', 'lecturer', 'scholarship_admin', 'discipline_admin', 'student_affairs_head', 'system_admin'];

    public function index()
    {
        $authRole = session('auth_user.admin_role');
        $adminsQuery = DB::table('admins')
            ->select('id', 'full_name', 'ic_no', 'role', 'created_at')
            ->orderBy('full_name');

        if ($authRole !== 'system_admin') {
            $adminsQuery->where('role', 'lecturer');
        }

        $admins = $adminsQuery->paginate(15);
        $canManageAllAdmins = $authRole === 'system_admin';

        return view('admin.admin_users.index', compact('admins', 'canManageAllAdmins'));
    }

    public function create()
    {
        return view('admin.admin_users.create', [
            'roleOptions' => $this->roleOptions(),
            'lecturerPages' => app(LecturerPageAccess::class)->allFor(0),
            'canConfigureLecturerPages' => session('auth_user.admin_role') === 'system_admin',
        ]);
    }

    public function store(Request $request, LecturerPageAccess $lecturerPages)
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'ic_no' => ['required', 'string', 'max:20', 'unique:admins,ic_no'],
            'email' => ['nullable', 'email', 'max:150', 'unique:admins,email'],
            'role' => ['required', Rule::in(array_keys($this->roleOptions()))],
            'password' => ['required', 'string', 'min:8'],
            'lecturer_pages' => ['nullable', 'array'],
            'lecturer_pages.*' => ['string', Rule::in(array_keys(LecturerPageAccess::PAGES))],
        ]);

        $adminId = DB::table('admins')->insertGetId([
            'full_name' => $validated['full_name'],
            'ic_no' => $validated['ic_no'],
            'email' => $validated['email'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'photo' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($validated['role'] === 'lecturer') {
            $lecturerPages->sync($adminId, $this->validatedLecturerPages($validated), (int) session('auth_user.id'));
        }

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

        $this->authorizeAdminUserAccess($adminUser);

        return view('admin.admin_users.edit', [
            'adminUser' => $adminUser,
            'roleOptions' => $this->roleOptions(),
            'lecturerPages' => app(LecturerPageAccess::class)->allFor($id),
            'canConfigureLecturerPages' => session('auth_user.admin_role') === 'system_admin',
        ]);
    }

    public function update(Request $request, AccountSessionManager $sessions, LecturerPageAccess $lecturerPages, int $id)
    {
        $adminUser = DB::table('admins')->where('id', $id)->first();
        if (!$adminUser) {
            return redirect()->route('admin.admin-users.index')
                ->withErrors(['admin' => __('Rekod admin tidak dijumpai.')]);
        }
        $this->authorizeAdminUserAccess($adminUser);

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'ic_no' => ['required', 'string', 'max:20', Rule::unique('admins', 'ic_no')->ignore($id)],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('admins', 'email')->ignore($id)],
            'role' => ['required', Rule::in(array_keys($this->roleOptions()))],
            'password' => ['nullable', 'string', 'min:8'],
            'lecturer_pages' => ['nullable', 'array'],
            'lecturer_pages.*' => ['string', Rule::in(array_keys(LecturerPageAccess::PAGES))],
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
        if ($validated['role'] === 'lecturer' && session('auth_user.admin_role') === 'system_admin') {
            $lecturerPages->sync($id, $this->validatedLecturerPages($validated), (int) session('auth_user.id'));
        } elseif ($validated['role'] !== 'lecturer') {
            $lecturerPages->forget($id);
        }
        if (!empty($validated['password']) || $adminUser->role !== $validated['role']) {
            $sessions->revokeAccount('admin', $id);
        }
        if (!empty($validated['password'])) {
            myhepSendPushNotification('admin', $id, [
                'category' => 'account',
                'title' => 'Password changed by administrator',
                'body' => 'An administrator changed your StudentEdge password. Contact the system administrator if this was unexpected.',
                'url' => route('login'),
                'tag' => 'admin-password-changed-' . $id,
                'requireInteraction' => true,
            ]);
        }

        return redirect()->route('admin.admin-users.index')
            ->with('success', __('Maklumat admin berjaya dikemaskini.'));
    }

    public function resetPassword(AccountSessionManager $sessions, int $id)
    {
        $adminUser = DB::table('admins')->where('id', $id)->first();
        if (!$adminUser) {
            return redirect()->route('admin.admin-users.index')
                ->withErrors(['admin' => __('Rekod admin tidak dijumpai.')]);
        }
        $this->authorizeAdminUserAccess($adminUser);

        DB::table('admins')
            ->where('id', $id)
            ->update([
                'password' => Hash::make('Admin@12345'),
                'updated_at' => now(),
            ]);
        $sessions->revokeAccount('admin', $id);
        auditLog('admin_users.reset_password', 'admins', $id, 'Reset kata laluan admin kepada default');

        myhepSendPushNotification('admin', $id, [
            'category' => 'account',
            'title' => 'Password reset by administrator',
            'body' => 'An administrator reset your StudentEdge password. Contact the system administrator if this was unexpected.',
            'url' => route('login'),
            'tag' => 'admin-password-reset-' . $id,
            'requireInteraction' => true,
        ]);

        return redirect()->route('admin.admin-users.index')
            ->with('success', __('Kata laluan admin telah direset kepada Admin@12345.'));
    }

    public function destroy(AccountSessionManager $sessions, int $id)
    {
        if ((int) session('auth_user.id') === $id) {
            return redirect()->route('admin.admin-users.index')
                ->withErrors(['admin' => __('Anda tidak boleh padam akaun sendiri.')]);
        }

        $adminUser = DB::table('admins')->where('id', $id)->first();
        if (!$adminUser) {
            return redirect()->route('admin.admin-users.index')
                ->withErrors(['admin' => __('Rekod admin tidak dijumpai.')]);
        }
        $this->authorizeAdminUserAccess($adminUser);

        $deleted = DB::table('admins')->where('id', $id)->delete();
        if (!$deleted) {
            return redirect()->route('admin.admin-users.index')
                ->withErrors(['admin' => __('Rekod admin tidak dijumpai.')]);
        }
        app(LecturerPageAccess::class)->forget($id);
        $sessions->revokeAccount('admin', $id);
        auditLog('admin_users.delete', 'admins', $id, 'Padam rekod admin');

        return redirect()->route('admin.admin-users.index')
            ->with('success', __('Rekod admin berjaya dipadam.'));
    }

    private function roleOptions(): array
    {
        if (session('auth_user.admin_role') !== 'system_admin') {
            return ['lecturer' => adminRoleLabel('lecturer')];
        }

        return collect(self::ADMIN_ROLES)
            ->mapWithKeys(fn (string $role): array => [$role => adminRoleLabel($role)])
            ->all();
    }

    private function authorizeAdminUserAccess(object $adminUser): void
    {
        if (session('auth_user.admin_role') !== 'system_admin' && $adminUser->role !== 'lecturer') {
            abort(403);
        }
    }

    private function validatedLecturerPages(array $validated): array
    {
        if (session('auth_user.admin_role') !== 'system_admin') {
            return array_keys(LecturerPageAccess::PAGES);
        }

        return array_values($validated['lecturer_pages'] ?? []);
    }
}
