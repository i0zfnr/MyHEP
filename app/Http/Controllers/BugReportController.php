<?php

namespace App\Http\Controllers;

use App\Mail\BugReportSubmitted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class BugReportController extends Controller
{
    private const CATEGORIES = ['bug', 'feature', 'account', 'other'];

    public function create(Request $request): View
    {
        $data = [
            'categories' => self::CATEGORIES,
            'authenticatedReporter' => $this->authenticatedReporter($request),
        ];

        return view($data['authenticatedReporter'] ? 'bug_reports.create_authenticated' : 'bug_reports.create', $data);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'reporter_name' => ['required', 'string', 'max:150'],
            'reporter_email' => ['required', 'email', 'max:150'],
            'category' => ['required', 'in:' . implode(',', self::CATEGORIES)],
            'subject' => ['required', 'string', 'max:200'],
            'page_url' => ['nullable', 'url', 'max:500'],
            'description' => ['required', 'string', 'max:3000'],
            'screenshot' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($authenticatedReporter = $this->authenticatedReporter($request)) {
            $validated['reporter_name'] = $authenticatedReporter->full_name;

            if (filled($authenticatedReporter->email)) {
                $validated['reporter_email'] = $authenticatedReporter->email;
            }
        }

        $screenshotPath = $request->file('screenshot')
            ? $request->file('screenshot')->store('bug_reports/screenshots', 'public')
            : null;

        $bugReportId = \DB::table('bug_reports')->insertGetId([
            'reporter_name' => trim($validated['reporter_name']),
            'reporter_email' => trim($validated['reporter_email']),
            'category' => $validated['category'],
            'subject' => trim($validated['subject']),
            'page_url' => filled($validated['page_url'] ?? null) ? trim($validated['page_url']) : null,
            'description' => trim($validated['description']),
            'screenshot_path' => $screenshotPath,
            'status' => 'new',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        auditLog('bug_reports.create', 'bug_reports', $bugReportId, 'Public bug report submitted');

        $report = [
            'id' => $bugReportId,
            'reporter_name' => trim($validated['reporter_name']),
            'reporter_email' => trim($validated['reporter_email']),
            'category' => $validated['category'],
            'subject' => trim($validated['subject']),
            'page_url' => filled($validated['page_url'] ?? null) ? trim($validated['page_url']) : null,
            'description' => trim($validated['description']),
            'has_screenshot' => $screenshotPath !== null,
        ];

        try {
            Mail::to((string) config('mail.system_admin_report_address'))
                ->send(new BugReportSubmitted($report));
            \DB::table('bug_reports')->where('id', $bugReportId)->update([
                'email_notification_status' => 'sent',
                'email_notification_error' => null,
                'email_notification_attempted_at' => now(),
            ]);
            auditLog('bug_reports.email_sent', 'bug_reports', $bugReportId, 'New report email sent to system admin');
        } catch (Throwable $exception) {
            \DB::table('bug_reports')->where('id', $bugReportId)->update([
                'email_notification_status' => 'failed',
                'email_notification_error' => \Illuminate\Support\Str::limit($exception->getMessage(), 1000, ''),
                'email_notification_attempted_at' => now(),
            ]);
            report($exception);
            auditLog('bug_reports.email_failed', 'bug_reports', $bugReportId, 'New report email could not be sent');
        }

        try {
            myhepSendPushToAdminsByScope('system', [
                'category' => 'account',
                'title' => 'New system report',
                'body' => trim($validated['subject']) . ' — submitted by ' . trim($validated['reporter_name']),
                'url' => route('admin.bug-reports.index'),
                'tag' => 'bug-report-' . $bugReportId,
                'requireInteraction' => true,
            ]);
        } catch (Throwable $exception) {
            report($exception);
            auditLog('bug_reports.push_failed', 'bug_reports', $bugReportId, 'New report push notification could not be sent');
        }

        return redirect()
            ->route('bug-reports.create')
            ->with('success', __('bug_reports.public_success', ['id' => $bugReportId]));
    }

    private function authenticatedReporter(Request $request): ?object
    {
        $authUser = $request->session()->get('auth_user', []);

        $table = match ($authUser['role'] ?? null) {
            'student' => 'students',
            'admin' => 'admins',
            default => null,
        };

        if ($table === null || empty($authUser['id'])) {
            return null;
        }

        return DB::table($table)
            ->select('full_name', 'email')
            ->find((int) $authUser['id']);
    }
}
