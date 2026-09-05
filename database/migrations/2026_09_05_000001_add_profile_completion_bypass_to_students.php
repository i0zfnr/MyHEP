<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            if (! Schema::hasColumn('students', 'profile_completion_bypass')) {
                $table->boolean('profile_completion_bypass')->default(false)->after('profile_photo_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            if (Schema::hasColumn('students', 'profile_completion_bypass')) {
                $table->dropColumn('profile_completion_bypass');
            }
        });
    }
};
