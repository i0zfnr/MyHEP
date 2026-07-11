<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function index(Request $request): View
    {
        $studentId = (int) session('auth_user.id');
        $token = (string) $request->query('token', '');
        $checkpoint = $token !== ''
            ? $this->checkpointByToken($token)
            : null;

        $currentMovement = $this->currentMovement($studentId);
        $movementTypes = DB::table('movement_types')
            ->where('is_active', true)
            ->orderByRaw("CASE direction WHEN 'return' THEN 1 ELSE 0 END")
            ->orderBy('id')
            ->get();

        $records = DB::table('student_movements')
            ->join('movement_types', 'movement_types.id', '=', 'student_movements.movement_type_id')
            ->join('movement_checkpoints', 'movement_checkpoints.id', '=', 'student_movements.checkpoint_id')
            ->where('student_movements.student_id', $studentId)
            ->select(
                'student_movements.*',
                'movement_types.name as movement_type_name',
                'movement_checkpoints.name as checkpoint_name'
            )
            ->orderByDesc('student_movements.checkout_at')
            ->paginate(10)
            ->withQueryString();

        return view('student.movements.index', [
            'checkpoint' => $checkpoint,
            'currentMovement' => $currentMovement,
            'movementTypes' => $movementTypes,
            'records' => $records,
            'token' => $token,
            'hasScannedToken' => $token !== '',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $studentId = (int) session('auth_user.id');
        $validated = $request->validate([
            'checkpoint_id' => ['required', 'integer', 'exists:movement_checkpoints,id'],
            'movement_type_id' => ['required', 'integer', 'exists:movement_types,id'],
            'gps_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $checkpoint = DB::table('movement_checkpoints')->where('id', $validated['checkpoint_id'])->first();
        if (!$this->checkpointIsUsable($checkpoint)) {
            return redirect()->route('student.movements.index')
                ->withErrors(['checkpoint' => __('QR checkpoint tidak aktif atau telah tamat tempoh.')]);
        }

        $type = DB::table('movement_types')
            ->where('id', $validated['movement_type_id'])
            ->where('is_active', true)
            ->first();

        if (!$type) {
            return redirect()->route('student.movements.index')
                ->withErrors(['movement_type_id' => __('Jenis pergerakan tidak sah.')]);
        }

        if (!$this->passesGpsValidation($checkpoint, $validated['gps_latitude'] ?? null, $validated['gps_longitude'] ?? null)) {
            return redirect()->route('student.movements.index', ['token' => $checkpoint->qr_token])
                ->withErrors(['gps' => __('Lokasi anda berada di luar radius checkpoint yang dibenarkan.')]);
        }

        $currentMovement = $this->currentMovement($studentId);
        $now = now();

        if ($type->direction === 'return') {
            if (!$currentMovement) {
                return redirect()->route('student.movements.index', ['token' => $checkpoint->qr_token])
                    ->withErrors(['movement_type_id' => __('Tiada rekod keluar kampus yang sedang aktif.')]);
            }

            $expectedReturn = $currentMovement->expected_return_at ? Carbon::parse($currentMovement->expected_return_at) : null;
            $lateMinutes = $expectedReturn && $now->greaterThan($expectedReturn)
                ? $expectedReturn->diffInMinutes($now)
                : 0;

            DB::table('student_movements')
                ->where('id', $currentMovement->id)
                ->update([
                    'return_at' => $now,
                    'movement_status' => 'returned',
                    'rule_status' => $lateMinutes > 0 ? 'late' : 'compliant',
                    'late_minutes' => $lateMinutes,
                    'gps_latitude' => $validated['gps_latitude'] ?? $currentMovement->gps_latitude,
                    'gps_longitude' => $validated['gps_longitude'] ?? $currentMovement->gps_longitude,
                    'device_info' => substr((string) $request->userAgent(), 0, 255),
                    'updated_at' => $now,
                ]);

            auditLog('student_movement.return', 'student_movements', (int) $currentMovement->id, 'Student returned to campus');

            return redirect()->route('student.movements.index')
                ->with('success', __('Kepulangan ke kampus berjaya direkodkan.'));
        }

        if ($currentMovement) {
            return redirect()->route('student.movements.index', ['token' => $checkpoint->qr_token])
                ->withErrors(['movement_type_id' => __('Anda masih direkodkan berada di luar kampus. Sila pilih Return to Campus terlebih dahulu.')]);
        }

        $expectedReturn = $this->expectedReturnAt($now, (int) $type->default_return_days);
        $movementId = DB::table('student_movements')->insertGetId([
            'student_id' => $studentId,
            'movement_type_id' => $type->id,
            'checkpoint_id' => $checkpoint->id,
            'checkout_at' => $now,
            'expected_return_at' => $expectedReturn,
            'return_at' => null,
            'movement_status' => 'outside',
            'rule_status' => 'pending',
            'late_minutes' => 0,
            'gps_latitude' => $validated['gps_latitude'] ?? null,
            'gps_longitude' => $validated['gps_longitude'] ?? null,
            'device_info' => substr((string) $request->userAgent(), 0, 255),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        auditLog('student_movement.checkout', 'student_movements', (int) $movementId, 'Student checked out from campus');

        return redirect()->route('student.movements.index')
            ->with('success', __('Pergerakan keluar kampus berjaya direkodkan.'));
    }

    private function currentMovement(int $studentId): ?object
    {
        return DB::table('student_movements')
            ->join('movement_types', 'movement_types.id', '=', 'student_movements.movement_type_id')
            ->join('movement_checkpoints', 'movement_checkpoints.id', '=', 'student_movements.checkpoint_id')
            ->where('student_movements.student_id', $studentId)
            ->whereNull('student_movements.return_at')
            ->select(
                'student_movements.*',
                'movement_types.name as movement_type_name',
                'movement_checkpoints.name as checkpoint_name'
            )
            ->orderByDesc('student_movements.checkout_at')
            ->first();
    }

    private function checkpointByToken(string $token): ?object
    {
        return DB::table('movement_checkpoints')
            ->where('qr_token', $token)
            ->first();
    }

    private function checkpointIsUsable(?object $checkpoint): bool
    {
        if (!$checkpoint || !$checkpoint->is_active) {
            return false;
        }

        $now = now();
        if ($checkpoint->valid_from && Carbon::parse($checkpoint->valid_from)->greaterThan($now)) {
            return false;
        }

        return !$checkpoint->valid_until || Carbon::parse($checkpoint->valid_until)->greaterThanOrEqualTo($now);
    }

    private function expectedReturnAt(Carbon $checkoutAt, int $returnDays): Carbon
    {
        $expectedDate = $checkoutAt->copy()->addDays($returnDays);
        $settingKey = $expectedDate->isFriday() || $expectedDate->isSaturday()
            ? 'curfew_weekend'
            : 'curfew_weekday';
        $time = DB::table('movement_settings')->where('key', $settingKey)->value('value') ?: '19:00';

        [$hour, $minute] = array_pad(explode(':', $time), 2, '00');

        return $expectedDate->setTime((int) $hour, (int) $minute);
    }

    private function passesGpsValidation(object $checkpoint, mixed $latitude, mixed $longitude): bool
    {
        $enabled = DB::table('movement_settings')->where('key', 'gps_validation_enabled')->value('value') === '1';
        if (!$enabled) {
            return true;
        }

        if ($checkpoint->latitude === null || $checkpoint->longitude === null || !$checkpoint->gps_radius_meters) {
            return true;
        }

        if ($latitude === null || $longitude === null) {
            return false;
        }

        $distance = $this->distanceMeters((float) $checkpoint->latitude, (float) $checkpoint->longitude, (float) $latitude, (float) $longitude);

        return $distance <= (int) $checkpoint->gps_radius_meters;
    }

    private function distanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
