<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ProgramApprovalRouting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProgramController extends Controller
{
    private const METHODS = ['pdf', 'docx', 'none'];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(['active', 'completed'])],
            'scope' => ['nullable', Rule::in(['mine', 'others', 'review'])],
        ]);
        $filters['scope'] ??= 'mine';
        $query = DB::table('programs')->leftJoin('admins', 'admins.id', '=', 'programs.created_by')
            ->leftJoin('program_reports', 'program_reports.program_id', '=', 'programs.id')
            ->select(
                'programs.*',
                'admins.full_name as director_name',
                'program_reports.status as report_status',
                'program_reports.tpsa_reviewer_id',
                'program_reports.director_reviewer_id',
                'program_reports.kj_hep_reviewer_id'
            );
        if (filled($filters['q'] ?? null)) {
            $q = trim($filters['q']);
            $query->where(fn ($builder) => $builder->where('programs.title', 'like', "%{$q}%")
                ->orWhere('programs.reference_no', 'like', "%{$q}%")
                ->orWhere('programs.venue', 'like', "%{$q}%")
                ->orWhere('admins.full_name', 'like', "%{$q}%"));
        }
        if (filled($filters['status'] ?? null)) {
            $query->where('programs.status', $filters['status']);
        }
        $adminId = (int) session('auth_user.id');
        if (($filters['scope'] ?? null) === 'mine') $query->where('programs.created_by', $adminId);
        if (($filters['scope'] ?? null) === 'others') $query->where('programs.created_by', '!=', $adminId);
        if (($filters['scope'] ?? null) === 'review') {
            $query->where(function ($builder) use ($adminId): void {
                $builder->where(fn ($stage) => $stage->where('program_reports.status', 'pending_tpsa')->where('program_reports.tpsa_reviewer_id', $adminId))
                    ->orWhere(fn ($stage) => $stage->where('program_reports.status', 'pending_director')->where('program_reports.director_reviewer_id', $adminId))
                    ->orWhere(fn ($stage) => $stage->where('program_reports.status', 'pending_kj_hep')->where('program_reports.kj_hep_reviewer_id', $adminId));
            });
        }

        $programs = $query->orderByDesc('programs.updated_at')->paginate(15)->withQueryString();
        $hasOversight = $this->canAssignReviewers();
        $programs->getCollection()->each(function (object $program) use ($hasOversight): void {
            $program->can_view_detail = true;
            $program->is_owned = (int) $program->created_by === (int) session('auth_user.id');
            $program->can_manage = (session('auth_user.admin_role') === 'system_admin') || $program->is_owned;
        });

        $stats = [
            'total_students' => DB::table('students')->count(),
            'total' => DB::table('programs')->count(),
            'pending' => DB::table('program_reports')->whereIn('status', ['pending_tpsa', 'pending_director', 'pending_kj_hep'])->count(),
            'active' => DB::table('programs')->where('status', 'active')->count(),
            'archived' => DB::table('program_reports')->where('status', 'archived')->count(),
            'awaiting_me' => DB::table('program_reports')->where(function ($builder) use ($adminId): void {
                $builder->where(fn ($stage) => $stage->where('status', 'pending_tpsa')->where('tpsa_reviewer_id', $adminId))
                    ->orWhere(fn ($stage) => $stage->where('status', 'pending_director')->where('director_reviewer_id', $adminId))
                    ->orWhere(fn ($stage) => $stage->where('status', 'pending_kj_hep')->where('kj_hep_reviewer_id', $adminId));
            })->count(),
        ];

        return view('admin.programs.index', [
            'programs' => $programs,
            'filters' => $filters,
            'stats' => $stats,
        ]);
    }

    public function create(): View
    {
        $authStaff = DB::table('admins')->where('id', session('auth_user.id'))->first();
        $defaultBranch = $authStaff?->reporting_branch ?: ProgramApprovalRouting::inferBranch($authStaff?->staff_department, $authStaff?->position) ?: 'tpsa';

        return view('admin.programs.form', [
            'program' => null,
            'defaultBranch' => $defaultBranch,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProgram($request);
        $programId = DB::transaction(function () use ($request, $validated): int {
            $now = now();
            $programId = DB::table('programs')->insertGetId($this->programPayload($validated) + [
                'created_by' => (int) session('auth_user.id'),
                'status' => 'active',
                'paperwork_approval_confirmed_at' => $validated['registration_type'] === 'approved_program' ? $now : null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            if ($validated['registration_type'] === 'approved_program') {
                $this->createPaperworkVersion($request, $programId, 1, $validated);
            }

            return $programId;
        });
        auditLog('programs.create', 'programs', $programId, $validated['registration_type'] === 'approved_program' ? 'Approved program registered' : 'Attendance-only activity created');

        return redirect()->route('admin.programs.operations', $programId)->with('success', $validated['registration_type'] === 'approved_program'
            ? __('Approved program registered. You can now prepare attendance and the participant questionnaire.')
            : __('Attendance-only activity created. Configure points and open attendance when ready.'));
    }

    public function show(int $program): View
    {
        $record = $this->program($program);
        abort_unless($this->canViewDetail($record), 403);
        return view('admin.programs.show', [
            'program' => $record,
            'canEdit' => $this->canManage($record),
            'report' => DB::table('program_reports')->where('program_id', $program)->first(),
        ]);
    }

    public function edit(int $program): View
    {
        $record = $this->program($program);
        abort_unless($this->canManage($record) && $record->status === 'active', 403);
        $authStaff = DB::table('admins')->where('id', $record->created_by)->first();
        $defaultBranch = $record->approval_branch ?: ($authStaff?->reporting_branch ?: ProgramApprovalRouting::inferBranch($authStaff?->staff_department, $authStaff?->position) ?: 'tpsa');

        return view('admin.programs.form', [
            'program' => $record,
            'defaultBranch' => $defaultBranch,
        ]);
    }

    public function update(Request $request, int $program): RedirectResponse
    {
        $record = $this->program($program);
        abort_unless($this->canManage($record) && $record->status === 'active', 403);
        $validated = $this->validateProgram($request);
        DB::transaction(function () use ($request, $validated, $program): void {
            DB::table('programs')->where('id', $program)->update($this->programPayload($validated) + ['updated_at' => now()]);
            if ($validated['registration_type'] === 'approved_program') {
                $version = ((int) DB::table('program_paperworks')->where('program_id', $program)->max('version')) + 1;
                $this->createPaperworkVersion($request, $program, $version, $validated);
            }
        });
        auditLog('programs.update', 'programs', $program, 'Program paperwork updated and versioned');

        return redirect()->route('admin.programs.show', $program)->with('success', __('Program information and paperwork were updated.'));
    }

    public function destroy(int $program): RedirectResponse
    {
        $record = $this->program($program);
        abort_unless($this->canManage($record), 403);

        $paperworkPaths = DB::table('program_paperworks')->where('program_id', $program)
            ->where('disk', 'local')->whereNotNull('path')->pluck('path')->all();

        $certPaths = Schema::hasTable('program_certificates')
            ? DB::table('program_certificates')->where('program_id', $program)->whereNotNull('path')->pluck('path')->all()
            : [];

        $reportPaths = Schema::hasTable('program_reports')
            ? DB::table('program_reports')->where('program_id', $program)->whereNotNull('attachment_path')->pluck('attachment_path')->all()
            : [];

        DB::transaction(function () use ($program): void {
            if (Schema::hasTable('program_certificates')) {
                DB::table('program_certificates')->where('program_id', $program)->delete();
            }
            if (Schema::hasTable('program_attendances')) {
                DB::table('program_attendances')->where('program_id', $program)->delete();
            }
            if (Schema::hasTable('program_surveys')) {
                DB::table('program_surveys')->where('program_id', $program)->delete();
            }
            if (Schema::hasTable('program_reports')) {
                DB::table('program_reports')->where('program_id', $program)->delete();
            }
            if (Schema::hasTable('program_reviewers')) {
                DB::table('program_reviewers')->where('program_id', $program)->delete();
            }
            DB::table('program_paperworks')->where('program_id', $program)->delete();
            DB::table('programs')->where('id', $program)->delete();
        });

        foreach ($paperworkPaths as $path) {
            Storage::disk('local')->delete($path);
        }
        foreach ($certPaths as $path) {
            Storage::disk('local')->delete($path);
        }
        foreach ($reportPaths as $path) {
            Storage::disk('local')->delete($path);
        }

        auditLog('programs.delete', 'programs', $program, 'Program record and all associated data deleted');

        return redirect()->route('admin.programs.index')->with('success', __('Program and all associated data were successfully deleted.'));
    }

    public function download(int $program, int $paperwork): StreamedResponse
    {
        $record = $this->program($program);
        abort_unless($this->canViewPaperwork($record), 403);
        $document = DB::table('program_paperworks')->where('program_id', $program)->where('id', $paperwork)->first();
        abort_unless($document && $document->disk === 'local' && $document->path && Storage::disk('local')->exists($document->path), 404);
        auditLog('programs.paperwork.download', 'programs', $program, 'Program paperwork downloaded');

        return Storage::disk('local')->download($document->path, $document->original_name, [
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function validateProgram(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'registration_type' => ['required', Rule::in(['approved_program', 'attendance_only_activity'])],
            'approval_branch' => ['required', Rule::in(['tpa', 'tpsa', 'tpsp'])],
            'reference_no' => [Rule::requiredIf(fn () => $request->input('registration_type') === 'approved_program'), 'nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string', 'max:10000'],
            'objectives' => ['nullable', 'string', 'max:10000'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'venue' => ['required', 'string', 'max:180'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'geofence_radius_m' => ['nullable', 'integer', 'between:10,5000'],
            'target_participants' => ['required', 'string', 'max:255'],
            'estimated_participants' => ['nullable', 'integer', 'between:1,100000'],
            'participation_points' => ['required', 'integer', 'between:0,100'],
            'certificate_enabled' => ['sometimes', 'boolean'],
            'certificate_template' => ['sometimes', 'nullable', Rule::in(['standard_placeholder'])],
            'paperwork_method' => ['required', Rule::in(self::METHODS)],
            'paperwork_file' => ['nullable', 'file', 'mimes:pdf,docx', 'max:20480', Rule::requiredIf(fn () => $request->isMethod('post') && $request->input('registration_type') === 'approved_program')],
        ]);

        if ($validated['registration_type'] === 'attendance_only_activity') {
            $validated['paperwork_method'] = 'none';
            $validated['questionnaire_enabled'] = false;
            $validated['reference_no'] = $validated['reference_no'] ?? null;
        }

        return $validated;
    }

    private function programPayload(array $data): array
    {
        return collect($data)->except('paperwork_file')->map(fn ($value) => is_string($value) ? trim($value) : $value)->all();
    }

    private function createPaperworkVersion(Request $request, int $programId, int $version, array $data): void
    {
        $file = $request->file('paperwork_file');
        $path = $file?->storeAs('program_paperworks/'.$programId, 'v'.$version.'-'.Str::uuid().'.'.$file->getClientOriginalExtension(), 'local');
        DB::table('program_paperworks')->insert([
            'program_id' => $programId,
            'version' => $version,
            'method' => $data['paperwork_method'],
            'disk' => $file ? 'local' : null,
            'path' => $path,
            'original_name' => $file?->getClientOriginalName(),
            'mime_type' => $file?->getMimeType(),
            'size_bytes' => $file?->getSize(),
            'structured_snapshot' => json_encode($this->programPayload($data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'created_by' => (int) session('auth_user.id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function program(int $id): object
    {
        $record = DB::table('programs')->leftJoin('admins', 'admins.id', '=', 'programs.created_by')
            ->leftJoin('program_reports', 'program_reports.program_id', '=', 'programs.id')
            ->where('programs.id', $id)->select(
                'programs.*',
                'admins.full_name as director_name',
                'program_reports.status as report_status',
                'program_reports.tpsa_reviewer_id as report_tpsa_reviewer_id',
                'program_reports.director_reviewer_id as report_director_reviewer_id',
                'program_reports.kj_hep_reviewer_id as report_kj_hep_reviewer_id'
            )->first();
        abort_unless($record, 404);

        return $record;
    }

    private function canManage(object $program): bool
    {
        return (int) $program->created_by === (int) session('auth_user.id')
            || in_array(session('auth_user.admin_role'), ['student_affairs_head', 'system_admin'], true);
    }

    private function canViewPaperwork(object $program): bool
    {
        return $this->canManage($program) || $this->isWorkflowReviewer($program);
    }

    private function canViewDetail(object $program): bool
    {
        return (int) session('auth_user.id') > 0;
    }

    private function canAssignReviewers(): bool
    {
        return in_array(session('auth_user.admin_role'), ['student_affairs_head', 'system_admin'], true);
    }

    private function isWorkflowReviewer(object $program): bool
    {
        $adminId = (int) session('auth_user.id');

        if ($adminId <= 0) {
            return false;
        }
        return in_array($adminId, [
            (int) ($program->report_tpsa_reviewer_id ?? 0),
            (int) ($program->report_director_reviewer_id ?? 0),
            (int) ($program->report_kj_hep_reviewer_id ?? 0),
        ], true);
    }
}
