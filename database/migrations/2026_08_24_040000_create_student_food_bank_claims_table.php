<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_food_bank_claims', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id')->index();
            $table->timestamp('claimed_at')->index();
            $table->string('academic_session', 50)->nullable();
            $table->unsignedTinyInteger('semester')->nullable();
            $table->string('meal_type', 60)->default('general_food_pack');
            $table->string('notes', 255)->nullable();
            $table->string('location', 150)->default('Food Bank Siswa Politeknik Besut');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('student_id')
                ->references('id')
                ->on('students')
                ->onDelete('cascade');

            $table->index(['student_id', 'claimed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_food_bank_claims');
    }
};
