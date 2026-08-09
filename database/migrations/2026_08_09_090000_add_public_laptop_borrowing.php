<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jhep_laptop_staff', function (Blueprint $table): void {
            $table->id();
            $table->string('nric', 20)->unique();
            $table->string('full_name', 150);
            $table->string('department', 150)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('jhep_laptop_loans', function (Blueprint $table): void {
            $table->unsignedBigInteger('laptop_staff_id')->nullable()->after('staff_id')->index();
            $table->unsignedBigInteger('staff_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('jhep_laptop_loans', function (Blueprint $table): void {
            $table->dropColumn('laptop_staff_id');
        });

        Schema::dropIfExists('jhep_laptop_staff');
    }
};
