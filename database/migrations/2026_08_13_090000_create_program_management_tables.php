<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('created_by');
            $table->string('title', 180);
            $table->string('reference_no', 80)->nullable();
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->string('venue', 180)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedSmallInteger('geofence_radius_m')->default(50);
            $table->string('target_participants', 255)->nullable();
            $table->unsignedInteger('estimated_participants')->nullable();
            $table->decimal('estimated_budget', 12, 2)->nullable();
            $table->string('paperwork_method', 20);
            $table->string('status', 32)->default('draft');
            $table->timestamps();

            $table->index(['created_by', 'updated_at']);
            $table->index(['status', 'starts_at']);
        });

        Schema::create('program_paperworks', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->unsignedInteger('version');
            $table->string('method', 20);
            $table->string('disk', 40)->nullable();
            $table->string('path')->nullable()->unique();
            $table->string('original_name')->nullable();
            $table->string('mime_type', 120)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->json('structured_snapshot')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->unique(['program_id', 'version']);
            $table->index(['program_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_paperworks');
        Schema::dropIfExists('programs');
    }
};
