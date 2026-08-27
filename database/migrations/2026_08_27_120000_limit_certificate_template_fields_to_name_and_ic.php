<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('certificate_template_fields')) {
            return;
        }

        DB::table('certificate_template_fields')
            ->where('field_key', 'matric_no')
            ->update([
                'field_key' => 'ic_no',
                'label' => 'IC Number',
                'updated_at' => now(),
            ]);

        DB::table('certificate_template_fields')
            ->whereIn('field_key', ['program_title', 'program_date', 'serial_no'])
            ->delete();
    }

    public function down(): void
    {
        if (! Schema::hasTable('certificate_template_fields')) {
            return;
        }

        DB::table('certificate_template_fields')
            ->where('field_key', 'ic_no')
            ->update([
                'field_key' => 'matric_no',
                'label' => 'Matric Number',
                'updated_at' => now(),
            ]);
    }
};
