<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            DB::getDriverName() !== 'mysql'
            || !Schema::hasTable('admins')
            || !Schema::hasColumn('admins', 'role')
        ) {
            return;
        }

        DB::statement(
            "ALTER TABLE admins MODIFY role ENUM('guard','scholarship_admin','discipline_admin','student_affairs_head','system_admin') NOT NULL"
        );
    }

    public function down(): void
    {
        if (
            DB::getDriverName() !== 'mysql'
            || !Schema::hasTable('admins')
            || !Schema::hasColumn('admins', 'role')
        ) {
            return;
        }

        DB::table('admins')
            ->where('role', 'student_affairs_head')
            ->update(['role' => 'system_admin']);

        DB::statement(
            "ALTER TABLE admins MODIFY role ENUM('guard','scholarship_admin','discipline_admin','system_admin') NOT NULL"
        );
    }
};
