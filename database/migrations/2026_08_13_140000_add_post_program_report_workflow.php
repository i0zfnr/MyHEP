<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table): void {
            $table->timestamp('paperwork_approval_confirmed_at')->nullable()->after('paperwork_method');
        });

        Schema::create('program_reports', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('program_id')->unique();
            $table->longText('content');
            $table->string('status', 32)->default('draft');
            $table->string('ai_provider', 40)->nullable();
            $table->string('ai_model', 120)->nullable();
            $table->unsignedBigInteger('generated_by');
            $table->timestamp('generated_at');
            $table->unsignedBigInteger('tpsa_reviewer_id')->nullable();
            $table->timestamp('tpsa_reviewed_at')->nullable();
            $table->text('tpsa_review_note')->nullable();
            $table->unsignedBigInteger('director_reviewer_id')->nullable();
            $table->timestamp('director_reviewed_at')->nullable();
            $table->text('director_review_note')->nullable();
            $table->unsignedBigInteger('kj_hep_reviewer_id')->nullable();
            $table->timestamp('kj_hep_reviewed_at')->nullable();
            $table->text('kj_hep_review_note')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'updated_at']);
        });

        DB::table('programs')->whereIn('status', ['draft', 'pending_deputy', 'pending_director', 'approved'])
            ->update([
                'status' => 'active',
                'paperwork_approval_confirmed_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('program_reports');
        Schema::table('programs', function (Blueprint $table): void {
            $table->dropColumn('paperwork_approval_confirmed_at');
        });
    }
};
