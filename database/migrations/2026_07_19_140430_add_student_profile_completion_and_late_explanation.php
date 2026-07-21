<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'photo')) {
                $table->string('photo')->nullable()->after('study_address');
            }
        });

        Schema::table('student_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('student_movements', 'late_explanation')) {
                $table->text('late_explanation')->nullable()->after('late_minutes');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_movements', function (Blueprint $table) {
            if (Schema::hasColumn('student_movements', 'late_explanation')) {
                $table->dropColumn('late_explanation');
            }
        });

        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'photo')) {
                $table->dropColumn('photo');
            }
        });
    }
};
