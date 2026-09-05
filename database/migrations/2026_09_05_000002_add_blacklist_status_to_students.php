<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            if (! Schema::hasColumn('students', 'is_blacklisted')) {
                $table->boolean('is_blacklisted')->default(false)->after('profile_completion_bypass');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            if (Schema::hasColumn('students', 'is_blacklisted')) {
                $table->dropColumn('is_blacklisted');
            }
        });
    }
};
