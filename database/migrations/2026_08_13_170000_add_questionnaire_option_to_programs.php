<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('programs', fn (Blueprint $table) => $table->boolean('questionnaire_enabled')->default(true)->after('participation_points')); }
    public function down(): void { Schema::table('programs', fn (Blueprint $table) => $table->dropColumn('questionnaire_enabled')); }
};
