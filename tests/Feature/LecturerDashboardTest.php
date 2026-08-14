<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LecturerDashboardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('role');
            $table->string('staff_category')->nullable();
            $table->string('photo')->nullable();
        });
        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('matric_no')->nullable();
        });
        Schema::create('offenses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('status');
            $table->string('place')->nullable();
            $table->timestamps();
        });
        Schema::create('fine_payment_applications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('offense_id');
            $table->string('status');
            $table->date('meeting_date')->nullable();
            $table->timestamps();
        });
        Schema::create('programs', function (Blueprint $table): void {
            $table->id(); $table->unsignedBigInteger('created_by'); $table->string('title'); $table->string('status');
            $table->string('approval_branch')->nullable(); $table->unsignedBigInteger('deputy_reviewer_id')->nullable();
            $table->unsignedBigInteger('director_reviewer_id')->nullable(); $table->timestamp('director_reviewed_at')->nullable();
            $table->dateTime('starts_at')->nullable(); $table->timestamps();
        });

        DB::table('admins')->insert([
            'id' => 1,
            'full_name' => 'General Lecturer',
            'role' => 'lecturer',
            'staff_category' => 'general',
        ]);
    }

    public function test_staff_receives_program_dashboard_without_student_discipline_metrics(): void
    {
        $response = $this->withSession([
            'auth_user' => [
                'id' => 1,
                'role' => 'admin',
                'admin_role' => 'lecturer',
                'staff_category' => 'general',
                'name' => 'General Lecturer',
            ],
        ])->get('/admin/dashboard');

        $response->assertOk()
            ->assertDontSee('data-dashboard-visualization-toggle', false)
            ->assertSee('My Program Overview')
            ->assertSee('Program Status Distribution')
            ->assertSee('Program Activity')
            ->assertDontSee('Jumlah Pelajar');
    }
}
