<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            if (!Schema::hasColumn('students', 'profile_photo_status')) {
                $table->string('profile_photo_status', 24)->nullable()->after('photo');
            }

            if (!Schema::hasColumn('students', 'profile_photo_reviewed_at')) {
                $table->timestamp('profile_photo_reviewed_at')->nullable()->after('profile_photo_status');
            }

            if (!Schema::hasColumn('students', 'profile_photo_reviewed_by')) {
                $table->unsignedBigInteger('profile_photo_reviewed_by')->nullable()->after('profile_photo_reviewed_at');
            }
        });

        if (Schema::hasColumn('students', 'photo') && Schema::hasColumn('students', 'profile_photo_status')) {
            DB::table('students')
                ->whereNotNull('photo')
                ->where('photo', '!=', '')
                ->whereNull('profile_photo_status')
                ->update(['profile_photo_status' => 'legacy']);
        }
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            foreach (['profile_photo_reviewed_by', 'profile_photo_reviewed_at', 'profile_photo_status'] as $column) {
                if (Schema::hasColumn('students', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
