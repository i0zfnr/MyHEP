<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Support\DynamicQrToken;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class ProgramActivityController extends Controller
{
    public function index(): View
    {
        $studentId = (int) session('auth_user.id');
        $programQuery = DB::table('programs')
            ->leftJoin('program_attendances', function ($join) use ($studentId): void {
                $join->on('program_attendances.program_id', '=', 'programs.id')
                    ->where('program_attendances.student_id', '=', $studentId)
                    ->where('program_attendances.attendee_type', '=', 'internal');
            });
        if (Schema::hasTable('program_certificates')) {
            $programQuery->leftJoin('program_certificates', function ($join) use ($studentId): void {
                $join->on('program_certificates.program_id', '=', 'programs.id')->where('program_certificates.student_id', '=', $studentId);
            });
        }
        $certificateSelect = Schema::hasTable('program_certificates')
            ? ['program_certificates.id as certificate_id', 'program_certificates.status as certificate_status']
            : [DB::raw('NULL as certificate_id'), DB::raw('NULL as certificate_status')];
        $programs = $programQuery
            ->whereIn('programs.status', ['active', 'completed'])
            ->select('programs.*', 'program_attendances.validation_status', 'program_attendances.checked_in_at', ...$certificateSelect)
            ->orderByRaw("CASE WHEN programs.attendance_status = 'open' THEN 0 ELSE 1 END")
            ->orderByDesc('programs.starts_at')
            ->get();

        $pagePermissions = $this->studentProgramPagePermissions($studentId);
        $programs->each(function ($program) use ($pagePermissions): void {
            $permissions = $pagePermissions->get((int) $program->id, collect());
            $program->has_qr_presenter_permission = $permissions->contains(fn ($type) => in_array($type, ['qr_presenter', 'all'], true));
            $program->has_questionnaire_page_permission = $permissions->contains(fn ($type) => in_array($type, ['questionnaire', 'all'], true));
        });

        $totalPoints = $programs->where('validation_status', 'valid')->sum('participation_points');
        $programsJoined = $programs->whereNotNull('checked_in_at')->count();

        // Check active in-system surveys for PB students, including completed ones so students can update their answers.
        $activeSurveys = DB::table('program_surveys')
            ->join('programs', 'programs.id', '=', 'program_surveys.program_id')
            ->leftJoin('program_attendances', function ($join) use ($studentId): void {
                $join->on('program_attendances.program_id', '=', 'programs.id')
                    ->where('program_attendances.student_id', '=', $studentId)
                    ->where('program_attendances.attendee_type', '=', 'internal');
            })
            ->leftJoin('program_survey_responses', function ($join): void {
                $join->on('program_survey_responses.program_survey_id', '=', 'program_surveys.id')
                    ->on('program_survey_responses.program_attendance_id', '=', 'program_attendances.id');
            })
            ->where('program_surveys.status', 'published')
            ->where('programs.status', 'active')
            ->select(
                'program_surveys.program_id',
                'program_surveys.title as survey_title',
                'programs.title as program_title',
                'programs.questionnaire_publish_mode',
                DB::raw('COUNT(program_survey_responses.id) as response_count')
            )
            ->groupBy(
                'program_surveys.program_id',
                'program_surveys.title',
                'programs.title',
                'programs.questionnaire_publish_mode'
            )
            ->get()
            ->map(function ($surveyProgram) {
                $surveyProgram->already_submitted = (int) ($surveyProgram->response_count ?? 0) > 0;

                return $surveyProgram;
            })
            ->filter(function ($surveyProgram) use ($programs): bool {
                if (($surveyProgram->questionnaire_publish_mode ?? 'internal_system') !== 'qr_code') {
                    return true;
                }

                $program = $programs->firstWhere('id', $surveyProgram->program_id);

                return (bool) ($program?->checked_in_at || $program?->has_questionnaire_page_permission);
            })
            ->keyBy('program_id');

        return view('student.programs.index', compact('programs', 'totalPoints', 'programsJoined', 'activeSurveys'));
    }

    public function attendanceQrAccess(): View
    {
        $studentId = (int) session('auth_user.id');
        $programs = $this->studentQrPresenterPrograms($studentId);

        return view('student.programs.attendance-qr', compact('programs'));
    }

    public function attendanceQrPresenter(int $id): View
    {
        $studentId = (int) session('auth_user.id');
        abort_unless($this->hasProgramPagePermission($id, $studentId, 'qr_presenter'), 404);

        $program = DB::table('programs')
            ->where('id', $id)
            ->where('status', 'active')
            ->where('attendance_status', 'open')
            ->first();
        abort_unless($program, 404);

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
        $presenterTokenUrl = route('student.programs.attendance-qr.live-token', $program->id);
        $presenterExitUrl = route('student.programs.attendance-qr.index');

        return view('admin.programs.presenter', compact(
            'program',
            'survey',
            'tokenData',
            'initialCheckinUrl',
            'studentCheckinUrl',
            'totalJoined',
            'internalCount',
            'externalCount',
            'presenterTokenUrl',
            'presenterExitUrl'
        ));
    }

    public function attendanceQrLiveToken(int $id)
    {
        $studentId = (int) session('auth_user.id');
        if (! $this->hasProgramPagePermission($id, $studentId, 'qr_presenter')) {
            return response()->json(['error' => __('Unauthorized.')], 403);
        }

        $program = DB::table('programs')
            ->where('id', $id)
            ->where('status', 'active')
            ->where('attendance_status', 'open')
            ->first();
        if (! $program) {
            return response()->json(['error' => __('Program not found or attendance closed.')], 404);
        }

        $tokenData = DynamicQrToken::generate($program->id);
        $attendances = DB::table('program_attendances')->where('program_id', $program->id)->get();
        $ratings = $attendances->pluck('satisfaction_rating')->filter();

        return response()->json([
            'token' => $tokenData['token'],
            'expires_in' => $tokenData['expires_in'],
            'timestamp' => $tokenData['timestamp'],
            'public_url' => route('public.programs.qr_checkin', ['program' => $program->id, 't' => $tokenData['token']]),
            'student_url' => route('student.programs.show', ['program' => $program->id, 't' => $tokenData['token']]),
            'attendance_status' => $program->attendance_status,
            'stats' => [
                'total' => $attendances->count(),
                'internal' => $attendances->where('attendee_type', 'internal')->count(),
                'external' => $attendances->where('attendee_type', 'external')->count(),
                'target' => $program->estimated_participants ?: 0,
                'avg_rating' => $ratings->isNotEmpty() ? round($ratings->avg(), 1) : 0.0,
            ],
        ]);
    }

    public function downloadCertificate(int $certificate)
    {
        $studentId = (int) session('auth_user.id');
        $item = DB::table('program_certificates')->where('id',$certificate)->where('student_id',$studentId)->first();
        abort_unless($item && $item->status === 'ready' && $item->path && Storage::disk($item->disk)->exists($item->path),404);
        return Storage::disk($item->disk)->download($item->path,$item->matric_no.' - Certificate.pdf');
    }

    public function quickScanAttendance(Request $request, int $id)
    {
        try {
            $studentId = (int) session('auth_user.id');
            $studentColumns = ['id', 'full_name', 'matric_no'];
            if (Schema::hasColumn('students', 'program')) {
                $studentColumns[] = 'program';
            }

            $student = DB::table('students')->where('id', $studentId)->first($studentColumns);
            if (! $student) {
                return response()->json(['success' => false, 'message' => __('Student profile not found.')], 403);
            }

            $program = DB::table('programs')->where('id', $id)->where('status', 'active')->first();
            if (! $program) {
                return response()->json(['success' => false, 'message' => __('Program not found or inactive.')], 404);
            }

            if (($program->attendance_status ?? 'closed') !== 'open') {
                return response()->json(['success' => false, 'message' => __('Attendance is currently closed for this program.')], 422);
            }

            $token = $request->input('qr_token') ?? $request->input('token') ?? $request->input('t');
            if (filled($token)) {
                if (! DynamicQrToken::verify($token, $program->id)) {
                    return response()->json([
                        'success' => false,
                        'message' => __('The scanned QR code has expired. Please scan the current QR code on the screen.'),
                    ], 422);
                }
            }

            $attendanceQuery = DB::table('program_attendances')
                ->where('program_id', $program->id)
                ->where('student_id', $student->id);

            if (Schema::hasColumn('program_attendances', 'attendee_type')) {
                $attendanceQuery->where('attendee_type', 'internal');
            }

            $existing = $attendanceQuery->first();

            $survey = DB::table('program_surveys')
                ->where('program_id', $program->id)
                ->where('status', 'published')
                ->orderByDesc('id')
                ->first();

            $hasSurvey = (bool) $survey;
            $surveyUrl = $hasSurvey ? route('student.programs.survey', $program->id) : null;

            if ($existing) {
                $existingStatus = data_get($existing, 'validation_status', 'valid');

                return response()->json([
                    'success' => true,
                    'already_recorded' => true,
                    'status' => $existingStatus,
                    'message' => __('DONE KEY IN'),
                    'student' => [
                        'full_name' => $student->full_name,
                        'matric_no' => $student->matric_no,
                        'program' => data_get($student, 'program', '-'),
                    ],
                    'program' => [
                        'id' => $program->id,
                        'title' => data_get($program, 'title', __('Program')),
                        'venue' => data_get($program, 'venue', '-'),
                        'points' => $existingStatus === 'valid' ? (int) data_get($program, 'participation_points', 0) : 0,
                        'has_survey' => $hasSurvey,
                        'survey_url' => $surveyUrl,
                    ],
                ]);
            }

            $programLatitude = data_get($program, 'latitude');
            $programLongitude = data_get($program, 'longitude');
            $usesGeofence = $programLatitude !== null && $programLongitude !== null;
            $lat = $request->input('latitude');
            $lng = $request->input('longitude');
            $accuracy = $request->input('location_accuracy_m');
            $capturedAt = $this->databaseTimestamp($request->input('location_captured_at'));

            $distance = ($usesGeofence && filled($lat) && filled($lng))
                ? $this->distanceMeters((float) $programLatitude, (float) $programLongitude, (float) $lat, (float) $lng)
                : null;

            $validationStatus = ! $usesGeofence
                ? 'valid'
                : (($distance !== null && $distance > (int) data_get($program, 'geofence_radius_m', 50))
                    ? 'invalid_outside_radius'
                    : (($accuracy !== null && (float) $accuracy > 100) ? 'needs_review_accuracy' : 'valid'));

            $attendanceData = [
                'program_id' => $program->id,
                'student_id' => $student->id,
                'attendee_type' => 'internal',
                'full_name' => $student->full_name,
                'identifier' => $student->matric_no,
                'institution_or_unit' => data_get($student, 'program'),
                'checked_in_at' => now(),
                'latitude' => $lat,
                'longitude' => $lng,
                'geofence_valid' => $validationStatus === 'valid',
                'created_at' => now(),
                'updated_at' => now(),
            ];

            foreach ([
                'validation_status' => $validationStatus,
                'distance_m' => $distance === null ? null : round($distance, 2),
                'location_accuracy_m' => $accuracy === null ? null : round((float) $accuracy, 2),
                'location_captured_at' => $capturedAt,
            ] as $column => $value) {
                if (Schema::hasColumn('program_attendances', $column)) {
                    $attendanceData[$column] = $value;
                }
            }

            $attendanceData = array_filter(
                $attendanceData,
                fn ($value, string $column): bool => Schema::hasColumn('program_attendances', $column),
                ARRAY_FILTER_USE_BOTH
            );

            DB::table('program_attendances')->insertGetId($attendanceData);

            return response()->json([
                'success' => true,
                'already_recorded' => false,
                'status' => $validationStatus,
                'message' => __('DONE KEY IN'),
                'student' => [
                    'full_name' => $student->full_name,
                    'matric_no' => $student->matric_no,
                    'program' => data_get($student, 'program', '-'),
                ],
                'program' => [
                    'id' => $program->id,
                    'title' => data_get($program, 'title', __('Program')),
                    'venue' => data_get($program, 'venue', '-'),
                    'points' => $validationStatus === 'valid' ? (int) data_get($program, 'participation_points', 0) : 0,
                    'has_survey' => $hasSurvey,
                    'survey_url' => $surveyUrl,
                ],
            ]);
        } catch (\Throwable $exception) {
            Log::error('Program quick scan attendance failed', [
                'program_id' => $id,
                'student_id' => session('auth_user.id'),
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => __('Attendance could not be recorded. Please ask the admin to check the server log.'),
            ], 500);
        }
    }

    public function survey(int $id): View
    {
        $studentId = (int) session('auth_user.id');
        $student = DB::table('students')->where('id', $studentId)->first(['id', 'full_name', 'matric_no', 'program']);
        abort_unless($student, 404);

        $program = DB::table('programs')->where('id', $id)->where('status', 'active')->first();
        abort_unless($program, 404);

        $survey = DB::table('program_surveys')
            ->where('program_id', $id)
            ->where('status', 'published')
            ->latest('id')
            ->first();
        abort_unless($survey, 404);

        $questions = DB::table('program_survey_questions')
            ->where('program_survey_id', $survey->id)
            ->orderBy('sort_order')
            ->get();

        $attendance = DB::table('program_attendances')
            ->where('program_id', $id)
            ->where('student_id', $studentId)
            ->where('attendee_type', 'internal')
            ->first();

        if (($program->questionnaire_publish_mode ?? 'internal_system') === 'qr_code'
            && ! $attendance
            && ! $this->hasProgramPagePermission($program->id, $studentId, 'questionnaire')) {
            abort(404);
        }

        $alreadySubmitted = $attendance ? DB::table('program_survey_responses')
            ->where('program_survey_id', $survey->id)
            ->where('program_attendance_id', $attendance->id)
            ->exists() : false;

        $existingAnswers = $attendance ? DB::table('program_survey_responses')
            ->where('program_survey_id', $survey->id)
            ->where('program_attendance_id', $attendance->id)
            ->pluck('answer_value', 'question_id')
            ->mapWithKeys(fn ($answer, $questionId) => [(int) $questionId => (string) $answer])
            ->all() : [];

        return view('student.programs.survey', compact('program', 'survey', 'questions', 'student', 'attendance', 'alreadySubmitted', 'existingAnswers'));
    }

    public function storeSurvey(Request $request, int $id): RedirectResponse
    {
        $studentId = (int) session('auth_user.id');
        $student = DB::table('students')->where('id', $studentId)->first(['id', 'full_name', 'matric_no', 'program']);
        abort_unless($student, 404);

        $program = DB::table('programs')->where('id', $id)->where('status', 'active')->first();
        abort_unless($program, 404);

        $survey = DB::table('program_surveys')
            ->where('program_id', $id)
            ->where('status', 'published')
            ->latest('id')
            ->first();
        abort_unless($survey, 404);

        if (($program->questionnaire_publish_mode ?? 'internal_system') === 'qr_code'
            && ! $this->hasProgramPagePermission($program->id, $studentId, 'questionnaire')) {
            $hasAttendance = DB::table('program_attendances')
                ->where('program_id', $id)
                ->where('student_id', $studentId)
                ->where('attendee_type', 'internal')
                ->exists();

            abort_unless($hasAttendance, 404);
        }

        $questions = DB::table('program_survey_questions')
            ->where('program_survey_id', $survey->id)
            ->orderBy('sort_order')
            ->get();

        $validated = $request->validate([
            'answers' => ['nullable', 'array'],
            'answers.*' => ['nullable', 'string', 'max:5000'],
        ]);

        $answers = collect($validated['answers'] ?? [])->mapWithKeys(fn ($answer, $qId) => [(int) $qId => $answer]);
        $missing = $questions->where('is_required', true)->first(fn ($q) => blank($answers->get((int) $q->id)));
        if ($missing) {
            return back()->withInput()->withErrors(['answers.'.(int) $missing->id => __('Please answer the required question: :question', ['question' => $missing->question_text])]);
        }

        $attendance = DB::table('program_attendances')
            ->where('program_id', $id)
            ->where('student_id', $studentId)
            ->where('attendee_type', 'internal')
            ->first();

        $attendanceId = $attendance?->id ?? DB::table('program_attendances')->insertGetId([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'attendee_type' => 'internal',
            'full_name' => $student->full_name,
            'identifier' => $student->matric_no,
            'institution_or_unit' => $student->program,
            'checked_in_at' => now(),
            'validation_status' => 'valid',
            'geofence_valid' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::transaction(function () use ($survey, $attendanceId, $questions, $answers): void {
            DB::table('program_survey_responses')
                ->where('program_survey_id', $survey->id)
                ->where('program_attendance_id', $attendanceId)
                ->delete();

            foreach ($questions as $question) {
                if (filled($answer = $answers->get((int) $question->id))) {
                    DB::table('program_survey_responses')->insert([
                        'program_survey_id' => $survey->id,
                        'program_attendance_id' => $attendanceId,
                        'question_id' => $question->id,
                        'answer_value' => (string) $answer,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        return redirect()->route('student.programs.index')->with('success', __('Terima kasih! Soal selidik anda telah berjaya dihantar.'));
    }

    public function show(Request $request, int $id): View|RedirectResponse
    {
        $studentId = (int) session('auth_user.id');
        $program = DB::table('programs')->where('id', $id)->where('status', 'active')->first();
        abort_unless($program, 404);
        abort_unless($program->attendance_status === 'open', 404);

        $student = DB::table('students')->where('id', $studentId)->first(['id', 'full_name', 'matric_no', 'program']);
        abort_unless($student, 404);

        $attendance = DB::table('program_attendances')->where('program_id', $id)->where('student_id', $studentId)->where('attendee_type', 'internal')->first();
        $token = $request->input('t') ?? $request->input('token') ?? $request->input('qr_token');

        if (($program->attendance_checkin_mode ?? 'qr_code') === 'qr_code' && blank($token) && ! $attendance) {
            return redirect()->route('student.programs.index')->with('info', __('Program ini memerlukan imbasan Kod QR di skrin dewan untuk mendaftar kehadiran.'));
        }

        return view('student.programs.show', compact('program', 'student', 'attendance', 'token'));
    }

    public function store(Request $request, int $id): RedirectResponse
    {
        $studentId = (int) session('auth_user.id');
        $program = DB::table('programs')->where('id', $id)->where('status', 'active')->where('attendance_status', 'open')->first();
        abort_unless($program, 404);
        $student = DB::table('students')->where('id', $studentId)->first(['id', 'full_name', 'matric_no', 'program']);
        abort_unless($student, 404);

        if (DB::table('program_attendances')->where('program_id', $id)->where('student_id', $studentId)->where('attendee_type', 'internal')->exists()) {
            return redirect()->route('student.programs.index')->with('success', __('You have already submitted attendance for this program.'));
        }

        $token = $request->input('qr_token') ?? $request->input('token') ?? $request->input('t');
        if (($program->attendance_checkin_mode ?? 'qr_code') === 'qr_code') {
            if (blank($token) || ! DynamicQrToken::verify($token, $program->id)) {
                return back()->withInput()->withErrors([
                    'qr_token' => __('The scanned QR code has expired or is invalid. Please scan the current QR code displayed on the screen.'),
                ]);
            }
        } elseif (filled($token)) {
            if (! DynamicQrToken::verify($token, $program->id)) {
                return back()->withInput()->withErrors([
                    'qr_token' => __('The scanned QR code has expired. Please scan the current QR code displayed on the screen.'),
                ]);
            }
        }

        $usesGeofence = $program->latitude !== null && $program->longitude !== null;
        $validated = $request->validate([
            'qr_token' => ['nullable', 'string'],
            'latitude' => [$usesGeofence ? 'required' : 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => [$usesGeofence ? 'required' : 'nullable', 'numeric', 'between:-180,180'],
            'location_accuracy_m' => [$usesGeofence ? 'required' : 'nullable', 'numeric', 'min:0', 'max:5000'],
            'location_captured_at' => [$usesGeofence ? 'required' : 'nullable', 'date', 'before_or_equal:now', 'after:'.now()->subMinutes(5)->toDateTimeString()],
        ]);

        $distance = ($usesGeofence && filled($validated['latitude'] ?? null) && filled($validated['longitude'] ?? null))
            ? $this->distanceMeters((float) $program->latitude, (float) $program->longitude, (float) $validated['latitude'], (float) $validated['longitude'])
            : null;
        $accuracy = $usesGeofence ? (float) ($validated['location_accuracy_m'] ?? null) : null;
        $status = ! $usesGeofence ? 'valid' : ($distance > (int) $program->geofence_radius_m ? 'invalid_outside_radius' : ($accuracy > 100 ? 'needs_review_accuracy' : 'valid'));

        $attendanceData = [
            'program_id' => $program->id,
            'student_id' => $student->id,
            'attendee_type' => 'internal',
            'full_name' => $student->full_name,
            'identifier' => $student->matric_no,
            'institution_or_unit' => $student->program,
            'checked_in_at' => now(),
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'geofence_valid' => $status === 'valid',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        foreach ([
            'validation_status' => $status,
            'distance_m' => $distance === null ? null : round($distance, 2),
            'location_accuracy_m' => $accuracy === null ? null : round($accuracy, 2),
            'location_captured_at' => $this->databaseTimestamp($validated['location_captured_at'] ?? null),
        ] as $column => $value) {
            if (Schema::hasColumn('program_attendances', $column)) {
                $attendanceData[$column] = $value;
            }
        }

        DB::table('program_attendances')->insert($attendanceData);

        return redirect()->route('student.programs.index')->with('success', $status === 'valid'
            ? __('Valid attendance recorded. You earned :points merit points.', ['points' => $program->participation_points])
            : __('Attendance was recorded but did not qualify for merit points.'));
    }

    private function studentProgramPagePermissions(int $studentId)
    {
        if (! Schema::hasTable('program_student_page_permissions')) {
            return collect();
        }

        return DB::table('program_student_page_permissions')
            ->where('student_id', $studentId)
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get(['program_id', 'access_type'])
            ->groupBy('program_id')
            ->map(fn ($permissions) => $permissions->pluck('access_type'));
    }

    public static function studentHasQrPresenterAccess(int $studentId): bool
    {
        if (! Schema::hasTable('program_student_page_permissions')) {
            return false;
        }

        return DB::table('program_student_page_permissions')
            ->join('programs', 'programs.id', '=', 'program_student_page_permissions.program_id')
            ->where('program_student_page_permissions.student_id', $studentId)
            ->whereIn('program_student_page_permissions.access_type', ['qr_presenter', 'all'])
            ->where('programs.status', 'active')
            ->where('programs.attendance_status', 'open')
            ->where(function ($query): void {
                $query->whereNull('program_student_page_permissions.expires_at')
                    ->orWhere('program_student_page_permissions.expires_at', '>', now());
            })
            ->exists();
    }

    private function studentQrPresenterPrograms(int $studentId)
    {
        if (! Schema::hasTable('program_student_page_permissions')) {
            return collect();
        }

        return DB::table('program_student_page_permissions')
            ->join('programs', 'programs.id', '=', 'program_student_page_permissions.program_id')
            ->where('program_student_page_permissions.student_id', $studentId)
            ->whereIn('program_student_page_permissions.access_type', ['qr_presenter', 'all'])
            ->where('programs.status', 'active')
            ->where('programs.attendance_status', 'open')
            ->where(function ($query): void {
                $query->whereNull('program_student_page_permissions.expires_at')
                    ->orWhere('program_student_page_permissions.expires_at', '>', now());
            })
            ->select(
                'programs.id',
                'programs.title',
                'programs.reference_no',
                'programs.venue',
                'programs.starts_at',
                'programs.ends_at',
                'programs.attendance_status',
                'program_student_page_permissions.expires_at'
            )
            ->orderByDesc('programs.starts_at')
            ->get();
    }

    private function hasProgramPagePermission(int $programId, int $studentId, string $accessType): bool
    {
        if (! Schema::hasTable('program_student_page_permissions')) {
            return false;
        }

        return DB::table('program_student_page_permissions')
            ->where('program_id', $programId)
            ->where('student_id', $studentId)
            ->whereIn('access_type', [$accessType, 'all'])
            ->where(function ($query): void {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->exists();
    }

    private function distanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; $dLat = deg2rad($lat2 - $lat1); $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function databaseTimestamp(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }
}
