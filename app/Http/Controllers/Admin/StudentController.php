<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AccountSessionManager;
use App\Support\ProgramIdentifier;
use App\Support\StudentClassIdentifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use ZipArchive;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $canViewSensitiveStudents = adminCan('students.sensitive');
        $studentStats = ['total' => DB::table('students')->count()];
        if ($canViewSensitiveStudents) {
            $studentStats['default_ic'] = DB::table('students')->whereNull('password')->count();
            $studentStats['custom_password'] = DB::table('students')->whereNotNull('password')->count();
        }

        $filters = $this->validateFilters($request, $canViewSensitiveStudents);
        $studentsQuery = $this->filteredStudentsQuery($filters, $canViewSensitiveStudents);
        if ($canViewSensitiveStudents) {
            $studentsQuery
                ->select('id', 'full_name', 'matric_no', 'ic_no', 'program', 'phone', 'photo', 'created_at')
                ->selectRaw('CASE WHEN password IS NULL THEN 0 ELSE 1 END as has_custom_password');
        } else {
            $studentsQuery->select('id', 'full_name', 'matric_no', 'program', 'photo', 'created_at');
        }

        $students = $studentsQuery->orderBy('full_name')->paginate(15)->withQueryString();

        return view('admin.students.index', compact('students', 'filters', 'studentStats', 'canViewSensitiveStudents'));
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
        ]);

        $q = trim((string) ($validated['q'] ?? ''));
        if ($q === '') {
            return response()->json(['data' => []]);
        }

        $students = DB::table('students')
            ->select('id', 'full_name', 'matric_no')
            ->where(function ($sub) use ($q) {
                $sub->where('full_name', 'like', "%{$q}%")
                    ->orWhere('matric_no', 'like', "%{$q}%");
            })
            ->orderBy('full_name')
            ->limit(20)
            ->get();

        return response()->json(['data' => $students]);
    }

    public function export(Request $request)
    {
        $filters = $this->validateFilters($request);
        $rows = $this->filteredStudentsQuery($filters)
            ->select('id', 'full_name', 'matric_no', 'ic_no', 'program', 'phone', 'created_at')
            ->selectRaw('CASE WHEN password IS NULL THEN "default_ic" ELSE "custom_password" END as password_status')
            ->orderBy('full_name')
            ->get()
            ->map(fn ($student) => [
                $student->id,
                $student->full_name,
                $student->matric_no,
                maskIdentityNumber($student->ic_no),
                $student->program,
                $student->phone ?? '',
                $student->password_status,
                $student->created_at,
            ]);

        auditLog('students.export', 'students', null, 'Exported filtered student records');
        $response = downloadCsv(
            'students_' . now()->format('Ymd_His') . '.csv',
            ['ID', 'Nama', 'No Matrik', 'No IC', 'Program', 'Telefon', 'Status Kata Laluan', 'Tarikh Daftar'],
            $rows
        );
        $response->headers->set('Cache-Control', 'private, no-store, max-age=0');

        return $response;
    }

    public function create()
    {
        return view('admin.students.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateStudent($request);

        DB::table('students')->insert([
            'full_name' => $validated['full_name'],
            'matric_no' => filled($validated['matric_no'] ?? null) ? $validated['matric_no'] : null,
            'ic_no' => $validated['ic_no'],
            'email' => $validated['email'] ?? null,
            'program' => ProgramIdentifier::from($validated['matric_no'] ?? null, $validated['program']),
            'phone' => $validated['phone'] ?? null,
            'address' => $validated['address'] ?? null,
            'residence_status' => $validated['residence_status'],
            'room_number' => $validated['residence_status'] === 'inside_campus'
                ? ($validated['room_number'] ?? null)
                : null,
            'photo' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.students.index')
            ->with('success', __('Pelajar berjaya ditambah.'));
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_file' => ['required', 'file', 'max:51200', 'mimes:csv,txt,xlsx'],
        ], [
            'student_file.uploaded' => __('students.import.upload_failed'),
            'student_file.max' => __('students.import.too_large'),
            'student_file.mimes' => __('students.import.unsupported_format'),
        ]);

        $uploadedFile = $validated['student_file'];
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        try {
            $rows = $this->readImportRows($uploadedFile->getRealPath(), $extension);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'student_file' => __('students.import.unreadable'),
            ]);
        }

        if ($rows === []) {
            throw ValidationException::withMessages([
                'student_file' => __('students.import.empty_or_missing_headers'),
            ]);
        }

        $result = $this->importStudentRows($rows);
        auditLog('students.import', 'students', null, json_encode($result));

        return redirect()->route('admin.students.index')
            ->with('success', __('students.import.completed'))
            ->with('import_result', $result);
    }

    public function importPage(): View
    {
        $studentStats = [
            'total' => DB::table('students')->count(),
            'default_ic' => DB::table('students')->whereNull('password')->count(),
            'custom_password' => DB::table('students')->whereNotNull('password')->count(),
        ];
        $filters = [];
        $students = DB::table('students')
            ->select('id', 'full_name', 'matric_no', 'ic_no', 'program', 'phone', 'created_at')
            ->selectRaw('CASE WHEN password IS NULL THEN 0 ELSE 1 END as has_custom_password')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.students.index', compact('students', 'filters', 'studentStats'));
    }

    public function edit(int $id)
    {
        $student = DB::table('students')->where('id', $id)->first();
        if (!$student) {
            return $this->studentNotFoundRedirect();
        }

        return view('admin.students.edit', compact('student'));
    }

    public function show(int $id): View|RedirectResponse
    {
        $student = DB::table('students')
            ->select([
                'id', 'full_name', 'matric_no', 'ic_no', 'email', 'phone', 'program', 'class_name', 'photo',
                'semester', 'academic_session', 'date_of_birth', 'religion', 'race', 'parliament', 'dun',
                'address', 'study_address', 'residence_status', 'room_number', 'guardian_name',
                'guardian_ic_no', 'guardian_phone', 'mother_ic_no', 'guardian_occupation',
                'family_income', 'guardian_address',
            ])
            ->where('id', $id)
            ->first();
        if (!$student) {
            return $this->studentNotFoundRedirect();
        }

        $latestMovements = DB::table('student_movements')
            ->join('movement_types', 'movement_types.id', '=', 'student_movements.movement_type_id')
            ->join('movement_checkpoints', 'movement_checkpoints.id', '=', 'student_movements.checkpoint_id')
            ->where('student_movements.student_id', $id)
            ->select('student_movements.*', 'movement_types.name as movement_type_name', 'movement_checkpoints.name as checkpoint_name')
            ->orderByDesc('student_movements.checkout_at')
            ->limit(8)
            ->get();

        return view('admin.students.show', compact('student', 'latestMovements'));
    }

    public function update(Request $request, int $id)
    {
        $student = DB::table('students')->where('id', $id)->first();
        if (!$student) {
            return $this->studentNotFoundRedirect();
        }

        $validated = $this->validateStudent($request, $id);

        DB::table('students')
            ->where('id', $id)
            ->update([
                'full_name' => $validated['full_name'],
                'matric_no' => filled($validated['matric_no'] ?? null) ? $validated['matric_no'] : null,
                'ic_no' => $validated['ic_no'],
                'email' => $validated['email'] ?? null,
                'program' => ProgramIdentifier::from($validated['matric_no'] ?? null, $validated['program']),
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'residence_status' => $validated['residence_status'],
                'room_number' => $validated['residence_status'] === 'inside_campus'
                    ? ($validated['room_number'] ?? null)
                    : null,
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.students.index')
            ->with('success', __('Maklumat pelajar berjaya dikemaskini.'));
    }

    public function destroy(AccountSessionManager $sessions, int $id)
    {
        $documentPaths = Schema::hasTable('student_documents')
            ? DB::table('student_documents')->where('student_id', $id)->pluck('path')
            : collect();
        $deleted = DB::table('students')->where('id', $id)->delete();
        if (!$deleted) {
            return $this->studentNotFoundRedirect();
        }
        if (Schema::hasTable('student_documents')) {
            DB::table('student_documents')->where('student_id', $id)->delete();
            foreach ($documentPaths as $path) {
                Storage::disk('student_documents')->delete($path);
            }
        }
        $sessions->revokeAccount('student', $id);
        auditLog('students.delete', 'students', $id, 'Padam rekod pelajar');

        return redirect()->route('admin.students.index')
            ->with('success', __('Rekod pelajar berjaya dipadam.'));
    }

    public function destroyAll(Request $request, AccountSessionManager $sessions): RedirectResponse
    {
        $request->validate([
            'confirmation' => ['required', 'in:DELETE ALL STUDENTS'],
        ]);

        $students = DB::table('students')->select('id', 'photo')->get();
        if ($students->isEmpty()) {
            return redirect()->route('admin.students.index')->with('success', __('No student records to delete.'));
        }

        $studentIds = $students->pluck('id');
        $documentPaths = Schema::hasTable('student_documents')
            ? DB::table('student_documents')->whereIn('student_id', $studentIds)->pluck('path')
            : collect();

        DB::transaction(function () use ($studentIds): void {
            foreach (['student_scholarship_status_forms', 'student_movements', 'student_documents', 'scholarships', 'offenses', 'fine_payment_applications'] as $table) {
                if (Schema::hasTable($table)) {
                    DB::table($table)->whereIn('student_id', $studentIds)->delete();
                }
            }

            if (Schema::hasTable('push_subscriptions')) {
                DB::table('push_subscriptions')->where('user_type', 'student')->whereIn('user_id', $studentIds)->delete();
            }

            if (Schema::hasTable('password_reset_codes')) {
                DB::table('password_reset_codes')->where('role', 'student')->whereIn('target_id', $studentIds)->delete();
            }

            if (Schema::hasTable('audit_logs')) {
                DB::table('audit_logs')->where('target_type', 'student')->whereIn('target_id', $studentIds)->delete();
            }

            DB::table('students')->whereIn('id', $studentIds)->delete();
        });

        foreach ($students as $student) {
            $sessions->revokeAccount('student', (int) $student->id);
            if (filled($student->photo)) {
                Storage::disk('public')->delete($student->photo);
            }
        }
        foreach ($documentPaths as $path) {
            Storage::disk('student_documents')->delete($path);
        }

        auditLog('students.delete_all', 'students', null, "Deleted {$students->count()} student records and related data");

        return redirect()->route('admin.students.index')
            ->with('success', __('Deleted :count student records and their related data.', ['count' => $students->count()]));
    }

    public function resetPassword(AccountSessionManager $sessions, int $id)
    {
        $student = DB::table('students')->where('id', $id)->first();
        if (!$student) {
            return $this->studentNotFoundRedirect();
        }

        DB::table('students')
            ->where('id', $id)
            ->update([
                // Null password means fallback login to IC is enabled.
                'password' => null,
                'updated_at' => now(),
            ]);
        $sessions->revokeAccount('student', $id);
        auditLog('students.reset_password', 'students', $id, 'Reset kata laluan pelajar kepada IC');

        myhepSendPushNotification('student', $id, [
            'category' => 'account',
            'title' => 'Password reset by administrator',
            'body' => 'An administrator reset your StudentEdge password. Contact the system administrator if this was unexpected.',
            'url' => route('login'),
            'tag' => 'student-admin-password-reset-' . $id,
            'requireInteraction' => true,
        ]);

        return redirect()->route('admin.students.index')
            ->with('success', __('Kata laluan pelajar telah direset kepada No. IC.'));
    }

    private function validateFilters(Request $request, bool $allowSensitive = true): array
    {
        $rules = [
            'q' => ['nullable', 'string', 'max:150'],
            'matric_no' => ['nullable', 'string', 'max:30'],
            'program' => ['nullable', 'string', 'max:100'],
        ];
        if ($allowSensitive) {
            $rules['password_status'] = ['nullable', Rule::in(['default', 'custom'])];
        }

        return $request->validate($rules);
    }

    private function filteredStudentsQuery(array $filters, bool $allowSensitive = true)
    {
        $query = DB::table('students');

        if (!empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($sub) use ($q, $allowSensitive) {
                $sub->where('full_name', 'like', "%{$q}%")
                    ->orWhere('matric_no', 'like', "%{$q}%");
                if ($allowSensitive) {
                    $sub->orWhere('ic_no', 'like', "%{$q}%");
                }
            });
        }

        if (!empty($filters['matric_no'])) {
            $query->where('matric_no', 'like', '%' . $this->cleanIdentity($filters['matric_no']) . '%');
        }

        if (!empty($filters['program'])) {
            $query->where('program', 'like', '%' . trim($filters['program']) . '%');
        }

        if (!empty($filters['password_status'])) {
            if ($filters['password_status'] === 'custom') {
                $query->whereNotNull('password');
            } else {
                $query->whereNull('password');
            }
        }

        return $query;
    }

    private function validateStudent(Request $request, ?int $id = null): array
    {
        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:150'],
            'matric_no' => ['nullable', 'string', 'max:20', Rule::unique('students', 'matric_no')->ignore($id)],
            'ic_no' => ['required', 'string', 'max:20', Rule::unique('students', 'ic_no')->ignore($id)],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('students', 'email')->ignore($id)],
            'program' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'residence_status' => ['required', Rule::in(['inside_campus', 'live_out'])],
            'room_number' => ['nullable', 'string', 'max:30'],
        ]);

        return $validated;
    }

    private function studentNotFoundRedirect()
    {
        return redirect()->route('admin.students.index')
            ->withErrors(['student' => __('Rekod pelajar tidak dijumpai.')]);
    }

    private function readImportRows(string $path, string $extension): array
    {
        return $extension === 'xlsx'
            ? $this->readXlsxRows($path)
            : $this->readCsvRows($path);
    }

    private function readCsvRows(string $path): array
    {
        $handle = fopen($path, 'rb');
        if (!$handle) {
            return [];
        }

        $rows = [];
        while (($row = fgetcsv($handle)) !== false) {
            if ($row === [null] || $this->rowIsBlank($row)) {
                continue;
            }
            $rows[] = array_map(fn ($value) => trim((string) $value), $row);
        }
        fclose($handle);

        return $this->rowsWithHeaders($rows);
    }

    private function readXlsxRows(string $path): array
    {
        $zip = new ZipArchive();
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
            if (!$sheet || !isset($sheet->sheetData->row)) {
                continue;
            }

            foreach ($sheet->sheetData->row as $rowNode) {
                $row = [];
                foreach ($rowNode->c as $cell) {
                    $attributes = $cell->attributes();
                    $cellRef = (string) ($attributes['r'] ?? '');
                    $type = (string) ($attributes['t'] ?? '');
                    $index = $this->xlsxColumnIndex($cellRef);

                    if ($type === 's') {
                        $value = $sharedStrings[(int) ($cell->v ?? 0)] ?? '';
                    } elseif ($type === 'inlineStr') {
                        $value = (string) ($cell->is->t ?? '');
                    } else {
                        $value = (string) ($cell->v ?? '');
                    }

                    $row[$index] = trim($value);
                }

                if (!$this->rowIsBlank($row)) {
                    ksort($row);
                    $rows[] = $row;
                }
            }
        }
        $zip->close();

        return $this->rowsWithHeaders($rows);
    }

    private function importStudentRows(array $rows): array
    {
        $result = [
            'total_rows' => count($rows),
            'students_created' => 0,
            'students_updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $studentColumns = collect(Schema::getColumnListing('students'))->flip();
        $now = now();

        DB::transaction(function () use ($rows, &$result, $studentColumns, $now) {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                $fullName = $this->rowValue($row, ['nama pelajar', 'nama penuh', 'nama', 'student name', 'full name', 'name']);
                $icNo = $this->cleanIdentity($this->rowValue($row, ['no ic', 'no. ic', 'no kp', 'no. kp', 'nombor kp', 'no kad pengenalan', 'nombor kad pengenalan', 'ic no', 'ic number', 'mykad', 'kad pengenalan']));
                $matricNo = $this->cleanIdentity($this->rowValue($row, ['no matrik', 'no. matrik', 'nombor matrik', 'no pend', 'no. pend', 'matric no', 'matric number', 'id pelajar', 'student id', 'no pendaftaran', 'nombor pendaftaran']));
                $className = StudentClassIdentifier::normalize($this->rowValue($row, ['kelas', 'class', 'class name']));
                $program = $this->rowValue($row, ['program', 'nama program', 'kursus', 'kod kursus', 'course', 'course code'])
                    ?: $className
                    ?: 'UNKNOWN';

                if ($fullName === '' || $icNo === '') {
                    $result['skipped']++;
                    $result['errors'][] = "Row {$rowNumber}: nama atau no IC tidak lengkap.";
                    continue;
                }

                $studentQuery = DB::table('students')->where('ic_no', $icNo);
                if ($matricNo !== '') {
                    $studentQuery->orWhere('matric_no', $matricNo);
                }
                $student = $studentQuery->first();

                $payload = [
                    'full_name' => Str::upper($fullName),
                    'matric_no' => $matricNo !== '' ? $matricNo : null,
                    'ic_no' => $icNo,
                    'program' => ProgramIdentifier::from($matricNo, $program),
                    'updated_at' => $now,
                ];

                foreach ([
                    'email' => ['email', 'emel', 'e-mail'],
                    'phone' => ['telefon', 'no telefon', 'phone', 'phone no', 'mobile'],
                    'semester' => ['semester', 'sem'],
                    'academic_session' => ['sesi akademik', 'sesi semasa', 'academic session', 'current session', 'session'],
                    'race' => ['bangsa', 'race'],
                    'religion' => ['agama', 'religion'],
                    'address' => ['alamat', 'address'],
                    'residence_status' => ['status kediaman', 'residence status'],
                    'room_number' => ['no bilik', 'room number', 'bilik'],
                ] as $column => $aliases) {
                    $value = $this->rowValue($row, $aliases);
                    if ($value !== '' && $studentColumns->has($column)) {
                        $payload[$column] = $column === 'email' ? strtolower($value) : $value;
                    }
                }

                if ($studentColumns->has('class_name') && $className !== null) {
                    $payload['class_name'] = $className;
                }
                if ($studentColumns->has('semester') && ($semester = StudentClassIdentifier::semester($className)) !== null) {
                    $payload['semester'] = $semester;
                }

                if (!$student && $studentColumns->has('residence_status') && empty($payload['residence_status'])) {
                    $payload['residence_status'] = 'inside_campus';
                }

                try {
                    if ($student) {
                        DB::table('students')->where('id', $student->id)->update($payload);
                        $result['students_updated']++;
                    } else {
                        $payload['created_at'] = $now;
                        DB::table('students')->insert($payload);
                        $result['students_created']++;
                    }
                } catch (\Throwable $e) {
                    $result['skipped']++;
                    $result['errors'][] = "Row {$rowNumber}: gagal import ({$e->getMessage()}).";
                }
            }
        });

        $result['errors'] = array_slice($result['errors'], 0, 20);

        return $result;
    }

    private function xlsxSharedStrings(ZipArchive $zip): array
    {
        $xml = $zip->getFromName('xl/sharedStrings.xml');
        if ($xml === false) {
            return [];
        }

        $strings = [];
        $shared = simplexml_load_string($xml);
        if (!$shared) {
            return [];
        }

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
        if (!$workbook || !$rels) {
            return [];
        }

        $workbook->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $workbook->registerXPathNamespace('r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
        $relationTargets = [];
        foreach ($rels->Relationship as $relationship) {
            $attributes = $relationship->attributes();
            $target = ltrim((string) $attributes['Target'], '/');
            $relationTargets[(string) $attributes['Id']] = Str::startsWith($target, 'xl/')
                ? $target
                : 'xl/' . $target;
        }

        $paths = [];
        foreach ($workbook->xpath('//main:sheets/main:sheet') ?: [] as $sheet) {
            $relationId = (string) $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
            if (isset($relationTargets[$relationId])) {
                $paths[] = $relationTargets[$relationId];
            }
        }

        return $paths;
    }

    private function xlsxColumnIndex(string $cellRef): int
    {
        preg_match('/^[A-Z]+/i', $cellRef, $matches);
        $letters = strtoupper($matches[0] ?? 'A');
        $index = 0;

        foreach (str_split($letters) as $letter) {
            $index = ($index * 26) + (ord($letter) - 64);
        }

        return max(0, $index - 1);
    }

    private function rowsWithHeaders(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $headerIndex = $this->detectHeaderRowIndex($rows);
        if ($headerIndex === null) {
            return [];
        }

        $headers = array_map(fn ($header) => $this->normalizeHeader((string) $header), $rows[$headerIndex]);
        $mappedRows = [];

        foreach (array_slice($rows, $headerIndex + 1) as $row) {
            $mapped = [];
            foreach ($headers as $index => $header) {
                if ($header !== '') {
                    $mapped[$header] = trim((string) ($row[$index] ?? ''));
                }
            }
            if (!$this->rowIsBlank($mapped)) {
                $mappedRows[] = $mapped;
            }
        }

        return $mappedRows;
    }

    private function detectHeaderRowIndex(array $rows): ?int
    {
        foreach ($rows as $index => $row) {
            $headers = array_map(fn ($header) => $this->normalizeHeader((string) $header), $row);
            $hasName = $this->headerContainsAny($headers, ['nama pelajar', 'nama penuh', 'nama', 'student name', 'full name']);
            $hasIc = $this->headerContainsAny($headers, ['no kad pengenalan', 'no ic', 'no kp', 'nombor kp', 'ic no', 'ic number', 'mykad']);

            if ($hasName && $hasIc) {
                return $index;
            }
        }

        return null;
    }

    private function headerContainsAny(array $headers, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (in_array($this->normalizeHeader($needle), $headers, true)) {
                return true;
            }
        }

        return false;
    }

    private function rowValue(array $row, array $aliases): string
    {
        foreach ($aliases as $alias) {
            $key = $this->normalizeHeader($alias);
            if (isset($row[$key]) && trim((string) $row[$key]) !== '') {
                return trim((string) $row[$key]);
            }
        }

        return '';
    }

    private function normalizeHeader(string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
        $value = Str::lower(trim($value));
        $value = preg_replace('/[^a-z0-9]+/i', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }

    private function normalizeSearchText(string $value): string
    {
        $value = Str::upper($value);
        $value = preg_replace('/[^A-Z0-9]+/', ' ', $value) ?? $value;

        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }

    private function cleanIdentity(string $value): string
    {
        return Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $value) ?? $value);
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
}
