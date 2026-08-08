<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jhep_laptops', function (Blueprint $table): void {
            $table->id();
            $table->string('asset_code', 40)->unique();
            $table->string('name', 100);
            $table->uuid('qr_token')->unique();
            $table->string('serial_number', 100)->nullable();
            $table->string('status', 20)->default('available')->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('jhep_laptop_loans', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('laptop_id')->index();
            $table->unsignedBigInteger('staff_id')->index();
            $table->timestamp('borrowed_at')->index();
            $table->timestamp('returned_at')->nullable()->index();
            $table->timestamps();
            $table->index(['laptop_id', 'returned_at']);
        });

        DB::table('jhep_laptops')->insert(collect(range(1, 4))->map(fn (int $number): array => [
            'asset_code' => "JHEP-LAPTOP-{$number}",
            'name' => "Laptop JHEP {$number}",
            'qr_token' => (string) Str::uuid(),
            'status' => 'available',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ])->all());
    }

    public function down(): void
    {
        Schema::dropIfExists('jhep_laptop_loans');
        Schema::dropIfExists('jhep_laptops');
    }
};
