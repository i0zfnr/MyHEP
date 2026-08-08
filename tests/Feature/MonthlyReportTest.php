<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MonthlyReportTest extends TestCase
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
        Schema::create('offenses', function (Blueprint $table): void {
            $table->id();
            $table->string('status');
            $table->timestamps();
        });
        foreach (['fine_payment_applications', 'vehicle_sticker_applications'] as $tableName) {
            Schema::create($tableName, function (Blueprint $table): void {
                $table->id();
                $table->string('status');
                $table->timestamps();
            });
        }
        Schema::create('scholarships', function (Blueprint $table): void {
            $table->id();
            $table->string('status');
            $table->decimal('amount', 10, 2)->nullable();
            $table->timestamps();
        });
        Schema::create('scholarship_announcements', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
        });

        \Illuminate\Support\Facades\DB::table('admins')->insert([
            'id' => 1,
            'full_name' => 'System Admin',
            'role' => 'system_admin',
        ]);
    }

    public function test_zero_activity_report_uses_compact_empty_states(): void
    {
        $response = $this->withSession([
            'auth_user' => [
                'id' => 1,
                'role' => 'admin',
                'admin_role' => 'system_admin',
                'name' => 'System Admin',
            ],
        ])->get('/admin/reports/monthly?month=2026-08');

        $response->assertOk()
            ->assertSee('No discipline activity in this period')
            ->assertSee('No fine applications this month')
            ->assertSee('No scholarship activity in this period')
            ->assertSee('No scholarship records this month')
            ->assertSee('href="#disciplineReport"', false)
            ->assertSee('href="#scholarshipReport"', false);
    }
}
