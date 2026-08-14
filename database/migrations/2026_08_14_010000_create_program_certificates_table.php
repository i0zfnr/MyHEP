<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_certificates', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('program_attendance_id');
            $table->unsignedBigInteger('student_id');
            $table->string('matric_no', 80)->index();
            $table->string('student_name', 180);
            $table->string('serial_no', 100)->unique();
            $table->string('status', 24)->default('pending')->index();
            $table->string('disk', 30)->default('local');
            $table->string('path')->nullable();
            $table->text('failure_reason')->nullable();
            $table->unsignedBigInteger('generated_by');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
            $table->unique(['program_id', 'student_id']);
            $table->index(['program_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_certificates');
    }
};
