<?php

namespace Tests\Feature;

use App\Support\SystemFeatures;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AiHelperFeatureControlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('role');
        });
        Schema::create('system_features', function (Blueprint $table): void {
            $table->id();
            $table->string('feature_key')->unique();
            $table->boolean('enabled');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        DB::table('admins')->insert([
            ['id' => 1, 'full_name' => 'System Admin', 'role' => 'system_admin'],
            ['id' => 2, 'full_name' => 'Discipline Admin', 'role' => 'discipline_admin'],
        ]);
    }

    public function test_admin_ai_helper_control_blocks_regular_admin_but_not_system_admin(): void
    {
        $this->actingAsSystemAdmin()
            ->patch('/admin/features/admin_ai_helper', ['enabled' => 0])
            ->assertRedirect('/admin/features');

        $this->assertDatabaseHas('system_features', [
            'feature_key' => 'admin_ai_helper',
            'enabled' => false,
        ]);

        $this->actingAsSystemAdmin()->get('/admin/ai-helper')
            ->assertOk()->assertSee('AI Helper');
        $this->actingAsRegularAdmin()->get('/admin/ai-helper')->assertStatus(503);
    }

    public function test_student_ai_helper_has_an_independent_control(): void
    {
        $this->actingAsSystemAdmin()
            ->patch('/admin/features/student_ai_helper', ['enabled' => 0])
            ->assertRedirect('/admin/features');

        $this->assertDatabaseHas('system_features', [
            'feature_key' => 'student_ai_helper',
            'enabled' => false,
        ]);
    }

    public function test_system_admin_can_disable_liquid_design_for_other_administrators(): void
    {
        $this->actingAsSystemAdmin()
            ->patch('/admin/features/admin_liquid_design', ['enabled' => 0])
            ->assertRedirect('/admin/features');

        $this->assertDatabaseHas('system_features', [
            'feature_key' => 'admin_liquid_design',
            'enabled' => false,
        ]);

        $features = app(SystemFeatures::class);
        $this->assertFalse($features->adminLiquidDesignEnabled('discipline_admin'));
        $this->assertFalse($features->adminLiquidDesignEnabled('scholarship_admin'));
        $this->assertFalse($features->adminLiquidDesignEnabled('student_affairs_head'));
        $this->assertTrue($features->adminLiquidDesignEnabled('system_admin'));
    }

    private function actingAsSystemAdmin(): static
    {
        return $this->withSession(['auth_user' => [
            'id' => 1,
            'role' => 'admin',
            'admin_role' => 'system_admin',
            'name' => 'System Admin',
        ]]);
    }

    private function actingAsRegularAdmin(): static
    {
        return $this->withSession(['auth_user' => [
            'id' => 2,
            'role' => 'admin',
            'admin_role' => 'discipline_admin',
            'name' => 'Discipline Admin',
        ]]);
    }
}
