<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('programs') && ! Schema::hasColumn('programs', 'attendance_checkin_mode')) {
            Schema::table('programs', function (Blueprint $table): void {
                $table->string('attendance_checkin_mode', 32)->default('qr_code')->after('attendance_status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('programs') && Schema::hasColumn('programs', 'attendance_checkin_mode')) {
            Schema::table('programs', function (Blueprint $table): void {
                $table->dropColumn('attendance_checkin_mode');
            });
        }
    }
};
