<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ai_generated_paperworks')) {
            Schema::create('ai_generated_paperworks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('admin_id')->index();
                $table->unsignedBigInteger('program_id')->nullable()->index();
                $table->string('title');
                $table->string('date_text')->nullable();
                $table->string('venue')->nullable();
                $table->string('organizer')->nullable();
                $table->string('target_group')->nullable();
                $table->string('participant_count')->nullable();
                $table->string('ajk_file_path')->nullable();
                $table->string('ajk_file_name')->nullable();
                $table->text('ajk_text')->nullable();
                $table->text('itinerary')->nullable();
                $table->text('financial_details')->nullable();
                $table->json('structured_content')->nullable();
                $table->string('output_format', 20)->default('docx');
                $table->string('docx_path')->nullable();
                $table->string('pdf_path')->nullable();
                $table->string('ai_provider', 40)->nullable();
                $table->string('ai_model', 60)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generated_paperworks');
    }
};
