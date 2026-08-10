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

        DB::table('admins')->insert([
            'id' => 1,
            'full_name' => 'General Lecturer',
            'role' => 'lecturer',
            'staff_category' => 'general',
        ]);
    }

    public function test_every_lecturer_receives_discipline_dashboard_metrics_and_visualization(): void
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
            ->assertSee('data-dashboard-viz-toggle', false)
            ->assertSee('data-dashboard-viz', false)
            ->assertSee('class="stat-card blue"', false);
    }
}
