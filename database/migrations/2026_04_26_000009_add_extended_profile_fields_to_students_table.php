<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'semester')) {
                $table->string('semester', 20)->nullable();
            }
            if (!Schema::hasColumn('students', 'academic_session')) {
                $table->string('academic_session', 30)->nullable();
            }
            if (!Schema::hasColumn('students', 'religion')) {
                $table->string('religion', 50)->nullable();
            }
            if (!Schema::hasColumn('students', 'parliament')) {
                $table->string('parliament', 120)->nullable();
            }
            if (!Schema::hasColumn('students', 'dun')) {
                $table->string('dun', 120)->nullable();
            }
            if (!Schema::hasColumn('students', 'race')) {
                $table->string('race', 80)->nullable();
            }
            if (!Schema::hasColumn('students', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable();
            }
            if (!Schema::hasColumn('students', 'guardian_name')) {
                $table->string('guardian_name', 150)->nullable();
            }
            if (!Schema::hasColumn('students', 'guardian_ic_no')) {
                $table->string('guardian_ic_no', 20)->nullable();
            }
            if (!Schema::hasColumn('students', 'guardian_address')) {
                $table->text('guardian_address')->nullable();
            }
            if (!Schema::hasColumn('students', 'guardian_phone')) {
                $table->string('guardian_phone', 20)->nullable();
            }
            if (!Schema::hasColumn('students', 'mother_ic_no')) {
                $table->string('mother_ic_no', 20)->nullable();
            }
            if (!Schema::hasColumn('students', 'guardian_occupation')) {
                $table->string('guardian_occupation', 120)->nullable();
            }
            if (!Schema::hasColumn('students', 'family_income')) {
                $table->decimal('family_income', 12, 2)->nullable();
            }
            if (!Schema::hasColumn('students', 'study_address')) {
                $table->text('study_address')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $columns = [
                'semester',
                'academic_session',
                'religion',
                'parliament',
                'dun',
                'race',
                'date_of_birth',
                'guardian_name',
                'guardian_ic_no',
                'guardian_address',
                'guardian_phone',
                'mother_ic_no',
                'guardian_occupation',
                'family_income',
                'study_address',
            ];

            $existing = array_values(array_filter($columns, fn ($column) => Schema::hasColumn('students', $column)));
            if (!empty($existing)) {
                $table->dropColumn($existing);
            }
        });
    }
};
