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
            'accent_theme' => 'candy_blue',
            'glass_transparency' => 40,
        ])->assertOk()
            ->assertJson(['accent_theme' => 'candy_blue'])
            ->assertSessionHas('accent_theme', 'candy_blue');
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
