<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('movement_checkpoints')) {
            Schema::create('movement_checkpoints', function (Blueprint $table) {
                $table->id();
                $table->string('name', 120);
                $table->string('code', 40)->unique();
                $table->string('qr_token', 80)->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamp('valid_from')->nullable();
                $table->timestamp('valid_until')->nullable();
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->unsignedInteger('gps_radius_meters')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('movement_types')) {
            Schema::create('movement_types', function (Blueprint $table) {
                $table->id();
                $table->string('name', 80);
                $table->string('slug', 80)->unique();
                $table->string('direction', 20)->default('checkout');
                $table->unsignedTinyInteger('default_return_days')->default(0);
                $table->boolean('requires_return')->default(true);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('movement_settings')) {
            Schema::create('movement_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key', 80)->unique();
                $table->string('value', 255)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('student_movements')) {
            Schema::create('student_movements', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('student_id');
                $table->unsignedBigInteger('movement_type_id');
                $table->unsignedBigInteger('checkpoint_id');
                $table->timestamp('checkout_at');
                $table->timestamp('expected_return_at')->nullable();
                $table->timestamp('return_at')->nullable();
                $table->string('movement_status', 20)->default('outside');
                $table->string('rule_status', 20)->default('pending');
                $table->unsignedInteger('late_minutes')->default(0);
                $table->decimal('gps_latitude', 10, 7)->nullable();
                $table->decimal('gps_longitude', 10, 7)->nullable();
                $table->string('device_info', 255)->nullable();
                $table->timestamps();

                $table->index(['student_id', 'movement_status'], 'idx_student_movements_current');
                $table->index(['checkout_at', 'return_at'], 'idx_student_movements_times');
                $table->index(['rule_status', 'return_at'], 'idx_student_movements_rules');
            });
        }

        DB::table('movement_checkpoints')->updateOrInsert(
            ['code' => 'GUARD_HOUSE_MAIN'],
            [
                'name' => 'Guard House Main',
                'qr_token' => Str::random(48),
                'is_active' => true,
                'valid_from' => now(),
                'valid_until' => now()->addDay(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        foreach ([
            ['name' => 'Day Out', 'slug' => 'day_out', 'direction' => 'checkout', 'default_return_days' => 0, 'requires_return' => true],
            ['name' => 'Return to Campus', 'slug' => 'return_to_campus', 'direction' => 'return', 'default_return_days' => 0, 'requires_return' => false],
            ['name' => 'Overnight Stay', 'slug' => 'overnight_stay', 'direction' => 'checkout', 'default_return_days' => 1, 'requires_return' => true],
            ['name' => 'Official Programme', 'slug' => 'official_programme', 'direction' => 'checkout', 'default_return_days' => 0, 'requires_return' => true],
            ['name' => 'Emergency Leave', 'slug' => 'emergency_leave', 'direction' => 'checkout', 'default_return_days' => 0, 'requires_return' => true],
        ] as $type) {
            DB::table('movement_types')->updateOrInsert(
                ['slug' => $type['slug']],
                array_merge($type, ['is_active' => true, 'updated_at' => now(), 'created_at' => now()])
            );
        }

        foreach ([
            'curfew_weekday' => '19:00',
            'curfew_weekend' => '23:00',
            'gps_validation_enabled' => '0',
            'default_qr_valid_minutes' => '1440',
        ] as $key => $value) {
            DB::table('movement_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_movements');
        Schema::dropIfExists('movement_settings');
        Schema::dropIfExists('movement_types');
        Schema::dropIfExists('movement_checkpoints');
    }
};
