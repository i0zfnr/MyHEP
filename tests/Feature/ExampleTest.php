<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_guest_is_cleanly_redirected_to_login_from_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
        $response->assertStatus(302);
    }

    public function test_theme_preference_is_saved_in_the_session(): void
    {
        $response = $this->post('/theme', ['theme' => 'dark']);

        $response->assertRedirect();
        $response->assertSessionHas('theme', 'dark');
    }

    public function test_invalid_theme_preference_is_rejected(): void
    {
        $response = $this->from('/')->post('/theme', ['theme' => 'neon']);

        $response->assertRedirect('/');
        $response->assertSessionHasErrors('theme');
    }

    public function test_notification_feed_requires_authentication(): void
    {
        $response = $this->get('/notifications/feed');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_admin_can_load_notification_feed(): void
    {
        if (!Schema::hasTable('admins')) {
            Schema::create('admins', function (Blueprint $table): void {
                $table->id();
                $table->string('role');
                $table->string('full_name');
            });
        } elseif (!Schema::hasColumn('admins', 'full_name')) {
            Schema::table('admins', function (Blueprint $table): void {
                $table->string('full_name')->default('Admin');
            });
        }

        DB::table('admins')->updateOrInsert(
            ['id' => 1],
            [
                'role' => 'scholarship_admin',
                'full_name' => 'Renamed Admin',
            ]
        );

        $response = $this
            ->withSession([
                'auth_user' => [
                    'id' => 1,
                    'role' => 'admin',
                    'admin_role' => 'scholarship_admin',
                    'name' => 'Old Admin Name',
                ],
            ])
            ->getJson('/notifications/feed');

        $response
            ->assertOk()
            ->assertHeader('Cache-Control', 'no-store, private')
            ->assertJsonPath('count', 0)
            ->assertJsonPath('items.0.id', 'all-clear');

        $response->assertSessionHas('auth_user.name', 'Renamed Admin');
    }
}
