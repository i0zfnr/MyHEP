<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProgramManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('role');
            $table->string('staff_department')->nullable();
            $table->string('reporting_branch')->nullable();
            $table->string('position')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
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
            $table->boolean('questionnaire_enabled')->default(true);
            $table->decimal('estimated_budget', 12, 2)->nullable();
            $table->string('paperwork_method');
            $table->timestamp('paperwork_approval_confirmed_at')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('deputy_reviewer_id')->nullable();
            $table->timestamp('deputy_reviewed_at')->nullable();
            $table->text('deputy_review_note')->nullable();
            $table->unsignedBigInteger('director_reviewer_id')->nullable();
            $table->timestamp('director_reviewed_at')->nullable();
            $table->text('director_review_note')->nullable();
            $table->timestamps();
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
        Schema::create('program_reviewers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('assigned_by');
            $table->timestamps();
        });
        Schema::create('program_reports', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_id')->unique();
            $table->longText('content');
            $table->string('status')->default('draft');
            $table->unsignedBigInteger('tpsa_reviewer_id')->nullable();
            $table->unsignedBigInteger('director_reviewer_id')->nullable();
            $table->unsignedBigInteger('kj_hep_reviewer_id')->nullable();
            $table->timestamps();
        });
        Schema::create('lecturer_page_access', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('page_key');
            $table->boolean('enabled')->default(false);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->unique(['admin_id', 'page_key']);
        });
        DB::table('admins')->insert([
            ['id' => 1, 'full_name' => 'Program Director', 'role' => 'lecturer', 'staff_department' => 'jtmk', 'reporting_branch' => 'tpa', 'position' => 'Lecturer', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'full_name' => 'Other Lecturer', 'role' => 'lecturer', 'staff_department' => 'jpa', 'reporting_branch' => 'tpa', 'position' => 'Lecturer', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'full_name' => 'KJ HEP', 'role' => 'student_affairs_head', 'staff_department' => null, 'reporting_branch' => null, 'position' => 'KJ HEP', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'full_name' => 'Saifuddin Bin Semail', 'role' => 'lecturer', 'staff_department' => 'pejabat_pengarah', 'reporting_branch' => 'tpa', 'position' => 'TIMBALAN PENGARAH', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'full_name' => 'Udom A/L Ewon', 'role' => 'lecturer', 'staff_department' => 'pejabat_pengarah', 'reporting_branch' => null, 'position' => 'PENGARAH', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'full_name' => 'System Admin', 'role' => 'system_admin', 'staff_department' => null, 'reporting_branch' => null, 'position' => 'System Administrator', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
        Storage::fake('local');
    }

    public function test_staff_can_register_approved_program_paperwork(): void
    {
        $this->signIn(1, 'lecturer')->post('/admin/programs', $this->payload())->assertRedirect('/admin/programs/1/operations');
        $this->assertDatabaseHas('programs', ['id' => 1, 'title' => 'Digital Leadership Camp', 'paperwork_method' => 'pdf', 'status' => 'active']);
        $this->assertDatabaseHas('program_paperworks', ['program_id' => 1, 'version' => 1, 'method' => 'pdf']);
    }

    public function test_pdf_is_private_and_creates_a_versioned_record(): void
    {
        $data = $this->payload(['paperwork_method' => 'pdf', 'paperwork_file' => UploadedFile::fake()->create('approved-paperwork.pdf', 120, 'application/pdf')]);
        $this->signIn(1, 'lecturer')->post('/admin/programs', $data)->assertRedirect('/admin/programs/1/operations');
        $paperwork = DB::table('program_paperworks')->first();
        $this->assertNotNull($paperwork->path);
        Storage::disk('local')->assertExists($paperwork->path);
        $this->signIn(2, 'lecturer')->get("/admin/programs/1/paperworks/{$paperwork->id}/download")->assertForbidden();
        $this->signIn(3, 'student_affairs_head')->get("/admin/programs/1/paperworks/{$paperwork->id}/download")->assertOk();
    }

    public function test_only_owner_or_oversight_can_edit_an_active_program(): void
    {
        $this->signIn(1, 'lecturer')->post('/admin/programs', $this->payload());
        $this->signIn(2, 'lecturer')->get('/admin/programs/1/edit')->assertForbidden();
        $this->signIn(3, 'student_affairs_head')->get('/admin/programs/1/edit')->assertOk();
        $this->signIn(1, 'lecturer')->get('/admin/programs/1/edit')->assertOk();
        $this->assertDatabaseHas('programs', ['id' => 1, 'status' => 'active']);
    }

    public function test_registration_does_not_repeat_external_paperwork_approval(): void
    {
        $this->signIn(1, 'lecturer')->post('/admin/programs', $this->payload());
        $this->assertDatabaseHas('programs', ['id' => 1, 'status' => 'active']);
        $this->assertNotNull(DB::table('programs')->where('id', 1)->value('paperwork_approval_confirmed_at'));
        $this->signIn(2, 'lecturer')->get('/admin/programs/1')->assertOk();
    }

    public function test_staff_can_create_attendance_only_activity_without_approved_paperwork(): void
    {
        $data = $this->payload([
            'registration_type' => 'attendance_only_activity',
            'reference_no' => null,
            'paperwork_method' => 'none',
            'paperwork_file' => null,
            'title' => 'Weekly Student Fitness Activity',
        ]);

        $this->signIn(1, 'lecturer')->post('/admin/programs', $data)->assertRedirect();

        $this->assertDatabaseHas('programs', [
            'title' => 'Weekly Student Fitness Activity',
            'registration_type' => 'attendance_only_activity',
            'paperwork_method' => 'none',
            'questionnaire_enabled' => false,
            'paperwork_approval_confirmed_at' => null,
        ]);
        $programId = (int) DB::table('programs')->where('title', 'Weekly Student Fitness Activity')->value('id');
        $this->assertDatabaseMissing('program_paperworks', ['program_id' => $programId]);
    }

    public function test_assigned_reviewer_sees_report_in_awaiting_my_review_queue(): void
    {
        $this->signIn(1, 'lecturer')->post('/admin/programs', $this->payload());
        DB::table('program_reports')->insert([
            'program_id' => 1,
            'content' => 'Official report draft',
            'status' => 'pending_tpsa',
            'tpsa_reviewer_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->signIn(2, 'lecturer')
            ->get('/admin/programs?scope=review')
            ->assertOk()
            ->assertSee('Digital Leadership Camp')
            ->assertSee('Awaiting My Review')
            ->assertSee('Review Report');

        $this->signIn(4, 'lecturer')
            ->get('/admin/programs?scope=review')
            ->assertOk()
            ->assertDontSee('Digital Leadership Camp');
    }

    public function test_only_system_admin_can_permanently_delete_program_and_private_paperwork(): void
    {
        $data = $this->payload(['paperwork_method' => 'pdf', 'paperwork_file' => UploadedFile::fake()->create('proposal.pdf', 80, 'application/pdf')]);
        $this->signIn(1, 'lecturer')->post('/admin/programs', $data);
        $paperwork = DB::table('program_paperworks')->first();
        Storage::disk('local')->assertExists($paperwork->path);

        $this->signIn(1, 'lecturer')->delete('/admin/programs/1')->assertForbidden();
        $this->signIn(3, 'student_affairs_head')->delete('/admin/programs/1')->assertForbidden();
        $this->signIn(6, 'system_admin')->delete('/admin/programs/1')->assertRedirect('/admin/programs');

        $this->assertDatabaseMissing('programs', ['id' => 1]);
        $this->assertDatabaseMissing('program_paperworks', ['program_id' => 1]);
        Storage::disk('local')->assertMissing($paperwork->path);
    }

    private function signIn(int $id, string $role): static
    {
        return $this->withSession(['auth_user' => ['id' => $id, 'role' => 'admin', 'admin_role' => $role, 'name' => "Staff {$id}"]]);
    }

    private function payload(array $override = []): array
    {
        return $override + [
            'registration_type' => 'approved_program', 'approval_branch' => 'tpa', 'title' => 'Digital Leadership Camp', 'reference_no' => 'HEP/PROGRAM/2026/001',
            'target_participants' => 'Polytechnic students', 'paperwork_method' => 'pdf',
            'paperwork_file' => UploadedFile::fake()->create('approved-paperwork.pdf', 120, 'application/pdf'), 'geofence_radius_m' => 50,
            'participation_points' => 10,
            'starts_at' => '2026-09-10 09:00:00', 'ends_at' => '2026-09-10 17:00:00', 'venue' => 'Dewan Utama',
            'latitude' => 5.8001, 'longitude' => 102.5001, 'objectives' => 'Develop student leadership.',
        ];
    }
}
