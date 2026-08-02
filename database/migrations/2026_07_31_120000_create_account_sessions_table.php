<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_sessions', function (Blueprint $table): void {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('session_id')->unique();
            $table->string('owner_type', 20);
            $table->unsignedBigInteger('owner_id');
            $table->string('active_role', 20);
            $table->unsignedBigInteger('active_account_id');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('authenticated_at');
            $table->timestamp('last_seen_at');
            $table->timestamps();

            $table->index(['owner_type', 'owner_id', 'last_seen_at']);
            $table->index('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_sessions');
    }
};
