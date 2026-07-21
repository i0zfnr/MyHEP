<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('students') || !Schema::hasColumn('students', 'matric_no')) {
            return;
        }

        $driver = DB::connection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE students MODIFY matric_no VARCHAR(20) NULL');
            if (Schema::hasTable('scholarships')) {
                DB::statement("
                    UPDATE students
                    INNER JOIN scholarships ON scholarships.student_id = students.id
                    SET students.matric_no = NULL
                    WHERE scholarships.provider_name = 'SCHOLARSHIP B40 TVET'
                    AND (
                        students.matric_no LIKE 'B40%'
                        OR students.matric_no REGEXP '^23(DIT|DBF|DDC)[0-9]{5}$'
                    )
                ");
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('students') || !Schema::hasColumn('students', 'matric_no')) {
            return;
        }

        $driver = DB::connection()->getDriverName();
        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE students MODIFY matric_no VARCHAR(20) NOT NULL');
        }
    }
};
