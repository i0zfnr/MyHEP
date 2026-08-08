<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            $table->string('staff_category', 40)->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('staff_category');
            $table->index(['role', 'staff_category', 'is_active'], 'admins_staff_management_index');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            $table->dropIndex('admins_staff_management_index');
            $table->dropColumn(['staff_category', 'is_active']);
        });
    }
};
