<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_approval_signatures', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('admin_id')->unique();
            $table->string('signature_path');
            $table->string('signature_mime', 40);
            $table->unsignedBigInteger('uploaded_by');
            $table->timestamps();
            $table->index('uploaded_by');
        });

        Schema::table('program_reports', function (Blueprint $table): void {
            $table->string('program_director_signature_path')->nullable();
            $table->string('tpsa_signature_path')->nullable();
            $table->string('director_signature_path')->nullable();
            $table->string('kj_hep_signature_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('program_reports', function (Blueprint $table): void {
            $table->dropColumn(['program_director_signature_path', 'tpsa_signature_path', 'director_signature_path', 'kj_hep_signature_path']);
        });

        Schema::dropIfExists('staff_approval_signatures');
    }
};
