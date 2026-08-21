<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_scholarship_status_forms', function (Blueprint $table) {
            if (!Schema::hasColumn('student_scholarship_status_forms', 'application_type')) {
                $table->string('application_type', 30)->default('scholarship')->after('student_id');
            }
            if (!Schema::hasColumn('student_scholarship_status_forms', 'welfare_category')) {
                $table->string('welfare_category', 100)->nullable()->after('monthly_amount');
            }
            if (!Schema::hasColumn('student_scholarship_status_forms', 'welfare_description')) {
                $table->text('welfare_description')->nullable()->after('welfare_category');
            }
            if (!Schema::hasColumn('student_scholarship_status_forms', 'welfare_amount')) {
                $table->decimal('welfare_amount', 10, 2)->nullable()->after('welfare_description');
            }
            if (!Schema::hasColumn('student_scholarship_status_forms', 'guardian_name')) {
                $table->string('guardian_name', 150)->nullable()->after('welfare_amount');
            }
            if (!Schema::hasColumn('student_scholarship_status_forms', 'guardian_ic_no')) {
                $table->string('guardian_ic_no', 30)->nullable()->after('guardian_name');
            }
            if (!Schema::hasColumn('student_scholarship_status_forms', 'guardian_relationship')) {
                $table->string('guardian_relationship', 60)->nullable()->after('guardian_ic_no');
            }
            if (!Schema::hasColumn('student_scholarship_status_forms', 'guardian_phone')) {
                $table->string('guardian_phone', 30)->nullable()->after('guardian_relationship');
            }
            if (!Schema::hasColumn('student_scholarship_status_forms', 'guardian_occupation')) {
                $table->string('guardian_occupation', 150)->nullable()->after('guardian_phone');
            }
            if (!Schema::hasColumn('student_scholarship_status_forms', 'family_income')) {
                $table->decimal('family_income', 10, 2)->nullable()->after('guardian_occupation');
            }
            if (!Schema::hasColumn('student_scholarship_status_forms', 'dependents_count')) {
                $table->integer('dependents_count')->nullable()->after('family_income');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_scholarship_status_forms', function (Blueprint $table) {
            $columns = [
                'application_type',
                'welfare_category',
                'welfare_description',
                'welfare_amount',
                'guardian_name',
                'guardian_ic_no',
                'guardian_relationship',
                'guardian_phone',
                'guardian_occupation',
                'family_income',
                'dependents_count',
            ];
            foreach ($columns as $column) {
                if (Schema::hasColumn('student_scholarship_status_forms', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
