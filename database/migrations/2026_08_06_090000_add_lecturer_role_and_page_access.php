<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE admins MODIFY role ENUM('guard','lecturer','scholarship_admin','discipline_admin','student_affairs_head','system_admin') NOT NULL");

        Schema::create('lecturer_page_access', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('page_key', 80);
            $table->boolean('enabled')->default(true);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->unique(['admin_id', 'page_key']);
            $table->index(['page_key', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lecturer_page_access');

        DB::table('admins')->where('role', 'lecturer')->update(['role' => 'discipline_admin']);
        DB::statement("ALTER TABLE admins MODIFY role ENUM('guard','scholarship_admin','discipline_admin','student_affairs_head','system_admin') NOT NULL");
    }
};
