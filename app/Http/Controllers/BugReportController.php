<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BugReportController extends Controller
{
    private const CATEGORIES = ['bug', 'feature', 'account', 'other'];

    public function create(): View
    {
        return view('bug_reports.create', [
            'categories' => self::CATEGORIES,
        ]);
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

        return redirect()
            ->route('bug-reports.create')
            ->with('success', __('bug_reports.public_success', ['id' => $bugReportId]));
    }
}
