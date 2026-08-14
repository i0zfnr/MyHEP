<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('program_reviewers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_id');
            $table->unsignedBigInteger('admin_id');
            $table->unsignedBigInteger('assigned_by');
            $table->timestamps();

            $table->unique(['program_id', 'admin_id']);
            $table->index(['admin_id', 'program_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_reviewers');
    }
};
