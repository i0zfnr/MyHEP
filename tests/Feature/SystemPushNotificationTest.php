<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SystemPushNotificationTest extends TestCase
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
        Schema::create('push_subscriptions', function (Blueprint $table): void {
            $table->id();
            $table->string('user_type', 20);
            $table->unsignedBigInteger('user_id');
            $table->text('endpoint');
            $table->char('endpoint_hash', 64)->unique();
            $table->string('public_key');
            $table->string('auth_token');
            $table->string('content_encoding')->nullable();
            $table->timestamps();
        });

        DB::table('admins')->insert([
            ['id' => 1, 'full_name' => 'System Admin', 'role' => 'system_admin'],
            ['id' => 2, 'full_name' => 'Discipline Admin', 'role' => 'discipline_admin'],
        ]);
        config([
            'services.webpush.public_key' => '',
            'services.webpush.private_key' => '',
        ]);
    }

    public function test_only_system_admin_can_access_push_controls(): void
    {
        $this->signIn(2, 'discipline_admin')->get('/admin/maintenance')->assertForbidden();
        $this->signIn(2, 'discipline_admin')->post('/admin/maintenance/push/test')->assertForbidden();
        $this->signIn(2, 'discipline_admin')->post('/admin/maintenance/push/broadcast', [
            'starts_at' => now()->addHour()->format('Y-m-d H:i:s'),
        ])->assertForbidden();
    }

    public function test_page_shows_push_controls_and_subscription_counts(): void
    {
        $this->addSubscription('admin', 1, 'admin-one');
        $this->addSubscription('student', 10, 'student-ten');

        $this->signIn(1, 'system_admin')->get('/admin/maintenance')
            ->assertOk()
            ->assertSee('Push Notification Centre')
            ->assertSee('Send Test Notification')
            ->assertSee('Send Maintenance Notification')
            ->assertSee('This announcement does not enable maintenance mode automatically.');
    }

    public function test_test_push_requires_a_device_for_current_admin(): void
    {
        $this->signIn(1, 'system_admin')->post('/admin/maintenance/push/test')
            ->assertRedirect('/admin/maintenance')
            ->assertSessionHasErrors('push');

        $this->addSubscription('admin', 1, 'admin-one');

        $this->signIn(1, 'system_admin')->post('/admin/maintenance/push/test')
            ->assertRedirect('/admin/maintenance')
            ->assertSessionHas('success', 'Test notification sent to 1 registered device(s).');
    }

    public function test_system_admin_can_broadcast_a_scheduled_maintenance_notice(): void
    {
        $this->addSubscription('admin', 1, 'admin-one');
        $this->addSubscription('student', 10, 'student-ten');

        $this->signIn(1, 'system_admin')->post('/admin/maintenance/push/broadcast', [
            'starts_at' => now()->addHour()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addHours(2)->format('Y-m-d H:i:s'),
            'message' => 'Please save your work before the scheduled maintenance.',
        ])->assertRedirect('/admin/maintenance')
            ->assertSessionHas('success', 'Maintenance notification sent to 2 subscribed account(s).');
    }

    private function signIn(int $id, string $role): static
    {
        return $this->withSession(['auth_user' => [
            'id' => $id,
            'role' => 'admin',
            'admin_role' => $role,
            'name' => $role === 'system_admin' ? 'System Admin' : 'Discipline Admin',
        ]]);
    }

    private function addSubscription(string $type, int $id, string $key): void
    {
        DB::table('push_subscriptions')->insert([
            'user_type' => $type,
            'user_id' => $id,
            'endpoint' => "https://push.example.test/{$key}",
            'endpoint_hash' => hash('sha256', $key),
            'public_key' => 'test-public-key',
            'auth_token' => 'test-auth-token',
            'content_encoding' => 'aes128gcm',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
