<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AccountSessionManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GuardManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $guards = DB::table('admins')
            ->select('id', 'full_name', 'ic_no', 'email', 'is_active', 'created_at')
            ->where('role', 'guard')
            ->when($search !== '', fn ($query) => $query->where(function ($nested) use ($search): void {
                $nested->where('full_name', 'like', "%{$search}%")->orWhere('ic_no', 'like', "%{$search}%");
            }))
            ->orderBy('full_name')->paginate(15)->withQueryString();

        return view('admin.account_management.index', [
            'accounts' => $guards, 'mode' => 'guard', 'title' => 'Guard Management',
            'description' => 'Manage guard accounts used for movement and checkpoint operations.',
            'createRoute' => route('admin.guards.create'), 'categories' => [], 'filters' => ['search' => $search],
        ]);
    }

    public function create(): View { return $this->formView(null); }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAccount($request);
        $id = DB::table('admins')->insertGetId($this->payload($validated) + [
            'password' => Hash::make($validated['password']), 'role' => 'guard', 'staff_category' => null,
            'photo' => null, 'created_at' => now(),
        ]);
        auditLog('guards.create', 'admins', $id, 'Created guard account');
        return redirect()->route('admin.guards.index')->with('success', 'Guard account created successfully.');
    }

    public function edit(int $id): View { return $this->formView($this->guardOrFail($id)); }

    public function update(Request $request, AccountSessionManager $sessions, int $id): RedirectResponse
    {
        $guard = $this->guardOrFail($id);
        $validated = $this->validateAccount($request, $id, false);
        $payload = $this->payload($validated);
        if (! empty($validated['password'])) $payload['password'] = Hash::make($validated['password']);
        DB::table('admins')->where('id', $id)->update($payload);
        if (! empty($validated['password']) || (bool) $guard->is_active !== (bool) $payload['is_active']) $sessions->revokeAccount('admin', $id);
        auditLog('guards.update', 'admins', $id, 'Updated guard account');
        return redirect()->route('admin.guards.index')->with('success', 'Guard account updated successfully.');
    }

    public function resetPassword(AccountSessionManager $sessions, int $id): RedirectResponse
    {
        $this->guardOrFail($id);
        DB::table('admins')->where('id', $id)->update(['password' => Hash::make('Guard@12345'), 'updated_at' => now()]);
        $sessions->revokeAccount('admin', $id);
        auditLog('guards.reset_password', 'admins', $id, 'Reset guard password');
        return redirect()->route('admin.guards.index')->with('success', 'Guard password reset to Guard@12345.');
    }

    public function destroy(AccountSessionManager $sessions, int $id): RedirectResponse
    {
        $this->guardOrFail($id);
        if ((int) session('auth_user.id') === $id) abort(422, 'You cannot delete your own account.');
        DB::table('admins')->where('id', $id)->delete();
        $sessions->revokeAccount('admin', $id);
        auditLog('guards.delete', 'admins', $id, 'Deleted guard account');
        return redirect()->route('admin.guards.index')->with('success', 'Guard account deleted successfully.');
    }

    private function formView(?object $account): View
    {
        return view('admin.account_management.form', [
            'account' => $account, 'mode' => 'guard', 'title' => $account ? 'Edit Guard' : 'Add Guard',
            'submitRoute' => $account ? route('admin.guards.update', $account->id) : route('admin.guards.store'),
            'backRoute' => route('admin.guards.index'), 'categories' => [], 'pageOptions' => [],
        ]);
    }

    private function validateAccount(Request $request, ?int $id = null, bool $passwordRequired = true): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'ic_no' => ['required', 'string', 'max:20', Rule::unique('admins', 'ic_no')->ignore($id)],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('admins', 'email')->ignore($id)],
            'is_active' => ['required', 'boolean'],
            'password' => [$passwordRequired ? 'required' : 'nullable', 'string', 'min:8'],
        ]);
    }

    private function payload(array $validated): array
    {
        return ['full_name' => trim($validated['full_name']), 'ic_no' => trim($validated['ic_no']),
            'email' => filled($validated['email'] ?? null) ? strtolower(trim($validated['email'])) : null,
            'is_active' => (bool) $validated['is_active'], 'updated_at' => now()];
    }

    private function guardOrFail(int $id): object
    {
        return DB::table('admins')->where('id', $id)->where('role', 'guard')->firstOrFail();
    }
}
