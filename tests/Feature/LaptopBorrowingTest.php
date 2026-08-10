<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class LaptopBorrowingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('ic_no')->nullable();
            $table->string('role');
            $table->string('staff_category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('photo')->nullable();
            $table->timestamps();
        });
        Schema::create('lecturer_page_access', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('page_key');
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
        Schema::create('jhep_laptops', function (Blueprint $table): void {
            $table->id();
            $table->string('asset_code')->unique();
            $table->string('name');
            $table->uuid('qr_token')->unique();
            $table->string('serial_number')->nullable();
            $table->string('status')->default('available');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
        Schema::create('jhep_laptop_loans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('laptop_id');
            $table->unsignedBigInteger('staff_id')->nullable();
            $table->unsignedBigInteger('laptop_staff_id')->nullable();
            $table->timestamp('borrowed_at');
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
        });
        Schema::create('jhep_laptop_staff', function (Blueprint $table): void {
            $table->id();
            $table->string('nric')->unique();
            $table->string('full_name');
            $table->string('department')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('admins')->insert([
            $this->admin(1, 'System Admin', 'system_admin'),
            $this->admin(2, 'Head Student Affairs', 'student_affairs_head'),
            $this->admin(3, 'General Staff', 'lecturer', 'general'),
            $this->admin(4, 'Scholarship Staff', 'lecturer', 'scholarship'),
        ]);
        DB::table('jhep_laptops')->insert([
            'id' => 1, 'asset_code' => 'JHEP-LAPTOP-1', 'name' => 'Laptop JHEP 1',
            'qr_token' => '11111111-1111-4111-8111-111111111111', 'status' => 'available',
            'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_staff_scan_borrows_then_returns_the_same_laptop(): void
    {
        $this->signIn(3, 'general')->get('/admin/laptops/scan')
            ->assertOk()
            ->assertSee('Borrow or return a laptop')
            ->assertSee('staff-scan-page', false)
            ->assertSee('href="'.route('admin.dashboard').'"', false)
            ->assertSee('Opening camera. Point it at a JHEP laptop QR code.');

        $this->postJson('/admin/laptops/scan', ['token' => '11111111-1111-4111-8111-111111111111'])
            ->assertOk()->assertJson(['action' => 'borrowed']);
        $this->assertDatabaseHas('jhep_laptops', ['id' => 1, 'status' => 'borrowed']);
        $this->assertDatabaseHas('jhep_laptop_loans', ['laptop_id' => 1, 'staff_id' => 3, 'returned_at' => null]);

        $this->postJson('/admin/laptops/scan', ['token' => '11111111-1111-4111-8111-111111111111'])
            ->assertOk()->assertJson(['action' => 'returned']);
        $this->assertDatabaseHas('jhep_laptops', ['id' => 1, 'status' => 'available']);
        $this->assertNotNull(DB::table('jhep_laptop_loans')->value('returned_at'));
    }

    public function test_another_staff_member_cannot_return_someone_elses_laptop(): void
    {
        $this->signIn(3, 'general')->postJson('/admin/laptops/scan', ['token' => '11111111-1111-4111-8111-111111111111'])->assertOk();

        $this->signIn(4, 'scholarship')->postJson('/admin/laptops/scan', ['token' => '11111111-1111-4111-8111-111111111111'])
            ->assertStatus(409)->assertJson(['action' => 'blocked']);
    }

    public function test_only_head_and_system_admin_can_open_laptop_management(): void
    {
        $this->signIn(1, null, 'system_admin')->get('/admin/laptops')->assertOk()->assertSee('JHEP Laptop Loans');
        $this->signIn(2, null, 'student_affairs_head')->get('/admin/laptops')->assertOk();
        $this->signIn(3, 'general')->get('/admin/laptops')->assertForbidden();
    }

    public function test_every_admin_role_can_open_the_laptop_scanner_and_scan_public_qr_links(): void
    {
        DB::table('admins')->insert([
            $this->admin(5, 'Guard', 'guard'),
            $this->admin(6, 'Scholarship Admin', 'scholarship_admin'),
            $this->admin(7, 'Discipline Admin', 'discipline_admin'),
        ]);

        foreach ([3 => 'lecturer', 5 => 'guard', 6 => 'scholarship_admin', 7 => 'discipline_admin', 2 => 'student_affairs_head', 1 => 'system_admin'] as $id => $role) {
            $this->signIn($id, $role === 'lecturer' ? 'general' : null, $role)->get('/admin/laptops/scan')
                ->assertOk()
                ->assertSee('url.pathname.match', false);
        }
    }

    public function test_imported_staff_can_borrow_from_the_public_qr_page_without_logging_in(): void
    {
        DB::table('jhep_laptop_staff')->insert([
            'id' => 8, 'nric' => '900101011234', 'full_name' => 'Borrowing Staff', 'is_active' => true,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $url = '/laptop-borrow/11111111-1111-4111-8111-111111111111';

        $this->get($url)->assertOk()->assertSee('Take This Laptop')->assertDontSee('Login');
        $this->postJson($url.'/staff-check', ['nric' => '900101-01-1234'])->assertOk()->assertJson(['eligible' => true]);
        $this->postJson($url, ['nric' => '900101011234'])->assertOk()->assertJson(['message' => 'Laptop JHEP 1 has been recorded as borrowed.']);

        $this->assertDatabaseHas('jhep_laptop_loans', ['laptop_id' => 1, 'staff_id' => null, 'laptop_staff_id' => 8, 'returned_at' => null]);
        $this->assertDatabaseHas('jhep_laptops', ['id' => 1, 'status' => 'borrowed']);

        $this->postJson($url.'/staff-check', ['nric' => '900101011234'])->assertOk()->assertJson(['eligible' => true, 'action' => 'return']);
        $this->postJson($url, ['nric' => '900101011234'])->assertOk()->assertJson(['action' => 'returned']);
        $this->assertDatabaseHas('jhep_laptops', ['id' => 1, 'status' => 'available']);
    }

    public function test_active_staff_account_nric_can_borrow_from_the_public_qr_page(): void
    {
        DB::table('admins')->where('id', 3)->update(['ic_no' => '060717030254']);
        $url = '/laptop-borrow/11111111-1111-4111-8111-111111111111';

        $this->postJson($url.'/staff-check', ['nric' => '060717-03-0254'])
            ->assertOk()
            ->assertJson(['eligible' => true, 'action' => 'borrow']);

        $this->postJson($url, ['nric' => '060717030254'])
            ->assertOk()
            ->assertJson(['action' => 'borrowed']);

        $this->assertDatabaseHas('jhep_laptop_loans', [
            'laptop_id' => 1,
            'staff_id' => 3,
            'laptop_staff_id' => null,
            'returned_at' => null,
        ]);
    }

    public function test_unregistered_nric_cannot_borrow_from_the_public_qr_page(): void
    {
        $url = '/laptop-borrow/11111111-1111-4111-8111-111111111111';

        $this->postJson($url.'/staff-check', ['nric' => '900101011234'])->assertOk()->assertJson(['eligible' => false]);
        $this->postJson($url, ['nric' => '900101011234'])->assertUnprocessable();
        $this->assertDatabaseMissing('jhep_laptop_loans', ['laptop_id' => 1]);
    }

    private function signIn(int $id, ?string $category = null, string $role = 'lecturer'): static
    {
        return $this->withSession(['auth_user' => ['id' => $id, 'role' => 'admin', 'admin_role' => $role, 'staff_category' => $category, 'name' => "Account {$id}"]]);
    }

    private function admin(int $id, string $name, string $role, ?string $category = null): array
    {
        return ['id' => $id, 'full_name' => $name, 'role' => $role, 'staff_category' => $category, 'is_active' => true, 'photo' => null, 'created_at' => now(), 'updated_at' => now()];
    }
}
