<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AccountSessionManager;
use App\Support\LecturerPageAccess;
use App\Support\Nric;
use App\Support\ProgramApprovalRouting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use ZipArchive;

class StaffManagementController extends Controller
{
    private const CATEGORIES = [
        'discipline' => 'Discipline Lecturer',
        'scholarship' => 'Scholarship Lecturer',
        'general' => 'PBT Staff',
    ];

    private const DEPARTMENTS = [
        'pejabat_pengarah' => 'Pejabat Pengarah',
        'jtmk' => 'Jabatan Teknologi Maklumat & Komunikasi (JTMK)',
        'jpa' => 'Jabatan Pengajian Am (JPA)',
        'jrkv' => 'Jabatan Reka Bentuk & Komunikasi Visual (JRKV)',
        'jmsk' => 'Jabatan Matematik, Sains dan Komputer (JMSK)',
        'unit_khidmat_pengurusan' => 'Unit Khidmat Pengurusan',
        'unit_pengurusan_kewangan' => 'Unit Pengurusan Kewangan',
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $department = trim((string) $request->query('department'));
        // Keep the roster total stable when a lecturer is promoted to another admin role.
        $totalAccounts = DB::table('admins')->whereNotNull('staff_category')->count();

        $staff = DB::table('admins')
            ->select('id', 'full_name', 'ic_no', 'email', 'staff_category', 'staff_department', 'reporting_branch', 'position', 'is_active', 'created_at')
            ->where('role', 'lecturer')
            ->when($search !== '', fn ($query) => $query->where(function ($nested) use ($search): void {
                $nested->where('full_name', 'like', "%{$search}%")
                    ->orWhere('ic_no', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('position', 'like', "%{$search}%");
            }))
            ->when(isset(self::DEPARTMENTS[$department]), fn ($query) => $query->where('staff_department', $department))
            ->orderByRaw('CASE WHEN staff_department IS NULL THEN 1 ELSE 0 END')
            ->orderBy('staff_department')
            ->orderBy('full_name')
            ->paginate(100)
            ->withQueryString();

        return view('admin.account_management.index', [
            'accounts' => $staff,
            'mode' => 'staff',
            'title' => 'Staff Management',
            'description' => 'Manage lecturer and PBT staff accounts, department assignments, and access from one place.',
            'createRoute' => route('admin.staff.create'),
            'categories' => self::CATEGORIES,
            'departments' => self::DEPARTMENTS,
            'filters' => compact('search', 'department'),
            'totalAccounts' => $totalAccounts,
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
        auditLog('staff.create', 'admins', $id, 'Created lecturer/staff account with page access: '.implode(', ', $validated['lecturer_pages'] ?? []));

        return redirect()->route('admin.staff.index')->with('success', 'Staff account created successfully.');
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'staff_file' => ['required', 'file', 'max:20480', 'mimes:csv,txt,xlsx'],
        ]);
        $file = $validated['staff_file'];
        $extension = strtolower($file->getClientOriginalExtension());
        $rows = $extension === 'xlsx'
            ? $this->staffRowsFromXlsx($file->getRealPath())
            : $this->staffRowsFromCsv($file->getRealPath());

        if ($rows === []) {
            return back()->withErrors(['staff_file' => 'No staff rows were found. Include name, IC, position, email, and department columns or recognized department headings.']);
        }

        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];
        DB::transaction(function () use ($rows, &$result): void {
            foreach ($rows as $row) {
                $nric = Nric::normalize($row['ic_no'] ?? '');
                $name = trim((string) ($row['full_name'] ?? ''));
                $department = $this->departmentKey((string) ($row['department'] ?? ''));
                if ($nric === '' || $name === '' || $department === null) {
                    $result['skipped']++;
                    $result['errors'][] = 'Skipped a row with a missing name, IC, or recognized department.';

                    continue;
                }

                $existing = DB::table('admins')->where('ic_no', $nric)->first();
                if ($existing && $existing->role !== 'lecturer') {
                    $result['skipped']++;
                    $result['errors'][] = "{$name}: IC is already assigned to a non-staff administrator.";

                    continue;
                }

                $email = filled($row['email'] ?? null) ? strtolower(trim((string) $row['email'])) : null;
                if ($email && DB::table('admins')->where('email', $email)->when($existing, fn ($query) => $query->where('id', '!=', $existing->id))->exists()) {
                    $result['skipped']++;
                    $result['errors'][] = "{$name}: email is already assigned to another account.";

                    continue;
                }

                $payload = [
                    'full_name' => Str::limit(Str::upper($name), 150, ''),
                    'email' => $email,
                    'staff_category' => 'general',
                    'staff_department' => $department,
                    'reporting_branch' => ProgramApprovalRouting::inferBranch($department, $row['position'] ?? null),
                    'position' => filled($row['position'] ?? null) ? Str::limit(trim((string) $row['position']), 180, '') : null,
                    'is_active' => true,
                    'updated_at' => now(),
                ];

                if ($existing) {
                    DB::table('admins')->where('id', $existing->id)->update($payload);
                    $result['updated']++;
                } else {
                    DB::table('admins')->insert($payload + [
                        'ic_no' => $nric,
                        'password' => Hash::make('Staff@12345'),
                        'role' => 'lecturer',
                        'photo' => null,
                        'created_at' => now(),
                    ]);
                    $result['created']++;
                }
            }
        });

        $result['errors'] = array_slice(array_unique($result['errors']), 0, 10);
        auditLog('staff.import', 'admins', null, "Created {$result['created']}, updated {$result['updated']}, skipped {$result['skipped']} staff records");

        return redirect()->route('admin.staff.index')
            ->with('success', "Staff import completed: {$result['created']} created, {$result['updated']} updated, {$result['skipped']} skipped.")
            ->with('import_errors', $result['errors']);
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
        auditLog('staff.update', 'admins', $id, 'Updated lecturer/staff account with page access: '.implode(', ', $validated['lecturer_pages'] ?? []));

        return redirect()->route('admin.staff.index')->with('success', 'Staff account updated successfully.');
    }

    public function resetPassword(AccountSessionManager $sessions, int $id): RedirectResponse
    {
        $this->staffOrFail($id);
        $staff = $this->staffOrFail($id);
        DB::table('admins')->where('id', $id)->update(['password' => Hash::make($staff->ic_no), 'updated_at' => now()]);
        $sessions->revokeAccount('admin', $id);
        auditLog('staff.reset_password', 'admins', $id, 'Reset staff password');

        return redirect()->route('admin.staff.index')->with('success', 'Staff password reset to NRIC.');
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
            'departments' => self::DEPARTMENTS,
            'pageOptions' => $pages->allFor((int) ($account->id ?? 0)),
        ]);
    }

    private function validateAccount(Request $request, ?int $id = null, bool $passwordRequired = true): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'ic_no' => ['required', 'string', 'max:20', fn (string $attribute, string $value, \Closure $fail) => Nric::isAssignedToAdmin($value, $id) && $fail('This NRIC is already assigned to an existing admin or staff account.')],
            'email' => ['required', 'email', 'max:150', Rule::unique('admins', 'email')->ignore($id)],
            'staff_category' => ['required', Rule::in(array_keys(self::CATEGORIES))],
            'staff_department' => ['nullable', Rule::in(array_keys(self::DEPARTMENTS))],
            'position' => ['nullable', 'string', 'max:180'],
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
            'staff_department' => $validated['staff_department'] ?? null,
            'reporting_branch' => ProgramApprovalRouting::inferBranch($validated['staff_department'] ?? null, $validated['position'] ?? null),
            'position' => filled($validated['position'] ?? null) ? trim($validated['position']) : null,
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

    private function staffRowsFromCsv(string $path): array
    {
        $handle = fopen($path, 'rb');
        if (! $handle) {
            return [];
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if (! $this->rowIsBlank($row)) {
                $rows[] = array_map(fn ($value) => trim((string) $value), $row);
            }
        }
        fclose($handle);

        return $this->mapStaffRows($rows);
    }

    private function staffRowsFromXlsx(string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            return [];
        }

        $sharedStrings = $this->xlsxSharedStrings($zip);
        $rows = [];
        foreach ($this->xlsxSheetPaths($zip) as $sheetPath) {
            $sheetXml = $zip->getFromName($sheetPath);
            if ($sheetXml === false) {
                continue;
            }

            $sheet = simplexml_load_string($sheetXml);
            if (! $sheet || ! isset($sheet->sheetData->row)) {
                continue;
            }

            foreach ($sheet->sheetData->row as $rowNode) {
                $row = [];
                foreach ($rowNode->c as $cell) {
                    $attributes = $cell->attributes();
                    $index = $this->xlsxColumnIndex((string) ($attributes['r'] ?? ''));
                    $type = (string) ($attributes['t'] ?? '');
                    if ($type === 's') {
                        $value = $sharedStrings[(int) ($cell->v ?? 0)] ?? '';
                    } elseif ($type === 'inlineStr') {
                        $parts = [];
                        if (isset($cell->is->t)) {
                            $parts[] = (string) $cell->is->t;
                        }
                        foreach ($cell->is->r ?? [] as $run) {
                            $parts[] = (string) ($run->t ?? '');
                        }
                        $value = implode('', $parts);
                    } else {
                        $value = (string) ($cell->v ?? '');
                    }
                    $row[$index] = trim($value);
                }

                if (! $this->rowIsBlank($row)) {
                    ksort($row);
                    $rows[] = $row;
                }
            }
        }
        $zip->close();

        return $this->mapStaffRows($rows);
    }

    private function mapStaffRows(array $rows): array
    {
        $headerIndex = null;
        $columns = [];
        foreach ($rows as $index => $row) {
            foreach ($row as $column => $value) {
                $header = $this->normalizeImportText((string) $value);
                if ($header !== '' && ! array_key_exists($header, $columns)) {
                    $columns[$header] = $column;
                }
            }
            if ($this->firstColumn($columns, ['nama', 'nama penuh', 'full name', 'name']) !== null
                && $this->firstColumn($columns, ['no ic', 'no. ic', 'nric', 'ic no', 'ic number']) !== null) {
                $headerIndex = $index;
                break;
            }
            $columns = [];
        }

        if ($headerIndex === null) {
            return [];
        }

        $nameColumn = $this->firstColumn($columns, ['nama', 'nama penuh', 'full name', 'name']);
        $nricColumn = $this->firstColumn($columns, ['no ic', 'no. ic', 'nric', 'ic no', 'ic number']);
        $positionColumn = $this->firstColumn($columns, ['jawatan', 'position', 'position title']);
        $emailColumn = $this->firstColumn($columns, ['email', 'emel', 'e mail']);
        $departmentColumn = $this->firstColumn($columns, ['bahagian', 'jabatan', 'department', 'unit', 'section']);
        $currentDepartment = null;
        $staff = [];

        foreach (array_slice($rows, $headerIndex + 1) as $row) {
            $heading = $this->firstMeaningfulValue($row);
            $headingDepartment = $this->departmentKey($heading);
            $nric = trim((string) ($row[$nricColumn] ?? ''));
            $name = trim((string) ($row[$nameColumn] ?? ''));
            if ($headingDepartment !== null && preg_replace('/[^0-9]/', '', $nric) === '') {
                $currentDepartment = $headingDepartment;

                continue;
            }

            $departmentValue = $departmentColumn === null ? '' : trim((string) ($row[$departmentColumn] ?? ''));
            $department = $this->departmentKey($departmentValue) ?? $currentDepartment;
            if ($name === '' && $nric === '') {
                continue;
            }
            $staff[] = [
                'full_name' => $name,
                'ic_no' => $nric,
                'position' => $positionColumn === null ? '' : trim((string) ($row[$positionColumn] ?? '')),
                'email' => $emailColumn === null ? '' : trim((string) ($row[$emailColumn] ?? '')),
                'department' => $department,
            ];
        }

        return $staff;
    }

    private function departmentKey(string $value): ?string
    {
        $value = $this->normalizeImportText($value);
        if ($value === '') {
            return null;
        }

        return match (true) {
            str_contains($value, 'pejabat pengarah') => 'pejabat_pengarah',
            str_contains($value, 'jtmk'), str_contains($value, 'teknologi maklumat') => 'jtmk',
            str_contains($value, 'jpa'), str_contains($value, 'pengajian am') => 'jpa',
            str_contains($value, 'jrkv'), str_contains($value, 'reka bentuk') => 'jrkv',
            str_contains($value, 'jmsk'), str_contains($value, 'matematik sains') => 'jmsk',
            str_contains($value, 'khidmat pengurusan') => 'unit_khidmat_pengurusan',
            str_contains($value, 'pengurusan kewangan') => 'unit_pengurusan_kewangan',
            array_key_exists($value, self::DEPARTMENTS) => $value,
            default => null,
        };
    }

    private function normalizeImportText(string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
        $value = Str::lower(Str::ascii(trim($value)));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }

    private function firstColumn(array $columns, array $aliases): int|string|null
    {
        foreach ($aliases as $alias) {
            $key = $this->normalizeImportText($alias);
            if (array_key_exists($key, $columns)) {
                return $columns[$key];
            }
        }

        return null;
    }

    private function firstMeaningfulValue(array $row): string
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return '';
    }

    private function rowIsBlank(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function xlsxSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $shared = simplexml_load_string($xml);
        if (! $shared) {
            return [];
        }

        $strings = [];
        foreach ($shared->si as $item) {
            if (isset($item->t)) {
                $strings[] = (string) $item->t;

                continue;
            }
            $parts = [];
            foreach ($item->r as $run) {
                $parts[] = (string) ($run->t ?? '');
            }
            $strings[] = implode('', $parts);
        }

        return $strings;
    }

    private function xlsxSheetPaths(ZipArchive $zip): array
    {
        $workbookXml = $zip->getFromName('xl/workbook.xml');
        $relsXml = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($workbookXml === false || $relsXml === false) {
            return $zip->locateName('xl/worksheets/sheet1.xml') !== false ? ['xl/worksheets/sheet1.xml'] : [];
        }

        $workbook = simplexml_load_string($workbookXml);
        $rels = simplexml_load_string($relsXml);
        if (! $workbook || ! $rels) {
            return [];
        }

        $workbook->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $targets = [];
        foreach ($rels->Relationship as $relationship) {
            $attributes = $relationship->attributes();
            $target = ltrim((string) $attributes['Target'], '/');
            $targets[(string) $attributes['Id']] = Str::startsWith($target, 'xl/') ? $target : 'xl/'.$target;
        }

        $paths = [];
        foreach ($workbook->xpath('//main:sheets/main:sheet') ?: [] as $sheet) {
            $relationId = (string) $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
            if (isset($targets[$relationId])) {
                $paths[] = $targets[$relationId];
            }
        }

        return $paths;
    }

    private function xlsxColumnIndex(string $cellRef): int
    {
        preg_match('/^[A-Z]+/i', $cellRef, $matches);
        $index = 0;
        foreach (str_split(strtoupper($matches[0] ?? 'A')) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }
}
