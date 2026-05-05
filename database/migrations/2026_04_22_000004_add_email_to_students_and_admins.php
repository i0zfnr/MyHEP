<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'email')) {
                $table->string('email', 150)->nullable()->unique()->after('password');
            }
        });

        Schema::table('admins', function (Blueprint $table) {
            if (!Schema::hasColumn('admins', 'email')) {
                $table->string('email', 150)->nullable()->unique()->after('password');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'email')) {
                $table->dropUnique('students_email_unique');
                $table->dropColumn('email');
            }
        });

        Schema::table('admins', function (Blueprint $table) {
            if (Schema::hasColumn('admins', 'email')) {
                $table->dropUnique('admins_email_unique');
                $table->dropColumn('email');
            }
        });
    }
};
