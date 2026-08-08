<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AccountSessionManager
{
    public function available(): bool
    {
        return Schema::hasTable('account_sessions');
    }

    public function establish(Request $request, string $ownerType, int $ownerId): void
    {
        $owner = ['type' => $ownerType, 'id' => $ownerId];
        $request->session()->put('session_owner', $owner);
        $this->register($request, $owner);
    }

    public function adopt(Request $request): ?array
    {
        $authUser = $request->session()->get('auth_user');
        if (! is_array($authUser)) {
            return null;
        }

        $owner = ! empty($authUser['linked_admin_id'])
            ? ['type' => 'admin', 'id' => (int) $authUser['linked_admin_id']]
            : ['type' => (string) $authUser['role'], 'id' => (int) $authUser['id']];

        if (! in_array($owner['type'], ['student', 'admin'], true) || $owner['id'] < 1) {
            return null;
        }

        $request->session()->put('session_owner', $owner);
        $this->register($request, $owner);

        return $owner;
    }

    public function owner(Request $request): ?array
    {
        $owner = $request->session()->get('session_owner');
        if (! is_array($owner) || ! in_array($owner['type'] ?? null, ['student', 'admin'], true) || (int) ($owner['id'] ?? 0) < 1) {
            return null;
        }

        return ['type' => $owner['type'], 'id' => (int) $owner['id']];
    }

    public function exists(Request $request, array $owner): bool
    {
        $publicId = $request->session()->get('account_session_public_id');

        return $this->available() && is_string($publicId) && DB::table('account_sessions')
            ->where('public_id', $publicId)
            ->where('owner_type', $owner['type'])
            ->where('owner_id', $owner['id'])
            ->where('last_seen_at', '>=', now()->subMinutes((int) config('session.lifetime', 120)))
            ->exists();
    }

    public function touch(Request $request, array $owner): void
    {
        if (! $this->available()) {
            return;
        }

        $authUser = $request->session()->get('auth_user', []);
        $publicId = $request->session()->get('account_session_public_id');
        $sessionId = $request->session()->getId();
        DB::table('account_sessions')
            ->where('public_id', $publicId)
            ->where('owner_type', $owner['type'])
            ->where('owner_id', $owner['id'])
            ->where(function ($query) use ($authUser, $sessionId): void {
                $query->where('last_seen_at', '<', now()->subMinute())
                    ->orWhere('active_role', '!=', $authUser['role'] ?? '')
                    ->orWhere('active_account_id', '!=', (int) ($authUser['id'] ?? 0))
                    ->orWhere('session_id', '!=', $sessionId);
            })
            ->update([
                'session_id' => $sessionId,
                'active_role' => $authUser['role'] ?? $owner['type'],
                'active_account_id' => (int) ($authUser['id'] ?? $owner['id']),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                'last_seen_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function rotate(Request $request, string $oldSessionId, array $owner): void
    {
        if (! $this->available() || $oldSessionId === $request->session()->getId()) {
            return;
        }

        $publicId = $request->session()->get('account_session_public_id');
        $updated = DB::table('account_sessions')
            ->where('public_id', $publicId)
            ->where('owner_type', $owner['type'])
            ->where('owner_id', $owner['id'])
            ->update(['session_id' => $request->session()->getId(), 'updated_at' => now()]);

        if ($updated === 0) {
            $this->register($request, $owner);
        } else {
            $this->touch($request, $owner);
        }
    }

    public function remove(string $sessionId): void
    {
        if ($this->available()) {
            DB::table('account_sessions')->where('session_id', $sessionId)->delete();
        }
    }

    public function sessionsFor(array $owner, string $currentSessionId): Collection
    {
        if (! $this->available()) {
            return collect();
        }

        return DB::table('account_sessions')
            ->where('owner_type', $owner['type'])
            ->where('owner_id', $owner['id'])
            ->where('last_seen_at', '>=', now()->subMinutes((int) config('session.lifetime', 120)))
            ->orderByDesc('last_seen_at')
            ->get()
            ->map(function ($session) use ($currentSessionId) {
                $session->is_current = hash_equals($currentSessionId, $session->session_id);
                $session->device_label = $this->deviceLabel($session->user_agent);

                return $session;
            });
    }

    public function revoke(array $owner, string $publicId, string $currentSessionId): string
    {
        if (! $this->available()) {
            return 'not_found';
        }

        $session = DB::table('account_sessions')
            ->where('public_id', $publicId)
            ->where('owner_type', $owner['type'])
            ->where('owner_id', $owner['id'])
            ->first();

        if (! $session) {
            return 'not_found';
        }

        if (hash_equals($currentSessionId, $session->session_id)) {
            return 'current';
        }

        $this->deleteBackingSession($session->session_id);
        DB::table('account_sessions')->where('id', $session->id)->delete();

        return 'revoked';
    }

    public function revokeOthers(array $owner, string $currentSessionId): int
    {
        if (! $this->available()) {
            return 0;
        }

        $sessions = DB::table('account_sessions')
            ->where('owner_type', $owner['type'])
            ->where('owner_id', $owner['id'])
            ->where('session_id', '!=', $currentSessionId)
            ->get(['id', 'session_id']);

        foreach ($sessions as $session) {
            $this->deleteBackingSession($session->session_id);
        }

        DB::table('account_sessions')->whereIn('id', $sessions->pluck('id'))->delete();

        return $sessions->count();
    }

    public function revokeAccount(string $ownerType, int $ownerId, ?string $exceptPublicId = null): int
    {
        if (! $this->available()) {
            return 0;
        }

        $query = DB::table('account_sessions')
            ->where('owner_type', $ownerType)
            ->where('owner_id', $ownerId);

        if ($exceptPublicId) {
            $query->where('public_id', '!=', $exceptPublicId);
        }

        $sessions = $query->get(['id', 'session_id']);
        foreach ($sessions as $session) {
            $this->deleteBackingSession($session->session_id);
        }

        DB::table('account_sessions')->whereIn('id', $sessions->pluck('id'))->delete();

        return $sessions->count();
    }

    private function register(Request $request, array $owner): void
    {
        if (! $this->available()) {
            return;
        }

        $authUser = $request->session()->get('auth_user', []);
        $publicId = $request->session()->get('account_session_public_id');
        if (! is_string($publicId) || $publicId === '') {
            $publicId = (string) Str::uuid();
            $request->session()->put('account_session_public_id', $publicId);
        }

        DB::table('account_sessions')->updateOrInsert(
            ['public_id' => $publicId],
            [
                'session_id' => $request->session()->getId(),
                'owner_type' => $owner['type'],
                'owner_id' => $owner['id'],
                'active_role' => $authUser['role'] ?? $owner['type'],
                'active_account_id' => (int) ($authUser['id'] ?? $owner['id']),
                'ip_address' => $request->ip(),
                'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
                'authenticated_at' => now(),
                'last_seen_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    private function deleteBackingSession(string $sessionId): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }

        $connection = config('session.connection');
        $table = config('session.table', 'sessions');
        if ($table && Schema::connection($connection)->hasTable($table)) {
            DB::connection($connection)->table($table)->where('id', $sessionId)->delete();
        }
    }

    private function deviceLabel(?string $userAgent): string
    {
        $userAgent = strtolower((string) $userAgent);
        $browser = str_contains($userAgent, 'edg/') ? 'Edge'
            : (str_contains($userAgent, 'firefox/') ? 'Firefox'
                : (str_contains($userAgent, 'chrome/') ? 'Chrome'
                    : (str_contains($userAgent, 'safari/') ? 'Safari' : __('ui.unknown_browser'))));
        $device = preg_match('/android|iphone|ipad|mobile/', $userAgent) ? __('ui.mobile_device') : __('ui.desktop_device');

        return $browser.' · '.$device;
    }
}
