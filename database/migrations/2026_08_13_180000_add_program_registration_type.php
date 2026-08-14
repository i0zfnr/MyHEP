<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('programs', fn (Blueprint $table) => $table->string('registration_type', 30)->default('approved_program')->after('created_by')); }
    public function down(): void { Schema::table('programs', fn (Blueprint $table) => $table->dropColumn('registration_type')); }
};
