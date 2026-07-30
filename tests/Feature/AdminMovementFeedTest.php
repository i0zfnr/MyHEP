<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\MovementController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Schema\Blueprint;
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
}
