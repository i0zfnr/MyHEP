<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AppearanceSettingsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
        });

        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('role');
        });

        DB::table('students')->insert(['id' => 10, 'full_name' => 'Student']);
        DB::table('admins')->insert([
            ['id' => 1, 'full_name' => 'System Admin', 'role' => 'system_admin'],
            ['id' => 2, 'full_name' => 'Discipline Admin', 'role' => 'discipline_admin'],
        ]);
    }

    public function test_student_can_save_liquid_glass_transparency(): void
    {
        $this->withSession([
            'auth_user' => ['id' => 10, 'role' => 'student', 'name' => 'Student'],
        ])->post('/settings', [
            'locale' => 'en',
            'theme' => 'dark',
            'glass_transparency' => 55,
        ])->assertRedirect('/settings')
            ->assertSessionHas('glass_transparency', 55)
            ->assertSessionHas('theme', 'dark');
    }

    public function test_preferences_can_save_immediately_with_a_json_request(): void
    {
        $this->withSession([
            'auth_user' => ['id' => 10, 'role' => 'student', 'name' => 'Student'],
        ])->postJson('/settings', [
            'locale' => 'ms',
            'theme' => 'dark',
            'glass_transparency' => 55,
        ])->assertOk()
            ->assertJson([
                'locale' => 'ms',
                'theme' => 'dark',
                'glass_transparency' => 55,
            ])
            ->assertSessionHas('locale', 'ms')
            ->assertSessionHas('theme', 'dark')
            ->assertSessionHas('glass_transparency', 55);
    }

    public function test_student_can_save_a_beta_accent_theme(): void
    {
        $this->withSession([
            'auth_user' => ['id' => 10, 'role' => 'student', 'name' => 'Student'],
        ])->postJson('/settings', [
            'locale' => 'en',
            'theme' => 'light',
            'accent_theme' => 'pink',
            'glass_transparency' => 40,
        ])->assertOk()
            ->assertJson(['accent_theme' => 'pink'])
            ->assertSessionHas('accent_theme', 'pink');
    }

    public function test_student_can_save_the_red_accent_theme(): void
    {
        $this->withSession([
            'auth_user' => ['id' => 10, 'role' => 'student', 'name' => 'Student'],
        ])->postJson('/settings', [
            'locale' => 'en',
            'theme' => 'dark',
            'accent_theme' => 'red',
            'glass_transparency' => 40,
        ])->assertOk()
            ->assertJson(['accent_theme' => 'red'])
            ->assertSessionHas('accent_theme', 'red');
    }

    public function test_liquid_glass_transparency_is_limited_to_safe_readable_values(): void
    {
        $this->withSession([
            'auth_user' => ['id' => 10, 'role' => 'student', 'name' => 'Student'],
        ])->from('/settings')->post('/settings', [
            'locale' => 'en',
            'theme' => 'light',
            'glass_transparency' => -1,
        ])->assertRedirect('/settings')
            ->assertSessionHasErrors('glass_transparency');

        $this->withSession([
            'auth_user' => ['id' => 10, 'role' => 'student', 'name' => 'Student'],
        ])->from('/settings')->post('/settings', [
            'locale' => 'en',
            'theme' => 'light',
            'glass_transparency' => 101,
        ])->assertRedirect('/settings')
            ->assertSessionHasErrors('glass_transparency');

        $this->withSession([
            'auth_user' => ['id' => 10, 'role' => 'student', 'name' => 'Student'],
        ])->post('/settings', [
            'locale' => 'en',
            'theme' => 'light',
            'glass_transparency' => 0,
        ])->assertRedirect('/settings')
            ->assertSessionHas('glass_transparency', 0);

        $this->withSession([
            'auth_user' => ['id' => 10, 'role' => 'student', 'name' => 'Student'],
        ])->post('/settings', [
            'locale' => 'en',
            'theme' => 'light',
            'glass_transparency' => 100,
        ])->assertRedirect('/settings')
            ->assertSessionHas('glass_transparency', 100);
    }

    public function test_system_admin_can_save_liquid_glass_transparency(): void
    {
        $this->withSession([
            'auth_user' => [
                'id' => 1,
                'role' => 'admin',
                'admin_role' => 'system_admin',
                'name' => 'System Admin',
            ],
        ])->post('/settings', [
            'locale' => 'en',
            'theme' => 'dark',
            'accent_theme' => 'violet',
            'glass_transparency' => 80,
        ])->assertRedirect('/settings')
            ->assertSessionHas('glass_transparency', 80)
            ->assertSessionHas('accent_theme', 'violet');
    }

    public function test_regular_admin_can_turn_liquid_design_on_and_off_when_available(): void
    {
        $session = [
            'auth_user' => [
                'id' => 2,
                'role' => 'admin',
                'admin_role' => 'discipline_admin',
                'name' => 'Discipline Admin',
            ],
        ];

        $this->withSession($session)->postJson('/settings', [
            'locale' => 'en',
            'theme' => 'light',
            'liquid_design_enabled' => true,
        ])->assertOk()
            ->assertJson(['liquid_design_enabled' => true])
            ->assertSessionHas('liquid_design_enabled', true);

        $this->withSession(array_merge($session, ['liquid_design_enabled' => true]))
            ->postJson('/settings', [
                'locale' => 'en',
                'theme' => 'light',
                'liquid_design_enabled' => false,
            ])->assertOk()
            ->assertJson(['liquid_design_enabled' => false])
            ->assertSessionHas('liquid_design_enabled', false);
    }

    public function test_regular_admin_settings_page_shows_the_switch_and_uses_the_saved_choice(): void
    {
        $authUser = [
            'id' => 2,
            'role' => 'admin',
            'admin_role' => 'discipline_admin',
            'name' => 'Discipline Admin',
        ];

        $this->withSession(['auth_user' => $authUser])
            ->get('/settings')
            ->assertOk()
            ->assertSee('data-liquid-design-toggle', false)
            ->assertSee('data-liquid-design="off"', false)
            ->assertSee('liquid-design-disabled', false);

        $this->withSession([
            'auth_user' => $authUser,
            'liquid_design_enabled' => true,
        ])->get('/settings')
            ->assertOk()
            ->assertSee('data-liquid-design-toggle', false)
            ->assertSee('data-liquid-design="on"', false)
            ->assertSee('liquid-design-enabled', false);
    }

    public function test_regular_admin_cannot_enable_liquid_design_when_system_admin_disables_it(): void
    {
        Schema::create('system_features', function (Blueprint $table): void {
            $table->id();
            $table->string('feature_key')->unique();
            $table->boolean('enabled');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        DB::table('system_features')->insert([
            'feature_key' => 'admin_liquid_design',
            'enabled' => false,
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->withSession([
            'auth_user' => [
                'id' => 2,
                'role' => 'admin',
                'admin_role' => 'discipline_admin',
                'name' => 'Discipline Admin',
            ],
        ])->postJson('/settings', [
            'locale' => 'en',
            'theme' => 'light',
            'liquid_design_enabled' => true,
        ])->assertForbidden();
    }

    public function test_other_admin_roles_cannot_change_beta_visual_settings(): void
    {
        $this->withSession([
            'auth_user' => [
                'id' => 2,
                'role' => 'admin',
                'admin_role' => 'discipline_admin',
                'name' => 'Discipline Admin',
            ],
        ])->post('/settings', [
            'locale' => 'en',
            'theme' => 'dark',
            'accent_theme' => 'violet',
        ])->assertForbidden();
    }
}
