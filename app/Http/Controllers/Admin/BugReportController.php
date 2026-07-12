<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BugReportController extends Controller
{
    private const STATUSES = ['new', 'in_progress', 'resolved', 'closed'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(array_merge(['all'], self::STATUSES))],
        ]);

        $query = DB::table('bug_reports')
            ->select('bug_reports.*', 'admins.full_name as resolved_by_name')
            ->leftJoin('admins', 'admins.id', '=', 'bug_reports.resolved_by')
            ->orderByRaw("case when bug_reports.status in ('new', 'in_progress') then 0 else 1 end")
            ->orderByDesc('bug_reports.created_at');

        if (!empty($filters['q'])) {
            $q = trim($filters['q']);
            $query->where(function ($sub) use ($q) {
                $sub->where('bug_reports.subject', 'like', "%{$q}%")
                    ->orWhere('bug_reports.description', 'like', "%{$q}%")
                    ->orWhere('bug_reports.reporter_name', 'like', "%{$q}%")
                    ->orWhere('bug_reports.reporter_email', 'like', "%{$q}%");
            });
        }

        if (($filters['status'] ?? 'all') !== 'all') {
            $query->where('bug_reports.status', $filters['status']);
        }

        $bugReports = $query->paginate(10)->withQueryString();

        $stats = [
            'new' => (int) DB::table('bug_reports')->where('status', 'new')->count(),
            'in_progress' => (int) DB::table('bug_reports')->where('status', 'in_progress')->count(),
            'resolved' => (int) DB::table('bug_reports')->where('status', 'resolved')->count(),
            'closed' => (int) DB::table('bug_reports')->where('status', 'closed')->count(),
        ];

        return view('admin.bug_reports.index', [
            'bugReports' => $bugReports,
            'filters' => $filters,
            'statuses' => self::STATUSES,
            'stats' => $stats,
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $bugReport = DB::table('bug_reports')->where('id', $id)->first();

        if (!$bugReport) {
            return redirect()->route('admin.bug-reports.index')
                ->withErrors(['bug_reports' => __('bug_reports.not_found')]);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(self::STATUSES)],
            'admin_notes' => ['nullable', 'string', 'max:3000'],
        ]);

        $resolvedStatuses = ['resolved', 'closed'];
        $status = $validated['status'];
        $adminId = (int) session('auth_user.id');

        DB::table('bug_reports')
            ->where('id', $id)
            ->update([
                'status' => $status,
                'admin_notes' => filled($validated['admin_notes'] ?? null) ? trim($validated['admin_notes']) : null,
                'resolved_by' => in_array($status, $resolvedStatuses, true) ? $adminId : null,
                'resolved_at' => in_array($status, $resolvedStatuses, true) ? now() : null,
                'updated_at' => now(),
            ]);

        auditLog('bug_reports.update', 'bug_reports', $id, 'Bug report status updated to ' . $status);

        myhepSendPushToAccountsByEmail($bugReport->reporter_email, [
            'title' => 'Bug report updated',
            'body' => 'Your submitted bug report "' . \Illuminate\Support\Str::limit($bugReport->subject, 48) . '" is now ' . str_replace('_', ' ', $status) . '.',
            'url' => route('bug-reports.create'),
            'tag' => 'bug-report-' . $id,
            'requireInteraction' => in_array($status, ['resolved', 'closed'], true),
        ]);

        return redirect()->route('admin.bug-reports.index')
            ->with('success', __('bug_reports.admin_update_success'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $bugReport = DB::table('bug_reports')->where('id', $id)->first();

        if (!$bugReport) {
            return redirect()->route('admin.bug-reports.index')
                ->withErrors(['bug_reports' => __('bug_reports.not_found')]);
        }

        if (!empty($bugReport->screenshot_path) && Storage::disk('public')->exists($bugReport->screenshot_path)) {
            Storage::disk('public')->delete($bugReport->screenshot_path);
        }

        DB::table('bug_reports')->where('id', $id)->delete();
        auditLog('bug_reports.delete', 'bug_reports', $id, 'Bug report deleted by admin');

        return redirect()->route('admin.bug-reports.index')
            ->with('success', __('bug_reports.admin_delete_success'));
    }
}
