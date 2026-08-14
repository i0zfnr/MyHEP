<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AiProvider;
use App\Services\OfficialProgramReportExporter;
use App\Support\ProgramApprovalRouting;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProgramOperationController extends Controller
{
    public function operations(int $id): View
    {
        $program = DB::table('programs')->where('id', $id)->first();
        if (! $program) {
            abort(404, __('Program not found.'));
        }

        $authId = (int) session('auth_user.id');
        $authRole = (string) (session('auth_user.admin_role') ?? '');
        $hasOversight = in_array($authRole, ['system_admin', 'student_affairs_head'], true);
        $isDirector = (int) $program->created_by === $authId;

        if (! $hasOversight && ! $isDirector && ! $this->isReviewer($program, $authId)) {
            abort(403, __('You are not authorized to access this program operations workspace.'));
        }

        $latestPaperwork = DB::table('program_paperworks')
            ->where('program_id', $program->id)
            ->orderByDesc('version')
            ->first();

        $survey = DB::table('program_surveys')
            ->where('program_id', $program->id)
            ->orderByDesc('id')
            ->first();

        $questions = $survey
            ? DB::table('program_survey_questions')
                ->where('program_survey_id', $survey->id)
                ->orderBy('sort_order')
                ->get()
            : collect();

        $attendances = DB::table('program_attendances')
            ->where('program_id', $program->id)
            ->orderByDesc('checked_in_at')
            ->get();

        $totalJoined = $attendances->count();
        $internalCount = $attendances->where('attendee_type', 'internal')->count();
        $externalCount = $attendances->where('attendee_type', 'external')->count();
        $estimated = max(1, (int) ($program->estimated_participants ?: 50));
        $attendanceRate = round(($totalJoined / $estimated) * 100, 1);

        $ratings = $attendances->pluck('satisfaction_rating')->filter();
        $averageRating = $ratings->isNotEmpty() ? round($ratings->avg(), 1) : 0.0;

        $surveyResponsesCount = $survey
            ? DB::table('program_survey_responses')
                ->where('program_survey_id', $survey->id)
                ->distinct('program_attendance_id')
                ->count('program_attendance_id')
            : 0;

        $publicCheckinUrl = route('public.programs.qr_checkin', $program->id);
        $report = DB::table('program_reports')->where('program_id', $program->id)->first();
        $reportBranch = $this->reportBranch($program);
        $reportBranchLabel = strtoupper($reportBranch);
        $reportReviewers = $report ? DB::table('admins')->whereIn('id', array_filter([
            $report->tpsa_reviewer_id, $report->director_reviewer_id, $report->kj_hep_reviewer_id,
        ]))->get()->keyBy('id') : collect();
        $canManageReport = ($isDirector || $hasOversight) && (! $report || in_array($report->status, ['draft', 'rejected'], true));
        $canManageCertificates = $isDirector || $hasOversight;
        $canManageAttendance = ($isDirector || $hasOversight) && in_array($program->status, ['active', 'approved', 'scheduled'], true);
        $attendanceSetup = [
            'venue' => filled($program->venue),
            'questionnaire' => ! $program->questionnaire_enabled || ($survey && $survey->status === 'published'),
        ];
        $attendanceReady = ! in_array(false, $attendanceSetup, true);
        $canReviewReport = $report && match ($report->status) {
            'pending_tpsa' => (int) $report->tpsa_reviewer_id === $authId,
            'pending_director' => (int) $report->director_reviewer_id === $authId,
            'pending_kj_hep' => (int) $report->kj_hep_reviewer_id === $authId,
            default => false,
        };

        return view('admin.programs.operations', compact(
            'program',
            'latestPaperwork',
            'survey',
            'questions',
            'attendances',
            'totalJoined',
            'internalCount',
            'externalCount',
            'attendanceRate',
            'averageRating',
            'surveyResponsesCount',
            'publicCheckinUrl',
            'report',
            'reportBranchLabel',
            'reportReviewers',
            'canManageReport',
            'canManageCertificates',
            'canReviewReport',
            'canManageAttendance',
            'attendanceSetup',
            'attendanceReady'
        ));
    }

    public function openAttendance(int $id): RedirectResponse
    {
        $program = $this->ownedActiveProgram($id);
        $publishedSurveyExists = DB::table('program_surveys')
            ->where('program_id', $program->id)
            ->where('status', 'published')
            ->exists();

        if (blank($program->venue) || ($program->questionnaire_enabled && ! $publishedSurveyExists)) {
            return back()->withErrors([
                'attendance' => __('Set the venue and, when enabled, publish a questionnaire before opening attendance.'),
            ]);
        }

        DB::table('programs')->where('id', $program->id)->update([
            'attendance_status' => 'open',
            'attendance_opened_at' => now(),
            'attendance_closed_at' => null,
            'updated_at' => now(),
        ]);
        auditLog('programs.attendance.open', 'programs', $program->id, 'Program attendance opened');

        return back()->with('success', __('Attendance is now open. Participants can use the check-in form.'));
    }

    public function closeAttendance(int $id): RedirectResponse
    {
        $program = $this->ownedActiveProgram($id);
        DB::table('programs')->where('id', $program->id)->update([
            'attendance_status' => 'closed',
            'attendance_closed_at' => now(),
            'updated_at' => now(),
        ]);
        auditLog('programs.attendance.close', 'programs', $program->id, 'Program attendance closed');

        return back()->with('success', __('Attendance is closed. New submissions are no longer accepted.'));
    }

    public function generateReport(Request $request, int $id, AiProvider $ai, OfficialProgramReportExporter $exporter): RedirectResponse
    {
        $program = $this->ownedActiveProgram($id);
        $request->merge(['output_format' => $request->input('output_format', 'docx')]);
        $validated = $request->validate([
            'output_format' => ['required', 'in:docx,pdf,both'],
            'program_images' => ['nullable', 'array', 'max:8'],
            'program_images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'paperwork_file' => ['nullable', 'file', 'mimes:pdf,docx', 'max:20480'],
        ]);
        $data = $this->reportData($program);
        $requiresPaperwork = ($program->registration_type ?? 'approved_program') !== 'attendance_only_activity';
        $hasPaperwork = $request->hasFile('paperwork_file')
            || DB::table('program_paperworks')->where('program_id', $program->id)->exists();
        $activityImages = $request->file('program_images', []);
        $validAttendanceCount = DB::table('program_attendances')
            ->where('program_id', $program->id)
            ->where('validation_status', 'valid')
            ->count();
        $sourceErrors = [];
        if ($requiresPaperwork && ! $hasPaperwork) {
            $sourceErrors['paperwork_file'] = __('Upload the approved paperwork before generating this report.');
        }
        if (count($activityImages) === 0) {
            $sourceErrors['program_images'] = __('Add at least one activity photo from the completed program.');
        }
        if ($validAttendanceCount === 0) {
            $sourceErrors['attendance'] = __('At least one valid attendance record is required to generate the report.');
        }
        if ((bool) ($program->questionnaire_enabled ?? true) && $data['survey_responses'] === 0) {
            $sourceErrors['questionnaire'] = __('This program requires at least one questionnaire response before report generation.');
        }
        if ($sourceErrors !== []) {
            throw ValidationException::withMessages($sourceErrors);
        }
        $prompt = $this->reportPrompt($program, $data);

        try {
            $attachments = array_values(array_filter([$request->file('paperwork_file'), ...$activityImages]));
            $content = $ai->enabled() ? trim($ai->askWithAttachments($prompt, $attachments)) : $this->fallbackReport($program, $data);
        } catch (\Throwable $exception) {
            report($exception);
            $content = $this->fallbackReport($program, $data);
        }

        $imagePaths = [];
        foreach ($activityImages as $image) {
            $path = $image->store('program-report-media/'.$program->id, 'local');
            $imagePaths[] = Storage::disk('local')->path($path);
        }
        $files = $exporter->export($program, $data, $content, $validated['output_format'], $imagePaths);

        $reportValues = [
                'content' => $content,
                'status' => 'draft',
                'ai_provider' => $ai->enabled() ? $ai->name() : null,
                'ai_model' => $ai->enabled() ? $ai->model() : null,
                'generated_by' => (int) session('auth_user.id'),
                'generated_at' => now(),
                'tpsa_reviewer_id' => null,
                'tpsa_reviewed_at' => null,
                'tpsa_review_note' => null,
                'director_reviewer_id' => null,
                'director_reviewed_at' => null,
                'director_review_note' => null,
                'kj_hep_reviewer_id' => null,
                'kj_hep_reviewed_at' => null,
                'kj_hep_review_note' => null,
                'archived_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        if (Schema::hasColumn('program_reports', 'output_format')) {
            $reportValues += [
                'output_format' => $validated['output_format'],
                'docx_path' => $files['docx_path'],
                'pdf_path' => $files['pdf_path'],
            ];
        }
        if (Schema::hasColumn('program_reports', 'source_summary')) {
            $reportValues['source_summary'] = json_encode([
                'paperwork' => $hasPaperwork,
                'activity_images' => count($activityImages),
                'attendance_records' => $validAttendanceCount,
                'questionnaire_responses' => $data['survey_responses'],
                'generated_at' => now()->toIso8601String(),
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        DB::table('program_reports')->updateOrInsert(['program_id' => $program->id], $reportValues);
        auditLog('program_reports.generate', 'programs', $program->id, 'Post-program report draft generated');

        return back()->with('success', __('Report draft generated. Review and edit it before submission to TPSA.'));
    }

    public function downloadReport(int $id, string $format)
    {
        $program = DB::table('programs')->where('id', $id)->first();
        abort_unless($program, 404);
        $authId = (int) session('auth_user.id');
        $authRole = (string) (session('auth_user.admin_role') ?? '');
        abort_unless((int) $program->created_by === $authId || in_array($authRole, ['system_admin', 'student_affairs_head'], true) || $this->isReviewer($program, $authId), 403);
        abort_unless(in_array($format, ['docx', 'pdf'], true), 404);
        $report = DB::table('program_reports')->where('program_id', $id)->first();
        $column = $format.'_path';
        abort_unless($report && $report->{$column} && Storage::disk('local')->exists($report->{$column}), 404);

        return Storage::disk('local')->download($report->{$column}, 'Laporan Program - '.$program->title.'.'.$format);
    }

    public function uploadEditedReport(Request $request, int $id): RedirectResponse
    {
        $program = $this->ownedActiveProgram($id);
        $report = DB::table('program_reports')->where('program_id', $id)->first();
        abort_unless($report && in_array($report->status, ['draft', 'rejected'], true), 403);
        $validated = $request->validate(['final_report' => ['required', 'file', 'mimes:pdf,docx', 'max:20480']]);
        $file = $request->file('final_report');
        $extension = strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs('program-reports/'.$program->id, 'laporan-final-'.$program->id.'-'.now()->format('Ymd_His').'.'.$extension, 'local');
        $updates = ['output_format' => $extension, $extension.'_path' => $path, 'updated_at' => now()];
        DB::table('program_reports')->where('id', $report->id)->update($updates);
        auditLog('program_reports.final_file_upload', 'programs', $program->id, 'Final report file uploaded for organization review');

        return back()->with('success', __('Final report file uploaded and ready for organization review.'));
    }

    public function submitReport(int $id): RedirectResponse
    {
        $program = $this->ownedActiveProgram($id);
        $report = DB::table('program_reports')->where('program_id', $program->id)->first();
        abort_unless($report && in_array($report->status, ['draft', 'rejected'], true), 403);
        $branch = $this->reportBranch($program);
        $deputy = ProgramApprovalRouting::deputyFor($branch);
        $director = ProgramApprovalRouting::polytechnicDirector();
        $kjHep = ProgramApprovalRouting::kjHep();
        if (! $deputy || ! $director || ! $kjHep) {
            return back()->withErrors(['report' => __('The assigned deputy reviewer, Polytechnic Director, or KJ HEP could not be found. Verify the active staff organization records.')]);
        }
        if (Schema::hasColumn('program_reports', 'docx_path') && blank($report->docx_path) && blank($report->pdf_path)) {
            return back()->withErrors(['report' => __('Upload the final DOCX or PDF report before sending it for review.')]);
        }
        DB::table('program_reports')->where('id', $report->id)->update([
            'status' => 'pending_tpsa',
            'tpsa_reviewer_id' => $deputy->id,
            'director_reviewer_id' => $director->id,
            'kj_hep_reviewer_id' => $kjHep->id,
            'updated_at' => now(),
        ]);
        auditLog('program_reports.submit', 'programs', $program->id, 'Report submitted to '.strtoupper($branch));

        return back()->with('success', __('Report submitted to :branch for review.', ['branch' => strtoupper($branch)]));
    }

    public function reviewReport(Request $request, int $id): RedirectResponse
    {
        $program = DB::table('programs')->where('id', $id)->first();
        abort_unless($program, 404);
        $report = DB::table('program_reports')->where('program_id', $id)->first();
        abort_unless($report, 404);
        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject'],
            'review_note' => ['nullable', 'string', 'max:2000', 'required_if:decision,reject'],
        ]);
        $adminId = (int) session('auth_user.id');
        $note = filled($validated['review_note'] ?? null) ? trim($validated['review_note']) : null;
        $stage = match ($report->status) {
            'pending_tpsa' => ['reviewer' => 'tpsa_reviewer_id', 'reviewed' => 'tpsa_reviewed_at', 'note' => 'tpsa_review_note', 'next' => 'pending_director'],
            'pending_director' => ['reviewer' => 'director_reviewer_id', 'reviewed' => 'director_reviewed_at', 'note' => 'director_review_note', 'next' => 'pending_kj_hep'],
            'pending_kj_hep' => ['reviewer' => 'kj_hep_reviewer_id', 'reviewed' => 'kj_hep_reviewed_at', 'note' => 'kj_hep_review_note', 'next' => 'archived'],
            default => null,
        };
        abort_unless($stage && (int) $report->{$stage['reviewer']} === $adminId, 403);
        $approved = $validated['decision'] === 'approve';
        $nextStatus = $approved ? $stage['next'] : 'rejected';
        $payload = [
            'status' => $nextStatus,
            $stage['reviewed'] => now(),
            $stage['note'] => $note,
            'updated_at' => now(),
        ];
        if ($nextStatus === 'archived') {
            $payload['archived_at'] = now();
        }
        DB::transaction(function () use ($report, $program, $payload, $nextStatus): void {
            DB::table('program_reports')->where('id', $report->id)->update($payload);
            if ($nextStatus === 'archived') {
                DB::table('programs')->where('id', $program->id)->update(['status' => 'completed', 'updated_at' => now()]);
            }
        });
        auditLog('program_reports.review', 'programs', $program->id, 'Report decision: '.$validated['decision'].' at '.$report->status);

        $message = $approved ? match ($nextStatus) {
            'pending_director' => __('Report approved by the assigned deputy reviewer and forwarded to the Polytechnic Director.'),
            'pending_kj_hep' => __('Report approved by the Polytechnic Director and forwarded to KJ HEP.'),
            'archived' => __('Report accepted and archived under KJ HEP.'),
        } : __('Report returned to the Program Director for correction.');

        return back()->with('success', $message);
    }

    public function generateAiQuestionnaire(Request $request, int $id)
    {
        $program = $this->ownedActiveProgram($id);
        abort_unless((bool) $program->questionnaire_enabled, 403);

        $focus = (string) $request->input('focus', 'satisfaction');
        $questionCount = max(3, min(10, (int) $request->input('question_count', 5)));

        $latestPaperwork = DB::table('program_paperworks')
            ->where('program_id', $program->id)
            ->orderByDesc('version')
            ->first();

        $paperworkContext = '';
        if ($latestPaperwork && ! empty($latestPaperwork->structured_snapshot)) {
            $snapshot = json_decode($latestPaperwork->structured_snapshot, true);
            if (is_array($snapshot)) {
                $paperworkContext = implode(' ', array_filter($snapshot));
            }
        }

        $generated = $this->buildAiQuestions($program, $paperworkContext, $focus, $questionCount);

        return response()->json([
            'success' => true,
            'questions' => $generated,
        ]);
    }

    public function updateQuestionnaireSetting(Request $request, int $id): RedirectResponse
    {
        $program = $this->ownedActiveProgram($id);
        abort_if($program->attendance_status === 'open', 403, __('Close attendance before changing the questionnaire mode.'));
        $validated = $request->validate(['questionnaire_enabled' => ['required', 'boolean']]);
        DB::table('programs')->where('id', $program->id)->update([
            'questionnaire_enabled' => (bool) $validated['questionnaire_enabled'], 'updated_at' => now(),
        ]);
        auditLog('programs.questionnaire.setting', 'programs', $program->id, $validated['questionnaire_enabled'] ? 'Questionnaire enabled' : 'Attendance-only mode enabled');
        return back()->with('success', $validated['questionnaire_enabled']
            ? __('Attendance and questionnaire mode enabled.')
            : __('Attendance-only mode enabled. Students can earn points without answering questions.'));
    }

    public function saveSurvey(Request $request, int $id): RedirectResponse
    {
        $program = $this->ownedActiveProgram($id);
        abort_unless((bool) $program->questionnaire_enabled, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:180',
            'description' => 'nullable|string|max:1000',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string|max:255',
            'questions.*.question_type' => 'required|string|in:rating_5,multiple_choice,text',
            'questions.*.is_required' => 'nullable|boolean',
        ]);

        $surveyId = DB::table('program_surveys')->insertGetId([
            'program_id' => $program->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'status' => 'draft',
            'created_by' => (int) session('auth_user.id'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($validated['questions'] as $index => $q) {
            DB::table('program_survey_questions')->insert([
                'program_survey_id' => $surveyId,
                'question_text' => $q['question_text'],
                'question_type' => $q['question_type'],
                'options' => isset($q['options']) ? json_encode($q['options']) : null,
                'sort_order' => $index + 1,
                'is_required' => (bool) ($q['is_required'] ?? false),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return redirect()->route('admin.programs.operations', $program->id)
            ->with('success', __('Questionnaire draft saved successfully. Review and click Publish to post it to students.'));
    }

    public function publishSurvey(Request $request, int $id): RedirectResponse
    {
        $program = $this->ownedActiveProgram($id);
        abort_unless((bool) $program->questionnaire_enabled, 403);

        $survey = DB::table('program_surveys')
            ->where('program_id', $program->id)
            ->orderByDesc('id')
            ->first();

        if (! $survey) {
            return redirect()->back()->withErrors(__('No survey draft found. Please generate or create questions first.'));
        }

        DB::table('program_surveys')->where('id', $survey->id)->update([
            'status' => 'published',
            'published_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.programs.operations', $program->id)
            ->with('success', __('Questionnaire published successfully! Students can now access and answer it during check-in.'));
    }

    public function publicCheckin(int $id): View
    {
        $program = DB::table('programs')->where('id', $id)->first();
        if (! $program) {
            abort(404, __('Program not found.'));
        }
        abort_unless($program->status === 'active' && $program->attendance_status === 'open', 404);

        $survey = DB::table('program_surveys')
            ->where('program_id', $program->id)
            ->where('status', 'published')
            ->orderByDesc('id')
            ->first();

        $questions = $survey
            ? DB::table('program_survey_questions')
                ->where('program_survey_id', $survey->id)
                ->orderBy('sort_order')
                ->get()
            : collect();

        return view('public.programs.qr_checkin', compact('program', 'survey', 'questions'));
    }

    public function storePublicCheckin(Request $request, int $id): RedirectResponse
    {
        $program = DB::table('programs')->where('id', $id)->first();
        if (! $program) {
            abort(404);
        }
        abort_unless($program->status === 'active' && $program->attendance_status === 'open', 404);
        $usesGeofence = $program->latitude !== null && $program->longitude !== null;

        $validated = $request->validate([
            'full_name' => 'required|string|max:180',
            'identifier' => 'required|string|max:100',
            'email' => 'nullable|email|max:180',
            'institution_or_unit' => 'nullable|string|max:180',
            'satisfaction_rating' => 'nullable|integer|min:1|max:5',
            'feedback_comments' => 'nullable|string|max:1000',
            'answers' => 'nullable|array',
            'answers.*' => 'nullable|string|max:5000',
            'latitude' => ($usesGeofence ? 'required' : 'nullable').'|numeric|between:-90,90',
            'longitude' => ($usesGeofence ? 'required' : 'nullable').'|numeric|between:-180,180',
            'location_accuracy_m' => ($usesGeofence ? 'required' : 'nullable').'|numeric|min:0|max:5000',
            'location_captured_at' => ($usesGeofence ? 'required' : 'nullable').'|date|before_or_equal:now|after:'.now()->subMinutes(5)->toDateTimeString(),
        ]);

        $identifier = trim($validated['identifier']);
        $duplicateExists = DB::table('program_attendances')
            ->where('program_id', $program->id)
            ->where('attendee_type', 'external')
            ->whereRaw('LOWER(identifier) = ?', [mb_strtolower($identifier)])
            ->exists();

        if ($duplicateExists) {
            return back()->withInput()->withErrors(['identifier' => __('Attendance has already been submitted for this participant.')]);
        }

        $distance = $usesGeofence ? $this->distanceMeters(
            (float) $program->latitude,
            (float) $program->longitude,
            (float) $validated['latitude'],
            (float) $validated['longitude']
        ) : null;
        $accuracy = $usesGeofence ? (float) $validated['location_accuracy_m'] : null;
        $validationStatus = ! $usesGeofence
            ? 'valid'
            : ($distance > (int) $program->geofence_radius_m ? 'invalid_outside_radius' : ($accuracy > 100 ? 'needs_review_accuracy' : 'valid'));

        $survey = DB::table('program_surveys')
            ->where('program_id', $program->id)
            ->where('status', 'published')
            ->orderByDesc('id')
            ->first();

        $surveyQuestions = $survey
            ? DB::table('program_survey_questions')
                ->where('program_survey_id', $survey->id)
                ->orderBy('sort_order')
                ->get()
            : collect();

        $submittedAnswers = collect($validated['answers'] ?? [])->mapWithKeys(
            fn ($answer, $questionId): array => [(int) $questionId => $answer]
        );
        $allowedQuestionIds = $surveyQuestions->pluck('id')->map(fn ($id): int => (int) $id);

        if ($submittedAnswers->keys()->diff($allowedQuestionIds)->isNotEmpty()) {
            return back()->withInput()->withErrors([
                'answers' => __('One or more submitted questions do not belong to the published questionnaire.'),
            ]);
        }

        $missingRequired = $surveyQuestions
            ->filter(fn ($question): bool => (bool) $question->is_required)
            ->first(fn ($question): bool => blank($submittedAnswers->get((int) $question->id)));

        if ($missingRequired) {
            return back()->withInput()->withErrors([
                'answers.'.(int) $missingRequired->id => __('Please answer the required question: :question', [
                    'question' => $missingRequired->question_text,
                ]),
            ]);
        }

        $attendanceId = DB::table('program_attendances')->insertGetId([
            'program_id' => $program->id,
            'student_id' => null,
            'attendee_type' => 'external',
            'full_name' => $validated['full_name'],
            'identifier' => $identifier,
            'email' => $validated['email'] ?? null,
            'institution_or_unit' => $validated['institution_or_unit'] ?? null,
            'checked_in_at' => now(),
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'geofence_valid' => $validationStatus === 'valid',
            'validation_status' => $validationStatus,
            'distance_m' => $distance === null ? null : round($distance, 2),
            'location_accuracy_m' => $accuracy === null ? null : round($accuracy, 2),
            'location_captured_at' => $validated['location_captured_at'] ?? null,
            'satisfaction_rating' => $validated['satisfaction_rating'] ?? null,
            'feedback_comments' => $validated['feedback_comments'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($survey && $submittedAnswers->isNotEmpty()) {
            foreach ($surveyQuestions as $question) {
                $answer = $submittedAnswers->get((int) $question->id);
                if (filled($answer)) {
                    DB::table('program_survey_responses')->insert([
                        'program_survey_id' => $survey->id,
                        'program_attendance_id' => $attendanceId,
                        'question_id' => (int) $question->id,
                        'answer_value' => is_array($answer) ? json_encode($answer) : (string) $answer,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }

        $message = match ($validationStatus) {
            'valid' => __('Attendance recorded as valid. Thank you for participating in :program.', ['program' => $program->title]),
            'needs_review_accuracy' => __('Attendance was submitted but needs review because the GPS accuracy was low.'),
            default => __('Attendance was recorded as invalid because the device was outside the permitted venue radius.'),
        };

        return redirect()->back()->with('success', $message);
    }

    private function ownedActiveProgram(int $id): object
    {
        $program = DB::table('programs')->where('id', $id)->first();
        abort_unless($program, 404);

        $authId = (int) session('auth_user.id');
        $authRole = (string) (session('auth_user.admin_role') ?? '');
        $hasOversight = in_array($authRole, ['system_admin', 'student_affairs_head'], true);
        $isDirector = (int) $program->created_by === $authId;

        $canManage = $hasOversight || $isDirector;
        $validStatus = in_array($program->status, ['active', 'approved', 'scheduled', 'completed'], true);

        abort_unless($canManage && $validStatus, 403);

        return $program;
    }

    private function distanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function reportData(object $program): array
    {
        $attendances = DB::table('program_attendances')->where('program_id', $program->id)->get();
        $survey = DB::table('program_surveys')->where('program_id', $program->id)->where('status', 'published')->latest('id')->first();
        $responseCount = $survey ? DB::table('program_survey_responses')->where('program_survey_id', $survey->id)
            ->distinct('program_attendance_id')->count('program_attendance_id') : 0;
        $ratings = $attendances->pluck('satisfaction_rating')->filter();
        $answers = $survey ? DB::table('program_survey_responses as responses')
            ->join('program_survey_questions as questions', 'questions.id', '=', 'responses.question_id')
            ->where('responses.program_survey_id', $survey->id)
            ->orderBy('questions.sort_order')->limit(300)
            ->get(['questions.question_text', 'responses.answer_value'])
            ->map(fn ($row): array => ['question' => $row->question_text, 'answer' => $row->answer_value])->all() : [];

        return [
            'attendance_total' => $attendances->count(),
            'internal_total' => $attendances->where('attendee_type', 'internal')->count(),
            'external_total' => $attendances->where('attendee_type', 'external')->count(),
            'survey_responses' => $responseCount,
            'average_rating' => $ratings->isNotEmpty() ? round((float) $ratings->avg(), 2) : 0,
            'comments' => $attendances->pluck('feedback_comments')->filter()->take(30)->values()->all(),
            'questionnaire_answers' => $answers,
        ];
    }

    private function reportPrompt(object $program, array $data): string
    {
        return "Prepare a formal post-program report in Bahasa Melayu for internal Polytechnic management. "
            ."Use only the supplied facts; do not invent outcomes. Include Ringkasan Eksekutif, Maklumat Program, Objektif, "
            ."Kehadiran, Maklum Balas Peserta, Pencapaian, Isu, Cadangan Penambahbaikan, and Kesimpulan.\n\n"
            .'Program: '.json_encode((array) $program, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
            .'Recorded attendance and questionnaire results: '.json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n"
            .'Analyze the attached approved paperwork and activity photographs. Describe only visible or documented activities and do not identify people from images.';
    }

    private function fallbackReport(object $program, array $data): string
    {
        $comments = $data['comments'] === [] ? 'Tiada komen bertulis direkodkan.' : implode("\n- ", $data['comments']);

        return "LAPORAN PROGRAM: {$program->title}\n\n"
            ."1. Ringkasan Eksekutif\nProgram telah dilaksanakan berdasarkan rekod program yang diluluskan.\n\n"
            ."2. Maklumat Program\nTarikh: ".($program->starts_at ?: 'Tidak direkodkan')."\nTempat: ".($program->venue ?: 'Tidak direkodkan')."\n\n"
            ."3. Objektif\n".($program->objectives ?: 'Tidak direkodkan')."\n\n"
            ."4. Kehadiran\nJumlah peserta: {$data['attendance_total']}\nPelajar dalaman: {$data['internal_total']}\nTetamu luar: {$data['external_total']}\n\n"
            ."5. Maklum Balas Peserta\nJumlah respons: {$data['survey_responses']}\nPurata penilaian: {$data['average_rating']} / 5\n- {$comments}\n\n"
            ."6. Pencapaian dan Isu\nSila dilengkapkan oleh Pengarah Program berdasarkan bukti pelaksanaan.\n\n"
            ."7. Cadangan Penambahbaikan\nSila dilengkapkan oleh Pengarah Program.\n\n"
            ."8. Kesimpulan\nLaporan ini disediakan daripada data kehadiran dan maklum balas yang direkodkan dalam StudentEdge.";
    }

    private function isReviewer(object $program, int $authId): bool
    {
        return DB::table('program_reports')
            ->where('program_id', $program->id)
            ->where(function ($q) use ($authId): void {
                $q->where('tpsa_reviewer_id', $authId)
                    ->orWhere('director_reviewer_id', $authId)
                    ->orWhere('kj_hep_reviewer_id', $authId);
            })
            ->exists();
    }

    private function reportBranch(object $program): string
    {
        $owner = DB::table('admins')->where('id', $program->created_by)->first();

        return $owner?->reporting_branch
            ?: ProgramApprovalRouting::inferBranch($owner?->staff_department, $owner?->position)
            ?: 'tpsa';
    }

    private function buildAiQuestions(object $program, string $paperwork, string $focus, int $count): array
    {
        $questions = [];

        $questions[] = [
            'question_text' => __('What did you learn from this program?'),
            'question_type' => 'text',
            'is_required' => true,
        ];

        $questions[] = [
            'question_text' => __('Were the objectives of ').$program->title.__(' clearly achieved?'),
            'question_type' => 'rating_5',
            'is_required' => true,
        ];

        if ($program->venue) {
            $questions[] = [
                'question_text' => __('How comfortable and suitable was the venue (').$program->venue.')?',
                'question_type' => 'rating_5',
                'is_required' => true,
            ];
        }

        $questions[] = [
            'question_text' => __('How effective were the speakers and facilitators during the program?'),
            'question_type' => 'rating_5',
            'is_required' => true,
        ];

        $questions[] = [
            'question_text' => __('What suggestions or feedback do you have to improve future programs?'),
            'question_type' => 'text',
            'is_required' => false,
        ];

        return array_slice($questions, 0, $count);
    }
}
