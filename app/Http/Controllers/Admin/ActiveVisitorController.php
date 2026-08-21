<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AccountSessionManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ActiveVisitorController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
        ]);
        $activeSince = now()->subMinutes((int) config('session.lifetime', 120));

        $query = DB::table('account_sessions')
            ->leftJoin('students', function ($join): void {
                $join->on('students.id', '=', 'account_sessions.owner_id')
                    ->where('account_sessions.owner_type', '=', 'student');
            })
            ->leftJoin('admins', function ($join): void {
                $join->on('admins.id', '=', 'account_sessions.owner_id')
                    ->where('account_sessions.owner_type', '=', 'admin');
            })
            ->where('account_sessions.last_seen_at', '>=', $activeSince)
            ->select('account_sessions.*', DB::raw('COALESCE(students.full_name, admins.full_name) as account_name'))
            ->orderByDesc('account_sessions.last_seen_at');

        if (filled($filters['q'] ?? null)) {
            $term = trim($filters['q']);
            $query->where(function ($subquery) use ($term): void {
                $subquery->where('students.full_name', 'like', "%{$term}%")
                    ->orWhere('admins.full_name', 'like', "%{$term}%")
                    ->orWhere('account_sessions.ip_address', 'like', "%{$term}%");
            });
        }

        return view('admin.active_visitors.index', [
            'sessions' => $query->paginate(25)->withQueryString(),
            'filters' => $filters,
            'activeSince' => $activeSince,
            'activeCount' => DB::table('account_sessions')->where('last_seen_at', '>=', $activeSince)->count(),
            'studentCount' => DB::table('account_sessions')->where('owner_type', 'student')->where('last_seen_at', '>=', $activeSince)->count(),
            'adminCount' => DB::table('account_sessions')->where('owner_type', 'admin')->where('last_seen_at', '>=', $activeSince)->count(),
        ]);
    }

    public function clear(Request $request, AccountSessionManager $sessionManager): RedirectResponse
    {
        $currentSessionId = $request->session()->getId();
        $mode = $request->input('mode', 'others'); // 'others' (all except current) or 'all'

        if ($mode === 'all') {
            DB::table('account_sessions')->delete();
            $auth = session('auth_user');
            if (is_array($auth)) {
                $sessionManager->syncCurrent($request, [
                    'type' => $auth['account_type'] ?? 'admin',
                    'id' => $auth['id'] ?? 1,
                    'active_role' => $auth['admin_role'] ?? 'system_admin',
                    'active_account_id' => $auth['id'] ?? 1,
                ]);
            }
        } else {
            DB::table('account_sessions')
                ->where('session_id', '!=', $currentSessionId)
                ->delete();
        }

        return redirect()->route('admin.active-visitors.index')
            ->with('success', __('Active visitor logs cleared successfully.'));
    }

    public function destroy(int $id): RedirectResponse
    {
        DB::table('account_sessions')->where('id', $id)->delete();

        return redirect()->route('admin.active-visitors.index')
            ->with('success', __('Visitor session record deleted.'));
    }
}
