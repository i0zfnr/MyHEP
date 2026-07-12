<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

if (!function_exists('isStudentAuthenticated')) {
    function isStudentAuthenticated(): bool
    {
        $authUser = session('auth_user');
        return (bool) ($authUser && ($authUser['role'] ?? null) === 'student');
    }
}

if (!function_exists('isAdminAuthenticated')) {
    function isAdminAuthenticated(): bool
    {
        $authUser = session('auth_user');
        return (bool) ($authUser && ($authUser['role'] ?? null) === 'admin');
    }
}

if (!function_exists('canAccessScholarshipAdmin')) {
    function canAccessScholarshipAdmin(): bool
    {
        if (!isAdminAuthenticated()) {
            return false;
        }

        $adminRole = session('auth_user.admin_role');
        return in_array($adminRole, ['scholarship_admin', 'system_admin'], true);
    }
}

if (!function_exists('canAccessDisciplineAdmin')) {
    function canAccessDisciplineAdmin(): bool
    {
        if (!isAdminAuthenticated()) {
            return false;
        }

        $adminRole = session('auth_user.admin_role');
        return in_array($adminRole, ['discipline_admin', 'system_admin'], true);
    }
}

if (!function_exists('canAccessMovementAdmin')) {
    function canAccessMovementAdmin(): bool
    {
        if (!isAdminAuthenticated()) {
            return false;
        }

        $adminRole = session('auth_user.admin_role');
        return in_array($adminRole, ['guard', 'discipline_admin', 'system_admin'], true);
    }
}

if (!function_exists('baseRuleCategories')) {
    function baseRuleCategories(): array
    {
        return [
            'Kad Pelajar',
            'Pemakaian',
            'Kebersihan',
            'Bunyi Bising',
            'Perpustakaan',
            'Trafik Kampus',
            'Kemudahan Kampus',
            'Lain-lain',
        ];
    }
}

if (!function_exists('ensureRuleCategoriesSeeded')) {
    function ensureRuleCategoriesSeeded(): void
    {
        $now = now();
        $rows = collect(baseRuleCategories())
            ->map(fn ($name) => [
                'name' => $name,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        DB::table('rule_categories')->upsert($rows, ['name'], ['updated_at']);
    }
}

if (!function_exists('ruleCategoryOptions')) {
    function ruleCategoryOptions()
    {
        ensureRuleCategoriesSeeded();
        return DB::table('rule_categories')
            ->select('id', 'name')
            ->orderBy('name')
            ->get();
    }
}

if (!function_exists('downloadCsv')) {
    function downloadCsv(string $filename, array $headers, iterable $rows)
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

if (!function_exists('auditLog')) {
    function auditLog(string $action, ?string $targetType = null, ?int $targetId = null, ?string $description = null): void
    {
        try {
            $authUser = session('auth_user');
            DB::table('audit_logs')->insert([
                'actor_type' => $authUser['role'] ?? 'guest',
                'actor_id' => isset($authUser['id']) ? (int) $authUser['id'] : null,
                'action' => $action,
                'target_type' => $targetType,
                'target_id' => $targetId,
                'description' => $description,
                'ip_address' => request()->ip(),
                'user_agent' => Str::limit((string) request()->userAgent(), 255, ''),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Do not block main flow if audit logging fails.
        }
    }
}

if (!function_exists('systemCacheConfigPath')) {
    function systemCacheConfigPath(): string
    {
        return storage_path('app/system-cache.json');
    }
}

if (!function_exists('isSystemCacheEnabled')) {
    function isSystemCacheEnabled(): bool
    {
        $path = systemCacheConfigPath();
        if (!is_file($path)) {
            return true;
        }

        $payload = json_decode((string) file_get_contents($path), true);
        if (!is_array($payload)) {
            return true;
        }

        return (bool) ($payload['enabled'] ?? true);
    }
}

if (!function_exists('setSystemCacheEnabled')) {
    function setSystemCacheEnabled(bool $enabled): void
    {
        $payload = systemCacheMeta();
        $payload['enabled'] = $enabled;
        $payload['updated_at'] = now()->toIso8601String();
        writeSystemCacheMeta($payload);
    }
}

if (!function_exists('systemCacheKeys')) {
    function systemCacheKeys(): array
    {
        return [
            'myhep.home_stats.counts',
            'myhep.dashboard.discipline_stats',
            'myhep.dashboard.scholarship_stats',
            'myhep.dashboard.recent_offenses',
            'myhep.dashboard.recent_fine_applications',
            'myhep.dashboard.recent_scholarship_records',
            'myhep.dashboard.recent_scholarship_announcements',
        ];
    }
}

if (!function_exists('clearSystemCaches')) {
    function clearSystemCaches(): void
    {
        foreach (systemCacheKeys() as $key) {
            Cache::forget($key);
        }

        $payload = systemCacheMeta();
        $payload['last_cleared_at'] = now()->toIso8601String();
        writeSystemCacheMeta($payload);
    }
}

if (!function_exists('systemCacheRemember')) {
    function systemCacheRemember(string $key, int $ttlSeconds, callable $callback)
    {
        if (!isSystemCacheEnabled()) {
            return $callback();
        }

        return Cache::remember($key, now()->addSeconds($ttlSeconds), $callback);
    }
}

if (!function_exists('systemCacheMeta')) {
    function systemCacheMeta(): array
    {
        $path = systemCacheConfigPath();
        if (!is_file($path)) {
            return [
                'enabled' => true,
                'updated_at' => null,
                'last_cleared_at' => null,
            ];
        }

        $payload = json_decode((string) file_get_contents($path), true);
        if (!is_array($payload)) {
            return [
                'enabled' => true,
                'updated_at' => null,
                'last_cleared_at' => null,
            ];
        }

        return [
            'enabled' => (bool) ($payload['enabled'] ?? true),
            'updated_at' => $payload['updated_at'] ?? null,
            'last_cleared_at' => $payload['last_cleared_at'] ?? null,
        ];
    }
}

if (!function_exists('writeSystemCacheMeta')) {
    function writeSystemCacheMeta(array $payload): void
    {
        $path = systemCacheConfigPath();
        $directory = dirname($path);
        if (!is_dir($directory)) {
            @mkdir($directory, 0755, true);
        }

        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

if (!function_exists('myhepWebPushConfig')) {
    function myhepWebPushConfig(): array
    {
        return [
            'subject' => (string) config('services.webpush.subject', ''),
            'public_key' => (string) config('services.webpush.public_key', ''),
            'private_key' => (string) config('services.webpush.private_key', ''),
            'icon' => (string) config('services.webpush.icon', '/images/pwa/icon-192.png'),
            'badge' => (string) config('services.webpush.badge', '/images/pwa/icon-192.png'),
            'openssl_conf' => (string) config('services.webpush.openssl_conf', ''),
            'ca_bundle' => (string) config('services.webpush.ca_bundle', ''),
        ];
    }
}

if (!function_exists('myhepFirstExistingPath')) {
    function myhepFirstExistingPath(array $paths): ?string
    {
        foreach ($paths as $path) {
            $path = trim((string) $path);
            if ($path !== '' && is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}

if (!function_exists('myhepConfigureWebPushRuntime')) {
    function myhepConfigureWebPushRuntime(): array
    {
        $config = myhepWebPushConfig();

        $opensslConf = myhepFirstExistingPath([
            $config['openssl_conf'],
            base_path('openssl.cnf'),
            'C:\\laragon\\bin\\php\\php-8.3.28-Win32-vs16-x64\\extras\\ssl\\openssl.cnf',
            'C:\\laragon\\bin\\php\\php-8.1.10-Win32-vs16-x64\\extras\\ssl\\openssl.cnf',
            'C:\\laragon\\bin\\apache\\httpd-2.4.62-240904-win64-VS17\\conf\\openssl.cnf',
            'C:\\laragon\\bin\\apache\\httpd-2.4.54-win64-VS16\\conf\\openssl.cnf',
        ]);

        $caBundle = myhepFirstExistingPath([
            $config['ca_bundle'],
            base_path('cacert.pem'),
            'C:\\laragon\\etc\\ssl\\cacert.pem',
            'C:\\laragon\\bin\\git\\mingw64\\etc\\ssl\\cert.pem',
            'C:\\laragon\\bin\\git\\mingw64\\etc\\ssl\\certs\\ca-bundle.crt',
        ]);

        if ($opensslConf) {
            putenv('OPENSSL_CONF='.$opensslConf);
            $_ENV['OPENSSL_CONF'] = $opensslConf;
            $_SERVER['OPENSSL_CONF'] = $opensslConf;
        }

        if ($caBundle) {
            putenv('SSL_CERT_FILE='.$caBundle);
            putenv('CURL_CA_BUNDLE='.$caBundle);
            $_ENV['SSL_CERT_FILE'] = $caBundle;
            $_SERVER['SSL_CERT_FILE'] = $caBundle;
            $_ENV['CURL_CA_BUNDLE'] = $caBundle;
            $_SERVER['CURL_CA_BUNDLE'] = $caBundle;

            @ini_set('openssl.cafile', $caBundle);
            @ini_set('curl.cainfo', $caBundle);
        }

        return [
            'openssl_conf' => $opensslConf,
            'ca_bundle' => $caBundle,
        ];
    }
}

if (!function_exists('myhepWebPushEnabled')) {
    function myhepWebPushEnabled(): bool
    {
        $config = myhepWebPushConfig();

        return Schema::hasTable('push_subscriptions')
            && $config['subject'] !== ''
            && $config['public_key'] !== ''
            && $config['private_key'] !== ''
            && class_exists(\Minishlink\WebPush\WebPush::class)
            && class_exists(\Minishlink\WebPush\Subscription::class);
    }
}

if (!function_exists('myhepPushAbsoluteUrl')) {
    function myhepPushAbsoluteUrl(?string $url = null): string
    {
        if (!$url) {
            return '/';
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return Str::startsWith($url, '/') ? $url : '/'.ltrim($url, '/');
    }
}

if (!function_exists('myhepSendPushNotification')) {
    function myhepSendPushNotification(string $userType, int $userId, array $message): void
    {
        if ($userId <= 0 || !myhepWebPushEnabled()) {
            return;
        }

        try {
            $subscriptions = DB::table('push_subscriptions')
                ->where('user_type', $userType)
                ->where('user_id', $userId)
                ->get();

            if ($subscriptions->isEmpty()) {
                return;
            }

            $config = myhepWebPushConfig();
            $runtime = myhepConfigureWebPushRuntime();
            $payload = json_encode([
                'title' => (string) ($message['title'] ?? 'StudentEdge'),
                'body' => (string) ($message['body'] ?? ''),
                'url' => myhepPushAbsoluteUrl($message['url'] ?? ($userType === 'admin' ? '/admin/dashboard' : '/student/dashboard')),
                'tag' => (string) ($message['tag'] ?? 'studentedge-general'),
                'icon' => myhepPushAbsoluteUrl($message['icon'] ?? $config['icon']),
                'badge' => myhepPushAbsoluteUrl($message['badge'] ?? $config['badge']),
                'requireInteraction' => (bool) ($message['requireInteraction'] ?? false),
                'renotify' => (bool) ($message['renotify'] ?? false),
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $clientOptions = [];
            if (!empty($runtime['ca_bundle'])) {
                $clientOptions['verify'] = $runtime['ca_bundle'];
            }

            $webPush = new \Minishlink\WebPush\WebPush([
                'VAPID' => [
                    'subject' => $config['subject'],
                    'publicKey' => $config['public_key'],
                    'privateKey' => $config['private_key'],
                ],
            ], [], 30, $clientOptions);

            foreach ($subscriptions as $subscription) {
                try {
                    $report = $webPush->sendOneNotification(
                        \Minishlink\WebPush\Subscription::create([
                            'endpoint' => $subscription->endpoint,
                            'publicKey' => $subscription->public_key,
                            'authToken' => $subscription->auth_token,
                            'contentEncoding' => $subscription->content_encoding ?: 'aes128gcm',
                        ]),
                        $payload,
                        ['TTL' => (int) ($message['ttl'] ?? 300)]
                    );

                    if (!$report->isSuccess()) {
                        Log::warning('Web push delivery failed.', [
                            'user_type' => $userType,
                            'user_id' => $userId,
                            'endpoint_hash' => $subscription->endpoint_hash ?? null,
                            'reason' => $report->getReason(),
                            'tag' => $message['tag'] ?? 'studentedge-general',
                        ]);
                    }

                    if (method_exists($report, 'isSubscriptionExpired') && $report->isSubscriptionExpired()) {
                        DB::table('push_subscriptions')
                            ->where('endpoint_hash', $subscription->endpoint_hash)
                            ->delete();
                    }
                } catch (\Throwable $e) {
                    Log::warning('Web push delivery threw an exception.', [
                        'user_type' => $userType,
                        'user_id' => $userId,
                        'endpoint_hash' => $subscription->endpoint_hash ?? null,
                        'message' => $e->getMessage(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Web push dispatch bootstrap failed.', [
                'user_type' => $userType,
                'user_id' => $userId,
                'message' => $e->getMessage(),
            ]);
        }
    }
}

if (!function_exists('myhepPushSubscribedUserIds')) {
    function myhepPushSubscribedUserIds(string $userType): array
    {
        if (!Schema::hasTable('push_subscriptions')) {
            return [];
        }

        return DB::table('push_subscriptions')
            ->where('user_type', $userType)
            ->distinct()
            ->pluck('user_id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values()
            ->all();
    }
}

if (!function_exists('myhepSendPushToMany')) {
    function myhepSendPushToMany(string $userType, iterable $userIds, array $message): void
    {
        $sent = [];

        foreach ($userIds as $userId) {
            $userId = (int) $userId;
            if ($userId <= 0 || isset($sent[$userId])) {
                continue;
            }

            $sent[$userId] = true;
            myhepSendPushNotification($userType, $userId, $message);
        }
    }
}

if (!function_exists('myhepSendPushToAllStudents')) {
    function myhepSendPushToAllStudents(array $message): void
    {
        myhepSendPushToMany('student', myhepPushSubscribedUserIds('student'), $message);
    }
}

if (!function_exists('myhepAdminRolesForScope')) {
    function myhepAdminRolesForScope(string $scope): array
    {
        return match ($scope) {
            'scholarship' => ['scholarship_admin', 'system_admin'],
            'discipline' => ['discipline_admin', 'system_admin'],
            'movement' => ['guard', 'discipline_admin', 'system_admin'],
            'backoffice' => ['scholarship_admin', 'discipline_admin', 'system_admin'],
            default => ['system_admin'],
        };
    }
}

if (!function_exists('myhepSendPushToAdminsByScope')) {
    function myhepSendPushToAdminsByScope(string $scope, array $message): void
    {
        $subscribedAdminIds = myhepPushSubscribedUserIds('admin');
        if ($subscribedAdminIds === []) {
            return;
        }

        $adminIds = DB::table('admins')
            ->whereIn('id', $subscribedAdminIds)
            ->whereIn('role', myhepAdminRolesForScope($scope))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        myhepSendPushToMany('admin', $adminIds, $message);
    }
}

if (!function_exists('myhepSendPushToAccountsByEmail')) {
    function myhepSendPushToAccountsByEmail(?string $email, array $message): void
    {
        $email = strtolower(trim((string) $email));
        if ($email === '') {
            return;
        }

        $studentIds = DB::table('students')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $adminIds = DB::table('admins')
            ->whereRaw('LOWER(email) = ?', [$email])
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        myhepSendPushToMany('student', $studentIds, $message);
        myhepSendPushToMany('admin', $adminIds, $message);
    }
}
