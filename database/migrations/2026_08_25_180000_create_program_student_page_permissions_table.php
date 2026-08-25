<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('program_student_page_permissions')) {
            return;
        }

        Schema::create('program_student_page_permissions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('student_id');
            $table->string('access_type', 40);
            $table->unsignedBigInteger('granted_by')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['program_id', 'student_id', 'access_type'], 'program_student_page_permissions_unique');
            $table->index(['student_id', 'access_type', 'expires_at'], 'program_student_page_permissions_lookup');

            $table->foreign('program_id')->references('id')->on('programs')->cascadeOnDelete();
            $table->foreign('student_id')->references('id')->on('students')->cascadeOnDelete();
            $table->foreign('granted_by')->references('id')->on('admins')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_student_page_permissions');
    }
};
