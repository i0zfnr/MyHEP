<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            $table->string('staff_department', 80)->nullable()->after('staff_category');
            $table->string('position', 180)->nullable()->after('staff_department');
            $table->index(['role', 'staff_department', 'is_active'], 'admins_staff_department_index');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            $table->dropIndex('admins_staff_department_index');
            $table->dropColumn(['staff_department', 'position']);
        });
    }
};
