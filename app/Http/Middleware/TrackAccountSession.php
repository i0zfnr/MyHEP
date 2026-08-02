<?php

namespace App\Http\Middleware;

use App\Support\AccountSessionManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAccountSession
{
    public function __construct(private readonly AccountSessionManager $sessions) {}

    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->sessions->available()) {
            return $next($request);
        }

        $initialSessionId = $request->session()->getId();
        $initialAuth = $request->session()->get('auth_user');
        $owner = null;

        if (is_array($initialAuth)) {
            $owner = $this->sessions->owner($request);
            if (! $owner) {
                $owner = $this->sessions->adopt($request);
            } elseif (! $this->sessions->exists($request, $owner)) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')->withErrors([
                    'session' => __('ui.session_expired_or_revoked'),
                ]);
            } else {
                $this->sessions->touch($request, $owner);
            }
        }

        $response = $next($request);
        $finalAuth = $request->session()->get('auth_user');

        if (is_array($initialAuth) && ! is_array($finalAuth)) {
            $this->sessions->remove($initialSessionId);
        } elseif (is_array($finalAuth)) {
            $owner ??= $this->sessions->owner($request) ?? $this->sessions->adopt($request);
            if ($owner && $initialSessionId !== $request->session()->getId()) {
                $this->sessions->rotate($request, $initialSessionId, $owner);
            }
        }

        return $response;
    }
}
