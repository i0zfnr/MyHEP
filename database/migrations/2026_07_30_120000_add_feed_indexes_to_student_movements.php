<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('student_movements', function (Blueprint $table) {
            $table->index(['checkout_at', 'id'], 'idx_movements_feed');
            $table->index(['movement_status', 'checkout_at', 'id'], 'idx_movements_status_feed');
            $table->index(['movement_type_id', 'checkout_at', 'id'], 'idx_movements_type_feed');
            $table->index('return_at', 'idx_movements_return_at');
        });
    }

    public function down(): void
    {
        Schema::table('student_movements', function (Blueprint $table) {
            $table->dropIndex('idx_movements_feed');
            $table->dropIndex('idx_movements_status_feed');
            $table->dropIndex('idx_movements_type_feed');
            $table->dropIndex('idx_movements_return_at');
        });
    }
};
