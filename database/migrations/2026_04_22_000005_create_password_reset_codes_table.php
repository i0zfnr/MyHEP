<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('password_reset_codes')) {
            return;
        }

        Schema::create('password_reset_codes', function (Blueprint $table) {
            $table->id();
            $table->uuid('ref')->unique();
            $table->string('role', 20);
            $table->unsignedBigInteger('target_id');
            $table->string('email', 150);
            $table->string('code_hash', 255);
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['role', 'target_id', 'created_at'], 'idx_reset_codes_target');
            $table->index(['expires_at', 'used_at'], 'idx_reset_codes_expiry');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_codes');
    }
};
