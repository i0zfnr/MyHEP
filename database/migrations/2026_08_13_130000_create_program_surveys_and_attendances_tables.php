<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('program_survey_responses');
        Schema::dropIfExists('program_attendances');
        Schema::dropIfExists('program_survey_questions');
        Schema::dropIfExists('program_surveys');

        Schema::create('program_surveys', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft'); // draft, published
            $table->timestamp('published_at')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->index(['program_id', 'status'], 'ps_prog_stat_idx');
        });

        Schema::create('program_survey_questions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_survey_id');
            $table->string('question_text', 255);
            $table->string('question_type', 30)->default('rating_5'); // rating_5, multiple_choice, text
            $table->json('options')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            $table->index(['program_survey_id', 'sort_order'], 'psq_survey_sort_idx');
        });

        Schema::create('program_attendances', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('student_id')->nullable(); // Nullable for external attendees
            $table->string('attendee_type', 20)->default('internal'); // internal, external
            $table->string('full_name', 180);
            $table->string('identifier', 100); // Matric No, NRIC, or Phone for external
            $table->string('email', 180)->nullable();
            $table->string('institution_or_unit', 180)->nullable();
            $table->timestamp('checked_in_at');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('geofence_valid')->default(true);
            $table->unsignedTinyInteger('satisfaction_rating')->nullable();
            $table->text('feedback_comments')->nullable();
            $table->timestamps();

            $table->index(['program_id', 'attendee_type'], 'pa_prog_type_idx');
            $table->index(['program_id', 'checked_in_at'], 'pa_prog_checkin_idx');
        });

        Schema::create('program_survey_responses', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_survey_id');
            $table->unsignedBigInteger('program_attendance_id');
            $table->unsignedBigInteger('question_id');
            $table->text('answer_value');
            $table->timestamps();

            $table->index(['program_survey_id', 'program_attendance_id'], 'psr_survey_att_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_survey_responses');
        Schema::dropIfExists('program_attendances');
        Schema::dropIfExists('program_survey_questions');
        Schema::dropIfExists('program_surveys');
    }
};
