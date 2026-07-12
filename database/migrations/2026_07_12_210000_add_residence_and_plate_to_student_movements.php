<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'residence_status')) {
                $table->string('residence_status', 30)->default('inside_campus')->after('address');
            }

            if (!Schema::hasColumn('students', 'room_number')) {
                $table->string('room_number', 30)->nullable()->after('residence_status');
            }
        });

        Schema::table('student_movements', function (Blueprint $table) {
            if (!Schema::hasColumn('student_movements', 'vehicle_plate_no')) {
                $table->string('vehicle_plate_no', 30)->nullable()->after('movement_status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_movements', function (Blueprint $table) {
            $columns = array_values(array_filter([
                'vehicle_plate_no',
            ], fn ($column) => Schema::hasColumn('student_movements', $column)));

            if ($columns) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('students', function (Blueprint $table) {
            $columns = array_values(array_filter([
                'room_number',
                'residence_status',
            ], fn ($column) => Schema::hasColumn('students', $column)));

            if ($columns) {
                $table->dropColumn($columns);
            }
        });
    }
};
