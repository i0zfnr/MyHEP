<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_food_bank_claims', function (Blueprint $table): void {
            $table->unsignedBigInteger('student_id')->nullable()->change();
            $table->string('student_name')->nullable();
            $table->string('matric_no', 50)->nullable()->index();
            $table->unsignedSmallInteger('item_count')->nullable();
            $table->boolean('is_b40')->nullable();
        });
    }

    public function down(): void
    {
        if (DB::table('student_food_bank_claims')->whereNull('student_id')->exists()) {
            throw new RuntimeException('Public Food Bank claims must be linked or archived before reverting this migration.');
        }

        Schema::table('student_food_bank_claims', function (Blueprint $table): void {
            $table->dropColumn(['student_name', 'matric_no', 'item_count', 'is_b40']);
            $table->unsignedBigInteger('student_id')->nullable(false)->change();
        });
    }
};
