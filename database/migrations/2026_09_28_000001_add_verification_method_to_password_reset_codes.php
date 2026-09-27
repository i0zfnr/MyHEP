<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('password_reset_codes') && ! Schema::hasColumn('password_reset_codes', 'verification_method')) {
            Schema::table('password_reset_codes', function (Blueprint $table): void {
                $table->string('verification_method', 30)->nullable()->after('code_hash');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('password_reset_codes') && Schema::hasColumn('password_reset_codes', 'verification_method')) {
            Schema::table('password_reset_codes', function (Blueprint $table): void {
                $table->dropColumn('verification_method');
            });
        }
    }
};
