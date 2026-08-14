<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('program_reports', function (Blueprint $table): void {
            $table->string('output_format', 12)->nullable()->after('content');
            $table->string('docx_path')->nullable()->after('output_format');
            $table->string('pdf_path')->nullable()->after('docx_path');
        });
    }

    public function down(): void
    {
        Schema::table('program_reports', function (Blueprint $table): void {
            $table->dropColumn(['output_format', 'docx_path', 'pdf_path']);
        });
    }
};
