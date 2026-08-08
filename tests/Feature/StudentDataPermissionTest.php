<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StudentDataPermissionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('role');
            $table->string('photo')->nullable();
        });

        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('matric_no')->nullable();
            $table->string('ic_no');
            $table->string('program');
            $table->string('phone')->nullable();
            $table->string('password')->nullable();
            $table->timestamps();
        });

        Schema::create('offense_types', function (Blueprint $table): void {
            $table->id();
            $table->string('rule_reference');
            $table->string('description');
            $table->boolean('requires_note')->default(false);
        });

        DB::table('admins')->insert([
            ['id' => 1, 'full_name' => 'Campus Guard', 'role' => 'guard'],
            ['id' => 2, 'full_name' => 'Scholarship Officer', 'role' => 'scholarship_admin'],
            ['id' => 3, 'full_name' => 'Discipline Officer', 'role' => 'discipline_admin'],
        ]);
        DB::table('students')->insert([
            'id' => 10,
            'full_name' => 'Protected Student',
            'matric_no' => 'PB22001',
            'ic_no' => '800101010101',
            'program' => 'Information Technology',
            'phone' => '0199999999',
            'password' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_guard_can_use_basic_directory_without_receiving_sensitive_fields_or_actions(): void
    {
        $response = $this->signIn(1, 'guard', 'Campus Guard')->get('/admin/students');

        $response->assertOk()
            ->assertSee('Protected Student')
            ->assertSee('PB22001')
            ->assertSee('Information Technology')
            ->assertDontSee('0199999999')
            ->assertDontSee('********0101')
            ->assertDontSee('Export CSV')
            ->assertDontSee('View Profile')
            ->assertDontSee('Default IC');
    }

    public function test_guard_is_denied_student_profile_export_and_management_routes(): void
    {
        $this->signIn(1, 'guard', 'Campus Guard');

        $this->get('/admin/students/10')->assertForbidden();
        $this->get('/admin/students/export')->assertForbidden();
        $this->get('/admin/students/create')->assertForbidden();
    }

    public function test_scholarship_admin_keeps_basic_lookup_but_not_generic_sensitive_access(): void
    {
        $this->signIn(2, 'scholarship_admin', 'Scholarship Officer');

        $this->get('/admin/students')->assertOk();
        $this->get('/admin/students/search?q=PB22001')
            ->assertOk()
            ->assertJsonPath('data.0.matric_no', 'PB22001');
        $this->get('/admin/students/10')->assertForbidden();
        $this->get('/admin/students/export')->assertForbidden();
    }

    public function test_offense_registration_uses_ajax_student_picker_without_manual_dropdown(): void
    {
        $response = $this->signIn(3, 'discipline_admin', 'Discipline Officer')
            ->get('/admin/offenses/create');

        $response->assertOk()
            ->assertSee('id="student_search"', false)
            ->assertSee('type="hidden" name="student_id"', false)
            ->assertSee('id="student_search_results"', false)
            ->assertDontSee('<select name="student_id"', false)
            ->assertDontSee('Protected Student');
    }

    private function signIn(int $id, string $role, string $name): static
    {
        return $this->withSession([
            'auth_user' => [
                'id' => $id,
                'role' => 'admin',
                'admin_role' => $role,
                'name' => $name,
            ],
        ]);
    }
}
