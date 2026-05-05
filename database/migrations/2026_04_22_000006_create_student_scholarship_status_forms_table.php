<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('student_scholarship_status_forms')) {
            return;
        }

        Schema::create('student_scholarship_status_forms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->unique();
            $table->string('has_scholarship', 10);
            $table->string('sponsor_name', 150)->nullable();
            $table->decimal('monthly_amount', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->index(['has_scholarship', 'submitted_at'], 'idx_student_scholarship_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_scholarship_status_forms');
    }
};
