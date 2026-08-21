<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AiProvider;
use App\Services\OfficialProgramReportExporter;
use App\Services\ProgramReportContent;
use App\Support\DynamicQrToken;
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
        ];
        $attendanceReady = filled($program->venue);
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

    public function questionnaire(int $id): View
    {
        $program = $this->ownedActiveProgram($id);

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

        $totalAttendances = DB::table('program_attendances')->where('program_id', $program->id)->count();
        $internalCount = DB::table('program_attendances')->where('program_id', $program->id)->where('attendee_type', 'internal')->count();
        $externalCount = DB::table('program_attendances')->where('program_id', $program->id)->where('attendee_type', 'external')->count();

        $surveyResponsesCount = $survey
            ? DB::table('program_survey_responses')
                ->where('program_survey_id', $survey->id)
                ->distinct('program_attendance_id')
                ->count('program_attendance_id')
            : 0;

        $attendanceResponsesCount = DB::table('program_attendances')
            ->where('program_id', $program->id)
            ->whereNotNull('satisfaction_rating')
            ->count();
        $totalResponses = max($surveyResponsesCount, $attendanceResponsesCount);

        $responseRate = $totalAttendances > 0 ? round(($totalResponses / $totalAttendances) * 100, 1) : 0;

        $avgAttendanceRating = DB::table('program_attendances')
            ->where('program_id', $program->id)
            ->whereNotNull('satisfaction_rating')
            ->avg('satisfaction_rating');

        $questionStats = [];
        $ratingDistribution = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $allNumericScores = [];

        if ($survey && $questions->isNotEmpty()) {
            $allResponses = DB::table('program_survey_responses')
                ->where('program_survey_id', $survey->id)
                ->get()
                ->groupBy('question_id');

            foreach ($questions as $q) {
                $qResponses = $allResponses->get($q->id, collect());
                $count = $qResponses->count();
                $numericValues = [];
                $breakdown = [];

                if (in_array($q->question_type, ['rating_4', 'rating_5'], true)) {
                    $maxScale = $q->question_type === 'rating_4' ? 4 : 5;
                    for ($s = 1; $s <= $maxScale; $s++) {
                        $breakdown[$s] = 0;
                    }
                    foreach ($qResponses as $r) {
                        $val = (int) $r->answer_value;
                        if ($val >= 1 && $val <= $maxScale) {
                            $breakdown[$val]++;
                            $numericValues[] = $val;
                            $normScore = $maxScale === 4 ? round(($val / 4) * 5, 2) : $val;
                            $allNumericScores[] = $normScore;
                            $intBucket = min(5, max(1, (int) round($normScore)));
                            $ratingDistribution[$intBucket]++;
                        }
                    }
                    $qAvg = count($numericValues) > 0 ? round(array_sum($numericValues) / count($numericValues), 2) : null;
                } else {
                    $qAvg = null;
                }

                $questionStats[] = [
                    'id' => $q->id,
                    'text' => $q->question_text,
                    'type' => $q->question_type,
                    'category' => $q->category ?? 'General',
                    'total_answers' => $count,
                    'avg_score' => $qAvg,
                    'breakdown' => $breakdown,
                ];
            }
        }

        $overallAvg = count($allNumericScores) > 0
            ? round(array_sum($allNumericScores) / count($allNumericScores), 2)
            : ($avgAttendanceRating ? round((float) $avgAttendanceRating, 2) : 0);

        $recentComments = DB::table('program_attendances')
            ->where('program_id', $program->id)
            ->whereNotNull('feedback_comments')
            ->where('feedback_comments', '!=', '')
            ->select('full_name', 'attendee_type', 'institution_or_unit', 'satisfaction_rating', 'feedback_comments', 'checked_in_at')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $analytics = [
            'total_attendances' => $totalAttendances,
            'internal_count' => $internalCount,
            'external_count' => $externalCount,
            'total_responses' => $totalResponses,
            'response_rate' => $responseRate,
            'overall_avg' => $overallAvg,
            'satisfaction_percentage' => round(($overallAvg / 5) * 100, 1),
            'rating_distribution' => $ratingDistribution,
            'question_stats' => $questionStats,
            'recent_comments' => $recentComments,
        ];

        $publicCheckinUrl = route('public.programs.qr_checkin', $program->id);

        return view('admin.programs.questionnaire', compact(
            'program',
            'latestPaperwork',
            'survey',
            'questions',
            'surveyResponsesCount',
            'analytics',
            'publicCheckinUrl'
        ));
    }

    public function openAttendance(int $id): RedirectResponse
    {
        $program = $this->ownedActiveProgram($id);

        if (blank($program->venue)) {
            return back()->withErrors([
                'attendance' => __('Please set the program venue before opening attendance.'),
            ]);
        }

        DB::table('programs')->where('id', $program->id)->update([
            'attendance_status' => 'open',
            'attendance_opened_at' => now(),
            'attendance_closed_at' => null,
            'updated_at' => now(),
        ]);
        auditLog('programs.attendance.open', 'programs', $program->id, 'Program attendance opened');

        return back()->with('success', __('Attendance is now open. Participants can record their attendance.'));
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

    public function generateReport(Request $request, int $id, AiProvider $ai, OfficialProgramReportExporter $exporter, ProgramReportContent $reportContent): RedirectResponse
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
            $aiResponse = $ai->enabled() ? trim($ai->askWithAttachments($prompt, $attachments)) : '';
            $structuredReport = $ai->enabled()
                ? $reportContent->fromAiResponse($aiResponse, $program, $data)
                : $reportContent->fallback($program, $data);
        } catch (\Throwable $exception) {
            report($exception);
            $structuredReport = $reportContent->fallback($program, $data);
        }
        $content = $reportContent->toPlainText($structuredReport);

        $imagePaths = [];
        foreach ($activityImages as $image) {
            $path = $image->store('program-report-media/'.$program->id, 'local');
            $imagePaths[] = Storage::disk('local')->path($path);
        }
        $files = $exporter->export($program, $data, $structuredReport, $validated['output_format'], $imagePaths);

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

        return back()->with([
            'success' => __('Report draft generated. Review and edit it before submission to TPSA.'),
            'generated_report' => [
                'program_title' => $program->title,
                'docx_url' => $files['docx_path'] ? route('admin.programs.report.download', [$program->id, 'docx']) : null,
                'pdf_url' => $files['pdf_path'] ? route('admin.programs.report.download', [$program->id, 'pdf']) : null,
                'details_url' => route('admin.programs.show', $program->id),
                'operations_url' => route('admin.programs.operations', $program->id),
            ],
        ]);
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

        $focus = (string) $request->input('focus', 'official_sa04_1');
        $questionCount = max(3, min(15, (int) $request->input('question_count', 11)));

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
        $validated = $request->validate([
            'questionnaire_publish_mode' => ['required', 'in:internal_system,qr_code,closed'],
        ]);

        $mode = $validated['questionnaire_publish_mode'];
        $enabled = $mode !== 'closed';

        DB::transaction(function () use ($program, $mode, $enabled): void {
            DB::table('programs')->where('id', $program->id)->update([
                'questionnaire_enabled' => $enabled,
                'questionnaire_publish_mode' => $mode,
                'updated_at' => now(),
            ]);

            if ($enabled) {
                $survey = DB::table('program_surveys')->where('program_id', $program->id)->orderByDesc('id')->first();
                if ($survey) {
                    DB::table('program_surveys')->where('id', $survey->id)->update([
                        'status' => 'published',
                        'publish_mode' => $mode,
                        'published_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            } else {
                DB::table('program_surveys')->where('program_id', $program->id)->update([
                    'status' => 'draft',
                    'updated_at' => now(),
                ]);
            }
        });

        auditLog('programs.questionnaire.setting', 'programs', $program->id, 'Questionnaire mode updated to: '.$mode);

        $message = match($mode) {
            'internal_system' => __('Tetapan disimpan: Mod 1 - Terus Dalam Sistem (Pelajar PB jawab terus di portal tanpa imbas QR).'),
            'qr_code' => __('Tetapan disimpan: Mod 2 - Mod Imbasan QR (Pelajar dan tetamu imbas QR untuk jawab).'),
            'closed' => __('Tetapan disimpan: Soal selidik ditutup / Mod Kehadiran Sahaja.'),
        };

        return back()->with('success', $message);
    }

    public function saveSurvey(Request $request, int $id): RedirectResponse
    {
        $program = $this->ownedActiveProgram($id);

        $validated = $request->validate([
            'title' => 'required|string|max:180',
            'description' => 'nullable|string|max:1000',
            'questions' => 'required|array|min:1',
            'questions.*.question_text' => 'required|string|max:255',
            'questions.*.question_type' => 'required|string|in:rating_4,rating_5,multiple_choice,text',
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

        return redirect()->back()
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

        return redirect()->back()
            ->with('success', __('Questionnaire published successfully! Students can now access and answer it.'));
    }

    public function publishSurveyMode(Request $request, int $id): RedirectResponse
    {
        $program = $this->ownedActiveProgram($id);
        $validated = $request->validate([
            'publish_mode' => ['required', 'in:internal_system,qr_code'],
        ]);

        $survey = DB::table('program_surveys')
            ->where('program_id', $program->id)
            ->orderByDesc('id')
            ->first();

        if (! $survey) {
            return redirect()->back()->withErrors(['questionnaire' => __('No survey draft found. Please create questions in Questionnaire Builder first.')]);
        }

        DB::transaction(function () use ($program, $survey, $validated): void {
            DB::table('program_surveys')->where('id', $survey->id)->update([
                'status' => 'published',
                'publish_mode' => $validated['publish_mode'],
                'published_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('programs')->where('id', $program->id)->update([
                'questionnaire_enabled' => true,
                'questionnaire_publish_mode' => $validated['publish_mode'],
                'updated_at' => now(),
            ]);
        });

        auditLog('programs.questionnaire.publish_mode', 'programs', $program->id, 'Questionnaire published in mode: '.$validated['publish_mode']);

        $message = $validated['publish_mode'] === 'internal_system'
            ? __('Questionnaire published directly in-system for Politeknik Besut students. No QR scan is required for them.')
            : __('Questionnaire published in QR Code mode. Students can scan via PWA scanner and external guests can scan the public QR code.');

        return redirect()->back()->with('success', $message);
    }

    public function closeSurvey(int $id): RedirectResponse
    {
        $program = $this->ownedActiveProgram($id);
        $survey = DB::table('program_surveys')
            ->where('program_id', $program->id)
            ->orderByDesc('id')
            ->first();

        if ($survey) {
            DB::table('program_surveys')->where('id', $survey->id)->update([
                'status' => 'draft',
                'updated_at' => now(),
            ]);
        }

        DB::table('programs')->where('id', $program->id)->update([
            'questionnaire_publish_mode' => 'closed',
            'updated_at' => now(),
        ]);

        auditLog('programs.questionnaire.close', 'programs', $program->id, 'Questionnaire closed by director');

        return redirect()->back()->with('success', __('Questionnaire is now closed.'));
    }

    public function presenter(int $id): View
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
            abort(403, __('You are not authorized to access this presenter screen.'));
        }

        $survey = DB::table('program_surveys')
            ->where('program_id', $program->id)
            ->where('status', 'published')
            ->orderByDesc('id')
            ->first();

        $tokenData = DynamicQrToken::generate($program->id);
        $initialCheckinUrl = route('public.programs.qr_checkin', ['program' => $program->id, 't' => $tokenData['token']]);
        $studentCheckinUrl = route('student.programs.show', ['program' => $program->id, 't' => $tokenData['token']]);

        $attendances = DB::table('program_attendances')->where('program_id', $program->id)->get();
        $totalJoined = $attendances->count();
        $internalCount = $attendances->where('attendee_type', 'internal')->count();
        $externalCount = $attendances->where('attendee_type', 'external')->count();

        return view('admin.programs.presenter', compact(
            'program',
            'survey',
            'tokenData',
            'initialCheckinUrl',
            'studentCheckinUrl',
            'totalJoined',
            'internalCount',
            'externalCount'
        ));
    }

    public function liveToken(int $id)
    {
        $program = DB::table('programs')->where('id', $id)->first();
        if (! $program) {
            return response()->json(['error' => __('Program not found.')], 404);
        }

        $authId = (int) session('auth_user.id');
        $authRole = (string) (session('auth_user.admin_role') ?? '');
        $hasOversight = in_array($authRole, ['system_admin', 'student_affairs_head'], true);
        $isDirector = (int) $program->created_by === $authId;

        if (! $hasOversight && ! $isDirector && ! $this->isReviewer($program, $authId)) {
            return response()->json(['error' => __('Unauthorized.')], 403);
        }

        $tokenData = DynamicQrToken::generate($program->id);

        $attendances = DB::table('program_attendances')->where('program_id', $program->id)->get();
        $totalJoined = $attendances->count();
        $internalCount = $attendances->where('attendee_type', 'internal')->count();
        $externalCount = $attendances->where('attendee_type', 'external')->count();
        $ratings = $attendances->pluck('satisfaction_rating')->filter();
        $averageRating = $ratings->isNotEmpty() ? round($ratings->avg(), 1) : 0.0;

        return response()->json([
            'token' => $tokenData['token'],
            'expires_in' => $tokenData['expires_in'],
            'timestamp' => $tokenData['timestamp'],
            'public_url' => route('public.programs.qr_checkin', ['program' => $program->id, 't' => $tokenData['token']]),
            'student_url' => route('student.programs.show', ['program' => $program->id, 't' => $tokenData['token']]),
            'attendance_status' => $program->attendance_status,
            'stats' => [
                'total' => $totalJoined,
                'internal' => $internalCount,
                'external' => $externalCount,
                'target' => $program->estimated_participants ?: 0,
                'avg_rating' => $averageRating,
            ],
        ]);
    }

    public function publicCheckin(Request $request, int $id): View
    {
        $program = DB::table('programs')->where('id', $id)->first();
        if (! $program) {
            abort(404, __('Program not found.'));
        }
        abort_unless($program->status === 'active' && $program->attendance_status === 'open', 404);

        $token = $request->input('t') ?? $request->input('token');

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

        return view('public.programs.qr_checkin', compact('program', 'survey', 'questions', 'token'));
    }

    public function storePublicCheckin(Request $request, int $id): RedirectResponse
    {
        $program = DB::table('programs')->where('id', $id)->first();
        if (! $program) {
            abort(404);
        }
        abort_unless($program->status === 'active' && $program->attendance_status === 'open', 404);

        if ($request->filled('qr_token')) {
            if (! DynamicQrToken::verify($request->input('qr_token'), $program->id)) {
                return back()->withInput()->withErrors([
                    'qr_token' => __('The scanned QR code has expired. Please scan the current QR code displayed on the screen.'),
                ]);
            }
        }

        $usesGeofence = $program->latitude !== null && $program->longitude !== null;

        $validated = $request->validate([
            'qr_token' => 'nullable|string',
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
        $owner = DB::table('admins')->where('id', $program->created_by)->first();
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
            'prepared_by' => $owner?->full_name ?: 'Tidak direkodkan',
            'prepared_by_position' => $owner?->position ?: 'Pengarah Program',
            'organizer' => $owner?->staff_department ?: 'Tidak direkodkan',
        ];
    }

    private function reportPrompt(object $program, array $data): string
    {
        return "Anda ialah pembantu AI rasmi Politeknik Besut untuk penjanaan Laporan Pelaksanaan Program.\n\n"
            ."TUGAS ANDA:\n"
            ."1. Buka dan fahami templat rasmi laporan Politeknik Besut ('FORMAT LAPORAN POLIBESUT 2025').\n"
            ."2. Rujuk maklumat program yang dipilih oleh pengguna (tajuk, tarikh, masa, tempat, anjuran, kumpulan sasaran, objektif, latar belakang).\n"
            ."3. Teliti kertas kerja kelulusan yang dimuat naik dan rekod aktiviti program.\n"
            ."4. Analisis gambar/foto aktiviti yang disertakan untuk menggambarkan pengisian aktiviti sebenar.\n"
            ."5. Analisis data kehadiran digital serta respons kaji selidik / maklum balas pelajar daripada sistem MyHEP.\n"
            ."6. Janakan kandungan laporan lengkap dalam Bahasa Melayu rasmi bertaraf dokumen kerajaan, selaras 1-ke-1 dengan struktur templat rasmi.\n\n"
            ."FORMAT OUTPUT (Wajib JSON tulen tanpa Markdown / code fences):\n"
            ."{\n"
            .'  "kluster_kpi": "Sukarelawan|Patriotisme|Perpaduan|Kepimpinan|Komunikasi|Kebudayaan, kesenian dan warisan|Kerohanian|Psikologi|Sukan|Kesihatan|Kemahiran dan Inovasi|Kelab dan persatuan|Niche Area",'."\n"
            .'  "peringkat": "Jabatan|Politeknik|Institusi|Komuniti|Negeri|Kebangsaan|Antarabangsa",'."\n"
            .'  "executive_summary": "Penerangan komprehensif ringkasan program, latar belakang, dan pelaksanaan.",'."\n"
            .'  "objectives": ["Objektif 1 yang jelas", "Objektif 2", "Objektif 3"],'."\n"
            .'  "jawatankuasa": {'."\n"
            .'    "penaung": "Pengarah Politeknik Besut",'."\n"
            .'    "penasihat1": "Timbalan Pengarah Akademik / Hal Ehwal Pelajar",'."\n"
            .'    "penasihat2": "Ketua Jabatan / Unit",'."\n"
            .'    "pengarah_program": "Nama Pengarah Program",'."\n"
            .'    "setiausaha": "Nama Setiausaha",'."\n"
            .'    "ajk": "Senarai AJK Pelaksana",'."\n"
            .'    "urusetia": "Urusetia Program"'."\n"
            .'  },'."\n"
            .'  "penceramah": {'."\n"
            .'    "nama": "Nama penceramah/perasmi/jemputan luar jika ada, atau Tiada/Dalaman",'."\n"
            .'    "jawatan": "Jawatan",'."\n"
            .'    "gred": "Gred (contoh: DH52 / DV41 / N19 atau —)",'."\n"
            .'    "institusi": "Nama Institusi / Organisasi"'."\n"
            .'  },'."\n"
            .'  "aturcara": ['."\n"
            .'    {"tarikh": "Tarikh", "masa": "Masa Mula - Masa Tamat", "aktiviti": "Aktiviti/Pengisian"}'."\n"
            .'  ],'."\n"
            .'  "kewangan": "Ringkasan peruntukan dan perbelanjaan (contoh: Peruntukan Dalaman Jabatan / Akaun Amanah / Tiada)",'."\n"
            .'  "survey_summary": "Analisis dan ulasan maklum balas peserta serta penilaian kaji selidik.",'."\n"
            .'  "achievements": ["Hasil/impak dan pencapaian utama 1", "Hasil/impak 2", "Hasil/impak 3"],'."\n"
            .'  "issues": ["Isu/kekangan yang dihadapi semasa program"],'."\n"
            .'  "improvements": ["Cadangan penambahbaikan untuk masa hadapan"],'."\n"
            .'  "conclusion": "Kesimpulan keseluruhan pelaksanaan program."'."\n"
            ."}\n\n"
            .'MAKLUMAT PROGRAM PILIHAN PENGGUNA: '.json_encode((array) $program, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n\n"
            .'REKOD KEHADIRAN & MAKLUM BALAS STUDENTEDGE: '.json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)."\n\n"
            ."Sila teliti kertas kerja dan foto aktiviti yang dilampirkan, kemudian kembalikan JSON di atas.";
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
        if (!empty($program->approval_branch)) {
            return $program->approval_branch;
        }

        $owner = DB::table('admins')->where('id', $program->created_by)->first();

        return $owner?->reporting_branch
            ?: ProgramApprovalRouting::inferBranch($owner?->staff_department, $owner?->position)
            ?: 'tpsa';
    }

    private function buildAiQuestions(object $program, string $paperwork, string $focus, int $count): array
    {
        // 1. Official Borang SA-04(1) - Penilaian Peserta (Standard Politeknik Besut)
        if ($focus === 'official_sa04_1' || $focus === 'template_sa04_1' || $focus === 'official_sa04' || $focus === 'sa04') {
            return [
                ['question_text' => 'Objektif latihan / program tercapai.', 'question_type' => 'rating_4', 'is_required' => true],
                ['question_text' => 'Kandungan latihan / pengisian adalah sesuai.', 'question_type' => 'rating_4', 'is_required' => true],
                ['question_text' => 'Penyampaian penceramah / fasilitator yang baik dan berkesan.', 'question_type' => 'rating_4', 'is_required' => true],
                ['question_text' => 'Penggunaan alat bantuan mengajar / modul dengan berkesan.', 'question_type' => 'rating_4', 'is_required' => true],
                ['question_text' => 'Suasana tempat latihan / lokasi program yang sesuai dan kondusif.', 'question_type' => 'rating_4', 'is_required' => true],
                ['question_text' => 'Perancangan dan pelaksanaan program telah dibuat dengan lancar.', 'question_type' => 'rating_4', 'is_required' => true],
                ['question_text' => 'Masa yang diperuntukkan bagi setiap modul / slot adalah sesuai.', 'question_type' => 'rating_4', 'is_required' => true],
                ['question_text' => 'Meningkatkan pengetahuan dan pemahaman peserta.', 'question_type' => 'rating_4', 'is_required' => true],
                ['question_text' => 'Lebih berkeyakinan menjalankan tugas berkaitan / mengaplikasi apa yang dipelajari.', 'question_type' => 'rating_4', 'is_required' => true],
                ['question_text' => 'Pada keseluruhannya latihan / program ini adalah berjaya dan bermanfaat.', 'question_type' => 'rating_4', 'is_required' => true],
                ['question_text' => 'Kesediaan untuk berkongsi ilmu yang diperolehi berkaitan latihan (Sila nyatakan YA atau TIDAK berserta ulasan jika TIDAK).', 'question_type' => 'text', 'is_required' => false],
            ];
        }

        // 2. Official Borang SA-04(3) - Penilaian Keberkesanan Terhadap Staf
        if ($focus === 'official_sa04_3' || $focus === 'template_sa04_3') {
            return [
                ['question_text' => 'Staf menunjukkan peningkatan dalam menjalankan tugas.', 'question_type' => 'rating_5', 'is_required' => true],
                ['question_text' => 'Staf berkongsi kemahiran yang diperolehi dengan staf yang lain.', 'question_type' => 'rating_5', 'is_required' => true],
                ['question_text' => 'Keyakinan diri melaksanakan tugas meningkat.', 'question_type' => 'rating_5', 'is_required' => true],
                ['question_text' => 'Staf menunjukkan perubahan sikap dan penampilan yang sangat positif.', 'question_type' => 'rating_5', 'is_required' => true],
                ['question_text' => 'Peningkatan kerjasama dengan semua peringkat.', 'question_type' => 'rating_5', 'is_required' => true],
                ['question_text' => 'Menyumbang kepada peningkatan prestasi jabatan / unit secara keseluruhan.', 'question_type' => 'rating_5', 'is_required' => true],
                ['question_text' => 'Mengaplikasikan ilmu / kemahiran dalam melaksanakan tugas.', 'question_type' => 'rating_5', 'is_required' => true],
                ['question_text' => 'Ulasan / Komen Tahap Keberkesanan Latihan Terhadap Staf.', 'question_type' => 'text', 'is_required' => false],
            ];
        }

        // 3. AI Generation Grounded on Borang SA-04 and Program Paperwork
        $ai = app(AiProvider::class);
        if ($ai->enabled()) {
            try {
                $prompt = "Anda adalah AI Pembantu Politeknik Besut (MyHEP). Hasilkan soalan kaji selidik soal selidik maklum balas program berpandukan TEMPLAT RASMI BORANG SA-04(1) POLITEKNIK BESUT:\n"
                    ."Struktur Borang SA-04:\n"
                    ."- Penilaian Penceramah/Fasilitator (Objektif, Kandungan, Penyampaian, Alat Bantuan)\n"
                    ."- Penilaian Pelaksanaan (Tempat/Kondusif, Perancangan Kelancaran, Masa Modul)\n"
                    ."- Penilaian Keberkesanan (Peningkatan Pengetahuan, Keyakinan Aplikasi, Manfaat Keseluruhan)\n"
                    ."- Ulasan Peserta (Perkongsian ilmu YA/TIDAK atau ulasan penambahbaikan)\n\n"
                    ."Tajuk Program: {$program->title}\n"
                    ."Tempat: ".($program->venue ?: 'Politeknik Besut')."\n"
                    ."Fokus: {$focus}\n"
                    ."Konteks Kertas Kerja: ".mb_substr($paperwork, 0, 1500)."\n\n"
                    ."Hasilkan sebanyak {$count} soalan dalam Bahasa Melayu mengikut format Borang SA-04. Kembalikan HANYA JSON array tanpa blok markdown:\n"
                    .'[{"question_text": "Teks soalan", "question_type": "rating_4", "is_required": true}]'
                    ."\nNota: Gunakan question_type 'rating_4' untuk soalan skala skor 1-4, dan 'text' untuk ulasan bertulis.";

                $response = $ai->ask($prompt);
                $cleanJson = trim(preg_replace('/^```(?:json)?|```$/m', '', $response));
                $parsed = json_decode($cleanJson, true);

                if (is_array($parsed) && count($parsed) > 0) {
                    $valid = [];
                    foreach ($parsed as $item) {
                        if (! empty($item['question_text'])) {
                            $valid[] = [
                                'question_text' => (string) $item['question_text'],
                                'question_type' => in_array($item['question_type'] ?? '', ['rating_4', 'rating_5', 'text'], true) ? $item['question_type'] : 'rating_4',
                                'is_required' => ! empty($item['is_required']),
                            ];
                        }
                    }
                    if (! empty($valid)) {
                        return array_slice($valid, 0, $count);
                    }
                }
            } catch (\Throwable $e) {
                // Fallback to tailored SA-04 questions below
            }
        }

        // Tailored SA-04 Fallback
        $fallback = [
            ['question_text' => 'Objektif program '.($program->title ?: '').' tercapai dengan berkesan.', 'question_type' => 'rating_4', 'is_required' => true],
            ['question_text' => 'Kandungan latihan dan modul yang disampaikan adalah bersesuaian.', 'question_type' => 'rating_4', 'is_required' => true],
            ['question_text' => 'Penyampaian penceramah / fasilitator adalah menarik, jelas dan berkesan.', 'question_type' => 'rating_4', 'is_required' => true],
            ['question_text' => 'Suasana tempat latihan '.($program->venue ? '('.$program->venue.')' : '').' adalah selesa dan kondusif.', 'question_type' => 'rating_4', 'is_required' => true],
            ['question_text' => 'Perancangan dan pengurusan masa program berjalan dengan teratur dan lancar.', 'question_type' => 'rating_4', 'is_required' => true],
            ['question_text' => 'Program ini berjaya meningkatkan ilmu pengetahuan dan pemahaman saya.', 'question_type' => 'rating_4', 'is_required' => true],
            ['question_text' => 'Saya berkeyakinan untuk mengaplikasikan apa yang dipelajari dalam tugasan seharian.', 'question_type' => 'rating_4', 'is_required' => true],
            ['question_text' => 'Secara keseluruhannya, program ini adalah berjaya dan bermanfaat.', 'question_type' => 'rating_4', 'is_required' => true],
            ['question_text' => 'Kesediaan untuk berkongsi ilmu yang diperolehi (Sila nyatakan YA atau TIDAK berserta ulasan jika berkaitan).', 'question_type' => 'text', 'is_required' => false],
        ];

        return array_slice($fallback, 0, $count);
    }
}
