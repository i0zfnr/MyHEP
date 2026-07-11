<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bug_reports', function (Blueprint $table) {
            $table->id();
            $table->string('reporter_name', 150);
            $table->string('reporter_email', 150);
            $table->string('category', 30)->default('bug');
            $table->string('subject', 200);
            $table->string('page_url', 500)->nullable();
            $table->text('description');
            $table->string('screenshot_path', 255)->nullable();
            $table->string('status', 30)->default('new');
            $table->text('admin_notes')->nullable();
            $table->unsignedBigInteger('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('reporter_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bug_reports');
    }
};
