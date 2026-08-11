<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_movements', function (Blueprint $table) {
            // Supports the profile query: latest movements for one student.
            $table->index(['student_id', 'checkout_at'], 'idx_movements_student_checkout');
        });
    }

    public function down(): void
    {
        Schema::table('student_movements', function (Blueprint $table) {
            $table->dropIndex('idx_movements_student_checkout');
        });
    }
};
