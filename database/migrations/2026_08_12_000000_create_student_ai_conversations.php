<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_ai_conversations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('student_id')->index();
            $table->string('title', 120);
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();
            $table->index(['student_id', 'last_message_at']);
        });

        Schema::create('student_ai_messages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('conversation_id')->index();
            $table->string('role', 16);
            $table->text('content');
            $table->string('provider', 40)->nullable();
            $table->string('model', 120)->nullable();
            $table->timestamps();
            $table->foreign('conversation_id')->references('id')->on('student_ai_conversations')->cascadeOnDelete();
            $table->index(['conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_ai_messages');
        Schema::dropIfExists('student_ai_conversations');
    }
};
