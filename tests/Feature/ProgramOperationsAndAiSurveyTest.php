<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use App\Jobs\GenerateProgramCertificate;
use Tests\TestCase;

class ProgramOperationsAndAiSurveyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('full_name');
            $table->string('role')->default('lecturer');
            $table->string('staff_department')->nullable();
            $table->string('reporting_branch')->nullable();
            $table->string('position')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('programs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('created_by');
            $table->string('registration_type')->default('approved_program');
            $table->string('approval_branch')->nullable();
            $table->string('title');
            $table->string('reference_no')->nullable();
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->string('venue')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedSmallInteger('geofence_radius_m')->default(50);
            $table->string('target_participants')->nullable();
            $table->unsignedInteger('estimated_participants')->nullable();
            $table->unsignedSmallInteger('participation_points')->default(0);
            $table->boolean('certificate_enabled')->default(true);
            $table->string('certificate_template')->default('standard_placeholder');
            $table->boolean('questionnaire_enabled')->default(true);
            $table->string('questionnaire_publish_mode', 32)->default('internal_system');
            $table->decimal('estimated_budget', 12, 2)->nullable();
            $table->string('paperwork_method')->default('manual');
            $table->timestamp('paperwork_approval_confirmed_at')->nullable();
            $table->string('status')->default('draft');
            $table->string('attendance_status')->default('open');
            $table->string('attendance_checkin_mode', 32)->default('qr_code');
            $table->timestamp('attendance_opened_at')->nullable();
            $table->timestamp('attendance_closed_at')->nullable();
            $table->unsignedBigInteger('deputy_reviewer_id')->nullable();
            $table->unsignedBigInteger('director_reviewer_id')->nullable();
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('matric_no')->nullable();
            $table->string('program')->nullable();
            $table->timestamps();
        });

        Schema::create('program_student_page_permissions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('student_id');
            $table->string('access_type', 40);
            $table->unsignedBigInteger('granted_by')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['program_id', 'student_id', 'access_type'], 'program_student_page_permissions_unique');
        });

        Schema::create('program_paperworks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->unsignedInteger('version');
            $table->string('method');
            $table->string('disk')->nullable();
            $table->string('path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->json('structured_snapshot')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });

        Schema::create('program_surveys', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
        });

        Schema::create('program_survey_questions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_survey_id');
            $table->string('question_text', 255);
            $table->string('question_type', 30)->default('rating_5');
            $table->json('options')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });

        Schema::create('program_attendances', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('student_id')->nullable();
            $table->string('attendee_type', 20)->default('internal');
            $table->string('full_name', 180);
            $table->string('identifier', 100);
            $table->string('email', 180)->nullable();
            $table->string('institution_or_unit', 180)->nullable();
            $table->timestamp('checked_in_at');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('geofence_valid')->default(true);
            $table->string('validation_status', 30)->default('invalid_location');
            $table->decimal('distance_m', 10, 2)->nullable();
            $table->decimal('location_accuracy_m', 10, 2)->nullable();
            $table->timestamp('location_captured_at')->nullable();
            $table->unsignedTinyInteger('satisfaction_rating')->nullable();
            $table->text('feedback_comments')->nullable();
            $table->timestamps();
        });

        Schema::create('program_survey_responses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_survey_id');
            $table->unsignedBigInteger('program_attendance_id');
            $table->unsignedBigInteger('question_id');
            $table->text('answer_value');
            $table->timestamps();
        });

        Schema::create('program_reports', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_id')->unique();
            $table->longText('content');
            $table->string('status', 32)->default('draft');
            $table->string('ai_provider')->nullable();
            $table->string('ai_model')->nullable();
            $table->unsignedBigInteger('generated_by');
            $table->timestamp('generated_at');
            $table->string('output_format', 12)->nullable();
            $table->string('docx_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->json('source_summary')->nullable();
            $table->unsignedBigInteger('tpsa_reviewer_id')->nullable();
            $table->timestamp('tpsa_reviewed_at')->nullable();
            $table->text('tpsa_review_note')->nullable();
            $table->unsignedBigInteger('director_reviewer_id')->nullable();
            $table->timestamp('director_reviewed_at')->nullable();
            $table->text('director_review_note')->nullable();
            $table->unsignedBigInteger('kj_hep_reviewer_id')->nullable();
            $table->timestamp('kj_hep_reviewed_at')->nullable();
            $table->text('kj_hep_review_note')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
        Schema::create('program_certificates', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('program_id'); $table->unsignedBigInteger('program_attendance_id');
            $table->unsignedBigInteger('student_id'); $table->string('matric_no'); $table->string('student_name');
            $table->string('serial_no')->unique(); $table->string('template_key')->default('standard_placeholder'); $table->string('status')->default('pending'); $table->string('disk')->default('local');
            $table->string('path')->nullable(); $table->text('failure_reason')->nullable(); $table->unsignedBigInteger('generated_by');
            $table->timestamp('generated_at')->nullable(); $table->timestamps(); $table->unique(['program_id','student_id']);
        });

        DB::table('admins')->insert([
            ['id' => 1, 'username' => 'staff_director', 'email' => 'director@polibesut.edu.my', 'password' => bcrypt('password'), 'role' => 'lecturer', 'full_name' => 'Program Director', 'staff_department' => null, 'reporting_branch' => null, 'position' => 'Lecturer', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'username' => 'tpsa', 'email' => 'tpsa@polibesut.edu.my', 'password' => bcrypt('password'), 'role' => 'lecturer', 'full_name' => 'TPSA', 'staff_department' => null, 'reporting_branch' => 'tpsa', 'position' => 'TIMBALAN PENGARAH SOKONGAN AKADEMIK (TPSA)', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'username' => 'director', 'email' => 'pengarah@polibesut.edu.my', 'password' => bcrypt('password'), 'role' => 'lecturer', 'full_name' => 'Polytechnic Director', 'staff_department' => null, 'reporting_branch' => null, 'position' => 'PENGARAH', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'username' => 'kjhep', 'email' => 'kjhep@polibesut.edu.my', 'password' => bcrypt('password'), 'role' => 'student_affairs_head', 'full_name' => 'KJ HEP', 'staff_department' => null, 'reporting_branch' => null, 'position' => 'KJ HEP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'username' => 'discipline', 'email' => 'discipline@polibesut.edu.my', 'password' => bcrypt('password'), 'role' => 'discipline_admin', 'full_name' => 'Discipline Admin', 'staff_department' => null, 'reporting_branch' => null, 'position' => 'Warden', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function test_program_director_can_open_operations_workspace(): void
    {
        $programId = DB::table('programs')->insertGetId([
            'created_by' => 1,
            'title' => 'Merdeka Youth Camp 2026',
            'paperwork_method' => 'manual',
            'status' => 'active',
            'latitude' => 5.8000000,
            'longitude' => 102.5000000,
            'estimated_participants' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession([
            'auth_user' => [
                'id' => 1,
                'role' => 'admin',
                'admin_role' => 'lecturer',
                'name' => 'Program Director',
            ],
        ])->get(route('admin.programs.operations', $programId));

        $response->assertStatus(200);
        $response->assertSee('Merdeka Youth Camp 2026');
        $this->withSession([
            'auth_user' => [
                'id' => 1,
                'role' => 'admin',
                'admin_role' => 'lecturer',
                'name' => 'Program Director',
            ],
        ])->get(route('admin.programs.questionnaire', $programId))->assertSee('QUESTIONNAIRE BUILDER');
    }

    public function test_ai_questionnaire_generation_returns_questions(): void
    {
        $programId = DB::table('programs')->insertGetId([
            'created_by' => 1,
            'title' => 'Leadership Seminar 2026',
            'paperwork_method' => 'manual',
            'venue' => 'Dewan Utama',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->withSession([
            'auth_user' => [
                'id' => 1,
                'role' => 'admin',
                'admin_role' => 'lecturer',
                'name' => 'Program Director',
            ],
        ])->postJson(route('admin.programs.ai-questionnaire', $programId), [
            'focus' => 'satisfaction',
            'question_count' => 5,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'questions' => [
                '*' => ['question_text', 'question_type'],
            ],
        ]);
    }

    public function test_saving_and_publishing_survey(): void
    {
        $programId = DB::table('programs')->insertGetId([
            'created_by' => 1,
            'title' => 'Tech Workshop 2026',
            'paperwork_method' => 'manual',
            'status' => 'active',
            'venue' => 'Dewan Utama',
            'latitude' => 5.8000000,
            'longitude' => 102.5000000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $saveResponse = $this->withSession([
            'auth_user' => [
                'id' => 1,
                'role' => 'admin',
                'admin_role' => 'lecturer',
                'name' => 'Program Director',
            ],
        ])->from(route('admin.programs.operations', $programId))->post(route('admin.programs.survey.save', $programId), [
            'title' => 'Tech Workshop Feedback',
            'questions' => [
                [
                    'question_text' => 'Was the workshop useful?',
                    'question_type' => 'rating_5',
                    'is_required' => '0',
                ],
                [
                    'question_text' => 'Any additional feedback?',
                    'question_type' => 'text',
                    'is_required' => '1',
                ],
            ],
        ]);

        $saveResponse->assertRedirect(route('admin.programs.operations', $programId));
        $this->assertDatabaseHas('program_surveys', [
            'program_id' => $programId,
            'title' => 'Tech Workshop Feedback',
            'status' => 'draft',
        ]);
        $this->assertDatabaseHas('program_survey_questions', [
            'question_text' => 'Any additional feedback?',
            'question_type' => 'text',
            'is_required' => 1,
        ]);

        $publishResponse = $this->withSession([
            'auth_user' => [
                'id' => 1,
                'role' => 'admin',
                'admin_role' => 'lecturer',
                'name' => 'Program Director',
            ],
        ])->post(route('admin.programs.survey.publish', $programId));

        $publishResponse->assertRedirect(route('admin.programs.operations', $programId));
        $this->assertDatabaseHas('program_surveys', [
            'program_id' => $programId,
            'status' => 'published',
        ]);
    }

    public function test_public_qr_checkin_stores_attendance_and_responses(): void
    {
        $programId = DB::table('programs')->insertGetId([
            'created_by' => 1,
            'title' => 'Public Cultural Festival 2026',
            'paperwork_method' => 'manual',
            'status' => 'active',
            'venue' => 'Dewan Utama',
            'latitude' => 5.8000000,
            'longitude' => 102.5000000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $surveyId = DB::table('program_surveys')->insertGetId([
            'program_id' => $programId,
            'title' => 'Cultural Fest Feedback',
            'status' => 'published',
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $questionId = DB::table('program_survey_questions')->insertGetId([
            'program_survey_id' => $surveyId,
            'question_text' => 'How did you enjoy the cultural performances?',
            'question_type' => 'rating_5',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->post(route('public.programs.qr_checkin.store', $programId), [
            'full_name' => 'Ahmad External Student',
            'identifier' => '0129876543',
            'email' => 'ahmad@example.com',
            'institution_or_unit' => 'UniSZA Visitor',
            'satisfaction_rating' => 5,
            'feedback_comments' => 'Excellent event!',
            'answers' => [
                $questionId => '5',
            ],
            'latitude' => 5.8001000,
            'longitude' => 102.5001000,
            'location_accuracy_m' => 12,
            'location_captured_at' => now()->toIso8601String(),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('program_attendances', [
            'program_id' => $programId,
            'full_name' => 'Ahmad External Student',
            'attendee_type' => 'external',
            'satisfaction_rating' => 5,
            'validation_status' => 'valid',
            'geofence_valid' => true,
        ]);
        $this->assertDatabaseHas('program_survey_responses', [
            'program_survey_id' => $surveyId,
            'question_id' => $questionId,
            'answer_value' => '5',
        ]);
    }

    public function test_public_checkin_requires_answers_for_required_questions(): void
    {
        $programId = DB::table('programs')->insertGetId([
            'created_by' => 1,
            'title' => 'Learning Reflection Program',
            'paperwork_method' => 'pdf',
            'status' => 'active',
            'latitude' => 5.8000000,
            'longitude' => 102.5000000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $surveyId = DB::table('program_surveys')->insertGetId([
            'program_id' => $programId,
            'title' => 'Learning Reflection',
            'status' => 'published',
            'created_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('program_survey_questions')->insert([
            'program_survey_id' => $surveyId,
            'question_text' => 'What did you learn from this program?',
            'question_type' => 'text',
            'is_required' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->from(route('public.programs.qr_checkin', $programId))
            ->post(route('public.programs.qr_checkin.store', $programId), [
                'full_name' => 'Student Without Answer',
                'identifier' => 'TEST-1001',
                'latitude' => 5.8001000,
                'longitude' => 102.5001000,
                'location_accuracy_m' => 10,
                'location_captured_at' => now()->toIso8601String(),
            ]);

        $response->assertRedirect(route('public.programs.qr_checkin', $programId));
        $response->assertSessionHasErrors();
        $this->assertDatabaseMissing('program_attendances', [
            'program_id' => $programId,
            'identifier' => 'TEST-1001',
        ]);
    }

    public function test_program_owner_can_only_open_attendance_after_setup_is_complete(): void
    {
        $programId = DB::table('programs')->insertGetId([
            'created_by' => 1,
            'title' => 'Incomplete Program',
            'paperwork_method' => 'pdf',
            'status' => 'active',
            'attendance_status' => 'closed',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->signIn(1, 'lecturer')
            ->post(route('admin.programs.attendance.open', $programId))
            ->assertSessionHasErrors('attendance');

        $this->assertDatabaseHas('programs', ['id' => $programId, 'attendance_status' => 'closed']);
    }

    public function test_program_without_coordinates_can_open_and_record_attendance_without_gps(): void
    {
        $programId = DB::table('programs')->insertGetId([
            'created_by' => 1,
            'title' => 'Attendance Without GPS',
            'paperwork_method' => 'none',
            'questionnaire_enabled' => false,
            'status' => 'active',
            'attendance_status' => 'closed',
            'venue' => 'Dewan Kuliah',
            'geofence_radius_m' => 50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->signIn(1, 'lecturer')
            ->post(route('admin.programs.attendance.open', $programId))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('programs', ['id' => $programId, 'attendance_status' => 'open']);

        $this->post(route('public.programs.qr_checkin.store', $programId), [
            'full_name' => 'Student Without GPS',
            'identifier' => 'NO-GPS-ALLOWED',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('program_attendances', [
            'program_id' => $programId,
            'identifier' => 'NO-GPS-ALLOWED',
            'validation_status' => 'valid',
            'geofence_valid' => true,
            'distance_m' => null,
        ]);
    }

    public function test_server_marks_attendance_outside_the_geofence_as_invalid_and_blocks_duplicates(): void
    {
        $programId = DB::table('programs')->insertGetId([
            'created_by' => 1,
            'title' => 'Geofenced Program',
            'paperwork_method' => 'pdf',
            'status' => 'active',
            'attendance_status' => 'open',
            'venue' => 'Dewan Utama',
            'latitude' => 5.8000000,
            'longitude' => 102.5000000,
            'geofence_radius_m' => 50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $submission = [
            'full_name' => 'External Student',
            'identifier' => 'EXT-9001',
            'latitude' => 5.8100000,
            'longitude' => 102.5100000,
            'location_accuracy_m' => 10,
            'location_captured_at' => now()->toIso8601String(),
        ];

        $this->post(route('public.programs.qr_checkin.store', $programId), $submission)
            ->assertSessionHas('success');

        $this->assertDatabaseHas('program_attendances', [
            'program_id' => $programId,
            'identifier' => 'EXT-9001',
            'geofence_valid' => false,
            'validation_status' => 'invalid_outside_radius',
        ]);

        $this->post(route('public.programs.qr_checkin.store', $programId), $submission)
            ->assertSessionHasErrors('identifier');
        $this->assertSame(1, DB::table('program_attendances')->where('program_id', $programId)->count());
    }

    public function test_public_attendance_requires_recent_location_data(): void
    {
        $programId = DB::table('programs')->insertGetId([
            'created_by' => 1,
            'title' => 'Location Required Program',
            'paperwork_method' => 'pdf',
            'status' => 'active',
            'attendance_status' => 'open',
            'venue' => 'Dewan Utama',
            'latitude' => 5.8,
            'longitude' => 102.5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->post(route('public.programs.qr_checkin.store', $programId), [
            'full_name' => 'No GPS Student',
            'identifier' => 'NO-GPS-1',
        ])->assertSessionHasErrors(['latitude', 'longitude', 'location_accuracy_m', 'location_captured_at']);

        $this->assertDatabaseMissing('program_attendances', ['identifier' => 'NO-GPS-1']);
    }

    public function test_final_report_routes_from_program_director_to_tpsa_director_and_kj_hep(): void
    {
        config(['services.gemini.key' => null, 'services.openai.key' => null, 'services.deepseek.key' => null]);
        $programId = DB::table('programs')->insertGetId([
            'created_by' => 1, 'registration_type' => 'attendance_only_activity', 'title' => 'Student Leadership & Service Program', 'paperwork_method' => 'none',
            'questionnaire_enabled' => false,
            'status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('program_attendances')->insert([
            'program_id' => $programId, 'attendee_type' => 'internal', 'full_name' => 'Valid Student',
            'identifier' => 'PB-REPORT-1', 'checked_in_at' => now(), 'geofence_valid' => true,
            'validation_status' => 'valid', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->signIn(1, 'lecturer')->post(route('admin.programs.report.generate', $programId), [
            'program_images' => [UploadedFile::fake()->createWithContent(
                'activity.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
            )],
            'output_format' => 'both',
        ])->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('generated_report', function (array $result) use ($programId): bool {
            return $result['program_title'] === 'Student Leadership & Service Program'
                && str_contains((string) $result['docx_url'], "/admin/programs/{$programId}/report/download/docx")
                && str_contains((string) $result['pdf_url'], "/admin/programs/{$programId}/report/download/pdf");
        });
        $this->assertDatabaseHas('program_reports', ['program_id' => $programId, 'status' => 'draft']);

        $docxPath = DB::table('program_reports')->where('program_id', $programId)->value('docx_path');
        $zip = new \ZipArchive();
        $this->assertTrue($zip->open(\Illuminate\Support\Facades\Storage::disk('local')->path($docxPath)) === true);
        $documentXml = $zip->getFromName('word/document.xml');
        $zip->close();
        $this->assertIsString($documentXml);
        $this->assertNotFalse(simplexml_load_string($documentXml), 'Generated DOCX document.xml must be valid XML.');
        $this->assertStringContainsString('STUDENT LEADERSHIP &amp; SERVICE PROGRAM', $documentXml);
        $this->assertStringNotContainsString('[NAMA KURSUS/PROGRAM]', $documentXml);
        $this->assertStringNotContainsString('LAMPIRAN: RINGKASAN LAPORAN DIJANA AI', $documentXml);
        $this->assertSame(5, substr_count($documentXml, '<w:tbl>'), 'The official five-table report layout must remain intact.');

        $pdfPath = DB::table('program_reports')->where('program_id', $programId)->value('pdf_path');
        $this->assertNotNull($pdfPath);
        $this->assertStringStartsWith('%PDF-', (string) file_get_contents(
            \Illuminate\Support\Facades\Storage::disk('local')->path($pdfPath),
            false,
            null,
            0,
            5
        ));

        $this->signIn(1, 'lecturer')->post(route('admin.programs.report.submit', $programId))->assertRedirect();
        $this->assertDatabaseHas('program_reports', ['status' => 'pending_tpsa', 'tpsa_reviewer_id' => 2, 'director_reviewer_id' => 3, 'kj_hep_reviewer_id' => 4]);

        $this->signIn(2, 'lecturer')->post(route('admin.programs.report.review', $programId), ['decision' => 'approve'])->assertRedirect();
        $this->assertDatabaseHas('program_reports', ['status' => 'pending_director']);
        $this->signIn(3, 'lecturer')->post(route('admin.programs.report.review', $programId), ['decision' => 'approve'])->assertRedirect();
        $this->assertDatabaseHas('program_reports', ['status' => 'pending_kj_hep']);
        $this->signIn(4, 'student_affairs_head')->post(route('admin.programs.report.review', $programId), ['decision' => 'approve'])->assertRedirect();
        $this->assertDatabaseHas('program_reports', ['status' => 'archived']);
        $this->assertDatabaseHas('programs', ['id' => $programId, 'status' => 'completed']);
    }

    public function test_report_generation_blocks_incomplete_sources(): void
    {
        $programId = DB::table('programs')->insertGetId([
            'created_by' => 1, 'registration_type' => 'approved_program', 'title' => 'Incomplete Program',
            'paperwork_method' => 'pdf', 'questionnaire_enabled' => true, 'status' => 'active',
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->signIn(1, 'lecturer')
            ->post(route('admin.programs.report.generate', $programId), ['output_format' => 'docx'])
            ->assertSessionHasErrors(['paperwork_file', 'program_images', 'attendance', 'questionnaire']);

        $this->assertDatabaseMissing('program_reports', ['program_id' => $programId]);
    }

    public function test_discipline_admin_can_rank_students_by_valid_program_participation_points(): void
    {
        $studentId = DB::table('students')->insertGetId([
            'full_name' => 'Nur Aina', 'matric_no' => 'PB1001', 'program' => 'Diploma IT',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $programId = DB::table('programs')->insertGetId([
            'created_by' => 1, 'title' => 'Leadership Camp', 'paperwork_method' => 'pdf',
            'status' => 'active', 'participation_points' => 15, 'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('program_attendances')->insert([
            'program_id' => $programId, 'student_id' => $studentId, 'attendee_type' => 'internal',
            'full_name' => 'Nur Aina', 'identifier' => 'PB1001', 'checked_in_at' => now(),
            'geofence_valid' => true, 'validation_status' => 'valid', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->signIn(5, 'discipline_admin')
            ->get(route('admin.program-participation-points.index'))
            ->assertOk()
            ->assertSee('Nur Aina')
            ->assertSee('15');
    }

    public function test_program_director_bulk_queues_certificates_by_student_matric_number(): void
    {
        Queue::fake();
        $studentId = DB::table('students')->insertGetId(['full_name'=>'Certificate Student','matric_no'=>'PB22001','program'=>'DIT','created_at'=>now(),'updated_at'=>now()]);
        $programId = DB::table('programs')->insertGetId(['created_by'=>1,'title'=>'Certificate Program','paperwork_method'=>'none','questionnaire_enabled'=>false,'status'=>'completed','created_at'=>now(),'updated_at'=>now()]);
        $attendanceId = DB::table('program_attendances')->insertGetId(['program_id'=>$programId,'student_id'=>$studentId,'attendee_type'=>'internal','full_name'=>'Certificate Student','identifier'=>'PB22001','checked_in_at'=>now(),'geofence_valid'=>true,'validation_status'=>'valid','created_at'=>now(),'updated_at'=>now()]);

        $this->signIn(1,'lecturer')->post(route('admin.programs.certificates.generate',$programId))->assertRedirect()->assertSessionHasNoErrors();
        $this->assertDatabaseHas('program_certificates',['program_id'=>$programId,'program_attendance_id'=>$attendanceId,'student_id'=>$studentId,'matric_no'=>'PB22001','status'=>'pending']);
        Queue::assertPushed(GenerateProgramCertificate::class,1);
    }

    public function test_program_director_can_generate_one_test_certificate_immediately(): void
    {
        Storage::fake('local');
        $studentId = DB::table('students')->insertGetId(['full_name'=>'Preview Student','matric_no'=>'PB22009','program'=>'DIT','created_at'=>now(),'updated_at'=>now()]);
        $programId = DB::table('programs')->insertGetId(['created_by'=>1,'title'=>'Certificate Preview Program','paperwork_method'=>'none','questionnaire_enabled'=>false,'certificate_enabled'=>true,'certificate_template'=>'standard_placeholder','status'=>'completed','created_at'=>now(),'updated_at'=>now()]);
        $attendanceId = DB::table('program_attendances')->insertGetId(['program_id'=>$programId,'student_id'=>$studentId,'attendee_type'=>'internal','full_name'=>'Preview Student','identifier'=>'PB22009','checked_in_at'=>now(),'geofence_valid'=>true,'validation_status'=>'valid','created_at'=>now(),'updated_at'=>now()]);

        $this->signIn(1,'lecturer')->post(route('admin.programs.certificates.generate-test',$programId), [
            'certificate_template' => 'standard_placeholder',
        ])->assertRedirect(route('admin.program-certificates.index', ['program_id'=>$programId,'q'=>'PB22009']))
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $certificate = DB::table('program_certificates')->where('program_id',$programId)->where('student_id',$studentId)->first();
        $this->assertNotNull($certificate);
        $this->assertSame($attendanceId, (int) $certificate->program_attendance_id);
        $this->assertSame('ready', $certificate->status);
        $this->assertNotNull($certificate->generated_at);
        Storage::disk('local')->assertExists($certificate->path);
    }

    public function test_points_only_program_does_not_queue_certificates(): void
    {
        Queue::fake();
        $studentId = DB::table('students')->insertGetId(['full_name'=>'Points Student','matric_no'=>'PB22002','program'=>'DIT','created_at'=>now(),'updated_at'=>now()]);
        $programId = DB::table('programs')->insertGetId(['created_by'=>1,'title'=>'Points Only Program','paperwork_method'=>'none','questionnaire_enabled'=>false,'certificate_enabled'=>false,'participation_points'=>10,'status'=>'completed','created_at'=>now(),'updated_at'=>now()]);
        DB::table('program_attendances')->insert(['program_id'=>$programId,'student_id'=>$studentId,'attendee_type'=>'internal','full_name'=>'Points Student','identifier'=>'PB22002','checked_in_at'=>now(),'geofence_valid'=>true,'validation_status'=>'valid','created_at'=>now(),'updated_at'=>now()]);

        $this->signIn(1,'lecturer')->post(route('admin.programs.certificates.generate',$programId), [
            'certificate_template' => 'standard_placeholder',
        ])->assertRedirect()->assertSessionHasErrors('certificates');

        $this->assertDatabaseCount('program_certificates', 0);
        Queue::assertNothingPushed();
    }

    public function test_logged_in_politeknik_besut_student_can_submit_internal_attendance_and_earn_points(): void
    {
        $studentId = DB::table('students')->insertGetId([
            'full_name' => 'Siti Student', 'matric_no' => 'PB2001', 'program' => 'Diploma IT',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $programId = DB::table('programs')->insertGetId([
            'created_by' => 1, 'title' => 'Digital Skills Program', 'paperwork_method' => 'pdf',
            'status' => 'active', 'attendance_status' => 'open', 'venue' => 'Makmal IT',
            'latitude' => 5.8, 'longitude' => 102.5, 'geofence_radius_m' => 50,
            'participation_points' => 20, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $surveyId = DB::table('program_surveys')->insertGetId([
            'program_id' => $programId, 'title' => 'Learning', 'status' => 'published', 'created_by' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $questionId = DB::table('program_survey_questions')->insertGetId([
            'program_survey_id' => $surveyId, 'question_text' => 'What did you learn?', 'question_type' => 'text',
            'is_required' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $studentSession = ['auth_user' => ['id' => $studentId, 'role' => 'student', 'name' => 'Siti Student', 'matric_no' => 'PB2001']];
        $qrToken = \App\Support\DynamicQrToken::generate($programId)['token'];
        $this->withSession($studentSession)->post(route('student.programs.attendance.store', $programId), [
            'qr_token' => $qrToken,
            'latitude' => 5.8001, 'longitude' => 102.5001, 'location_accuracy_m' => 10,
            'location_captured_at' => now()->toIso8601String(),
        ])->assertRedirect(route('student.programs.index'));

        $this->assertDatabaseHas('program_attendances', [
            'program_id' => $programId, 'student_id' => $studentId, 'attendee_type' => 'internal',
            'identifier' => 'PB2001', 'validation_status' => 'valid',
        ]);
        $this->withSession($studentSession)->get(route('student.programs.index'))
            ->assertOk()->assertSee('20')->assertSee('Digital Skills Program');
    }

    public function test_program_owner_can_use_attendance_only_mode_and_students_still_earn_points(): void
    {
        $studentId = DB::table('students')->insertGetId(['full_name' => 'Attendance Student', 'matric_no' => 'PB3001', 'program' => 'DIT', 'created_at' => now(), 'updated_at' => now()]);
        $programId = DB::table('programs')->insertGetId([
            'created_by' => 1, 'title' => 'Sports Activity', 'paperwork_method' => 'pdf', 'status' => 'active',
            'attendance_status' => 'closed', 'questionnaire_enabled' => true, 'venue' => 'Field',
            'latitude' => 5.8, 'longitude' => 102.5, 'geofence_radius_m' => 50, 'participation_points' => 5,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->signIn(1, 'lecturer')->put(route('admin.programs.questionnaire-setting.update', $programId), ['questionnaire_publish_mode' => 'closed'])->assertRedirect();
        $this->signIn(1, 'lecturer')->post(route('admin.programs.attendance.open', $programId), ['mode' => 'portal_and_qr'])->assertRedirect();
        $this->assertDatabaseHas('programs', ['id' => $programId, 'questionnaire_enabled' => false, 'attendance_status' => 'open', 'attendance_checkin_mode' => 'portal_and_qr']);

        $this->withSession(['auth_user' => ['id' => $studentId, 'role' => 'student', 'name' => 'Attendance Student']])
            ->post(route('student.programs.attendance.store', $programId), [
                'latitude' => 5.8001, 'longitude' => 102.5001, 'location_accuracy_m' => 10,
                'location_captured_at' => now()->toIso8601String(),
            ])->assertRedirect(route('student.programs.index'));
        $this->assertDatabaseHas('program_attendances', ['program_id' => $programId, 'student_id' => $studentId, 'validation_status' => 'valid']);
    }

    public function test_kj_hep_can_grant_student_page_permission_for_program(): void
    {
        $studentId = DB::table('students')->insertGetId(['full_name' => 'Camera Problem Student', 'matric_no' => 'PB4001', 'program' => 'DBF', 'created_at' => now(), 'updated_at' => now()]);
        $programId = DB::table('programs')->insertGetId([
            'created_by' => 1, 'title' => 'QR Permission Program', 'paperwork_method' => 'pdf',
            'venue' => 'Dewan Utama', 'status' => 'active', 'attendance_status' => 'open',
            'attendance_checkin_mode' => 'qr_code', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->signIn(4, 'student_affairs_head')
            ->post(route('admin.programs.student-page-permissions.store', $programId), [
                'student_id' => $studentId,
                'access_type' => 'qr_presenter',
                'note' => 'Responsible class representative',
            ])
            ->assertRedirect(route('admin.programs.operations', $programId));

        $this->assertDatabaseHas('program_student_page_permissions', [
            'program_id' => $programId,
            'student_id' => $studentId,
            'access_type' => 'qr_presenter',
            'granted_by' => 4,
        ]);
    }

    public function test_non_oversight_admin_cannot_grant_student_page_permission(): void
    {
        $studentId = DB::table('students')->insertGetId(['full_name' => 'Restricted Student', 'matric_no' => 'PB4002', 'program' => 'DBF', 'created_at' => now(), 'updated_at' => now()]);
        $programId = DB::table('programs')->insertGetId([
            'created_by' => 1, 'title' => 'Restricted Permission Program', 'paperwork_method' => 'pdf',
            'venue' => 'Dewan Utama', 'status' => 'active', 'attendance_status' => 'open',
            'attendance_checkin_mode' => 'qr_code', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->signIn(5, 'discipline_admin')
            ->post(route('admin.programs.student-page-permissions.store', $programId), [
                'student_id' => $studentId,
                'access_type' => 'qr_presenter',
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('program_student_page_permissions', [
            'program_id' => $programId,
            'student_id' => $studentId,
        ]);
    }

    public function test_permitted_student_can_open_dynamic_qr_presenter_but_not_bypass_attendance_token(): void
    {
        $studentId = DB::table('students')->insertGetId(['full_name' => 'Permitted Student', 'matric_no' => 'PB4003', 'program' => 'DBF', 'created_at' => now(), 'updated_at' => now()]);
        $programId = DB::table('programs')->insertGetId([
            'created_by' => 1, 'title' => 'QR Only Permission Program', 'paperwork_method' => 'pdf',
            'venue' => 'Dewan Utama', 'status' => 'active', 'attendance_status' => 'open',
            'attendance_checkin_mode' => 'qr_code', 'participation_points' => 5,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('program_student_page_permissions')->insert([
            'program_id' => $programId,
            'student_id' => $studentId,
            'access_type' => 'qr_presenter',
            'granted_by' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $studentSession = ['auth_user' => ['id' => $studentId, 'role' => 'student', 'name' => 'Permitted Student']];

        $this->withSession($studentSession)
            ->get(route('student.programs.attendance-qr.index'))
            ->assertOk()
            ->assertSee('QR Only Permission Program');

        $this->withSession($studentSession)
            ->get(route('student.programs.attendance-qr.presenter', $programId))
            ->assertOk()
            ->assertSee('Live Projector QR');

        $this->withSession($studentSession)
            ->get(route('student.programs.attendance-qr.live-token', $programId))
            ->assertOk()
            ->assertJsonStructure(['token', 'student_url', 'stats']);

        $this->withSession($studentSession)
            ->post(route('student.programs.attendance.store', $programId))
            ->assertSessionHasErrors('qr_token');

        $this->assertDatabaseMissing('program_attendances', [
            'program_id' => $programId,
            'student_id' => $studentId,
        ]);
    }

    private function signIn(int $id, string $role): static
    {
        return $this->withSession(['auth_user' => ['id' => $id, 'role' => 'admin', 'admin_role' => $role, 'name' => "Staff {$id}"]]);
    }
}
