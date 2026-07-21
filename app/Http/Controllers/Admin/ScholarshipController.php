<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use ZipArchive;

class ScholarshipController extends Controller
{
    private const B40_TVET_PROVIDER = 'SCHOLARSHIP B40 TVET';
    private const B40_TVET_INSTITUTION = 'POLITEKNIK BESUT';

    public function index(Request $request): View
    {
        $filters = $this->validateFilters($request);
        $records = $this->filteredQuery($filters)
            ->select(
                'scholarships.id',
                'scholarships.type',
                'scholarships.provider_name',
                'scholarships.amount',
                'scholarships.status',
                'scholarships.proof_file',
                'scholarships.created_at',
                'students.full_name as student_name',
                'students.matric_no'
            )
            ->orderByDesc('scholarships.created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.scholarships.index', compact('records', 'filters'));
    }

    public function b40Tvet(Request $request): View
    {
        $filters = $this->validateB40Filters($request);
        $query = $this->b40TvetQuery($filters);

        $records = (clone $query)
            ->select(
                'scholarships.id',
                'scholarships.amount',
                'scholarships.status',
                'scholarships.created_at',
                'students.full_name as student_name',
                'students.matric_no',
                'students.ic_no',
                'students.program',
                'students.phone'
            )
            ->orderBy('students.full_name')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => (clone $query)->count(),
            'confirmed' => (clone $query)->where('scholarships.status', 'confirmed')->count(),
            'pending' => (clone $query)->where('scholarships.status', 'pending')->count(),
        ];

        return view('admin.scholarships.b40_tvet', compact('records', 'filters', 'stats'));
    }

    public function importB40Tvet(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_file' => ['required', 'file', 'max:10240', 'mimes:csv,txt,xlsx'],
        ]);

        $uploadedFile = $validated['student_file'];
        $extension = strtolower($uploadedFile->getClientOriginalExtension());

        try {
            $rows = $this->readImportRows($uploadedFile->getRealPath(), $extension);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages([
                'student_file' => 'Fail tidak dapat dibaca. Sila upload CSV atau XLSX yang sah.',
            ]);
        }

        if ($rows === []) {
            throw ValidationException::withMessages([
                'student_file' => 'Fail kosong atau tiada header.',
            ]);
        }

        $result = $this->importB40Rows($rows);
        auditLog('scholarships.b40_tvet_import', 'scholarships', null, json_encode($result));

        return redirect()->route('admin.scholarships.b40-tvet')
            ->with('success', 'Import selesai.')
            ->with('import_result', $result);
    }

    public function exportB40Tvet(Request $request)
    {
        $filters = $this->validateB40Filters($request);
        $rows = $this->b40TvetQuery($filters)
            ->select(
                'students.full_name',
                'students.matric_no',
                'students.ic_no',
                'students.program',
                'students.phone',
                'scholarships.amount',
                'scholarships.status',
                'scholarships.created_at'
            )
            ->orderBy('students.full_name')
            ->get()
            ->map(fn ($record) => [
                $record->full_name,
                $record->matric_no,
                $record->ic_no,
                $record->program,
                $record->phone ?? '',
                $record->amount !== null ? number_format((float) $record->amount, 2, '.', '') : '',
                $record->status,
                $record->created_at,
            ]);

        return downloadCsv(
            'scholarship_b40_tvet_politeknik_besut_' . now()->format('Ymd_His') . '.csv',
            ['Nama', 'No Matrik', 'No IC', 'Program', 'Telefon', 'Jumlah (RM)', 'Status', 'Tarikh Import'],
            $rows
        );
    }

    public function export(Request $request)
    {
        $filters = $this->validateFilters($request);
        $rows = $this->filteredQuery($filters)
            ->select(
                'scholarships.id',
                'students.full_name as student_name',
                'students.matric_no',
                'scholarships.type',
                'scholarships.provider_name',
                'scholarships.amount',
                'scholarships.status',
                'scholarships.created_at'
            )
            ->orderByDesc('scholarships.created_at')
            ->get()
            ->map(fn ($record) => [
                $record->id,
                $record->student_name,
                $record->matric_no,
                $record->type,
                $record->provider_name ?? '',
                $record->amount !== null ? number_format((float) $record->amount, 2, '.', '') : '',
                $record->status,
                $record->created_at,
            ]);

        return downloadCsv(
            'scholarships_' . now()->format('Ymd_His') . '.csv',
            ['ID', 'Pelajar', 'No Matrik', 'Jenis', 'Penyedia', 'Jumlah (RM)', 'Status', 'Tarikh Rekod'],
            $rows
        );
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('admin.scholarships.index')
            ->withErrors(['scholarship' => 'Add Record is unavailable for this module.']);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateScholarship($request);

        DB::table('scholarships')->insert([
            'student_id' => $validated['student_id'],
            'type' => $validated['type'],
            'provider_name' => $validated['provider_name'] ?? null,
            'amount' => $validated['amount'] ?? null,
            'status' => $validated['status'],
            'proof_file' => $validated['proof_file'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.scholarships.index')
            ->with('success', __('Rekod scholarship berjaya ditambah.'));
    }

    public function edit(int $id): View|RedirectResponse
    {
        $record = DB::table('scholarships')->where('id', $id)->first();
        if (!$record) {
            return $this->notFoundRedirect();
        }

        $selectedStudent = DB::table('students')
            ->select('id', 'full_name', 'matric_no')
            ->where('id', (int) old('student_id', $record->student_id))
            ->first();

        return view('admin.scholarships.edit', compact('record', 'selectedStudent'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $record = DB::table('scholarships')->where('id', $id)->first();
        if (!$record) {
            return $this->notFoundRedirect();
        }

        $validated = $this->validateScholarship($request);

        DB::table('scholarships')
            ->where('id', $id)
            ->update([
                'student_id' => $validated['student_id'],
                'type' => $validated['type'],
                'provider_name' => $validated['provider_name'] ?? null,
                'amount' => $validated['amount'] ?? null,
                'status' => $validated['status'],
                'proof_file' => $validated['proof_file'] ?? null,
                'updated_at' => now(),
            ]);

        return redirect()->route('admin.scholarships.index')
            ->with('success', __('Rekod scholarship berjaya dikemaskini.'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $deleted = DB::table('scholarships')->where('id', $id)->delete();
        if (!$deleted) {
            return $this->notFoundRedirect();
        }

        return redirect()->route('admin.scholarships.index')
            ->with('success', __('Rekod scholarship berjaya dipadam.'));
    }

    private function validateFilters(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'type' => ['nullable', Rule::in(['scholarship', 'welfare', 'sponsorship', 'none'])],
            'status' => ['nullable', Rule::in(['pending', 'confirmed', 'rejected'])],
        ]);
    }

    private function validateB40Filters(Request $request): array
    {
        return $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['pending', 'confirmed', 'rejected'])],
        ]);
    }

    private function b40TvetQuery(array $filters)
    {
        $query = DB::table('scholarships')
            ->join('students', 'students.id', '=', 'scholarships.student_id')
            ->where('scholarships.provider_name', self::B40_TVET_PROVIDER);

        if (!empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($sub) use ($q) {
                $sub->where('students.full_name', 'like', "%{$q}%")
                    ->orWhere('students.matric_no', 'like', "%{$q}%")
                    ->orWhere('students.ic_no', 'like', "%{$q}%")
                    ->orWhere('students.program', 'like', "%{$q}%");
            });
        }

        if (!empty($filters['status'])) {
            $query->where('scholarships.status', $filters['status']);
        }

        return $query;
    }

    private function filteredQuery(array $filters)
    {
        $query = DB::table('scholarships')
            ->join('students', 'students.id', '=', 'scholarships.student_id');

        if (!empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($sub) use ($q) {
                $sub->where('students.full_name', 'like', "%{$q}%")
                    ->orWhere('students.matric_no', 'like', "%{$q}%")
                    ->orWhere('scholarships.provider_name', 'like', "%{$q}%");
            });
        }

        if (!empty($filters['type'])) {
            $query->where('scholarships.type', $filters['type']);
        }

        if (!empty($filters['status'])) {
            $query->where('scholarships.status', $filters['status']);
        }

        return $query;
    }

    private function validateScholarship(Request $request): array
    {
        return $request->validate([
            'student_id' => ['required', 'integer', 'exists:students,id'],
            'type' => ['required', Rule::in(['scholarship', 'welfare', 'sponsorship', 'none'])],
            'provider_name' => ['nullable', 'string', 'max:150'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['pending', 'confirmed', 'rejected'])],
            'proof_file' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function notFoundRedirect(): RedirectResponse
    {
        return redirect()->route('admin.scholarships.index')
            ->withErrors(['scholarship' => 'Rekod scholarship tidak dijumpai.']);
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
                    $value = '';

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
            $target = (string) $attributes['Target'];
            $target = ltrim($target, '/');
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
        $dataRows = array_slice($rows, $headerIndex + 1);
        $mappedRows = [];

        foreach ($dataRows as $row) {
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
            $hasIc = $this->headerContainsAny($headers, ['no kad pengenalan', 'no ic', 'ic no', 'ic number', 'mykad']);
            $hasInstitution = $this->headerContainsAny($headers, ['institusi', 'institution', 'nama institusi', 'kampus', 'campus', 'ipt']);

            if ($hasName && $hasIc && $hasInstitution) {
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

    private function importB40Rows(array $rows): array
    {
        $result = [
            'total_rows' => count($rows),
            'matched_politeknik_besut' => 0,
            'students_created' => 0,
            'students_updated' => 0,
            'scholarships_created' => 0,
            'scholarships_updated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        $studentColumns = collect(Schema::getColumnListing('students'))->flip();
        $now = now();

        DB::transaction(function () use ($rows, &$result, $studentColumns, $now) {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                if (!$this->isPoliteknikBesutRow($row)) {
                    continue;
                }

                $result['matched_politeknik_besut']++;

                $fullName = $this->rowValue($row, ['nama pelajar', 'nama penuh', 'nama', 'student name', 'full name', 'name']);
                $icNo = $this->cleanIdentity($this->rowValue($row, ['no ic', 'no. ic', 'no kad pengenalan', 'nombor kad pengenalan', 'ic no', 'ic number', 'mykad', 'kad pengenalan']));
                $program = $this->rowValue($row, ['program', 'nama program', 'kursus', 'course']) ?: 'UNKNOWN';

                if ($fullName === '' || $icNo === '') {
                    $result['skipped']++;
                    $result['errors'][] = "Row {$rowNumber}: nama atau no IC tidak lengkap.";
                    continue;
                }

                $student = DB::table('students')
                    ->where('ic_no', $icNo)
                    ->first();

                $studentPayload = [
                    'full_name' => Str::upper($fullName),
                    'matric_no' => null,
                    'ic_no' => $icNo,
                    'program' => Str::upper($program),
                    'updated_at' => $now,
                ];

                foreach ([
                    'email' => ['email', 'emel', 'e-mail'],
                    'phone' => ['telefon', 'no telefon', 'phone', 'phone no', 'mobile'],
                    'semester' => ['semester', 'sem'],
                    'academic_session' => ['sesi akademik', 'academic session', 'session'],
                    'race' => ['bangsa', 'race'],
                    'religion' => ['agama', 'religion'],
                    'address' => ['alamat', 'address'],
                ] as $column => $aliases) {
                    $value = $this->rowValue($row, $aliases);
                    if ($value !== '' && $studentColumns->has($column)) {
                        $studentPayload[$column] = $column === 'email' ? strtolower($value) : $value;
                    }
                }

                if (!$student && $studentColumns->has('residence_status')) {
                    $studentPayload['residence_status'] = 'inside_campus';
                }

                try {
                    if ($student) {
                        DB::table('students')->where('id', $student->id)->update($studentPayload);
                        $studentId = (int) $student->id;
                        $result['students_updated']++;
                    } else {
                        $studentPayload['created_at'] = $now;
                        $studentId = (int) DB::table('students')->insertGetId($studentPayload);
                        $result['students_created']++;
                    }

                    $amount = $this->parseAmount($this->rowValue($row, ['jumlah', 'amaun', 'amount', 'nilai', 'rm']));
                    $existingScholarship = DB::table('scholarships')
                        ->where('student_id', $studentId)
                        ->where('provider_name', self::B40_TVET_PROVIDER)
                        ->first();

                    $scholarshipPayload = [
                        'student_id' => $studentId,
                        'type' => 'scholarship',
                        'provider_name' => self::B40_TVET_PROVIDER,
                        'amount' => $amount,
                        'status' => 'confirmed',
                        'proof_file' => null,
                        'updated_at' => $now,
                    ];

                    if ($existingScholarship) {
                        DB::table('scholarships')->where('id', $existingScholarship->id)->update($scholarshipPayload);
                        $result['scholarships_updated']++;
                    } else {
                        $scholarshipPayload['created_at'] = $now;
                        DB::table('scholarships')->insert($scholarshipPayload);
                        $result['scholarships_created']++;
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

    private function isPoliteknikBesutRow(array $row): bool
    {
        $institution = $this->rowValue($row, ['institusi', 'institution', 'nama institusi', 'kampus', 'campus', 'ipt']);
        $haystack = $institution !== '' ? $institution : implode(' ', array_values($row));

        return str_contains($this->normalizeSearchText($haystack), self::B40_TVET_INSTITUTION);
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

    private function parseAmount(string $value): ?float
    {
        $normalized = preg_replace('/[^0-9.]/', '', str_replace(',', '', $value));

        return $normalized === '' ? null : round((float) $normalized, 2);
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
