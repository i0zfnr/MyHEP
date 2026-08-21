<?php

namespace App\Support;

use Illuminate\Support\Str;

class DynamicQrToken
{
    public const DEFAULT_ROTATION_SECONDS = 30;
    public const DEFAULT_GRACE_PERIOD_SECONDS = 15; // 30s rotation + 15s grace = 45s max age

    /**
     * Generate a signed time-based token for a program.
     */
    public static function generate(int $programId, int $validSeconds = self::DEFAULT_ROTATION_SECONDS): array
    {
        $timestamp = time();
        $payloadData = [
            'pid' => $programId,
            't' => $timestamp,
            'n' => Str::random(8),
        ];

        $payload = rtrim(strtr(base64_encode(json_encode($payloadData, JSON_UNESCAPED_SLASHES)), '+/', '-_'), '=');
        $signature = hash_hmac('sha256', $payload, (string) config('app.key'));
        $token = $payload.'.'.$signature;

        return [
            'token' => $token,
            'expires_in' => $validSeconds,
            'timestamp' => $timestamp,
        ];
    }

    /**
     * Verify the signed token against program ID and age.
     */
    public static function verify(?string $token, int $programId, int $maxAgeSeconds = 45): bool
    {
        if (blank($token) || ! str_contains($token, '.')) {
            return false;
        }

        $parts = explode('.', $token, 2);
        if (count($parts) !== 2) {
            return false;
        }

        [$payload, $providedSignature] = $parts;
        $expectedSignature = hash_hmac('sha256', $payload, (string) config('app.key'));

        if (! hash_equals($expectedSignature, $providedSignature)) {
            return false;
        }

        $decoded = base64_decode(strtr($payload, '-_', '+/'));
        if (! $decoded) {
            return false;
        }

        $data = json_decode($decoded, true);
        if (! is_array($data) || (int) ($data['pid'] ?? 0) !== $programId) {
            return false;
        }

        $tokenTime = (int) ($data['t'] ?? 0);
        $age = time() - $tokenTime;

        // Allow up to $maxAgeSeconds age and slight clock drift tolerance (-5 seconds)
        return $age >= -5 && $age <= $maxAgeSeconds;
    }
}
