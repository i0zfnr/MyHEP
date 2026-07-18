<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PushSubscriptionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:2048'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'contentEncoding' => ['nullable', 'string', 'max:32'],
        ]);

        $authUser = session('auth_user');
        $endpointHash = hash('sha256', $validated['endpoint']);
        $existingCreatedAt = DB::table('push_subscriptions')
            ->where('endpoint_hash', $endpointHash)
            ->value('created_at');

        DB::table('push_subscriptions')->updateOrInsert(
            ['endpoint_hash' => $endpointHash],
            [
                'user_type' => $authUser['role'],
                'user_id' => (int) $authUser['id'],
                'endpoint' => $validated['endpoint'],
                'public_key' => $validated['keys']['p256dh'],
                'auth_token' => $validated['keys']['auth'],
                'content_encoding' => $validated['contentEncoding'] ?? 'aes128gcm',
                'locale' => app()->getLocale(),
                'user_agent' => Str::limit((string) $request->userAgent(), 255, ''),
                'last_seen_at' => now(),
                'updated_at' => now(),
                'created_at' => $existingCreatedAt ?: now(),
            ]
        );

        return response()->json(['ok' => true]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:2048'],
        ]);

        DB::table('push_subscriptions')
            ->where('endpoint_hash', hash('sha256', $validated['endpoint']))
            ->where('user_type', session('auth_user.role'))
            ->where('user_id', (int) session('auth_user.id'))
            ->delete();

        return response()->json(['ok' => true]);
    }
}
