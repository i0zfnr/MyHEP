<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('students')->whereIn('program', ['DF007', 'DF008', 'DF009', 'DV010', 'DV011'])->update([
            'program' => DB::raw("CASE
                WHEN matric_no LIKE '%DIT%' OR program IN ('DF008', 'DF009') THEN 'DIT'
                WHEN matric_no LIKE '%DDT%' OR program = 'DF007' THEN 'DDT'
                WHEN matric_no LIKE '%DDC%' OR program = 'DV010' THEN 'DDC'
                WHEN matric_no LIKE '%DBF%' OR program = 'DV011' THEN 'DBF'
                ELSE program
            END"),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Course codes cannot be restored reliably after normalizing to a program identifier.
    }
};
