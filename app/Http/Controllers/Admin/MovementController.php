<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'movement_type_id' => ['nullable', 'integer'],
            'movement_status' => ['nullable', Rule::in(['outside', 'returned'])],
            'rule_status' => ['nullable', Rule::in(['pending', 'compliant', 'late'])],
        ]);

        $records = $this->movementQuery($filters)
            ->orderByDesc('student_movements.checkout_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.movements.index', [
            'records' => $records,
            'filters' => $filters,
            'movementTypes' => DB::table('movement_types')->where('is_active', true)->orderBy('name')->get(),
            'summary' => $this->summary(),
        ]);
    }

    public function export(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'movement_type_id' => ['nullable', 'integer'],
            'movement_status' => ['nullable', Rule::in(['outside', 'returned'])],
            'rule_status' => ['nullable', Rule::in(['pending', 'compliant', 'late'])],
        ]);

        $rows = $this->movementQuery($filters)
            ->orderByDesc('student_movements.checkout_at')
            ->get()
            ->map(fn ($record) => [
                $record->id,
                $record->student_name,
                $record->matric_no,
                $record->program,
                $record->movement_type_name,
                $record->checkpoint_name,
                $record->checkout_at,
                $record->expected_return_at,
                $record->return_at ?? '',
                $record->movement_status,
                $record->rule_status,
                $record->late_minutes,
            ]);

        return downloadCsv(
            'student_movements_' . now()->format('Ymd_His') . '.csv',
            ['ID', 'Pelajar', 'No Matrik', 'Program', 'Jenis', 'Checkpoint', 'Keluar', 'Jangka Pulang', 'Pulang', 'Status', 'Rule Status', 'Lewat (min)'],
            $rows
        );
    }

    public function outside(): View
    {
        $records = $this->movementQuery(['movement_status' => 'outside'])
            ->orderBy('student_movements.checkout_at')
            ->paginate(20);

        return view('admin.movements.outside', [
            'records' => $records,
            'summary' => $this->summary(),
        ]);
    }

    public function violations(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);
        $filters['rule_status'] = 'late';

        $records = $this->movementQuery($filters)
            ->orderByDesc('student_movements.return_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.movements.violations', compact('records', 'filters'));
    }

    public function qr(): View
    {
        $checkpoint = DB::table('movement_checkpoints')->orderBy('id')->first();

        return view('admin.movements.qr', [
            'checkpoint' => $checkpoint,
            'scanUrl' => $checkpoint ? route('student.movements.index', ['token' => $checkpoint->qr_token]) : null,
            'settings' => $this->getSettings(),
        ]);
    }

    public function qrStatus()
    {
        $checkpoint = DB::table('movement_checkpoints')->orderBy('id')->first();
        $scanUrl = $checkpoint ? route('student.movements.index', ['token' => $checkpoint->qr_token]) : null;
        $isValid = $checkpoint
            && $checkpoint->is_active
            && (!$checkpoint->valid_from || now()->gte($checkpoint->valid_from))
            && (!$checkpoint->valid_until || now()->lte($checkpoint->valid_until));

        return response()->json([
            'checkpoint' => $checkpoint ? [
                'id' => (int) $checkpoint->id,
                'name' => $checkpoint->name,
                'is_active' => (bool) $checkpoint->is_active,
                'valid_from' => $checkpoint->valid_from,
                'valid_until' => $checkpoint->valid_until,
                'is_valid' => $isValid,
            ] : null,
            'scan_url' => $scanUrl,
        ]);
    }

    public function qrDisplay(): View
    {
        $checkpoint = DB::table('movement_checkpoints')->orderBy('id')->first();

        return view('admin.movements.qr_display', [
            'checkpoint' => $checkpoint,
            'scanUrl' => $checkpoint ? route('student.movements.index', ['token' => $checkpoint->qr_token]) : null,
        ]);
    }

    public function updateQr(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', Rule::in(['rotate', 'activate', 'deactivate', 'extend'])],
            'valid_minutes' => ['nullable', 'integer', 'min:5', 'max:10080'],
        ]);

        $checkpoint = DB::table('movement_checkpoints')->orderBy('id')->first();
        if (!$checkpoint) {
            return redirect()->route('admin.movements.qr')
                ->withErrors(['checkpoint' => __('Checkpoint tidak dijumpai.')]);
        }

        $payload = [
            'updated_by' => session('auth_user.id'),
            'updated_at' => now(),
        ];

        if ($validated['action'] === 'rotate') {
            $minutes = (int) ($validated['valid_minutes'] ?? $this->getSettings()['default_qr_valid_minutes']);
            $payload['qr_token'] = Str::random(48);
            $payload['is_active'] = true;
            $payload['valid_from'] = now();
            $payload['valid_until'] = now()->addMinutes($minutes);
        } elseif ($validated['action'] === 'activate') {
            $payload['is_active'] = true;
            $payload['valid_from'] = now();
            $payload['valid_until'] = now()->addMinutes((int) ($validated['valid_minutes'] ?? $this->getSettings()['default_qr_valid_minutes']));
        } elseif ($validated['action'] === 'deactivate') {
            $payload['is_active'] = false;
        } elseif ($validated['action'] === 'extend') {
            $payload['valid_until'] = now()->addMinutes((int) ($validated['valid_minutes'] ?? $this->getSettings()['default_qr_valid_minutes']));
        }

        DB::table('movement_checkpoints')->where('id', $checkpoint->id)->update($payload);
        auditLog('movement_qr.update', 'movement_checkpoints', (int) $checkpoint->id, 'Action: ' . $validated['action']);

        return redirect()->route('admin.movements.qr')
            ->with('success', __('Tetapan QR checkpoint berjaya dikemaskini.'));
    }

    public function settings(): array|View
    {
        $settings = $this->getSettings();
        $checkpoint = DB::table('movement_checkpoints')->orderBy('id')->first();
        $movementTypes = DB::table('movement_types')->orderBy('id')->get();

        return view('admin.movements.settings', compact('settings', 'checkpoint', 'movementTypes'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'curfew_weekday' => ['required', 'date_format:H:i'],
            'curfew_weekend' => ['required', 'date_format:H:i'],
            'gps_validation_enabled' => ['nullable', 'boolean'],
            'default_qr_valid_minutes' => ['required', 'integer', 'min:5', 'max:10080'],
            'checkpoint_name' => ['required', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'gps_radius_meters' => ['nullable', 'integer', 'min:10', 'max:5000'],
            'movement_types' => ['nullable', 'array'],
            'movement_types.*' => ['integer'],
        ]);

        foreach ([
            'curfew_weekday',
            'curfew_weekend',
            'default_qr_valid_minutes',
        ] as $key) {
            DB::table('movement_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => (string) $validated[$key], 'updated_at' => now(), 'created_at' => now()]
            );
        }

        DB::table('movement_settings')->updateOrInsert(
            ['key' => 'gps_validation_enabled'],
            ['value' => $request->boolean('gps_validation_enabled') ? '1' : '0', 'updated_at' => now(), 'created_at' => now()]
        );

        $checkpoint = DB::table('movement_checkpoints')->orderBy('id')->first();
        if ($checkpoint) {
            DB::table('movement_checkpoints')->where('id', $checkpoint->id)->update([
                'name' => $validated['checkpoint_name'],
                'latitude' => $validated['latitude'] ?? null,
                'longitude' => $validated['longitude'] ?? null,
                'gps_radius_meters' => $validated['gps_radius_meters'] ?? null,
                'updated_by' => session('auth_user.id'),
                'updated_at' => now(),
            ]);
        }

        $activeTypeIds = collect($validated['movement_types'] ?? [])->map(fn ($id) => (int) $id)->all();
        DB::table('movement_types')->update(['is_active' => false, 'updated_at' => now()]);
        if ($activeTypeIds) {
            DB::table('movement_types')->whereIn('id', $activeTypeIds)->update(['is_active' => true, 'updated_at' => now()]);
        }

        auditLog('movement_settings.update', 'movement_settings', null, 'Movement module settings updated');

        return redirect()->route('admin.movements.settings')
            ->with('success', __('Tetapan pergerakan pelajar berjaya disimpan.'));
    }

    private function movementQuery(array $filters)
    {
        $query = DB::table('student_movements')
            ->join('students', 'students.id', '=', 'student_movements.student_id')
            ->join('movement_types', 'movement_types.id', '=', 'student_movements.movement_type_id')
            ->join('movement_checkpoints', 'movement_checkpoints.id', '=', 'student_movements.checkpoint_id')
            ->select(
                'student_movements.*',
                'students.full_name as student_name',
                'students.matric_no',
                'students.program',
                'movement_types.name as movement_type_name',
                'movement_checkpoints.name as checkpoint_name'
            );

        if (!empty($filters['q'])) {
            $q = trim((string) $filters['q']);
            $query->where(function ($sub) use ($q) {
                $sub->where('students.full_name', 'like', "%{$q}%")
                    ->orWhere('students.matric_no', 'like', "%{$q}%")
                    ->orWhere('students.program', 'like', "%{$q}%");
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('student_movements.checkout_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('student_movements.checkout_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['movement_type_id'])) {
            $query->where('student_movements.movement_type_id', (int) $filters['movement_type_id']);
        }

        if (!empty($filters['movement_status'])) {
            $query->where('student_movements.movement_status', $filters['movement_status']);
        }

        if (!empty($filters['rule_status'])) {
            $query->where('student_movements.rule_status', $filters['rule_status']);
        }

        return $query;
    }

    private function summary(): array
    {
        $today = now()->toDateString();

        return [
            'outside_now' => DB::table('student_movements')->whereNull('return_at')->count(),
            'returned_today' => DB::table('student_movements')->whereDate('return_at', $today)->count(),
            'checkouts_today' => DB::table('student_movements')->whereDate('checkout_at', $today)->count(),
            'checkins_today' => DB::table('student_movements')->whereDate('return_at', $today)->count(),
            'late_returns' => DB::table('student_movements')->where('rule_status', 'late')->count(),
            'overnight_records' => DB::table('student_movements')
                ->join('movement_types', 'movement_types.id', '=', 'student_movements.movement_type_id')
                ->where('movement_types.slug', 'overnight_stay')
                ->count(),
        ];
    }

    private function getSettings(): array
    {
        return DB::table('movement_settings')->pluck('value', 'key')->all() + [
            'curfew_weekday' => '19:00',
            'curfew_weekend' => '23:00',
            'gps_validation_enabled' => '0',
            'default_qr_valid_minutes' => '1440',
        ];
    }
}
