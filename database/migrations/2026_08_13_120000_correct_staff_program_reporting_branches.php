<?php

use App\Support\ProgramApprovalRouting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('lecturer_page_access')->where('page_key', 'program_document_review')->delete();
        DB::table('admins')->orderBy('id')->get(['id', 'staff_department', 'position'])->each(function ($staff): void {
            DB::table('admins')->where('id', $staff->id)->update([
                'reporting_branch' => ProgramApprovalRouting::inferBranch($staff->staff_department, $staff->position),
            ]);
        });
    }

    public function down(): void
    {
        // Organization assignments are current operational data and are not safely reversible.
    }
};
