<?php

use App\Support\ProgramApprovalRouting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table): void {
            $table->string('reporting_branch', 10)->nullable()->after('staff_department')->index();
        });
        Schema::table('programs', function (Blueprint $table): void {
            $table->string('approval_branch', 10)->nullable()->after('created_by');
            $table->unsignedBigInteger('deputy_reviewer_id')->nullable()->after('status');
            $table->timestamp('deputy_reviewed_at')->nullable()->after('deputy_reviewer_id');
            $table->text('deputy_review_note')->nullable()->after('deputy_reviewed_at');
            $table->unsignedBigInteger('director_reviewer_id')->nullable()->after('deputy_review_note');
            $table->timestamp('director_reviewed_at')->nullable()->after('director_reviewer_id');
            $table->text('director_review_note')->nullable()->after('director_reviewed_at');
            $table->index(['approval_branch', 'status']);
        });

        DB::table('admins')->orderBy('id')->get(['id', 'staff_department', 'position'])->each(function ($staff): void {
            $branch = ProgramApprovalRouting::inferBranch($staff->staff_department, $staff->position);
            if ($branch) {
                DB::table('admins')->where('id', $staff->id)->update(['reporting_branch' => $branch]);
            }
        });
        Schema::dropIfExists('program_reviewers');
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table): void {
            $table->dropIndex(['approval_branch', 'status']);
            $table->dropColumn(['approval_branch', 'deputy_reviewer_id', 'deputy_reviewed_at', 'deputy_review_note', 'director_reviewer_id', 'director_reviewed_at', 'director_review_note']);
        });
        Schema::table('admins', function (Blueprint $table): void {
            $table->dropIndex(['reporting_branch']);
            $table->dropColumn('reporting_branch');
        });
    }
};
