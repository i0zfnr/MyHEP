<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AccountSessionManager;
use App\Support\LecturerPageAccess;
use App\Support\Nric;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffManagementController extends Controller
{
    private const CATEGORIES = [
        'discipline' => 'Discipline Lecturer',
        'scholarship' => 'Scholarship Lecturer',
        'general' => 'General JHEP Staff',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $category = trim((string) $request->query('category'));

        $staff = DB::table('admins')
            ->select('id', 'full_name', 'ic_no', 'email', 'staff_category', 'is_active', 'created_at')
            ->where('role', 'lecturer')
            ->when($search !== '', fn ($query) => $query->where(function ($nested) use ($search): void {
                $nested->where('full_name', 'like', "%{$search}%")->orWhere('ic_no', 'like', "%{$search}%");
            }))
            ->when(isset(self::CATEGORIES[$category]), fn ($query) => $query->where('staff_category', $category))
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.account_management.index', [
            'accounts' => $staff,
            'mode' => 'staff',
            'title' => 'Staff Management',
            'description' => 'Manage lecturer and JHEP staff accounts. Login roles are resolved automatically from the IC number.',
            'createRoute' => route('admin.staff.create'),
            'categories' => self::CATEGORIES,
            'filters' => compact('search', 'category'),
        ]);
    }

    public function create(LecturerPageAccess $pages): View
    {
        return $this->formView(null, $pages);
    }

    public function store(Request $request, LecturerPageAccess $pages): RedirectResponse
    {
        $this->normalizeIdentityInput($request);
        $validated = $this->validateAccount($request);
        $id = DB::table('admins')->insertGetId($this->payload($validated) + [
            'password' => Hash::make($validated['password']),
            'role' => 'lecturer',
            'photo' => null,
            'created_at' => now(),
        ]);
        $pages->sync($id, array_values($validated['lecturer_pages'] ?? []), (int) session('auth_user.id'));
        auditLog('staff.create', 'admins', $id, 'Created lecturer/staff account');

        return redirect()->route('admin.staff.index')->with('success', 'Staff account created successfully.');
    }

    public function importBorrowers(Request $request): RedirectResponse
    {
        $request->validate(['staff_file' => ['required', 'file', 'max:10240', 'mimes:csv,txt']]);
        $handle = fopen($request->file('staff_file')->getRealPath(), 'r');
        $header = fgetcsv($handle);
        if (! $header) {
            return back()->withErrors(['staff_file' => 'The staff CSV is empty.']);
        }

        $columns = array_flip(array_map(fn ($value) => Str::of((string) $value)->lower()->replaceMatches('/[^a-z0-9]/', '')->toString(), $header));
        $nricColumn = $columns['nric'] ?? $columns['icno'] ?? $columns['icnumber'] ?? null;
        $nameColumn = $columns['fullname'] ?? $columns['name'] ?? null;
        $departmentColumn = $columns['department'] ?? $columns['unit'] ?? null;
        if ($nricColumn === null || $nameColumn === null) {
            return back()->withErrors(['staff_file' => 'The CSV must contain NRIC and full_name (or name) columns.']);
        }

        $imported = 0;
        while (($row = fgetcsv($handle)) !== false) {
            $nric = preg_replace('/[^0-9]/', '', (string) ($row[$nricColumn] ?? '')) ?? '';
            $name = trim((string) ($row[$nameColumn] ?? ''));
            if ($nric === '' || $name === '') {
                continue;
            }

            $payload = [
                'full_name' => Str::limit($name, 150, ''),
                'department' => $departmentColumn === null ? null : Str::limit(trim((string) ($row[$departmentColumn] ?? '')), 150, ''),
                'is_active' => true,
                'updated_at' => now(),
            ];
            $existing = DB::table('jhep_laptop_staff')->where('nric', $nric)->value('id');
            if ($existing) {
                DB::table('jhep_laptop_staff')->where('id', $existing)->update($payload);
            } else {
                DB::table('jhep_laptop_staff')->insert($payload + ['nric' => $nric, 'created_at' => now()]);
            }
            $imported++;
        }
        fclose($handle);
        auditLog('laptop.staff_import', 'jhep_laptop_staff', null, "Imported {$imported} laptop borrower records");

        return redirect()->route('admin.staff.index')->with('success', "Imported or updated {$imported} all-staff borrower record(s).");
    }

    public function edit(int $id, LecturerPageAccess $pages): View
    {
        return $this->formView($this->staffOrFail($id), $pages);
    }

    public function update(Request $request, AccountSessionManager $sessions, LecturerPageAccess $pages, int $id): RedirectResponse
    {
        $staff = $this->staffOrFail($id);
        $this->normalizeIdentityInput($request);
        $validated = $this->validateAccount($request, $id, false);
        $payload = $this->payload($validated);
        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
        }
        DB::table('admins')->where('id', $id)->update($payload);
        $pages->sync($id, array_values($validated['lecturer_pages'] ?? []), (int) session('auth_user.id'));
        if (! empty($validated['password'])
            || (bool) $staff->is_active !== (bool) $payload['is_active']
            || $staff->staff_category !== $payload['staff_category']) {
            $sessions->revokeAccount('admin', $id);
        }
        auditLog('staff.update', 'admins', $id, 'Updated lecturer/staff account');

        return redirect()->route('admin.staff.index')->with('success', 'Staff account updated successfully.');
    }

    public function resetPassword(AccountSessionManager $sessions, int $id): RedirectResponse
    {
        $this->staffOrFail($id);
        DB::table('admins')->where('id', $id)->update(['password' => Hash::make('Staff@12345'), 'updated_at' => now()]);
        $sessions->revokeAccount('admin', $id);
        auditLog('staff.reset_password', 'admins', $id, 'Reset staff password');

        return redirect()->route('admin.staff.index')->with('success', 'Staff password reset to Staff@12345.');
    }

    public function destroy(AccountSessionManager $sessions, LecturerPageAccess $pages, int $id): RedirectResponse
    {
        $this->staffOrFail($id);
        if ((int) session('auth_user.id') === $id) {
            abort(422, 'You cannot delete your own account.');
        }
        DB::table('admins')->where('id', $id)->delete();
        $pages->forget($id);
        $sessions->revokeAccount('admin', $id);
        auditLog('staff.delete', 'admins', $id, 'Deleted lecturer/staff account');

        return redirect()->route('admin.staff.index')->with('success', 'Staff account deleted successfully.');
    }

    private function formView(?object $account, LecturerPageAccess $pages): View
    {
        return view('admin.account_management.form', [
            'account' => $account,
            'mode' => 'staff',
            'title' => $account ? 'Edit Staff' : 'Add Staff',
            'submitRoute' => $account ? route('admin.staff.update', $account->id) : route('admin.staff.store'),
            'backRoute' => route('admin.staff.index'),
            'categories' => self::CATEGORIES,
            'pageOptions' => $pages->allFor((int) ($account->id ?? 0)),
        ]);
    }

    private function validateAccount(Request $request, ?int $id = null, bool $passwordRequired = true): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'ic_no' => ['required', 'string', 'max:20', fn (string $attribute, string $value, \Closure $fail) => Nric::isAssignedToAdmin($value, $id) && $fail('This NRIC is already assigned to an existing admin or staff account.')],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('admins', 'email')->ignore($id)],
            'staff_category' => ['required', Rule::in(array_keys(self::CATEGORIES))],
            'is_active' => ['required', 'boolean'],
            'password' => [$passwordRequired ? 'required' : 'nullable', 'string', 'min:8'],
            'lecturer_pages' => ['nullable', 'array'],
            'lecturer_pages.*' => ['string', Rule::in(array_keys(LecturerPageAccess::PAGES))],
        ]);
    }

    private function payload(array $validated): array
    {
        return [
            'full_name' => trim($validated['full_name']),
            'ic_no' => trim($validated['ic_no']),
            'email' => filled($validated['email'] ?? null) ? strtolower(trim($validated['email'])) : null,
            'staff_category' => $validated['staff_category'],
            'is_active' => (bool) $validated['is_active'],
            'updated_at' => now(),
        ];
    }

    private function staffOrFail(int $id): object
    {
        return DB::table('admins')->where('id', $id)->where('role', 'lecturer')->firstOrFail();
    }

    private function normalizeIdentityInput(Request $request): void
    {
        $request->merge([
            'ic_no' => Nric::normalize($request->input('ic_no')),
            'email' => filled($request->input('email')) ? strtolower(trim((string) $request->input('email'))) : null,
        ]);
    }
}
