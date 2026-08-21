<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('programs') && ! Schema::hasColumn('programs', 'questionnaire_publish_mode')) {
            Schema::table('programs', function (Blueprint $table): void {
                $table->string('questionnaire_publish_mode', 32)->default('internal_system')->after('questionnaire_enabled');
            });
        }

        if (Schema::hasTable('program_surveys') && ! Schema::hasColumn('program_surveys', 'publish_mode')) {
            Schema::table('program_surveys', function (Blueprint $table): void {
                $table->string('publish_mode', 32)->default('internal_system')->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('programs') && Schema::hasColumn('programs', 'questionnaire_publish_mode')) {
            Schema::table('programs', function (Blueprint $table): void {
                $table->dropColumn('questionnaire_publish_mode');
            });
        }

        if (Schema::hasTable('program_surveys') && Schema::hasColumn('program_surveys', 'publish_mode')) {
            Schema::table('program_surveys', function (Blueprint $table): void {
                $table->dropColumn('publish_mode');
            });
        }
    }
};
