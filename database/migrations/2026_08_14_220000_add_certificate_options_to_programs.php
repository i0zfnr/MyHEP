<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table): void {
            $table->boolean('certificate_enabled')->default(true)->after('participation_points');
            $table->string('certificate_template', 50)->default('standard_placeholder')->after('certificate_enabled');
        });

        Schema::table('program_certificates', function (Blueprint $table): void {
            $table->string('template_key', 50)->default('standard_placeholder')->after('serial_no');
        });
    }

    public function down(): void
    {
        Schema::table('program_certificates', fn (Blueprint $table) => $table->dropColumn('template_key'));
        Schema::table('programs', fn (Blueprint $table) => $table->dropColumn(['certificate_enabled', 'certificate_template']));
    }
};
