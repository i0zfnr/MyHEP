<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FoodBankTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('matric_no')->unique();
            $table->string('ic_no')->nullable();
            $table->string('program')->nullable();
            $table->integer('semester')->nullable();
            $table->string('academic_session')->nullable();
            $table->string('phone')->nullable();
            $table->string('photo')->nullable();
            $table->timestamps();
        });

        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('role');
            $table->string('photo')->nullable();
            $table->timestamps();
        });

        Schema::create('student_food_bank_claims', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('student_id')->index();
            $table->timestamp('claimed_at');
            $table->string('academic_session', 50)->nullable();
            $table->unsignedTinyInteger('semester')->nullable();
            $table->string('meal_type', 60)->default('makanan_percuma');
            $table->string('notes', 255)->nullable();
            $table->string('location', 150)->default('Food Bank Siswa Politeknik Besut');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        DB::table('students')->insert([
            [
                'id' => 1,
                'full_name' => 'Ahmad Pelajar',
                'matric_no' => 'PB22001',
                'ic_no' => '020202115544',
                'program' => 'DIT',
                'semester' => 3,
                'academic_session' => '2025/2026',
                'phone' => '0123456789',
                'photo' => 'students/ahmad.jpg',
            ],
            [
                'id' => 2,
                'full_name' => 'Siti Nurhaliza',
                'matric_no' => 'PB22002',
                'ic_no' => '020303116655',
                'program' => 'DDT',
                'semester' => 4,
                'academic_session' => '2025/2026',
                'phone' => '0198765432',
                'photo' => 'students/siti.jpg',
            ],
        ]);

        DB::table('admins')->insert([
            ['id' => 1, 'full_name' => 'Scholarship Admin', 'role' => 'scholarship_admin'],
            ['id' => 2, 'full_name' => 'Discipline Admin', 'role' => 'discipline_admin'],
            ['id' => 3, 'full_name' => 'System Admin', 'role' => 'system_admin'],
        ]);
    }

    public function test_scholarship_admin_can_view_food_bank_dashboard(): void
    {
        $this->actingAsAdmin(1, 'scholarship_admin')
            ->get('/admin/foodbank')
            ->assertOk()
            ->assertSee('Food Bank');
    }

    public function test_non_scholarship_admin_cannot_access_food_bank(): void
    {
        $this->actingAsAdmin(2, 'discipline_admin')
            ->get('/admin/foodbank')
            ->assertForbidden();
    }

    public function test_scholarship_admin_can_view_printable_qr_poster(): void
    {
        $this->actingAsAdmin(1, 'scholarship_admin')
            ->get('/admin/foodbank/qr')
            ->assertOk()
            ->assertSee('Food Bank');
    }

    public function test_scholarship_admin_can_export_hq_report(): void
    {
        DB::table('student_food_bank_claims')->insert([
            'student_id' => 1,
            'location' => 'HEP Food Bank',
            'claimed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAsAdmin(1, 'scholarship_admin')
            ->get('/admin/foodbank/export');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('Ahmad Pelajar', $response->streamedContent());
    }

    public function test_student_can_view_food_bank_hub(): void
    {
        $this->actingAsStudent(1)
            ->get('/student/foodbank')
            ->assertOk()
            ->assertSee('Food Bank');
    }

    public function test_student_can_claim_food_aid_via_qr_landing_page(): void
    {
        $response = $this->actingAsStudent(1)
            ->get('/student/foodbank/claim');

        $response->assertOk()
            ->assertSee('PB22001')
            ->assertSee('DIT');

        $this->assertDatabaseHas('student_food_bank_claims', [
            'student_id' => 1,
            'location' => 'Food Bank Siswa Politeknik Besut',
        ]);
    }

    public function test_student_quick_scan_api_records_claim_and_throttles_duplicates(): void
    {
        // First scan succeeds
        $firstResponse = $this->actingAsStudent(1)
            ->postJson('/student/foodbank/quick-scan');

        $firstResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('already_claimed', false);

        // Immediate subsequent scan notifies recent claim
        $secondResponse = $this->actingAsStudent(1)
            ->postJson('/student/foodbank/quick-scan');

        $secondResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('already_claimed', true);
    }

    private function actingAsAdmin(int $id, string $adminRole): static
    {
        return $this->withSession(['auth_user' => [
            'id' => $id,
            'role' => 'admin',
            'admin_role' => $adminRole,
            'name' => 'Admin ' . $adminRole,
        ]]);
    }

    private function actingAsStudent(int $id): static
    {
        return $this->withSession(['auth_user' => [
            'id' => $id,
            'role' => 'student',
            'name' => 'Test Student ' . $id,
        ]]);
    }
}
