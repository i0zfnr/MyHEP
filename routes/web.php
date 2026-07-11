<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\BugReportController as AdminBugReportController;
use App\Http\Controllers\Admin\MovementController as AdminMovementController;
use App\Http\Controllers\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BugReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\MovementController as StudentMovementController;
use App\Http\Controllers\Student\ProfileController;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

if (! function_exists('myhepCountTable')) {
    function myhepCountTable(string $table, ?callable $scope = null): int
    {
        if (!Schema::hasTable($table)) {
            return 0;
        }

        try {
            $query = DB::table($table);
            if ($scope) {
                $scope($query);
            }

            return (int) $query->count();
        } catch (Throwable $e) {
            return 0;
        }
    }
}

if (! function_exists('myhepHomeStatCounts')) {
    function myhepHomeStatCounts(): array
    {
        return systemCacheRemember('myhep.home_stats.counts', 120, function () {
            return [
                'students_managed' => myhepCountTable('students'),
                'open_actions' => myhepCountTable('scholarships', fn ($query) => $query->where('status', 'pending'))
                    + myhepCountTable('fine_payment_applications', fn ($query) => $query->where('status', 'pending'))
                    + myhepCountTable('vehicle_sticker_applications', fn ($query) => $query->where('status', 'pending')),
                'digital_records' => myhepCountTable('students')
                    + myhepCountTable('scholarships')
                    + myhepCountTable('offenses')
                    + myhepCountTable('student_scholarship_status_forms')
                    + myhepCountTable('fine_payment_applications')
                    + myhepCountTable('vehicle_sticker_applications'),
            ];
        });
    }
}

Route::get('/', function () {
    $counts = myhepHomeStatCounts();

    $homeStats = array_merge($counts, [
        'server_time' => now()->format('Y-m-d H:i:s'),
        'system_online' => true,
    ]);

    return view('welcome', compact('homeStats'));
})->name('home');
Route::get('/system-overview/live', function () {
    $counts = myhepHomeStatCounts();

    return response()->json([
        'data' => array_merge($counts, [
            'server_time' => now()->format('Y-m-d H:i:s'),
            'system_online' => true,
        ]),
    ]);
})->name('system-overview.live');
Route::post('/locale', function (Request $request) {
    $validated = $request->validate([
        'locale' => ['required', 'in:en,ms'],
    ]);

    $request->session()->put('locale', $validated['locale']);

    return redirect()->back();
})->name('locale.update');

Route::get('/report-problem', [BugReportController::class, 'create'])->name('bug-reports.create');
Route::post('/report-problem', [BugReportController::class, 'store'])
    ->middleware('throttle:6,10')
    ->name('bug-reports.store');

// Login Routes
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->name('login.submit');
Route::get('/password/forgot', [LoginController::class, 'forgotForm'])->name('password.forgot');
Route::post('/password/forgot', [LoginController::class, 'sendResetCode'])->name('password.forgot.send');
Route::get('/password/verify', [LoginController::class, 'verifyForm'])->name('password.verify');
Route::post('/password/verify', [LoginController::class, 'verifyCode'])->name('password.verify.check');
Route::get('/password/reset', [LoginController::class, 'resetForm'])->name('password.reset');
Route::post('/password/reset', [LoginController::class, 'resetPassword'])->name('password.reset.update');
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
Route::get('/settings', [SettingController::class, 'show'])
    ->middleware('auth.session.any')
    ->name('settings.show');
Route::post('/settings', [SettingController::class, 'update'])
    ->middleware('auth.session.any')
    ->name('settings.update');

Route::get('/student/dashboard', [StudentDashboardController::class, 'index'])
    ->middleware('auth.session:student')
    ->name('student.dashboard');
Route::get('/student/scholarship-status', function () {
    $studentId = (int) session('auth_user.id');
    $student = DB::table('students')
        ->select('id', 'full_name', 'matric_no', 'program')
        ->where('id', $studentId)
        ->first();

    if (!$student) {
        return redirect()->route('student.dashboard')
            ->withErrors(['student' => __('Rekod pelajar tidak dijumpai.')]);
    }

    $submission = DB::table('student_scholarship_status_forms')
        ->where('student_id', $studentId)
        ->first();

    return view('student.scholarship_status.form', compact('student', 'submission'));
})->middleware('auth.session:student')->name('student.scholarship-status.form');
Route::post('/student/scholarship-status', function (Request $request) {
    $studentId = (int) session('auth_user.id');
    $validated = $request->validate([
        'has_scholarship' => ['required', Rule::in(['yes', 'no'])],
        'sponsor_name' => ['nullable', 'string', 'max:150', 'required_if:has_scholarship,yes'],
        'monthly_amount' => ['nullable', 'numeric', 'min:0', 'required_if:has_scholarship,yes'],
        'notes' => ['nullable', 'string', 'max:500'],
    ]);

    $payload = [
        'has_scholarship' => $validated['has_scholarship'],
        'sponsor_name' => $validated['has_scholarship'] === 'yes' ? trim((string) ($validated['sponsor_name'] ?? '')) : null,
        'monthly_amount' => $validated['has_scholarship'] === 'yes' ? $validated['monthly_amount'] : null,
        'notes' => !empty($validated['notes']) ? trim($validated['notes']) : null,
        'submitted_at' => now(),
        'updated_at' => now(),
    ];

    DB::transaction(function () use ($studentId, $payload, $validated) {
        $existing = DB::table('student_scholarship_status_forms')
            ->where('student_id', $studentId)
            ->first();

        if ($existing) {
            DB::table('student_scholarship_status_forms')
                ->where('student_id', $studentId)
                ->update($payload);
        } else {
            DB::table('student_scholarship_status_forms')
                ->insert(array_merge($payload, [
                    'student_id' => $studentId,
                    'created_at' => now(),
                ]));
        }

        // Sync student self-submitted scholarship status into scholarship records.
        // We keep one managed row per student using a fixed marker in proof_file.
        $scholarshipPayload = [
            'student_id' => $studentId,
            'type' => $validated['has_scholarship'] === 'yes' ? 'scholarship' : 'none',
            'provider_name' => $validated['has_scholarship'] === 'yes'
                ? trim((string) ($validated['sponsor_name'] ?? ''))
                : null,
            'amount' => $validated['has_scholarship'] === 'yes'
                ? $validated['monthly_amount']
                : null,
            // Student self-report should be reviewed by admin before treated as active.
            'status' => $validated['has_scholarship'] === 'yes' ? 'pending' : 'confirmed',
            'proof_file' => 'student_status_form',
            'updated_at' => now(),
        ];

        $managedScholarship = DB::table('scholarships')
            ->where('student_id', $studentId)
            ->where('proof_file', 'student_status_form')
            ->first();

        if ($managedScholarship) {
            DB::table('scholarships')
                ->where('id', $managedScholarship->id)
                ->update($scholarshipPayload);
        } else {
            DB::table('scholarships')->insert(array_merge($scholarshipPayload, [
                'created_at' => now(),
            ]));
        }
    });

    return redirect()->route('student.scholarships.index')
        ->with('success', __('Status biasiswa anda berjaya dihantar dan direkodkan.'));
})->middleware('auth.session:student')->name('student.scholarship-status.submit');
Route::get('/student/profile', [ProfileController::class, 'show'])
    ->middleware('auth.session:student')
    ->name('student.profile');
Route::post('/student/profile', [ProfileController::class, 'update'])
    ->middleware('auth.session:student')
    ->name('student.profile.update');
Route::post('/student/profile/password', [ProfileController::class, 'updatePassword'])
    ->middleware('auth.session:student')
    ->name('student.profile.password.update');
Route::get('/student/ai-helper', function () {
    return redirect()->route('student.dashboard')
        ->withErrors(['ai_helper' => __('AI Helper is currently unavailable for students.')]);
})->middleware('auth.session:student')->name('student.ai-helper.index');
Route::get('/student/movements', [StudentMovementController::class, 'index'])
    ->middleware('auth.session:student')
    ->name('student.movements.index');
Route::post('/student/movements', [StudentMovementController::class, 'store'])
    ->middleware('auth.session:student')
    ->name('student.movements.store');

Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])
    ->middleware('auth.session:admin')
    ->name('admin.dashboard');
Route::get('/admin/system-monitoring/live', [AdminDashboardController::class, 'live'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.system-monitoring.live');
Route::get('/admin/reports/monthly', [AdminReportController::class, 'monthly'])
    ->middleware(['auth.session:admin', 'admin.scope:backoffice'])
    ->name('admin.reports.monthly');
Route::get('/admin/student-scholarship-status', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'has_scholarship' => ['nullable', Rule::in(['yes', 'no', 'all'])],
    ]);

    $query = DB::table('students')
        ->leftJoin('student_scholarship_status_forms as forms', 'forms.student_id', '=', 'students.id')
        ->select(
            'students.id as student_id',
            'students.full_name',
            'students.matric_no',
            'students.program',
            'forms.has_scholarship',
            'forms.sponsor_name',
            'forms.monthly_amount',
            'forms.notes',
            'forms.submitted_at'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('students.full_name', 'like', "%{$q}%")
                ->orWhere('students.matric_no', 'like', "%{$q}%")
                ->orWhere('students.program', 'like', "%{$q}%");
        });
    }

    $statusFilter = $filters['has_scholarship'] ?? 'all';
    if ($statusFilter !== 'all') {
        $query->where('forms.has_scholarship', $statusFilter);
    }

    $records = $query
        ->orderByDesc(DB::raw('forms.submitted_at IS NOT NULL'))
        ->orderBy('students.full_name')
        ->paginate(20)
        ->withQueryString();

    $summary = [
        'total_students' => DB::table('students')->count(),
        'submitted' => DB::table('student_scholarship_status_forms')->count(),
        'has_scholarship' => DB::table('student_scholarship_status_forms')->where('has_scholarship', 'yes')->count(),
        'no_scholarship' => DB::table('student_scholarship_status_forms')->where('has_scholarship', 'no')->count(),
    ];

    return view('admin.student_scholarship_status.index', compact('records', 'filters', 'summary'));
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.student-scholarship-status.index');
Route::get('/admin/ai-helper', function () {
    return view('admin.ai_helper.index');
})->middleware(['auth.session:admin', 'admin.scope:backoffice'])->name('admin.ai-helper.index');
Route::get('/admin/movements', [AdminMovementController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:movement'])
    ->name('admin.movements.index');
Route::get('/admin/movements/export', [AdminMovementController::class, 'export'])
    ->middleware(['auth.session:admin', 'admin.scope:movement'])
    ->name('admin.movements.export');
Route::get('/admin/movements/outside', [AdminMovementController::class, 'outside'])
    ->middleware(['auth.session:admin', 'admin.scope:movement'])
    ->name('admin.movements.outside');
Route::get('/admin/movements/violations', [AdminMovementController::class, 'violations'])
    ->middleware(['auth.session:admin', 'admin.scope:movement'])
    ->name('admin.movements.violations');
Route::get('/admin/movements/qr', [AdminMovementController::class, 'qr'])
    ->middleware(['auth.session:admin', 'admin.scope:movement'])
    ->name('admin.movements.qr');
Route::get('/admin/movements/qr/status', [AdminMovementController::class, 'qrStatus'])
    ->middleware(['auth.session:admin', 'admin.scope:movement'])
    ->name('admin.movements.qr.status');
Route::get('/admin/movements/qr/display', [AdminMovementController::class, 'qrDisplay'])
    ->middleware(['auth.session:admin', 'admin.scope:movement'])
    ->name('admin.movements.qr.display');
Route::post('/admin/movements/qr', [AdminMovementController::class, 'updateQr'])
    ->middleware(['auth.session:admin', 'admin.scope:movement'])
    ->name('admin.movements.qr.update');
Route::get('/admin/movements/settings', [AdminMovementController::class, 'settings'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.movements.settings');
Route::post('/admin/movements/settings', [AdminMovementController::class, 'updateSettings'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.movements.settings.update');

Route::get('/admin/admin-users', [AdminUserController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.admin-users.index');

Route::get('/admin/maintenance', function () {
    $downFile = storage_path('framework/down');
    $downPayload = is_file($downFile)
        ? json_decode((string) file_get_contents($downFile), true)
        : [];

    $cacheMeta = systemCacheMeta();
    $maintenance = [
        'enabled' => app()->isDownForMaintenance(),
        'cache_enabled' => isSystemCacheEnabled(),
        'cache_last_cleared_at' => $cacheMeta['last_cleared_at'] ?? null,
        'cache_updated_at' => $cacheMeta['updated_at'] ?? null,
        'cache_key_count' => count(systemCacheKeys()),
        'secret' => is_array($downPayload) ? ($downPayload['secret'] ?? null) : null,
        'retry' => is_array($downPayload) ? ($downPayload['retry'] ?? null) : null,
        'refresh' => is_array($downPayload) ? ($downPayload['refresh'] ?? null) : null,
        'redirect' => is_array($downPayload) ? ($downPayload['redirect'] ?? null) : null,
        'bypass_url' => null,
        'server_time' => now()->format('Y-m-d H:i:s'),
    ];

    if (!empty($maintenance['secret'])) {
        $maintenance['bypass_url'] = url($maintenance['secret']);
    }

    return view('admin.maintenance.index', compact('maintenance'));
})->middleware(['auth.session:admin', 'admin.scope:system'])->name('admin.maintenance.index');

Route::post('/admin/maintenance', function (Request $request) {
    $validated = $request->validate([
        'action' => ['required', Rule::in(['enable', 'disable', 'cache_enable', 'cache_disable'])],
    ]);

    if ($validated['action'] === 'enable') {
        $secret = 'myhep-maintenance-' . Str::lower(Str::random(24));
        Artisan::call('down', [
            '--secret' => $secret,
            '--retry' => 60,
        ]);
        auditLog('maintenance.enable', 'system', null, 'Enable maintenance mode');

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'Maintenance mode enabled. Use the bypass URL to continue admin access.');
    }

    if ($validated['action'] === 'cache_enable') {
        setSystemCacheEnabled(true);
        clearSystemCaches();
        auditLog('cache.enable', 'system', null, 'Enable system cache');

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'System cache enabled.');
    }

    if ($validated['action'] === 'cache_disable') {
        setSystemCacheEnabled(false);
        clearSystemCaches();
        auditLog('cache.disable', 'system', null, 'Disable system cache');

        return redirect()->route('admin.maintenance.index')
            ->with('success', 'System cache disabled.');
    }

    Artisan::call('up');
    auditLog('maintenance.disable', 'system', null, 'Disable maintenance mode');

    return redirect()->route('admin.maintenance.index')
        ->with('success', 'Maintenance mode disabled. The system is public again.');
})->middleware(['auth.session:admin', 'admin.scope:system'])->name('admin.maintenance.update');

Route::get('/admin/admin-users/create', [AdminUserController::class, 'create'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.admin-users.create');
Route::post('/admin/admin-users', [AdminUserController::class, 'store'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.admin-users.store');
Route::get('/admin/admin-users/{id}/edit', [AdminUserController::class, 'edit'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.admin-users.edit');
Route::put('/admin/admin-users/{id}', [AdminUserController::class, 'update'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.admin-users.update');
Route::post('/admin/admin-users/{id}/reset-password', [AdminUserController::class, 'resetPassword'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.admin-users.reset-password');
Route::delete('/admin/admin-users/{id}', [AdminUserController::class, 'destroy'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.admin-users.destroy');
Route::get('/admin/bug-reports', [AdminBugReportController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.bug-reports.index');
Route::put('/admin/bug-reports/{id}', [AdminBugReportController::class, 'update'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.bug-reports.update');
Route::delete('/admin/bug-reports/{id}', [AdminBugReportController::class, 'destroy'])
    ->middleware(['auth.session:admin', 'admin.scope:system'])
    ->name('admin.bug-reports.destroy');

Route::get('/admin/students', [StudentController::class, 'index'])
    ->middleware(['auth.session:admin', 'admin.scope:movement'])
    ->name('admin.students.index');
Route::get('/admin/students/search', [StudentController::class, 'search'])
    ->middleware(['auth.session:admin', 'admin.scope:movement'])
    ->name('admin.students.search');
Route::get('/admin/students/export', [StudentController::class, 'export'])
    ->middleware(['auth.session:admin', 'admin.scope:discipline'])
    ->name('admin.students.export');
Route::get('/admin/students/create', [StudentController::class, 'create'])
    ->middleware(['auth.session:admin', 'admin.scope:discipline'])
    ->name('admin.students.create');
Route::post('/admin/students', [StudentController::class, 'store'])
    ->middleware(['auth.session:admin', 'admin.scope:discipline'])
    ->name('admin.students.store');
Route::get('/admin/students/{id}/edit', [StudentController::class, 'edit'])
    ->middleware(['auth.session:admin', 'admin.scope:discipline'])
    ->name('admin.students.edit');
Route::put('/admin/students/{id}', [StudentController::class, 'update'])
    ->middleware(['auth.session:admin', 'admin.scope:discipline'])
    ->name('admin.students.update');
Route::delete('/admin/students/{id}', [StudentController::class, 'destroy'])
    ->middleware(['auth.session:admin', 'admin.scope:discipline'])
    ->name('admin.students.destroy');
Route::post('/admin/students/{id}/reset-password', [StudentController::class, 'resetPassword'])
    ->middleware(['auth.session:admin', 'admin.scope:discipline'])
    ->name('admin.students.reset-password');

Route::get('/admin/scholarships', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'type' => ['nullable', Rule::in(['scholarship', 'welfare', 'sponsorship', 'none'])],
        'status' => ['nullable', Rule::in(['pending', 'confirmed', 'rejected'])],
    ]);

    $query = DB::table('scholarships')
        ->join('students', 'students.id', '=', 'scholarships.student_id')
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
        );

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

    $records = $query
        ->orderByDesc('scholarships.created_at')
        ->paginate(15)
        ->withQueryString();

    return view('admin.scholarships.index', compact('records', 'filters'));
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarships.index');

Route::get('/admin/scholarships/export', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'type' => ['nullable', Rule::in(['scholarship', 'welfare', 'sponsorship', 'none'])],
        'status' => ['nullable', Rule::in(['pending', 'confirmed', 'rejected'])],
    ]);

    $query = DB::table('scholarships')
        ->join('students', 'students.id', '=', 'scholarships.student_id')
        ->select(
            'scholarships.id',
            'students.full_name as student_name',
            'students.matric_no',
            'scholarships.type',
            'scholarships.provider_name',
            'scholarships.amount',
            'scholarships.status',
            'scholarships.created_at'
        );

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

    $rows = $query
        ->orderByDesc('scholarships.created_at')
        ->get()
        ->map(function ($record) {
            return [
                $record->id,
                $record->student_name,
                $record->matric_no,
                $record->type,
                $record->provider_name ?? '',
                $record->amount !== null ? number_format((float) $record->amount, 2, '.', '') : '',
                $record->status,
                $record->created_at,
            ];
        });

    return downloadCsv(
        'scholarships_' . now()->format('Ymd_His') . '.csv',
        ['ID', 'Pelajar', 'No Matrik', 'Jenis', 'Penyedia', 'Jumlah (RM)', 'Status', 'Tarikh Rekod'],
        $rows
    );
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarships.export');

Route::get('/admin/scholarships/create', function () {
    return redirect()->route('admin.scholarships.index')
        ->withErrors(['scholarship' => 'Add Record is unavailable for this module.']);
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarships.create');

Route::post('/admin/scholarships', function (Request $request) {
    $validated = $request->validate([
        'student_id' => ['required', 'integer', 'exists:students,id'],
        'type' => ['required', Rule::in(['scholarship', 'welfare', 'sponsorship', 'none'])],
        'provider_name' => ['nullable', 'string', 'max:150'],
        'amount' => ['nullable', 'numeric', 'min:0'],
        'status' => ['required', Rule::in(['pending', 'confirmed', 'rejected'])],
        'proof_file' => ['nullable', 'string', 'max:255'],
    ]);

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
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarships.store');

Route::get('/admin/scholarships/{id}/edit', function (int $id) {
    $record = DB::table('scholarships')->where('id', $id)->first();
    if (!$record) {
        return redirect()->route('admin.scholarships.index')
            ->withErrors(['scholarship' => 'Rekod scholarship tidak dijumpai.']);
    }

    $selectedStudent = DB::table('students')
        ->select('id', 'full_name', 'matric_no')
        ->where('id', (int) old('student_id', $record->student_id))
        ->first();

    return view('admin.scholarships.edit', compact('record', 'selectedStudent'));
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarships.edit');

Route::put('/admin/scholarships/{id}', function (Request $request, int $id) {
    $record = DB::table('scholarships')->where('id', $id)->first();
    if (!$record) {
        return redirect()->route('admin.scholarships.index')
            ->withErrors(['scholarship' => 'Rekod scholarship tidak dijumpai.']);
    }

    $validated = $request->validate([
        'student_id' => ['required', 'integer', 'exists:students,id'],
        'type' => ['required', Rule::in(['scholarship', 'welfare', 'sponsorship', 'none'])],
        'provider_name' => ['nullable', 'string', 'max:150'],
        'amount' => ['nullable', 'numeric', 'min:0'],
        'status' => ['required', Rule::in(['pending', 'confirmed', 'rejected'])],
        'proof_file' => ['nullable', 'string', 'max:255'],
    ]);

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
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarships.update');

Route::delete('/admin/scholarships/{id}', function (int $id) {
    $deleted = DB::table('scholarships')->where('id', $id)->delete();
    if (!$deleted) {
        return redirect()->route('admin.scholarships.index')
            ->withErrors(['scholarship' => 'Rekod scholarship tidak dijumpai.']);
    }

    return redirect()->route('admin.scholarships.index')
        ->with('success', __('Rekod scholarship berjaya dipadam.'));
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarships.destroy');

Route::get('/admin/scholarship-announcements', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:200'],
        'type' => ['nullable', Rule::in(['scholarship', 'welfare', 'general'])],
    ]);

    $query = DB::table('scholarship_announcements')
        ->join('admins', 'admins.id', '=', 'scholarship_announcements.admin_id')
        ->select(
            'scholarship_announcements.id',
            'scholarship_announcements.title',
            'scholarship_announcements.body',
            'scholarship_announcements.type',
            'scholarship_announcements.link_url',
            'scholarship_announcements.link_label',
            'scholarship_announcements.created_at',
            'admins.full_name as admin_name'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('scholarship_announcements.title', 'like', "%{$q}%")
                ->orWhere('scholarship_announcements.body', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['type'])) {
        $query->where('scholarship_announcements.type', $filters['type']);
    }

    $announcements = $query
        ->orderByDesc('scholarship_announcements.created_at')
        ->paginate(12)
        ->withQueryString();

    return view('admin.scholarship_announcements.index', compact('announcements', 'filters'));
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarship-announcements.index');

Route::get('/admin/scholarship-announcements/export', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:200'],
        'type' => ['nullable', Rule::in(['scholarship', 'welfare', 'general'])],
    ]);

    $query = DB::table('scholarship_announcements')
        ->join('admins', 'admins.id', '=', 'scholarship_announcements.admin_id')
        ->select(
            'scholarship_announcements.id',
            'scholarship_announcements.title',
            'scholarship_announcements.body',
            'scholarship_announcements.type',
            'scholarship_announcements.link_url',
            'scholarship_announcements.link_label',
            'scholarship_announcements.created_at',
            'admins.full_name as admin_name'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('scholarship_announcements.title', 'like', "%{$q}%")
                ->orWhere('scholarship_announcements.body', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['type'])) {
        $query->where('scholarship_announcements.type', $filters['type']);
    }

    $rows = $query
        ->orderByDesc('scholarship_announcements.created_at')
        ->get()
        ->map(fn ($item) => [
            $item->id,
            $item->title,
            $item->type,
            $item->body,
            $item->link_url ?? '',
            $item->link_label ?? '',
            $item->admin_name,
            $item->created_at,
        ]);

    return downloadCsv(
        'scholarship_announcements_' . now()->format('Ymd_His') . '.csv',
        ['ID', 'Tajuk', 'Jenis', 'Penerangan', 'Link URL', 'Link Label', 'Dicipta Oleh', 'Tarikh'],
        $rows
    );
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarship-announcements.export');

Route::get('/admin/scholarship-announcements/create', function () {
    return view('admin.scholarship_announcements.create');
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarship-announcements.create');

Route::post('/admin/scholarship-announcements', function (Request $request) {
    $validated = $request->validate([
        'title' => ['required', 'string', 'max:200'],
        'body' => ['required', 'string'],
        'type' => ['required', Rule::in(['scholarship', 'welfare', 'general'])],
        'link_url' => ['nullable', 'url', 'max:500'],
        'link_label' => ['nullable', 'string', 'max:100'],
    ]);

    DB::table('scholarship_announcements')->insert([
        'admin_id' => session('auth_user.id'),
        'title' => $validated['title'],
        'body' => $validated['body'],
        'type' => $validated['type'],
        'link_url' => $validated['link_url'] ?? null,
        'link_label' => $validated['link_label'] ?? null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->route('admin.scholarship-announcements.index')
        ->with('success', __('Pengumuman scholarship berjaya ditambah.'));
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarship-announcements.store');

Route::get('/admin/scholarship-announcements/{id}/edit', function (int $id) {
    $announcement = DB::table('scholarship_announcements')->where('id', $id)->first();
    if (!$announcement) {
        return redirect()->route('admin.scholarship-announcements.index')
            ->withErrors(['announcement' => 'Pengumuman tidak dijumpai.']);
    }

    return view('admin.scholarship_announcements.edit', compact('announcement'));
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarship-announcements.edit');

Route::put('/admin/scholarship-announcements/{id}', function (Request $request, int $id) {
    $announcement = DB::table('scholarship_announcements')->where('id', $id)->first();
    if (!$announcement) {
        return redirect()->route('admin.scholarship-announcements.index')
            ->withErrors(['announcement' => 'Pengumuman tidak dijumpai.']);
    }

    $validated = $request->validate([
        'title' => ['required', 'string', 'max:200'],
        'body' => ['required', 'string'],
        'type' => ['required', Rule::in(['scholarship', 'welfare', 'general'])],
        'link_url' => ['nullable', 'url', 'max:500'],
        'link_label' => ['nullable', 'string', 'max:100'],
    ]);

    DB::table('scholarship_announcements')
        ->where('id', $id)
        ->update([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'type' => $validated['type'],
            'link_url' => $validated['link_url'] ?? null,
            'link_label' => $validated['link_label'] ?? null,
            'updated_at' => now(),
        ]);

    return redirect()->route('admin.scholarship-announcements.index')
        ->with('success', __('Pengumuman scholarship berjaya dikemaskini.'));
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarship-announcements.update');

Route::delete('/admin/scholarship-announcements/{id}', function (int $id) {
    $deleted = DB::table('scholarship_announcements')->where('id', $id)->delete();
    if (!$deleted) {
        return redirect()->route('admin.scholarship-announcements.index')
            ->withErrors(['announcement' => 'Pengumuman tidak dijumpai.']);
    }
    auditLog('scholarship_announcements.delete', 'scholarship_announcements', $id, 'Padam pengumuman scholarship');

    return redirect()->route('admin.scholarship-announcements.index')
        ->with('success', __('Pengumuman scholarship berjaya dipadam.'));
})->middleware(['auth.session:admin', 'admin.scope:scholarship'])->name('admin.scholarship-announcements.destroy');

Route::get('/admin/discipline-announcements', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:200'],
    ]);

    $query = DB::table('discipline_announcements')
        ->join('admins', 'admins.id', '=', 'discipline_announcements.admin_id')
        ->select(
            'discipline_announcements.id',
            'discipline_announcements.title',
            'discipline_announcements.body',
            'discipline_announcements.created_at',
            'admins.full_name as admin_name'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('discipline_announcements.title', 'like', "%{$q}%")
                ->orWhere('discipline_announcements.body', 'like', "%{$q}%");
        });
    }

    $announcements = $query
        ->orderByDesc('discipline_announcements.created_at')
        ->paginate(12)
        ->withQueryString();

    return view('admin.discipline_announcements.index', compact('announcements', 'filters'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.discipline-announcements.index');

Route::get('/admin/discipline-announcements/export', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:200'],
    ]);

    $query = DB::table('discipline_announcements')
        ->join('admins', 'admins.id', '=', 'discipline_announcements.admin_id')
        ->select(
            'discipline_announcements.id',
            'discipline_announcements.title',
            'discipline_announcements.body',
            'discipline_announcements.created_at',
            'admins.full_name as admin_name'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('discipline_announcements.title', 'like', "%{$q}%")
                ->orWhere('discipline_announcements.body', 'like', "%{$q}%");
        });
    }

    $rows = $query
        ->orderByDesc('discipline_announcements.created_at')
        ->get()
        ->map(fn ($item) => [
            $item->id,
            $item->title,
            $item->body,
            $item->admin_name,
            $item->created_at,
        ]);

    return downloadCsv(
        'discipline_announcements_' . now()->format('Ymd_His') . '.csv',
        ['ID', 'Tajuk', 'Penerangan', 'Dicipta Oleh', 'Tarikh'],
        $rows
    );
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.discipline-announcements.export');

Route::get('/admin/discipline-announcements/create', function () {
    return view('admin.discipline_announcements.create');
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.discipline-announcements.create');

Route::post('/admin/discipline-announcements', function (Request $request) {
    $validated = $request->validate([
        'title' => ['required', 'string', 'max:200'],
        'body' => ['required', 'string'],
    ]);

    DB::table('discipline_announcements')->insert([
        'admin_id' => session('auth_user.id'),
        'title' => $validated['title'],
        'body' => $validated['body'],
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->route('admin.discipline-announcements.index')
        ->with('success', __('Pengumuman disiplin berjaya ditambah.'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.discipline-announcements.store');

Route::get('/admin/discipline-announcements/{id}/edit', function (int $id) {
    $announcement = DB::table('discipline_announcements')->where('id', $id)->first();
    if (!$announcement) {
        return redirect()->route('admin.discipline-announcements.index')
            ->withErrors(['announcement' => 'Pengumuman tidak dijumpai.']);
    }

    return view('admin.discipline_announcements.edit', compact('announcement'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.discipline-announcements.edit');

Route::put('/admin/discipline-announcements/{id}', function (Request $request, int $id) {
    $announcement = DB::table('discipline_announcements')->where('id', $id)->first();
    if (!$announcement) {
        return redirect()->route('admin.discipline-announcements.index')
            ->withErrors(['announcement' => 'Pengumuman tidak dijumpai.']);
    }

    $validated = $request->validate([
        'title' => ['required', 'string', 'max:200'],
        'body' => ['required', 'string'],
    ]);

    DB::table('discipline_announcements')
        ->where('id', $id)
        ->update([
            'title' => $validated['title'],
            'body' => $validated['body'],
            'updated_at' => now(),
        ]);

    return redirect()->route('admin.discipline-announcements.index')
        ->with('success', __('Pengumuman disiplin berjaya dikemaskini.'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.discipline-announcements.update');

Route::delete('/admin/discipline-announcements/{id}', function (int $id) {
    $deleted = DB::table('discipline_announcements')->where('id', $id)->delete();
    if (!$deleted) {
        return redirect()->route('admin.discipline-announcements.index')
            ->withErrors(['announcement' => 'Pengumuman tidak dijumpai.']);
    }
    auditLog('discipline_announcements.delete', 'discipline_announcements', $id, 'Padam pengumuman disiplin');

    return redirect()->route('admin.discipline-announcements.index')
        ->with('success', __('Pengumuman disiplin berjaya dipadam.'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.discipline-announcements.destroy');

Route::get('/admin/rules', function (Request $request) {
    $categories = ruleCategoryOptions();
    $categoryIds = $categories->pluck('id')->map(fn ($id) => (int) $id)->all();

    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'category_id' => ['nullable', Rule::in($categoryIds)],
    ]);

    $query = DB::table('rules')
        ->join('rule_categories', 'rule_categories.id', '=', 'rules.category_id')
        ->leftJoin('admins', 'admins.id', '=', 'rules.updated_by')
        ->select(
            'rules.id',
            'rules.title',
            'rules.category_id',
            'rule_categories.name as category_name',
            'rules.description',
            'rules.updated_at',
            'admins.full_name as updated_by_name'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('rules.title', 'like', "%{$q}%")
                ->orWhere('rules.description', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['category_id'])) {
        $query->where('rules.category_id', (int) $filters['category_id']);
    }

    $rules = $query
        ->orderBy('rule_categories.name')
        ->orderBy('rules.title')
        ->paginate(15)
        ->withQueryString();

    return view('admin.rules.index', compact('rules', 'filters', 'categories'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.rules.index');

Route::get('/admin/rules/export', function (Request $request) {
    $categories = ruleCategoryOptions();
    $categoryIds = $categories->pluck('id')->map(fn ($id) => (int) $id)->all();

    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'category_id' => ['nullable', Rule::in($categoryIds)],
    ]);

    $query = DB::table('rules')
        ->join('rule_categories', 'rule_categories.id', '=', 'rules.category_id')
        ->leftJoin('admins', 'admins.id', '=', 'rules.updated_by')
        ->select(
            'rules.id',
            'rules.title',
            'rule_categories.name as category_name',
            'rules.description',
            'admins.full_name as updated_by_name',
            'rules.updated_at'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('rules.title', 'like', "%{$q}%")
                ->orWhere('rules.description', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['category_id'])) {
        $query->where('rules.category_id', (int) $filters['category_id']);
    }

    $rows = $query
        ->orderBy('rule_categories.name')
        ->orderBy('rules.title')
        ->get()
        ->map(fn ($rule) => [
            $rule->id,
            $rule->title,
            $rule->category_name,
            $rule->description,
            $rule->updated_by_name ?? '',
            $rule->updated_at,
        ]);

    return downloadCsv(
        'rules_' . now()->format('Ymd_His') . '.csv',
        ['ID', 'Tajuk', 'Kategori', 'Penerangan', 'Kemaskini Oleh', 'Tarikh Kemaskini'],
        $rows
    );
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.rules.export');

Route::get('/admin/rules/create', function () {
    $categories = ruleCategoryOptions();
    return view('admin.rules.create', compact('categories'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.rules.create');

Route::post('/admin/rules', function (Request $request) {
    $categories = ruleCategoryOptions();
    $categoryIds = $categories->pluck('id')->map(fn ($id) => (int) $id)->all();
    $validated = $request->validate([
        'title' => ['required', 'string', 'max:200'],
        'category_id' => ['required', Rule::in($categoryIds)],
        'description' => ['required', 'string'],
    ]);

    DB::table('rules')->insert([
        'title' => $validated['title'],
        'category_id' => $validated['category_id'],
        'description' => $validated['description'],
        'updated_by' => session('auth_user.id'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return redirect()->route('admin.rules.index')
        ->with('success', __('Peraturan berjaya ditambah.'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.rules.store');

Route::get('/admin/rules/{id}/edit', function (int $id) {
    $rule = DB::table('rules')->where('id', $id)->first();
    if (!$rule) {
        return redirect()->route('admin.rules.index')
            ->withErrors(['rule' => 'Peraturan tidak dijumpai.']);
    }

    $categories = ruleCategoryOptions();
    return view('admin.rules.edit', compact('rule', 'categories'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.rules.edit');

Route::put('/admin/rules/{id}', function (Request $request, int $id) {
    $rule = DB::table('rules')->where('id', $id)->first();
    if (!$rule) {
        return redirect()->route('admin.rules.index')
            ->withErrors(['rule' => 'Peraturan tidak dijumpai.']);
    }

    $categories = ruleCategoryOptions();
    $categoryIds = $categories->pluck('id')->map(fn ($id) => (int) $id)->all();
    $validated = $request->validate([
        'title' => ['required', 'string', 'max:200'],
        'category_id' => ['required', Rule::in($categoryIds)],
        'description' => ['required', 'string'],
    ]);

    DB::table('rules')
        ->where('id', $id)
        ->update([
            'title' => $validated['title'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'],
            'updated_by' => session('auth_user.id'),
            'updated_at' => now(),
        ]);

    return redirect()->route('admin.rules.index')
        ->with('success', __('Peraturan berjaya dikemaskini.'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.rules.update');

Route::delete('/admin/rules/{id}', function (int $id) {
    $deleted = DB::table('rules')->where('id', $id)->delete();
    if (!$deleted) {
        return redirect()->route('admin.rules.index')
            ->withErrors(['rule' => 'Peraturan tidak dijumpai.']);
    }

    return redirect()->route('admin.rules.index')
        ->with('success', __('Peraturan berjaya dipadam.'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.rules.destroy');

Route::get('/admin/offenses', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'status' => ['nullable', Rule::in(['unpaid', 'applied', 'paid'])],
        'date_from' => ['nullable', 'date'],
        'date_to' => ['nullable', 'date'],
    ]);

    $query = DB::table('offenses')
        ->join('students', 'students.id', '=', 'offenses.student_id')
        ->select(
            'offenses.id',
            'offenses.offense_date',
            'offenses.offense_time',
            'offenses.place',
            'offenses.evidence_photo_path',
            'offenses.fine_amount',
            'offenses.status',
            'students.full_name as student_name',
            'students.matric_no'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('students.full_name', 'like', "%{$q}%")
                ->orWhere('students.matric_no', 'like', "%{$q}%")
                ->orWhere('offenses.place', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['status'])) {
        $query->where('offenses.status', $filters['status']);
    }

    if (!empty($filters['date_from'])) {
        $query->whereDate('offenses.offense_date', '>=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
        $query->whereDate('offenses.offense_date', '<=', $filters['date_to']);
    }

    $offenses = $query
        ->orderByDesc('offenses.offense_date')
        ->orderByDesc('offenses.offense_time')
        ->paginate(15)
        ->withQueryString();

    return view('admin.offenses.index', compact('offenses', 'filters'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.offenses.index');

Route::get('/admin/offenses/export', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'status' => ['nullable', Rule::in(['unpaid', 'applied', 'paid'])],
        'date_from' => ['nullable', 'date'],
        'date_to' => ['nullable', 'date'],
    ]);

    $query = DB::table('offenses')
        ->join('students', 'students.id', '=', 'offenses.student_id')
        ->select(
            'offenses.id',
            'students.full_name as student_name',
            'students.matric_no',
            'offenses.offense_date',
            'offenses.offense_time',
            'offenses.place',
            'offenses.evidence_photo_path',
            'offenses.fine_amount',
            'offenses.status'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('students.full_name', 'like', "%{$q}%")
                ->orWhere('students.matric_no', 'like', "%{$q}%")
                ->orWhere('offenses.place', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['status'])) {
        $query->where('offenses.status', $filters['status']);
    }

    if (!empty($filters['date_from'])) {
        $query->whereDate('offenses.offense_date', '>=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
        $query->whereDate('offenses.offense_date', '<=', $filters['date_to']);
    }

    $rows = $query
        ->orderByDesc('offenses.offense_date')
        ->orderByDesc('offenses.offense_time')
        ->get()
        ->map(function ($offense) {
            return [
                $offense->id,
                $offense->student_name,
                $offense->matric_no,
                $offense->offense_date,
                $offense->offense_time,
                $offense->place,
                $offense->evidence_photo_path ? 'ada' : 'tiada',
                number_format((float) $offense->fine_amount, 2, '.', ''),
                $offense->status,
            ];
        });

    return downloadCsv(
        'offenses_' . now()->format('Ymd_His') . '.csv',
        ['ID', 'Pelajar', 'No Matrik', 'Tarikh', 'Masa', 'Tempat', 'Bukti Gambar', 'Denda (RM)', 'Status'],
        $rows
    );
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.offenses.export');

Route::get('/admin/offenses/{id}/print', function (int $id) {
    $offense = DB::table('offenses')
        ->join('students', 'students.id', '=', 'offenses.student_id')
        ->leftJoin('admins', 'admins.id', '=', 'offenses.admin_id')
        ->where('offenses.id', $id)
        ->select(
            'offenses.id',
            'offenses.offense_date',
            'offenses.offense_time',
            'offenses.place',
            'offenses.evidence_photo_path',
            'offenses.fine_amount',
            'offenses.status',
            'offenses.created_at',
            'students.full_name as student_name',
            'students.matric_no',
            'students.ic_no',
            'students.program',
            'admins.full_name as issued_by'
        )
        ->first();

    if (!$offense) {
        return redirect()->route('admin.offenses.index')
            ->withErrors(['offense' => __('Rekod kesalahan tidak dijumpai.')]);
    }

    $items = DB::table('offense_items')
        ->join('offense_types', 'offense_types.id', '=', 'offense_items.offense_type_id')
        ->where('offense_items.offense_id', $id)
        ->select(
            'offense_types.rule_reference',
            'offense_types.description',
            'offense_items.note'
        )
        ->orderBy('offense_types.rule_reference')
        ->get();

    return view('admin.offenses.print', compact('offense', 'items'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.offenses.print');

Route::get('/admin/offenses/{id}/pdf', function (int $id) {
    $offense = DB::table('offenses')
        ->join('students', 'students.id', '=', 'offenses.student_id')
        ->leftJoin('admins', 'admins.id', '=', 'offenses.admin_id')
        ->where('offenses.id', $id)
        ->select(
            'offenses.id',
            'offenses.offense_date',
            'offenses.offense_time',
            'offenses.place',
            'offenses.evidence_photo_path',
            'offenses.fine_amount',
            'offenses.status',
            'offenses.created_at',
            'students.full_name as student_name',
            'students.matric_no',
            'students.ic_no',
            'students.program',
            'admins.full_name as issued_by'
        )
        ->first();

    if (!$offense) {
        return redirect()->route('admin.offenses.index')
            ->withErrors(['offense' => __('Rekod kesalahan tidak dijumpai.')]);
    }

    $items = DB::table('offense_items')
        ->join('offense_types', 'offense_types.id', '=', 'offense_items.offense_type_id')
        ->where('offense_items.offense_id', $id)
        ->select(
            'offense_types.rule_reference',
            'offense_types.description',
            'offense_items.note'
        )
        ->orderBy('offense_types.rule_reference')
        ->get();

    $html = view('admin.offenses.print', [
        'offense' => $offense,
        'items' => $items,
        'isPdf' => true,
    ])->render();

    $options = new Options();
    $options->set('isRemoteEnabled', false);
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    return response($dompdf->output(), 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="saman_' . $offense->id . '.pdf"; filename*=UTF-8\'\'saman_' . $offense->id . '.pdf',
    ]);
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.offenses.pdf');

Route::get('/admin/offenses/create', function () {
    $students = DB::table('students')
        ->select('id', 'full_name', 'matric_no')
        ->orderBy('full_name')
        ->get();

    $offenseTypes = DB::table('offense_types')
        ->select('id', 'rule_reference', 'description', 'requires_note')
        ->orderBy('rule_reference')
        ->orderBy('description')
        ->get();

    return view('admin.offenses.create', compact('students', 'offenseTypes'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.offenses.create');

Route::post('/admin/offenses', function (Request $request) {
    $validated = $request->validate([
        'student_id' => ['required', 'integer', 'exists:students,id'],
        'offense_date' => ['required', 'date'],
        'offense_time' => ['required', 'date_format:H:i'],
        'place' => ['required', 'string', 'max:150'],
        'fine_amount' => ['required', 'numeric', 'min:0'],
        'evidence_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        'offense_type_ids' => ['required', 'array', 'min:1'],
        'offense_type_ids.*' => ['integer', 'exists:offense_types,id'],
        'notes' => ['nullable', 'array'],
    ]);

    $adminId = session('auth_user.id');
    $photoPath = null;

    if ($request->hasFile('evidence_photo')) {
        $photoPath = $request->file('evidence_photo')->store('offenses/evidence', 'public');
    }

    try {
        DB::transaction(function () use ($validated, $request, $adminId, $photoPath) {
            $offenseId = DB::table('offenses')->insertGetId([
                'student_id' => $validated['student_id'],
                'admin_id' => $adminId,
                'offense_date' => $validated['offense_date'],
                'offense_time' => $validated['offense_time'],
                'place' => $validated['place'],
                'evidence_photo_path' => $photoPath,
                'fine_amount' => $validated['fine_amount'],
                'status' => 'unpaid',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $typeIds = array_values(array_unique($validated['offense_type_ids']));
            foreach ($typeIds as $typeId) {
                $note = data_get($request->input('notes', []), (string) $typeId);
                DB::table('offense_items')->insert([
                    'offense_id' => $offenseId,
                    'offense_type_id' => $typeId,
                    'note' => $note ?: null,
                    'created_at' => now(),
                ]);
            }
        });
    } catch (\Throwable $e) {
        if (!empty($photoPath)) {
            Storage::disk('public')->delete($photoPath);
        }

        throw $e;
    }

    if ($request->expectsJson()) {
        return response()->json([
            'ok' => true,
            'message' => __('Rekod kesalahan berjaya disimpan.'),
            'redirect' => route('admin.offenses.index'),
        ]);
    }

    return redirect()->route('admin.offenses.index')
        ->with('success', __('Rekod kesalahan berjaya disimpan.'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.offenses.store');

Route::get('/admin/offenses/{id}/edit', function (int $id) {
    $offense = DB::table('offenses')->where('id', $id)->first();
    if (!$offense) {
        return redirect()->route('admin.offenses.index')
            ->withErrors(['offense' => __('Rekod kesalahan tidak dijumpai.')]);
    }

    $students = DB::table('students')
        ->select('id', 'full_name', 'matric_no')
        ->orderBy('full_name')
        ->get();

    $offenseTypes = DB::table('offense_types')
        ->select('id', 'rule_reference', 'description', 'requires_note')
        ->orderBy('rule_reference')
        ->orderBy('description')
        ->get();

    $selectedItems = DB::table('offense_items')
        ->where('offense_id', $id)
        ->select('offense_type_id', 'note')
        ->get();

    $selectedTypeIds = $selectedItems->pluck('offense_type_id')->all();
    $selectedNotes = $selectedItems
        ->mapWithKeys(fn ($item) => [(string) $item->offense_type_id => $item->note])
        ->all();

    return view('admin.offenses.edit', compact(
        'offense',
        'students',
        'offenseTypes',
        'selectedTypeIds',
        'selectedNotes'
    ));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.offenses.edit');

Route::put('/admin/offenses/{id}', function (Request $request, int $id) {
    $offense = DB::table('offenses')->where('id', $id)->first();
    if (!$offense) {
        return redirect()->route('admin.offenses.index')
            ->withErrors(['offense' => __('Rekod kesalahan tidak dijumpai.')]);
    }

    $validated = $request->validate([
        'student_id' => ['required', 'integer', 'exists:students,id'],
        'offense_date' => ['required', 'date'],
        'offense_time' => ['required', 'date_format:H:i'],
        'place' => ['required', 'string', 'max:150'],
        'fine_amount' => ['required', 'numeric', 'min:0'],
        'status' => ['required', Rule::in(['unpaid', 'applied', 'paid'])],
        'evidence_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        'remove_evidence_photo' => ['nullable', 'boolean'],
        'offense_type_ids' => ['required', 'array', 'min:1'],
        'offense_type_ids.*' => ['integer', 'exists:offense_types,id'],
        'notes' => ['nullable', 'array'],
    ]);

    $oldPhotoPath = $offense->evidence_photo_path;
    $newPhotoPath = $oldPhotoPath;
    $removeExistingPhoto = $request->boolean('remove_evidence_photo');

    if ($removeExistingPhoto) {
        $newPhotoPath = null;
    }

    if ($request->hasFile('evidence_photo')) {
        $newPhotoPath = $request->file('evidence_photo')->store('offenses/evidence', 'public');
    }

    try {
        DB::transaction(function () use ($validated, $request, $id, $newPhotoPath) {
            DB::table('offenses')
                ->where('id', $id)
                ->update([
                    'student_id' => $validated['student_id'],
                    'offense_date' => $validated['offense_date'],
                    'offense_time' => $validated['offense_time'],
                    'place' => $validated['place'],
                    'evidence_photo_path' => $newPhotoPath,
                    'fine_amount' => $validated['fine_amount'],
                    'status' => $validated['status'],
                    'updated_at' => now(),
                ]);

            DB::table('offense_items')->where('offense_id', $id)->delete();

            $typeIds = array_values(array_unique($validated['offense_type_ids']));
            foreach ($typeIds as $typeId) {
                $note = data_get($request->input('notes', []), (string) $typeId);
                DB::table('offense_items')->insert([
                    'offense_id' => $id,
                    'offense_type_id' => $typeId,
                    'note' => $note ?: null,
                    'created_at' => now(),
                ]);
            }
        });
    } catch (\Throwable $e) {
        if ($request->hasFile('evidence_photo') && !empty($newPhotoPath) && $newPhotoPath !== $oldPhotoPath) {
            Storage::disk('public')->delete($newPhotoPath);
        }

        throw $e;
    }

    if (!empty($oldPhotoPath) && $oldPhotoPath !== $newPhotoPath) {
        Storage::disk('public')->delete($oldPhotoPath);
    }

    if ($request->expectsJson()) {
        return response()->json([
            'ok' => true,
            'message' => __('Rekod kesalahan berjaya dikemaskini.'),
            'redirect' => route('admin.offenses.index'),
        ]);
    }

    return redirect()->route('admin.offenses.index')
        ->with('success', __('Rekod kesalahan berjaya dikemaskini.'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.offenses.update');

Route::post('/admin/offenses/{id}/mark-paid', function (int $id) {
    $updated = DB::table('offenses')
        ->where('id', $id)
        ->update([
            'status' => 'paid',
            'updated_at' => now(),
        ]);

    if (!$updated) {
        return redirect()->route('admin.offenses.index')
            ->withErrors(['offense' => __('Rekod kesalahan tidak dijumpai.')]);
    }
    auditLog('offenses.mark_paid', 'offenses', $id, 'Tukar status kesalahan ke paid');

    return redirect()->route('admin.offenses.index')
        ->with('success', __('Status kesalahan telah ditetapkan kepada paid.'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.offenses.mark-paid');

Route::delete('/admin/offenses/{id}', function (int $id) {
    $offense = DB::table('offenses')
        ->select('id', 'evidence_photo_path')
        ->where('id', $id)
        ->first();

    if (!$offense) {
        return redirect()->route('admin.offenses.index')
            ->withErrors(['offense' => __('Rekod kesalahan tidak dijumpai.')]);
    }

    $deleted = DB::table('offenses')->where('id', $id)->delete();
    if (!$deleted) {
        return redirect()->route('admin.offenses.index')
            ->withErrors(['offense' => __('Rekod kesalahan tidak dijumpai.')]);
    }

    if (!empty($offense->evidence_photo_path)) {
        Storage::disk('public')->delete($offense->evidence_photo_path);
    }
    auditLog('offenses.delete', 'offenses', $id, 'Padam rekod kesalahan');

    return redirect()->route('admin.offenses.index')
        ->with('success', __('Rekod kesalahan berjaya dipadam.'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.offenses.destroy');

Route::get('/student/offenses', function () {
    $studentId = session('auth_user.id');

    $offenses = DB::table('offenses')
        ->where('student_id', $studentId)
        ->select('id', 'offense_date', 'offense_time', 'place', 'evidence_photo_path', 'fine_amount', 'status')
        ->orderByDesc('offense_date')
        ->orderByDesc('offense_time')
        ->paginate(10);

    $offenseIds = $offenses->pluck('id')->all();
    $itemsByOffense = collect();
    $fineAppsByOffense = collect();

    if (!empty($offenseIds)) {
        $itemsByOffense = DB::table('offense_items')
            ->join('offense_types', 'offense_types.id', '=', 'offense_items.offense_type_id')
            ->whereIn('offense_items.offense_id', $offenseIds)
            ->select(
                'offense_items.offense_id',
                'offense_types.rule_reference',
                'offense_types.description',
                'offense_items.note'
            )
            ->orderBy('offense_types.rule_reference')
            ->get()
            ->groupBy('offense_id');

        $fineAppsByOffense = DB::table('fine_payment_applications')
            ->whereIn('offense_id', $offenseIds)
            ->where('student_id', $studentId)
            ->select('offense_id', 'status', 'meeting_date', 'created_at')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('offense_id')
            ->map(fn ($rows) => $rows->first());
    }

    return view('student.offenses.index', compact('offenses', 'itemsByOffense', 'fineAppsByOffense'));
})->middleware('auth.session:student')->name('student.offenses.index');

Route::get('/student/scholarships', function () {
    $studentId = session('auth_user.id');

    // Self-healing sync:
    // If student already submitted scholarship status form, ensure one managed
    // scholarship row exists so it always appears in student scholarship records.
    $statusForm = DB::table('student_scholarship_status_forms')
        ->where('student_id', $studentId)
        ->first();

    if ($statusForm) {
        $managedRecord = DB::table('scholarships')
            ->where('student_id', $studentId)
            ->where('proof_file', 'student_status_form')
            ->first();

        $managedPayload = [
            'student_id' => $studentId,
            'type' => $statusForm->has_scholarship === 'yes' ? 'scholarship' : 'none',
            'provider_name' => $statusForm->has_scholarship === 'yes'
                ? trim((string) ($statusForm->sponsor_name ?? ''))
                : null,
            'amount' => $statusForm->has_scholarship === 'yes'
                ? $statusForm->monthly_amount
                : null,
            // Do not downgrade admin-reviewed records when the student portal self-heals.
            'status' => $managedRecord && in_array($managedRecord->status, ['confirmed', 'rejected'], true)
                ? $managedRecord->status
                : ($statusForm->has_scholarship === 'yes' ? 'pending' : 'confirmed'),
            'proof_file' => 'student_status_form',
            'updated_at' => now(),
        ];

        if ($managedRecord) {
            DB::table('scholarships')
                ->where('id', $managedRecord->id)
                ->update($managedPayload);
        } else {
            DB::table('scholarships')->insert(array_merge($managedPayload, [
                'created_at' => now(),
            ]));
        }
    }

    $records = DB::table('scholarships')
        ->where('student_id', $studentId)
        ->orderByDesc('created_at')
        ->paginate(10);

    $announcements = DB::table('scholarship_announcements')
        ->select('id', 'title', 'body', 'type', 'link_url', 'link_label', 'created_at')
        ->orderByDesc('created_at')
        ->limit(8)
        ->get();

    return view('student.scholarships.index', compact('records', 'announcements'));
})->middleware('auth.session:student')->name('student.scholarships.index');

Route::get('/student/scholarship-announcements', function () {
    $announcements = DB::table('scholarship_announcements')
        ->select('id', 'title', 'body', 'type', 'link_url', 'link_label', 'created_at')
        ->orderByDesc('created_at')
        ->paginate(12);

    return view('student.scholarships.announcements', compact('announcements'));
})->middleware('auth.session:student')->name('student.scholarships.announcements');

Route::get('/student/rules', function (Request $request) {
    $categories = ruleCategoryOptions();
    $categoryIds = $categories->pluck('id')->map(fn ($id) => (int) $id)->all();
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'category_id' => ['nullable', Rule::in($categoryIds)],
    ]);

    $query = DB::table('rules')
        ->join('rule_categories', 'rule_categories.id', '=', 'rules.category_id')
        ->select('rules.id', 'rules.title', 'rules.category_id', 'rule_categories.name as category_name', 'rules.description', 'rules.updated_at');

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('title', 'like', "%{$q}%")
                ->orWhere('description', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['category_id'])) {
        $query->where('rules.category_id', (int) $filters['category_id']);
    }

    $rules = $query
        ->orderBy('rule_categories.name')
        ->orderBy('rules.title')
        ->paginate(12)
        ->withQueryString();

    return view('student.rules.index', compact('rules', 'filters', 'categories'));
})->middleware('auth.session:student')->name('student.rules.index');

Route::get('/student/discipline-announcements', function () {
    $announcements = DB::table('discipline_announcements')
        ->join('admins', 'admins.id', '=', 'discipline_announcements.admin_id')
        ->select(
            'discipline_announcements.id',
            'discipline_announcements.title',
            'discipline_announcements.body',
            'discipline_announcements.created_at',
            'admins.full_name as admin_name'
        )
        ->orderByDesc('discipline_announcements.created_at')
        ->paginate(12);

    return view('student.discipline_announcements.index', compact('announcements'));
})->middleware('auth.session:student')->name('student.discipline-announcements.index');

Route::get('/student/vehicle-stickers', function () {
    $studentId = session('auth_user.id');
    $applications = DB::table('vehicle_sticker_applications')
        ->leftJoin('admins', 'admins.id', '=', 'vehicle_sticker_applications.approved_by')
        ->where('vehicle_sticker_applications.student_id', $studentId)
        ->select(
            'vehicle_sticker_applications.id',
            'vehicle_sticker_applications.vehicle_no',
            'vehicle_sticker_applications.vehicle_type',
            'vehicle_sticker_applications.license_card_path',
            'vehicle_sticker_applications.parent_permission_path',
            'vehicle_sticker_applications.vehicle_photo_path',
            'vehicle_sticker_applications.status',
            'vehicle_sticker_applications.created_at',
            'admins.full_name as approved_by_name'
        )
        ->orderByDesc('vehicle_sticker_applications.created_at')
        ->paginate(10);

    return view('student.vehicle_stickers.index', compact('applications'));
})->middleware('auth.session:student')->name('student.vehicle-stickers.index');

Route::post('/student/vehicle-stickers', function (Request $request) {
    $studentId = session('auth_user.id');
    $validated = $request->validate([
        'vehicle_no' => ['required', 'string', 'max:20'],
        'vehicle_type' => ['required', 'string', 'max:50'],
        'license_card_image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        'parent_permission_image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        'vehicle_plate_image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
    ]);

    $pendingExists = DB::table('vehicle_sticker_applications')
        ->where('student_id', $studentId)
        ->where('vehicle_no', $validated['vehicle_no'])
        ->where('status', 'pending')
        ->exists();

    if ($pendingExists) {
        return redirect()->route('student.vehicle-stickers.index')
            ->withErrors(['vehicle_no' => 'Permohonan pending untuk nombor kenderaan ini sudah wujud.'])
            ->withInput();
    }

    $licensePath = $request->file('license_card_image')->store('vehicle_stickers/license_cards', 'public');
    $permissionPath = $request->file('parent_permission_image')->store('vehicle_stickers/parent_permissions', 'public');
    $vehiclePhotoPath = $request->file('vehicle_plate_image')->store('vehicle_stickers/vehicle_photos', 'public');

    try {
        DB::table('vehicle_sticker_applications')->insert([
            'student_id' => $studentId,
            'vehicle_no' => strtoupper(trim($validated['vehicle_no'])),
            'vehicle_type' => $validated['vehicle_type'],
            'license_card_path' => $licensePath,
            'parent_permission_path' => $permissionPath,
            'vehicle_photo_path' => $vehiclePhotoPath,
            'status' => 'pending',
            'approved_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    } catch (\Throwable $e) {
        Storage::disk('public')->delete([$licensePath, $permissionPath, $vehiclePhotoPath]);
        throw $e;
    }

    return redirect()->route('student.vehicle-stickers.index')
        ->with('success', __('Permohonan sticker kenderaan berjaya dihantar.'));
})->middleware('auth.session:student')->name('student.vehicle-stickers.store');

Route::post('/student/fine-applications', function (Request $request) {
    $studentId = session('auth_user.id');

    $validated = $request->validate([
        'offense_id' => ['required', 'integer', 'exists:offenses,id'],
        'student_note' => ['nullable', 'string'],
    ]);

    $offense = DB::table('offenses')
        ->where('id', $validated['offense_id'])
        ->where('student_id', $studentId)
        ->first();

    if (!$offense) {
        return redirect()->route('student.offenses.index')
            ->withErrors(['offense_id' => 'Kesalahan tidak ditemui untuk pelajar ini.']);
    }

    $pendingExists = DB::table('fine_payment_applications')
        ->where('offense_id', $validated['offense_id'])
        ->where('student_id', $studentId)
        ->where('status', 'pending')
        ->exists();

    if ($pendingExists) {
        return redirect()->route('student.offenses.index')
            ->withErrors(['offense_id' => 'Permohonan pembayaran sedang diproses.']);
    }

    DB::transaction(function () use ($validated, $studentId) {
        DB::table('fine_payment_applications')->insert([
            'offense_id' => $validated['offense_id'],
            'student_id' => $studentId,
            'student_note' => $validated['student_note'] ?? null,
            'status' => 'pending',
            'meeting_date' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('offenses')
            ->where('id', $validated['offense_id'])
            ->update([
                'status' => 'applied',
                'updated_at' => now(),
            ]);
    });

    return redirect()->route('student.offenses.index')
        ->with('success', __('Permohonan pembayaran berjaya dihantar.'));
})->middleware('auth.session:student')->name('student.fine-applications.store');

Route::get('/admin/fine-applications', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
        'date_from' => ['nullable', 'date'],
        'date_to' => ['nullable', 'date'],
    ]);

    $query = DB::table('fine_payment_applications')
        ->join('students', 'students.id', '=', 'fine_payment_applications.student_id')
        ->join('offenses', 'offenses.id', '=', 'fine_payment_applications.offense_id')
        ->select(
            'fine_payment_applications.id',
            'fine_payment_applications.status',
            'fine_payment_applications.student_note',
            'fine_payment_applications.meeting_date',
            'fine_payment_applications.created_at',
            'students.full_name as student_name',
            'students.matric_no',
            'offenses.offense_date',
            'offenses.place',
            'offenses.fine_amount'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('students.full_name', 'like', "%{$q}%")
                ->orWhere('students.matric_no', 'like', "%{$q}%")
                ->orWhere('offenses.place', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['status'])) {
        $query->where('fine_payment_applications.status', $filters['status']);
    }

    if (!empty($filters['date_from'])) {
        $query->whereDate('fine_payment_applications.created_at', '>=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
        $query->whereDate('fine_payment_applications.created_at', '<=', $filters['date_to']);
    }

    $applications = $query
        ->orderByRaw("CASE fine_payment_applications.status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
        ->orderByDesc('fine_payment_applications.created_at')
        ->paginate(15)
        ->withQueryString();

    return view('admin.fine_applications.index', compact('applications', 'filters'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.fine-applications.index');

Route::get('/admin/fine-applications/export', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
        'date_from' => ['nullable', 'date'],
        'date_to' => ['nullable', 'date'],
    ]);

    $query = DB::table('fine_payment_applications')
        ->join('students', 'students.id', '=', 'fine_payment_applications.student_id')
        ->join('offenses', 'offenses.id', '=', 'fine_payment_applications.offense_id')
        ->select(
            'fine_payment_applications.id',
            'students.full_name as student_name',
            'students.matric_no',
            'offenses.offense_date',
            'offenses.place',
            'offenses.fine_amount',
            'fine_payment_applications.student_note',
            'fine_payment_applications.status',
            'fine_payment_applications.meeting_date',
            'fine_payment_applications.created_at'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('students.full_name', 'like', "%{$q}%")
                ->orWhere('students.matric_no', 'like', "%{$q}%")
                ->orWhere('offenses.place', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['status'])) {
        $query->where('fine_payment_applications.status', $filters['status']);
    }

    if (!empty($filters['date_from'])) {
        $query->whereDate('fine_payment_applications.created_at', '>=', $filters['date_from']);
    }

    if (!empty($filters['date_to'])) {
        $query->whereDate('fine_payment_applications.created_at', '<=', $filters['date_to']);
    }

    $rows = $query
        ->orderByRaw("CASE fine_payment_applications.status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
        ->orderByDesc('fine_payment_applications.created_at')
        ->get()
        ->map(function ($app) {
            return [
                $app->id,
                $app->student_name,
                $app->matric_no,
                $app->matric_no,
                $app->offense_date,
                $app->place,
                number_format((float) $app->fine_amount, 2, '.', ''),
                $app->student_note ?? '',
                $app->status,
                $app->meeting_date ?? '',
                $app->created_at,
            ];
        });

    return downloadCsv(
        'fine_payment_applications_' . now()->format('Ymd_His') . '.csv',
        ['ID', 'Pelajar', 'No Matrik', 'Tarikh Kesalahan', 'Tempat', 'Denda (RM)', 'Catatan Pelajar', 'Status', 'Meeting Date', 'Tarikh Mohon'],
        $rows
    );
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.fine-applications.export');

Route::get('/admin/vehicle-stickers', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
    ]);

    $query = DB::table('vehicle_sticker_applications')
        ->join('students', 'students.id', '=', 'vehicle_sticker_applications.student_id')
        ->leftJoin('admins', 'admins.id', '=', 'vehicle_sticker_applications.approved_by')
        ->select(
            'vehicle_sticker_applications.id',
            'vehicle_sticker_applications.vehicle_no',
            'vehicle_sticker_applications.vehicle_type',
            'vehicle_sticker_applications.license_card_path',
            'vehicle_sticker_applications.parent_permission_path',
            'vehicle_sticker_applications.vehicle_photo_path',
            'vehicle_sticker_applications.status',
            'vehicle_sticker_applications.created_at',
            'students.full_name as student_name',
            'students.matric_no',
            'admins.full_name as approved_by_name'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('students.full_name', 'like', "%{$q}%")
                ->orWhere('students.matric_no', 'like', "%{$q}%")
                ->orWhere('vehicle_sticker_applications.vehicle_no', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['status'])) {
        $query->where('vehicle_sticker_applications.status', $filters['status']);
    }

    $applications = $query
        ->orderByRaw("CASE vehicle_sticker_applications.status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
        ->orderByDesc('vehicle_sticker_applications.created_at')
        ->paginate(15)
        ->withQueryString();

    return view('admin.vehicle_stickers.index', compact('applications', 'filters'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.vehicle-stickers.index');

Route::get('/admin/vehicle-stickers/export', function (Request $request) {
    $filters = $request->validate([
        'q' => ['nullable', 'string', 'max:150'],
        'status' => ['nullable', Rule::in(['pending', 'approved', 'rejected'])],
    ]);

    $query = DB::table('vehicle_sticker_applications')
        ->join('students', 'students.id', '=', 'vehicle_sticker_applications.student_id')
        ->leftJoin('admins', 'admins.id', '=', 'vehicle_sticker_applications.approved_by')
        ->select(
            'vehicle_sticker_applications.id',
            'students.full_name as student_name',
            'students.matric_no',
            'vehicle_sticker_applications.vehicle_no',
            'vehicle_sticker_applications.vehicle_type',
            'vehicle_sticker_applications.status',
            'admins.full_name as approved_by_name',
            'vehicle_sticker_applications.created_at'
        );

    if (!empty($filters['q'])) {
        $q = trim($filters['q']);
        $query->where(function ($sub) use ($q) {
            $sub->where('students.full_name', 'like', "%{$q}%")
                ->orWhere('students.matric_no', 'like', "%{$q}%")
                ->orWhere('vehicle_sticker_applications.vehicle_no', 'like', "%{$q}%");
        });
    }

    if (!empty($filters['status'])) {
        $query->where('vehicle_sticker_applications.status', $filters['status']);
    }

    $rows = $query
        ->orderByRaw("CASE vehicle_sticker_applications.status WHEN 'pending' THEN 0 WHEN 'approved' THEN 1 ELSE 2 END")
        ->orderByDesc('vehicle_sticker_applications.created_at')
        ->get()
        ->map(function ($app) {
            return [
                $app->id,
                $app->student_name,
                $app->matric_no,
                $app->vehicle_no,
                $app->vehicle_type,
                $app->status,
                $app->approved_by_name ?? '',
                $app->created_at,
            ];
        });

    return downloadCsv(
        'vehicle_stickers_' . now()->format('Ymd_His') . '.csv',
        ['ID', 'Pelajar', 'No Matrik', 'No Kenderaan', 'Jenis Kenderaan', 'Status', 'Disemak Oleh', 'Tarikh Mohon'],
        $rows
    );
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.vehicle-stickers.export');

Route::post('/admin/vehicle-stickers/{id}/decision', function (Request $request, int $id) {
    $validated = $request->validate([
        'status' => ['required', Rule::in(['approved', 'rejected'])],
    ]);

    $application = DB::table('vehicle_sticker_applications')->where('id', $id)->first();
    if (!$application) {
         return redirect()->route('admin.vehicle-stickers.index')
            ->withErrors(['status' => 'Permohonan sticker tidak dijumpai.']);
    }

    DB::table('vehicle_sticker_applications')
        ->where('id', $id)
        ->update([
            'status' => $validated['status'],
            'approved_by' => session('auth_user.id'),
            'updated_at' => now(),
        ]);
    auditLog('vehicle_stickers.decision', 'vehicle_sticker_applications', $id, 'Status: ' . $validated['status']);

    return redirect()->route('admin.vehicle-stickers.index')
        ->with('success', __('Status permohonan sticker berjaya dikemaskini.'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.vehicle-stickers.decision');

Route::post('/admin/fine-applications/{id}/decision', function (Request $request, int $id) {
    $validated = $request->validate([
        'status' => ['required', Rule::in(['approved', 'rejected'])],
        'meeting_date' => ['nullable', 'date', 'required_if:status,approved'],
    ]);

    $application = DB::table('fine_payment_applications')->where('id', $id)->first();
    if (!$application) {
        return redirect()->route('admin.fine-applications.index')
            ->withErrors(['status' => 'Permohonan tidak dijumpai.']);
    }

    DB::transaction(function () use ($validated, $application) {
        DB::table('fine_payment_applications')
            ->where('id', $application->id)
            ->update([
                'status' => $validated['status'],
                'meeting_date' => $validated['status'] === 'approved' ? $validated['meeting_date'] : null,
                'updated_at' => now(),
            ]);

        DB::table('offenses')
            ->where('id', $application->offense_id)
            ->update([
                'status' => $validated['status'] === 'approved' ? 'applied' : 'unpaid',
                'updated_at' => now(),
            ]);
    });
    auditLog('fine_applications.decision', 'fine_payment_applications', $id, 'Status: ' . $validated['status']);

    return redirect()->route('admin.fine-applications.index')
        ->with('success', __('Status permohonan berjaya dikemaskini.'));
})->middleware(['auth.session:admin', 'admin.scope:discipline'])->name('admin.fine-applications.decision');
 
