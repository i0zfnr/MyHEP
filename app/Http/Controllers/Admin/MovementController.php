<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\DynamicQrToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
            'movement_type_id' => ['nullable', 'integer'],
            'movement_status' => ['nullable', Rule::in(['outside', 'returned'])],
            'rule_status' => ['nullable', Rule::in(['pending', 'compliant', 'late'])],
            'per_page' => ['nullable', Rule::in(['25', '50', '100'])],
        ]);

        $perPage = (int) ($filters['per_page'] ?? 50);
        $records = $this->movementQuery($filters)
            ->orderByDesc('student_movements.checkout_at')
            ->orderByDesc('student_movements.id')
            ->cursorPaginate($perPage)
            ->withQueryString();
        $recordPayload = $records->getCollection()
            ->map(fn ($record) => $this->movementPayload($record))
            ->values();

        if ($request->expectsJson()) {
            return response()->json([
                'data' => $recordPayload,
                'next_cursor' => $records->nextCursor()?->encode(),
                'has_more' => $records->hasMorePages(),
            ]);
        }

        return view('admin.movements.index', [
            'records' => $records,
            'recordPayload' => $recordPayload,
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
                $record->residence_status,
                $record->room_number,
                $record->student_photo ?? '',
                $record->movement_type_name,
                $record->vehicle_plate_no ?? '',
                $record->checkpoint_name,
                $record->checkout_at,
                $record->expected_return_at,
                $record->return_at ?? '',
                $record->movement_status,
                $record->rule_status,
                $record->late_minutes,
                $record->late_explanation ?? '',
            ]);

        return downloadCsv(
            'student_movements_' . now()->format('Ymd_His') . '.csv',
            ['ID', 'Pelajar', 'No Matrik', 'Program', 'Status Kediaman', 'No Bilik', 'Gambar Profil', 'Jenis', 'No Plat', 'Checkpoint', 'Keluar', 'Jangka Pulang', 'Pulang', 'Status', 'Rule Status', 'Lewat (min)', 'Penjelasan Lewat'],
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


    public function violations(): View
    {
        $records = $this->movementQuery(['rule_status' => 'late'])
            ->paginate(50)
            ->withQueryString();

        return view('admin.movements.violations', [
            'records' => $records,
            'stats' => $this->getStats(),
        ]);
    }

    public function qr(): View
    {
        $checkpoint = DB::table('movement_checkpoints')->orderBy('id')->first();
        $dynamicData = $checkpoint && $checkpoint->is_active
            ? DynamicQrToken::generateForCheckpoint((int) $checkpoint->id)
            : null;
        $scanUrl = $dynamicData ? route('student.movements.scan', ['token' => $dynamicData['token']]) : ($checkpoint ? route('student.movements.scan', ['token' => $checkpoint->qr_token]) : null);

        return view('admin.movements.qr', [
            'checkpoint' => $checkpoint,
            'scanUrl' => $scanUrl,
            'settings' => $this->getSettings(),
        ]);
    }

    public function qrStatus()
    {
        $checkpoint = DB::table('movement_checkpoints')->orderBy('id')->first();
        $isValid = (bool) ($checkpoint && $checkpoint->is_active);
        $dynamicData = $isValid ? DynamicQrToken::generateForCheckpoint((int) $checkpoint->id) : null;
        $scanUrl = $dynamicData ? route('student.movements.scan', ['token' => $dynamicData['token']]) : ($checkpoint ? route('student.movements.scan', ['token' => $checkpoint->qr_token]) : null);

        return response()->json([
            'checkpoint' => $checkpoint ? [
                'id' => (int) $checkpoint->id,
                'name' => $checkpoint->name,
                'is_active' => (bool) $checkpoint->is_active,
                'is_valid' => $isValid,
            ] : null,
            'token' => $dynamicData['token'] ?? null,
            'scan_url' => $scanUrl,
            'expires_in' => $dynamicData['expires_in'] ?? 30,
            'server_time' => time(),
        ]);
    }

    public function qrDisplay(): View
    {
        $checkpoint = DB::table('movement_checkpoints')->orderBy('id')->first();
        $dynamicData = $checkpoint && $checkpoint->is_active
            ? DynamicQrToken::generateForCheckpoint((int) $checkpoint->id)
            : null;
        $scanUrl = $dynamicData ? route('student.movements.scan', ['token' => $dynamicData['token']]) : ($checkpoint ? route('student.movements.scan', ['token' => $checkpoint->qr_token]) : null);

        return view('admin.movements.qr_display', [
            'checkpoint' => $checkpoint,
            'scanUrl' => $scanUrl,
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
            $payload['qr_token'] = Str::random(48);
            $payload['is_active'] = true;
            $payload['valid_from'] = null;
            $payload['valid_until'] = null;
        } elseif ($validated['action'] === 'activate') {
            $payload['is_active'] = true;
            $payload['valid_from'] = null;
            $payload['valid_until'] = null;
        } elseif ($validated['action'] === 'deactivate') {
            $payload['is_active'] = false;
        } elseif ($validated['action'] === 'extend') {
            $payload['is_active'] = true;
            $payload['valid_from'] = null;
            $payload['valid_until'] = null;
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
                'students.id as student_id',
                'students.full_name as student_name',
                'students.matric_no',
                'students.photo as student_photo',
                'students.program',
                'students.residence_status',
                'students.room_number',
                'movement_types.name as movement_type_name',
                'movement_checkpoints.name as checkpoint_name'
            );

        if (!empty($filters['q'])) {
            $q = trim((string) $filters['q']);
            $canSearchIdentity = adminCan('students.sensitive');
            $query->where(function ($sub) use ($q, $canSearchIdentity) {
                $sub->where('students.full_name', 'like', "%{$q}%")
                    ->orWhere('students.matric_no', 'like', "%{$q}%")
                    ->orWhere('students.program', 'like', "%{$q}%");
                if ($canSearchIdentity) {
                    $sub->orWhere('students.ic_no', 'like', "%{$q}%");
                }
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

    private function movementPayload(object $record): array
    {
        $checkoutAt = \Illuminate\Support\Carbon::parse($record->checkout_at);
        $returnAt = $record->return_at
            ? \Illuminate\Support\Carbon::parse($record->return_at)
            : null;

        return [
            'id' => (int) $record->id,
            'student_name' => (string) $record->student_name,
            'student_initial' => strtoupper(substr((string) ($record->student_name ?: 'S'), 0, 1)),
            'matric_no' => (string) $record->matric_no,
            'student_photo_url' => !empty($record->student_photo)
                ? asset('storage/' . $record->student_photo)
                : null,
            'student_profile_url' => adminCan('students.sensitive')
                ? route('admin.students.show', $record->student_id)
                : null,
            'program' => (string) $record->program,
            'checkpoint_name' => (string) $record->checkpoint_name,
            'residence_label' => ($record->residence_status ?? 'inside_campus') === 'live_out'
                ? __('Live Out')
                : __('Inside Campus'),
            'room_number' => (string) ($record->room_number ?: '-'),
            'movement_type_label' => __((string) $record->movement_type_name),
            'vehicle_plate_no' => (string) ($record->vehicle_plate_no ?: '-'),
            'checkout_date' => $checkoutAt->format('d M Y'),
            'checkout_time' => $checkoutAt->format('h:i A'),
            'return_date' => $returnAt?->format('d M Y'),
            'return_time' => $returnAt?->format('h:i A'),
            'not_returned_label' => __('Not returned yet'),
            'movement_status_label' => __((string) $record->movement_status),
            'movement_status_tone' => $record->movement_status === 'outside' ? 'pending' : 'confirmed',
            'rule_status_label' => __((string) $record->rule_status),
            'rule_status_tone' => $record->rule_status === 'late'
                ? 'rejected'
                : ($record->rule_status === 'pending' ? 'pending' : 'confirmed'),
            'late_explanation' => (string) ($record->late_explanation ?: '-'),
            'profile_photo_label' => __('Profile photo'),
            'view_profile_label' => __('View Profile'),
        ];
    }

    private function summary(): array
    {
        return Cache::remember('admin.movement.summary', now()->addSeconds(10), function () {
            $start = now()->startOfDay();
            $end = $start->copy()->addDay();
            $counts = DB::table('student_movements')
                ->selectRaw(
                    'SUM(CASE WHEN return_at IS NULL THEN 1 ELSE 0 END) AS outside_now,
                     SUM(CASE WHEN return_at >= ? AND return_at < ? THEN 1 ELSE 0 END) AS returned_today,
                     SUM(CASE WHEN checkout_at >= ? AND checkout_at < ? THEN 1 ELSE 0 END) AS checkouts_today,
                     SUM(CASE WHEN rule_status = ? THEN 1 ELSE 0 END) AS late_returns',
                    [$start, $end, $start, $end, 'late']
                )
                ->first();

            $returnedToday = (int) ($counts->returned_today ?? 0);

            return [
                'outside_now' => (int) ($counts->outside_now ?? 0),
                'returned_today' => $returnedToday,
                'checkouts_today' => (int) ($counts->checkouts_today ?? 0),
                'checkins_today' => $returnedToday,
                'late_returns' => (int) ($counts->late_returns ?? 0),
                'overnight_records' => DB::table('student_movements')
                    ->join('movement_types', 'movement_types.id', '=', 'student_movements.movement_type_id')
                    ->where('movement_types.slug', 'overnight_stay')
                    ->count(),
            ];
        });
    }

    private function getSettings(): array
    {
        return DB::table('movement_settings')->pluck('value', 'key')->all() + [
            'curfew_weekday' => '19:00',
            'curfew_weekend' => '23:00',
            'gps_validation_enabled' => '0',
        ];
    }
}
