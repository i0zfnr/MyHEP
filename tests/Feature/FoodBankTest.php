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
            $table->decimal('family_income', 10, 2)->nullable();
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
            $table->unsignedBigInteger('student_id')->nullable()->index();
            $table->string('student_name')->nullable();
            $table->string('matric_no', 50)->nullable();
            $table->unsignedSmallInteger('item_count')->nullable();
            $table->boolean('is_b40')->nullable();
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
        $this->assertStringContainsString('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', $response->headers->get('content-type'));
        $this->assertSame('xlsx', pathinfo($response->headers->get('content-disposition'), PATHINFO_EXTENSION));
        $this->assertSame('PK', substr($response->streamedContent(), 0, 2));
    }

    public function test_student_can_view_food_bank_hub(): void
    {
        $this->actingAsStudent(1)
            ->get('/student/foodbank')
            ->assertOk()
            ->assertSee('Food Bank');
    }

    public function test_public_qr_form_records_claim_only_after_submission(): void
    {
        $response = $this->get('/student/foodbank/claim');

        $response->assertOk()
            ->assertSee('Borang Penerimaan Food Bank');
        $this->assertDatabaseCount('student_food_bank_claims', 0);

        $this->post('/student/foodbank/claim', [
            'student_name' => 'Ahmad Pelajar',
            'matric_no' => 'PB22001',
            'item_count' => 3,
            'is_b40' => '1',
        ])->assertRedirect('/student/foodbank/claim');

        $this->assertDatabaseHas('student_food_bank_claims', [
            'student_id' => 1,
            'student_name' => 'Ahmad Pelajar',
            'matric_no' => 'PB22001',
            'item_count' => 3,
            'is_b40' => 1,
            'location' => 'Food Bank Siswa Politeknik Besut',
        ]);
    }

    public function test_public_form_rejects_invalid_and_immediate_duplicate_claims(): void
    {
        $this->post('/student/foodbank/claim', [
            'student_name' => 'Siti Nurhaliza',
            'matric_no' => 'PB22002',
            'item_count' => 0,
            'is_b40' => '2',
        ])->assertSessionHasErrors(['item_count', 'is_b40']);

        $claim = ['student_name' => 'Siti Nurhaliza', 'matric_no' => 'PB22002', 'item_count' => 2, 'is_b40' => '0'];
        $this->post('/student/foodbank/claim', $claim)->assertRedirect('/student/foodbank/claim');
        $this->post('/student/foodbank/claim', $claim)->assertSessionHasErrors('matric_no');
        $this->assertDatabaseCount('student_food_bank_claims', 1);
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
