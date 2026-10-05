<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class StudentAuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $input = $request->validate([
            'matric_no' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'max:4096'],
            'device_name' => ['required', 'string', 'max:100'],
        ]);

        $key = 'mobile-student-login:'.sha1(strtolower(trim($input['matric_no'])).'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json([
                'message' => 'Too many sign-in attempts. Please try again later.',
                'retry_after' => RateLimiter::availableIn($key),
            ], 429)->header('Cache-Control', 'no-store');
        }

        $student = Student::query()->where('matric_no', trim($input['matric_no']))->first();

        // The older web flow permits an IC-number fallback when no password is
        // set. Mobile access requires a real hashed password.
        if (! $student || ! $student->password
            || ! Hash::check($input['password'], $student->password)
            || (bool) ($student->is_blacklisted ?? false)) {
            RateLimiter::hit($key, 900);

            return response()->json([
                'message' => 'Unable to sign in. Check your credentials or account access.',
            ], 401)->header('Cache-Control', 'no-store');
        }

        RateLimiter::clear($key);
        $expiresAt = now()->addDays(7);
        $token = $student->createToken(
            trim($input['device_name']),
            ['student:read'],
            $expiresAt,
        );

        return response()->json([
            'token_type' => 'Bearer',
            'access_token' => $token->plainTextToken,
            'expires_at' => $expiresAt->toIso8601String(),
            'student' => $this->studentData($student),
        ])->header('Cache-Control', 'no-store');
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'student' => $this->studentData($request->user()),
        ])->header('Cache-Control', 'no-store');
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Signed out.'])
            ->header('Cache-Control', 'no-store');
    }

    private function studentData(Student $student): array
    {
        return [
            'id' => $student->id,
            'full_name' => $student->full_name,
            'matric_no' => $student->matric_no,
            'program' => $student->program,
        ];
    }
}
