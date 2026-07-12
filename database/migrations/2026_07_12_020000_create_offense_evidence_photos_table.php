<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('offense_evidence_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('offense_id')->constrained('offenses')->cascadeOnDelete();
            $table->string('photo_path');
            $table->unsignedTinyInteger('sort_order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offense_evidence_photos');
    }
};
