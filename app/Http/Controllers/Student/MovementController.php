<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MovementController extends Controller
{
    private const SCAN_SESSION_KEY = 'student_movement.scan_pass';
    private const SCAN_PASS_TTL_MINUTES = 2;

    public function index(Request $request): View|RedirectResponse
    {
        $studentId = (int) session('auth_user.id');

        if ($request->boolean('reset_scan')) {
            $request->session()->forget(self::SCAN_SESSION_KEY);

            return redirect()->route('student.movements.index');
        }

        $token = (string) $request->query('token', '');
        if ($token !== '') {
            $checkpoint = $this->checkpointByToken($token);
            if ($this->checkpointIsUsable($checkpoint)) {
                $this->issueScanPass($request, $checkpoint);

                return redirect()->route('student.movements.index')
                    ->with('scan_ready', __('QR scan verified. Complete your movement within the next 2 minutes.'));
            }
        }

        $checkpoint = $this->activeScanCheckpoint($request);
        $student = DB::table('students')
            ->select('id', 'full_name', 'matric_no', 'program', 'residence_status', 'room_number', 'photo')
            ->where('id', $studentId)
            ->first();

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
            'student' => $student,
            'checkpoint' => $checkpoint,
            'currentMovement' => $currentMovement,
            'movementTypes' => $movementTypes,
            'records' => $records,
            'scanExpiresAt' => $this->scanPassExpiry($request),
        ]);
    }

    public function scan(Request $request): View|RedirectResponse
    {
        $token = (string) $request->query('token', '');
        if ($token !== '') {
            $checkpoint = $this->checkpointByToken($token);
            if ($this->checkpointIsUsable($checkpoint)) {
                $this->issueScanPass($request, $checkpoint);

                return redirect()->route('student.movements.index')
                    ->with('scan_ready', __('QR scan verified. Complete your movement within the next 2 minutes.'));
            }

            return redirect()->route('student.movements.scan')
                ->withErrors(['checkpoint' => __('The scanned QR pass is no longer valid. Please scan the latest guard house QR code again.')]);
        }

        return view('student.movements.scan');
    }

    public function store(Request $request): RedirectResponse
    {
        $studentId = (int) session('auth_user.id');
        $scanPass = $this->scanPass($request);
        $checkpoint = $this->activeScanCheckpoint($request, $scanPass);

        if (!$scanPass || !$checkpoint) {
            return redirect()->route('student.movements.index')
                ->withErrors(['checkpoint' => __('Please scan the latest guard house QR code before recording movement.')]);
        }

        $validated = $request->validate([
            'checkpoint_id' => ['required', 'integer', 'exists:movement_checkpoints,id'],
            'movement_type_id' => ['required', 'integer', 'exists:movement_types,id'],
            'vehicle_plate_no' => ['nullable', 'string', 'max:30'],
            'late_explanation' => ['nullable', 'string', 'max:2000'],
            'gps_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if ((int) $validated['checkpoint_id'] !== (int) $checkpoint->id || !$this->checkpointIsUsable($checkpoint)) {
            return redirect()->route('student.movements.index')
                ->withErrors(['checkpoint' => __('The scanned QR pass is no longer valid. Please scan the latest guard house QR code again.')]);
        }

        $type = DB::table('movement_types')
            ->where('id', $validated['movement_type_id'])
            ->where('is_active', true)
            ->first();

        if (!$type) {
            return redirect()->route('student.movements.index')
                ->withErrors(['movement_type_id' => __('Jenis pergerakan tidak sah.')]);
        }

        $plateNumber = strtoupper(trim((string) ($validated['vehicle_plate_no'] ?? '')));
        if ($type->direction !== 'return' && $plateNumber === '') {
            return redirect()->route('student.movements.index')
                ->withErrors(['vehicle_plate_no' => __('Please enter the vehicle plate number before confirming check-out.')])
                ->withInput();
        }

        if (!$this->passesGpsValidation($checkpoint, $validated['gps_latitude'] ?? null, $validated['gps_longitude'] ?? null)) {
            return redirect()->route('student.movements.index')
                ->withErrors(['gps' => __('Lokasi anda berada di luar radius checkpoint yang dibenarkan.')]);
        }

        $currentMovement = $this->currentMovement($studentId);
        $now = now();

        if ($type->direction === 'return') {
            if (!$currentMovement) {
                return redirect()->route('student.movements.index')
                    ->withErrors(['movement_type_id' => __('Tiada rekod keluar kampus yang sedang aktif.')]);
            }

            $expectedReturn = $currentMovement->expected_return_at ? Carbon::parse($currentMovement->expected_return_at) : null;
            $lateMinutes = $expectedReturn && $now->greaterThan($expectedReturn)
                ? $expectedReturn->diffInMinutes($now)
                : 0;

            if ($lateMinutes > 0 && blank($validated['late_explanation'] ?? null)) {
                return redirect()->route('student.movements.index')
                    ->withErrors(['late_explanation' => __('Please explain why you are checking in late.')])
                    ->withInput();
            }

            DB::table('student_movements')
                ->where('id', $currentMovement->id)
                ->update([
                    'return_at' => $now,
                    'movement_status' => 'returned',
                    'rule_status' => $lateMinutes > 0 ? 'late' : 'compliant',
                    'late_minutes' => $lateMinutes,
                    'late_explanation' => $lateMinutes > 0 ? trim((string) $validated['late_explanation']) : null,
                    'gps_latitude' => $validated['gps_latitude'] ?? $currentMovement->gps_latitude,
                    'gps_longitude' => $validated['gps_longitude'] ?? $currentMovement->gps_longitude,
                    'device_info' => substr((string) $request->userAgent(), 0, 255),
                    'updated_at' => $now,
                ]);

            auditLog('student_movement.return', 'student_movements', (int) $currentMovement->id, 'Student returned to campus');
            $request->session()->forget(self::SCAN_SESSION_KEY);

            if ($lateMinutes > 0) {
                myhepSendPushNotification('student', $studentId, [
                    'title' => 'Late return recorded',
                    'body' => 'Your return to campus was recorded late. Please review your movement record.',
                    'url' => route('student.movements.index'),
                    'tag' => 'student-movement-late-' . $currentMovement->id,
                    'requireInteraction' => true,
                ]);

                myhepSendPushToAdminsByScope('movement', [
                    'title' => 'Movement violation detected',
                    'body' => 'A student return was recorded late and needs admin visibility.',
                    'url' => route('admin.movements.violations'),
                    'tag' => 'movement-violation-' . $currentMovement->id,
                    'requireInteraction' => true,
                ]);
            }

            return redirect()->route('student.movements.index')
                ->with('success', __('Return to campus recorded successfully.'));
        }

        if ($currentMovement) {
            return redirect()->route('student.movements.index')
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
            'vehicle_plate_no' => $plateNumber,
            'rule_status' => 'pending',
            'late_minutes' => 0,
            'gps_latitude' => $validated['gps_latitude'] ?? null,
            'gps_longitude' => $validated['gps_longitude'] ?? null,
            'device_info' => substr((string) $request->userAgent(), 0, 255),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        auditLog('student_movement.checkout', 'student_movements', (int) $movementId, 'Student checked out from campus');
        $request->session()->forget(self::SCAN_SESSION_KEY);

        return redirect()->route('student.movements.index')
            ->with('success', __('Check-out movement recorded successfully.'));
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

    private function issueScanPass(Request $request, object $checkpoint): void
    {
        DB::table('movement_checkpoints')
            ->where('id', $checkpoint->id)
            ->update([
                'qr_token' => Str::random(48),
                'updated_at' => now(),
            ]);

        $request->session()->put(self::SCAN_SESSION_KEY, [
            'checkpoint_id' => (int) $checkpoint->id,
            'scanned_at' => now()->toIso8601String(),
            'expires_at' => now()->addMinutes(self::SCAN_PASS_TTL_MINUTES)->toIso8601String(),
        ]);
    }

    private function scanPass(Request $request): ?array
    {
        $scanPass = $request->session()->get(self::SCAN_SESSION_KEY);
        if (!is_array($scanPass) || empty($scanPass['checkpoint_id']) || empty($scanPass['expires_at'])) {
            return null;
        }

        try {
            if (Carbon::parse($scanPass['expires_at'])->isPast()) {
                $request->session()->forget(self::SCAN_SESSION_KEY);

                return null;
            }
        } catch (\Throwable) {
            $request->session()->forget(self::SCAN_SESSION_KEY);

            return null;
        }

        return $scanPass;
    }

    private function scanPassExpiry(Request $request): ?Carbon
    {
        $scanPass = $this->scanPass($request);

        return $scanPass ? Carbon::parse($scanPass['expires_at']) : null;
    }

    private function activeScanCheckpoint(Request $request, ?array $scanPass = null): ?object
    {
        $scanPass ??= $this->scanPass($request);
        if (!$scanPass) {
            return null;
        }

        $checkpoint = DB::table('movement_checkpoints')
            ->where('id', (int) $scanPass['checkpoint_id'])
            ->first();

        if (!$this->checkpointIsUsable($checkpoint)) {
            $request->session()->forget(self::SCAN_SESSION_KEY);

            return null;
        }

        return $checkpoint;
    }

    private function checkpointIsUsable(?object $checkpoint): bool
    {
        return (bool) ($checkpoint && $checkpoint->is_active);
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
