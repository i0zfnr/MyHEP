<?php

namespace Tests\Feature;

use App\Support\AccountSessionManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\TestCase;

class ActiveSessionManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('ic_no');
            $table->string('role');
            $table->string('photo')->nullable();
            $table->timestamps();
        });

        Schema::create('account_sessions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('session_id')->unique();
            $table->string('owner_type', 20);
            $table->unsignedBigInteger('owner_id');
            $table->string('active_role', 20);
            $table->unsignedBigInteger('active_account_id');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('authenticated_at');
            $table->timestamp('last_seen_at');
            $table->timestamps();
        });

        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('ic_no');
            $table->timestamps();
        });

        DB::table('admins')->insert([
            ['id' => 1, 'full_name' => 'Discipline Admin', 'ic_no' => '800101010101', 'role' => 'discipline_admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'full_name' => 'Other Admin', 'ic_no' => '810101010101', 'role' => 'discipline_admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'full_name' => 'Dual Role Admin', 'ic_no' => '820101010101', 'role' => 'system_admin', 'created_at' => now(), 'updated_at' => now()],
        ]);
        DB::table('students')->insert([
            'id' => 10,
            'full_name' => 'Dual Role Student',
            'ic_no' => '820101010101',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_settings_lists_only_the_current_accounts_sessions_without_exposing_session_ids(): void
    {
        $this->signIn()->get('/settings')->assertOk();
        $current = DB::table('account_sessions')->where('owner_id', 1)->first();
        $otherPublicId = (string) Str::uuid();
        $this->insertSession($otherPublicId, 'other-secret-session', 1, 'Mozilla/5.0 Chrome/126.0');
        $foreignPublicId = (string) Str::uuid();
        $this->insertSession($foreignPublicId, 'foreign-secret-session', 2, 'Mozilla/5.0 Firefox/127.0');

        $response = $this->get('/settings');
        $response->assertOk();
        $response->assertSee(__('ui.current_device'));
        $response->assertSee($otherPublicId);
        $response->assertDontSee($foreignPublicId);
        $response->assertDontSee($current->session_id);
        $response->assertDontSee('other-secret-session');
    }

    public function test_another_session_can_be_revoked_without_ending_the_current_session(): void
    {
        $this->signIn()->get('/settings')->assertOk();
        $otherPublicId = (string) Str::uuid();
        $this->insertSession($otherPublicId, 'other-session', 1);

        $this->delete("/settings/sessions/{$otherPublicId}")
            ->assertRedirect('/settings')
            ->assertSessionHas('success', __('ui.session_revoked'));

        $this->assertDatabaseMissing('account_sessions', ['public_id' => $otherPublicId]);
        $this->assertDatabaseCount('account_sessions', 1);
        $this->get('/settings')->assertOk();
    }

    public function test_account_cannot_revoke_another_accounts_session(): void
    {
        $this->signIn()->get('/settings')->assertOk();
        $foreignPublicId = (string) Str::uuid();
        $this->insertSession($foreignPublicId, 'foreign-session', 2);

        $this->delete("/settings/sessions/{$foreignPublicId}")->assertNotFound();
        $this->assertDatabaseHas('account_sessions', ['public_id' => $foreignPublicId]);
    }

    public function test_all_other_sessions_can_be_revoked_together(): void
    {
        $this->signIn()->get('/settings')->assertOk();
        $this->insertSession((string) Str::uuid(), 'second-session', 1);
        $this->insertSession((string) Str::uuid(), 'third-session', 1);

        $this->delete('/settings/sessions')
            ->assertRedirect('/settings')
            ->assertSessionHas('success', __('ui.other_sessions_revoked'));

        $this->assertDatabaseCount('account_sessions', 1);
    }

    public function test_a_revoked_tracked_session_is_rejected_on_its_next_request(): void
    {
        $this->signIn()->get('/settings')->assertOk();
        DB::table('account_sessions')->delete();

        $this->get('/settings')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('session')
            ->assertSessionMissing('auth_user');
    }

    public function test_role_switch_destroys_the_old_session_and_preserves_its_admin_owner(): void
    {
        $this->withSession([
            'auth_user' => [
                'id' => 3,
                'role' => 'admin',
                'admin_role' => 'system_admin',
                'name' => 'Dual Role Admin',
            ],
        ])->get('/settings')->assertOk();
        $before = DB::table('account_sessions')->where('owner_id', 3)->first();

        $this->post('/settings/role-mode', ['mode' => 'student', 'override' => 0])
            ->assertRedirect('/student/dashboard')
            ->assertSessionHas('auth_user.role', 'student')
            ->assertSessionHas('session_owner.type', 'admin')
            ->assertSessionHas('session_owner.id', 3);

        $after = DB::table('account_sessions')->where('public_id', $before->public_id)->first();
        $this->assertNotSame($before->session_id, $after->session_id);
        $this->assertSame('admin', $after->owner_type);
        $this->assertSame(3, (int) $after->owner_id);
        $this->assertSame('student', $after->active_role);
        $this->assertSame(10, (int) $after->active_account_id);
    }

    public function test_system_admin_can_override_general_jhep_staff_and_return_to_admin_mode(): void
    {
        $this->withSession([
            'auth_user' => [
                'id' => 3,
                'role' => 'admin',
                'admin_role' => 'system_admin',
                'name' => 'Dual Role Admin',
            ],
        ])->get('/settings')->assertOk()->assertSee('General JHEP Staff');

        $this->post('/settings/role-mode', ['mode' => 'general_staff'])
            ->assertRedirect('/admin/dashboard')
            ->assertSessionHas('auth_user.admin_role', 'lecturer')
            ->assertSessionHas('auth_user.staff_category', 'general')
            ->assertSessionHas('auth_user.staff_override', true)
            ->assertSessionHas('session_owner.type', 'admin')
            ->assertSessionHas('session_owner.id', 3);

        $this->get('/settings')->assertOk()->assertSee(__('ui.admin_mode'));

        $this->post('/settings/role-mode', ['mode' => 'admin'])
            ->assertRedirect('/admin/dashboard')
            ->assertSessionHas('auth_user.admin_role', 'system_admin')
            ->assertSessionMissing('auth_user.staff_override');
    }

    public function test_password_reset_session_cleanup_does_not_affect_another_account(): void
    {
        $this->insertSession((string) Str::uuid(), 'first-session', 1);
        $this->insertSession((string) Str::uuid(), 'second-session', 1);
        $foreignPublicId = (string) Str::uuid();
        $this->insertSession($foreignPublicId, 'foreign-session', 2);

        $revoked = app(AccountSessionManager::class)->revokeAccount('admin', 1);

        $this->assertSame(2, $revoked);
        $this->assertDatabaseCount('account_sessions', 1);
        $this->assertDatabaseHas('account_sessions', ['public_id' => $foreignPublicId]);
    }

    private function signIn(): static
    {
        return $this->withSession([
            'auth_user' => [
                'id' => 1,
                'role' => 'admin',
                'admin_role' => 'discipline_admin',
                'name' => 'Discipline Admin',
            ],
        ]);
    }

    private function insertSession(string $publicId, string $sessionId, int $ownerId, string $userAgent = 'Mozilla/5.0 Safari/605.1.15'): void
    {
        DB::table('account_sessions')->insert([
            'public_id' => $publicId,
            'session_id' => $sessionId,
            'owner_type' => 'admin',
            'owner_id' => $ownerId,
            'active_role' => 'admin',
            'active_account_id' => $ownerId,
            'ip_address' => '127.0.0.1',
            'user_agent' => $userAgent,
            'authenticated_at' => now()->subHour(),
            'last_seen_at' => now()->subMinute(),
            'created_at' => now()->subHour(),
            'updated_at' => now()->subMinute(),
        ]);
    }
}
