<?php

namespace App\Services;

use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class FirebasePhoneTokenVerifier
{
    /** @return array<string, mixed> */
    public function verify(string $idToken): array
    {
        $projectId = (string) config('services.firebase.project_id');
        if ($projectId === '') {
            throw new RuntimeException('Firebase project is not configured.');
        }

        $keySet = Cache::remember('firebase.secure-token-jwks', now()->addHour(), function (): array {
            return Http::timeout(8)
                ->get('https://www.googleapis.com/service_accounts/v1/jwk/securetoken@system.gserviceaccount.com')
                ->throw()
                ->json();
        });

        $decoded = JWT::decode($idToken, JWK::parseKeySet($keySet));
        $claims = (array) $decoded;
        $firebaseClaims = (array) ($claims['firebase'] ?? []);
        $now = now()->timestamp;

        if (($claims['aud'] ?? null) !== $projectId
            || ($claims['iss'] ?? null) !== 'https://securetoken.google.com/'.$projectId
            || empty($claims['sub'])
            || ! isset($claims['phone_number'])
            || ($firebaseClaims['sign_in_provider'] ?? null) !== 'phone'
            || ! isset($claims['iat'])
            || (int) $claims['iat'] > $now + 60
            || ! isset($claims['auth_time'])
            || (int) $claims['auth_time'] > $now + 60
            || $now - (int) $claims['auth_time'] > 600) {
            throw new RuntimeException('Firebase token claims are not valid for password recovery.');
        }

        return $claims;
    }
}
