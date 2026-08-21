<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Support\DynamicQrToken;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        $totalPoints = $programs->where('validation_status', 'valid')->sum('participation_points');
        $programsJoined = $programs->whereNotNull('checked_in_at')->count();

        // Check active in-system surveys for PB students
        $activeSurveys = DB::table('program_surveys')
            ->join('programs', 'programs.id', '=', 'program_surveys.program_id')
            ->where('program_surveys.status', 'published')
            ->where('programs.status', 'active')
            ->select('program_surveys.program_id', 'program_surveys.title as survey_title', 'programs.title as program_title', 'programs.questionnaire_publish_mode')
            ->get()
            ->keyBy('program_id');

        return view('student.programs.index', compact('programs', 'totalPoints', 'programsJoined', 'activeSurveys'));
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
        $studentId = (int) session('auth_user.id');
        $student = DB::table('students')->where('id', $studentId)->first(['id', 'full_name', 'matric_no', 'program']);
        if (! $student) {
            return response()->json(['success' => false, 'message' => __('Student profile not found.')], 403);
        }

        $program = DB::table('programs')->where('id', $id)->where('status', 'active')->first();
        if (! $program) {
            return response()->json(['success' => false, 'message' => __('Program not found or inactive.')], 404);
        }

        if ($program->attendance_status !== 'open') {
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

        $existing = DB::table('program_attendances')
            ->where('program_id', $program->id)
            ->where('student_id', $student->id)
            ->where('attendee_type', 'internal')
            ->first();

        $survey = DB::table('program_surveys')
            ->where('program_id', $program->id)
            ->where('status', 'published')
            ->orderByDesc('id')
            ->first();

        $hasSurvey = (bool) $survey;
        $surveyUrl = $hasSurvey ? route('student.programs.survey', $program->id) : null;

        if ($existing) {
            return response()->json([
                'success' => true,
                'already_recorded' => true,
                'status' => $existing->validation_status,
                'message' => __('DONE KEY IN'),
                'student' => [
                    'full_name' => $student->full_name,
                    'matric_no' => $student->matric_no,
                    'program' => $student->program,
                ],
                'program' => [
                    'id' => $program->id,
                    'title' => $program->title,
                    'venue' => $program->venue,
                    'points' => $existing->validation_status === 'valid' ? (int) $program->participation_points : 0,
                    'has_survey' => $hasSurvey,
                    'survey_url' => $surveyUrl,
                ],
            ]);
        }

        $usesGeofence = $program->latitude !== null && $program->longitude !== null;
        $lat = $request->input('latitude');
        $lng = $request->input('longitude');
        $accuracy = $request->input('location_accuracy_m');
        $capturedAt = $request->input('location_captured_at');

        $distance = ($usesGeofence && filled($lat) && filled($lng))
            ? $this->distanceMeters((float) $program->latitude, (float) $program->longitude, (float) $lat, (float) $lng)
            : null;

        $validationStatus = ! $usesGeofence
            ? 'valid'
            : (($distance !== null && $distance > (int) $program->geofence_radius_m)
                ? 'invalid_outside_radius'
                : (($accuracy !== null && (float) $accuracy > 100) ? 'needs_review_accuracy' : 'valid'));

        DB::table('program_attendances')->insertGetId([
            'program_id' => $program->id,
            'student_id' => $student->id,
            'attendee_type' => 'internal',
            'full_name' => $student->full_name,
            'identifier' => $student->matric_no,
            'institution_or_unit' => $student->program,
            'checked_in_at' => now(),
            'latitude' => $lat,
            'longitude' => $lng,
            'geofence_valid' => $validationStatus === 'valid',
            'validation_status' => $validationStatus,
            'distance_m' => $distance === null ? null : round($distance, 2),
            'location_accuracy_m' => $accuracy === null ? null : round((float) $accuracy, 2),
            'location_captured_at' => $capturedAt,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'already_recorded' => false,
            'status' => $validationStatus,
            'message' => __('DONE KEY IN'),
            'student' => [
                'full_name' => $student->full_name,
                'matric_no' => $student->matric_no,
                'program' => $student->program,
            ],
            'program' => [
                'id' => $program->id,
                'title' => $program->title,
                'venue' => $program->venue,
                'points' => $validationStatus === 'valid' ? (int) $program->participation_points : 0,
                'has_survey' => $hasSurvey,
                'survey_url' => $surveyUrl,
            ],
        ]);
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

        $alreadySubmitted = $attendance ? DB::table('program_survey_responses')
            ->where('program_survey_id', $survey->id)
            ->where('program_attendance_id', $attendance->id)
            ->exists() : false;

        return view('student.programs.survey', compact('program', 'survey', 'questions', 'student', 'attendance', 'alreadySubmitted'));
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

    public function show(Request $request, int $id): View
    {
        $studentId = (int) session('auth_user.id');
        $program = DB::table('programs')->where('id', $id)->where('status', 'active')->first();
        abort_unless($program, 404);
        $survey = DB::table('program_surveys')->where('program_id', $id)->where('status', 'published')->latest('id')->first();
        abort_unless($program->attendance_status === 'open' && (! $program->questionnaire_enabled || $survey), 404);
        $questions = $survey && $program->questionnaire_enabled
            ? DB::table('program_survey_questions')->where('program_survey_id', $survey->id)->orderBy('sort_order')->get()
            : collect();
        $student = DB::table('students')->where('id', $studentId)->first(['id', 'full_name', 'matric_no', 'program']);
        abort_unless($student, 404);
        $attendance = DB::table('program_attendances')->where('program_id', $id)->where('student_id', $studentId)->where('attendee_type', 'internal')->first();
        $token = $request->input('t') ?? $request->input('token');

        return view('student.programs.show', compact('program', 'survey', 'questions', 'student', 'attendance', 'token'));
    }

    public function store(Request $request, int $id): RedirectResponse
    {
        $studentId = (int) session('auth_user.id');
        $program = DB::table('programs')->where('id', $id)->where('status', 'active')->where('attendance_status', 'open')->first();
        abort_unless($program, 404);
        $student = DB::table('students')->where('id', $studentId)->first(['id', 'full_name', 'matric_no', 'program']);
        abort_unless($student, 404);
        $survey = DB::table('program_surveys')->where('program_id', $id)->where('status', 'published')->latest('id')->first();
        abort_unless(! $program->questionnaire_enabled || $survey, 404);

        if (DB::table('program_attendances')->where('program_id', $id)->where('student_id', $studentId)->where('attendee_type', 'internal')->exists()) {
            return back()->withErrors(['attendance' => __('You have already submitted attendance for this program.')]);
        }

        if ($request->filled('qr_token')) {
            if (! DynamicQrToken::verify($request->input('qr_token'), $program->id)) {
                return back()->withInput()->withErrors([
                    'qr_token' => __('The scanned QR code has expired. Please scan the current QR code displayed on the screen.'),
                ]);
            }
        }

        $usesGeofence = $program->latitude !== null && $program->longitude !== null;
        $validated = $request->validate([
            'qr_token' => ['nullable', 'string'],
            'answers' => ['nullable', 'array'], 'answers.*' => ['nullable', 'string', 'max:5000'],
            'latitude' => [$usesGeofence ? 'required' : 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => [$usesGeofence ? 'required' : 'nullable', 'numeric', 'between:-180,180'],
            'location_accuracy_m' => [$usesGeofence ? 'required' : 'nullable', 'numeric', 'min:0', 'max:5000'],
            'location_captured_at' => [$usesGeofence ? 'required' : 'nullable', 'date', 'before_or_equal:now', 'after:'.now()->subMinutes(5)->toDateTimeString()],
        ]);

        $questions = $survey && $program->questionnaire_enabled
            ? DB::table('program_survey_questions')->where('program_survey_id', $survey->id)->orderBy('sort_order')->get()
            : collect();
        $answers = collect($validated['answers'] ?? [])->mapWithKeys(fn ($answer, $questionId) => [(int) $questionId => $answer]);
        if ($answers->keys()->diff($questions->pluck('id')->map(fn ($id) => (int) $id))->isNotEmpty()) {
            return back()->withInput()->withErrors(['answers' => __('Invalid questionnaire response.')]);
        }
        $missing = $questions->where('is_required', true)->first(fn ($question) => blank($answers->get((int) $question->id)));
        if ($missing) {
            return back()->withInput()->withErrors(['answers.'.(int) $missing->id => __('Please answer the required question: :question', ['question' => $missing->question_text])]);
        }

        $distance = $usesGeofence ? $this->distanceMeters((float) $program->latitude, (float) $program->longitude, (float) $validated['latitude'], (float) $validated['longitude']) : null;
        $accuracy = $usesGeofence ? (float) $validated['location_accuracy_m'] : null;
        $status = ! $usesGeofence ? 'valid' : ($distance > (int) $program->geofence_radius_m ? 'invalid_outside_radius' : ($accuracy > 100 ? 'needs_review_accuracy' : 'valid'));

        DB::transaction(function () use ($program, $student, $survey, $questions, $answers, $validated, $distance, $accuracy, $status): void {
            $attendanceId = DB::table('program_attendances')->insertGetId([
                'program_id' => $program->id, 'student_id' => $student->id, 'attendee_type' => 'internal',
                'full_name' => $student->full_name, 'identifier' => $student->matric_no, 'institution_or_unit' => $student->program,
                'checked_in_at' => now(), 'latitude' => $validated['latitude'] ?? null, 'longitude' => $validated['longitude'] ?? null,
                'geofence_valid' => $status === 'valid', 'validation_status' => $status, 'distance_m' => $distance === null ? null : round($distance, 2),
                'location_accuracy_m' => $accuracy === null ? null : round($accuracy, 2), 'location_captured_at' => $validated['location_captured_at'] ?? null,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach ($questions as $question) {
                if (filled($answer = $answers->get((int) $question->id))) {
                    DB::table('program_survey_responses')->insert([
                        'program_survey_id' => $survey->id, 'program_attendance_id' => $attendanceId,
                        'question_id' => $question->id, 'answer_value' => (string) $answer,
                        'created_at' => now(), 'updated_at' => now(),
                    ]);
                }
            }
        });

        return redirect()->route('student.programs.index')->with('success', $status === 'valid'
            ? __('Valid attendance recorded. You earned :points participation points.', ['points' => $program->participation_points])
            : __('Attendance was recorded but did not qualify for participation points.'));
    }

    private function distanceMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; $dLat = deg2rad($lat2 - $lat1); $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
