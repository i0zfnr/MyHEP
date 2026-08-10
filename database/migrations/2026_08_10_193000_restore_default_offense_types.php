<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('offense_types')) {
            return;
        }

        $rules = [
            ['Part IV', 'No valid driving license', false],
            ['Part IV', 'No valid road tax', false],
            ['Part IV', 'No valid vehicle sticker', false],
            ['Rule 26A', 'Immediate disciplinary action', false],
            ['Rule 25', 'No student card', false],
            ['Rule 25', 'Not wearing student card', false],
            ['Rule 25', 'Wearing another student card', false],
            ['Rule 25', 'Damaged or modified student card', false],
            ['Rule 25', 'Wearing student card in inappropriate place', true],
            ['Rule 6', 'Inappropriate dressing (tight/worn-out/etc.)', false],
            ['Rule 6', 'Improper attire (no collar/slippers/etc.)', false],
            ['Rule 6', 'Long/untidy/colored/punk hair', true],
            ['Rule 21', 'Littering', false],
            ['Rule 21', 'Vandalism', false],
            ['Rule 21', 'Not maintaining cleanliness in campus', false],
            ['Rule 22', 'Causing disturbance', false],
            ['Rule 3(j)', 'Damaging books or materials', false],
            ['Rule 3(j)', 'Not returning borrowed materials', false],
            ['Rule 3(j)', 'Late return of books', true],
            ['Rule 23', 'Using campus buildings as sleeping place (other than hostel)', false],
            ['Rule 23', 'Causing disturbance in campus buildings', false],
            ['Other', 'Violating road signs or traffic directions', false],
            ['Other', 'Parking in prohibited areas', true],
        ];

        foreach ($rules as [$reference, $description, $requiresNote]) {
            DB::table('offense_types')->updateOrInsert(
                ['rule_reference' => $reference, 'description' => $description],
                ['requires_note' => $requiresNote]
            );
        }
    }

    public function down(): void
    {
        // Preserve rule records because they may already be referenced by offenses.
    }
};
