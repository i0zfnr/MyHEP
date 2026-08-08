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

        DB::table('students')->insert(['id' => 10, 'full_name' => 'Student']);
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

    public function test_liquid_glass_transparency_is_limited_to_safe_readable_values(): void
    {
        $this->withSession([
            'auth_user' => ['id' => 10, 'role' => 'student', 'name' => 'Student'],
        ])->from('/settings')->post('/settings', [
            'locale' => 'en',
            'theme' => 'light',
            'glass_transparency' => 5,
        ])->assertRedirect('/settings')
            ->assertSessionHasErrors('glass_transparency');
    }
}
