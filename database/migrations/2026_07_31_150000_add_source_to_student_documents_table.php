<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_documents', function (Blueprint $table): void {
            $table->string('source_type', 40)->nullable()->after('student_id');
            $table->unsignedBigInteger('source_id')->nullable()->after('source_type');
            $table->index(['source_type', 'source_id'], 'student_documents_source_index');
        });
    }

    public function down(): void
    {
        Schema::table('student_documents', function (Blueprint $table): void {
            $table->dropIndex('student_documents_source_index');
            $table->dropColumn(['source_type', 'source_id']);
        });
    }
};
