<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StaffAndGuardManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('ic_no')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
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
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->unique(['admin_id', 'page_key']);
        });

        DB::table('admins')->insert([
            $this->account(1, 'System Admin', 'system_admin'),
            $this->account(2, 'Head Student Affairs', 'student_affairs_head'),
            $this->account(3, 'Discipline Admin', 'discipline_admin'),
            $this->account(4, 'Cik Lan', 'lecturer', 'discipline'),
            $this->account(5, 'Scholarship Lecturer', 'lecturer', 'scholarship'),
        ]);
        DB::table('lecturer_page_access')->insert([
            'admin_id' => 4, 'page_key' => 'guard_management', 'enabled' => true,
            'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_head_and_system_admin_can_manage_staff_but_only_system_admin_can_manage_admins(): void
    {
        $this->signIn(2, 'student_affairs_head')->get('/admin/staff')->assertOk()->assertSee('Staff Management');
        $this->signIn(1, 'system_admin')->get('/admin/staff')->assertOk();
        $this->signIn(2, 'student_affairs_head')->get('/admin/admin-users')->assertForbidden();
        $this->signIn(1, 'system_admin')->get('/admin/admin-users')->assertOk();
    }

    public function test_head_can_create_discipline_staff_with_selected_page_access(): void
    {
        $response = $this->signIn(2, 'student_affairs_head')->post('/admin/staff', [
            'full_name' => 'New Discipline Lecturer', 'ic_no' => '900101015555',
            'email' => 'discipline@example.test', 'staff_category' => 'discipline',
            'is_active' => '1', 'password' => 'SecurePass123',
            'lecturer_pages' => ['offense_register', 'guard_management'],
        ]);

        $response->assertRedirect('/admin/staff');
        $id = (int) DB::table('admins')->where('ic_no', '900101015555')->value('id');
        $this->assertSame('lecturer', DB::table('admins')->where('id', $id)->value('role'));
        $this->assertSame('discipline', DB::table('admins')->where('id', $id)->value('staff_category'));
        $this->assertDatabaseHas('lecturer_page_access', ['admin_id' => $id, 'page_key' => 'guard_management', 'enabled' => 1]);
    }

    public function test_guard_management_is_available_to_authorized_operational_roles_only(): void
    {
        $this->signIn(1, 'system_admin')->get('/admin/guards')->assertOk();
        $this->signIn(2, 'student_affairs_head')->get('/admin/guards')->assertOk();
        $this->signIn(3, 'discipline_admin')->get('/admin/guards')->assertOk();
        $this->signIn(4, 'lecturer', 'discipline')->get('/admin/guards')->assertOk();
        $this->signIn(5, 'lecturer', 'scholarship')->get('/admin/guards')->assertForbidden();
    }

    public function test_discipline_lecturer_can_create_guard_when_individually_authorized(): void
    {
        $this->signIn(4, 'lecturer', 'discipline')->post('/admin/guards', [
            'full_name' => 'Night Guard', 'ic_no' => '880101015555', 'email' => null,
            'is_active' => '1', 'password' => 'GuardPass123',
        ])->assertRedirect('/admin/guards');

        $this->assertDatabaseHas('admins', ['ic_no' => '880101015555', 'role' => 'guard', 'is_active' => 1]);
    }

    public function test_inactive_staff_cannot_login_with_shared_admin_login(): void
    {
        DB::table('admins')->where('id', 4)->update(['is_active' => false]);

        $this->post('/login', ['role' => 'admin', 'username' => 'IC4', 'password' => 'Password123'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username');
        $this->assertFalse(session()->has('auth_user'));
    }

    public function test_lecturer_mobile_shell_contains_staff_bottom_navigation(): void
    {
        $this->signIn(4, 'lecturer', 'discipline')->get('/admin/guards')
            ->assertOk()
            ->assertSee('aria-label="Staff mobile navigation"', false)
            ->assertSee('Scan QR')
            ->assertSee('student-bottom-nav-eligible', false);
    }

    private function signIn(int $id, string $role, ?string $category = null): static
    {
        return $this->withSession(['auth_user' => ['id' => $id, 'role' => 'admin', 'admin_role' => $role, 'staff_category' => $category, 'name' => "Account {$id}"]]);
    }

    private function account(int $id, string $name, string $role, ?string $category = null): array
    {
        return ['id' => $id, 'full_name' => $name, 'ic_no' => "IC{$id}", 'email' => "user{$id}@example.test",
            'password' => Hash::make('Password123'), 'role' => $role, 'staff_category' => $category,
            'is_active' => true, 'photo' => null, 'created_at' => now(), 'updated_at' => now()];
    }
}
