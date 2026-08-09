<?php

namespace Tests\Feature;

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
            'id' => 1,
            'full_name' => 'System Admin',
            'role' => 'system_admin',
        ]);
    }

    public function test_system_admin_can_disable_ai_helper_and_direct_routes_are_blocked(): void
    {
        $this->actingAsSystemAdmin()
            ->patch('/admin/features/ai_helper', ['enabled' => 0])
            ->assertRedirect('/admin/features');

        $this->assertDatabaseHas('system_features', [
            'feature_key' => 'ai_helper',
            'enabled' => false,
        ]);

        $this->actingAsSystemAdmin()->get('/admin/ai-helper')
            ->assertStatus(503)
            ->assertSee('AI Helper');
        $this->actingAsSystemAdmin()->post('/admin/ai-helper', ['question' => 'Test'])
            ->assertStatus(503);
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
}
