<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\MovementController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminMovementFeedTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('matric_no');
            $table->string('photo')->nullable();
            $table->string('program');
            $table->string('residence_status')->nullable();
            $table->string('room_number')->nullable();
        });

        Schema::create('movement_types', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->boolean('is_active')->default(true);
        });

        Schema::create('movement_checkpoints', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });

        Schema::create('student_movements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->unsignedBigInteger('movement_type_id');
            $table->unsignedBigInteger('checkpoint_id');
            $table->string('movement_status');
            $table->string('rule_status');
            $table->string('vehicle_plate_no')->nullable();
            $table->text('late_explanation')->nullable();
            $table->dateTime('checkout_at');
            $table->dateTime('return_at')->nullable();
        });
    }

    public function test_movement_feed_returns_cursor_payload_for_lazy_loading(): void
    {
        $request = Request::create('/admin/movements', 'GET', [], [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);

        $response = app(MovementController::class)->index($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(
            ['data', 'next_cursor', 'has_more'],
            array_keys($response->getData(true))
        );
    }

    public function test_ajax_student_search_filters_without_returning_unrelated_movements(): void
    {
        DB::table('students')->insert([
            ['id' => 1, 'full_name' => 'Aina Rahman', 'matric_no' => 'DIT24001', 'program' => 'DIT'],
            ['id' => 2, 'full_name' => 'Farid Hassan', 'matric_no' => 'DVM24002', 'program' => 'DVM'],
        ]);
        DB::table('movement_types')->insert(['id' => 1, 'name' => 'Day Out', 'slug' => 'day_out', 'is_active' => true]);
        DB::table('movement_checkpoints')->insert(['id' => 1, 'name' => 'Guard House']);
        DB::table('student_movements')->insert([
            ['student_id' => 1, 'movement_type_id' => 1, 'checkpoint_id' => 1, 'movement_status' => 'outside', 'rule_status' => 'pending', 'checkout_at' => now(), 'return_at' => null],
            ['student_id' => 2, 'movement_type_id' => 1, 'checkpoint_id' => 1, 'movement_status' => 'returned', 'rule_status' => 'compliant', 'checkout_at' => now(), 'return_at' => now()],
        ]);

        $request = Request::create('/admin/movements?q=DIT24001', 'GET', ['q' => 'DIT24001'], [], [], [
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest',
        ]);
        $payload = app(MovementController::class)->index($request)->getData(true);

        $this->assertCount(1, $payload['data']);
        $this->assertSame('Aina Rahman', $payload['data'][0]['student_name']);
        $this->assertSame('DIT24001', $payload['data'][0]['matric_no']);
    }
}
