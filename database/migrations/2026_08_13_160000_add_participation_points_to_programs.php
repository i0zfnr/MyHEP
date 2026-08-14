<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table): void {
            $table->unsignedSmallInteger('participation_points')->default(0)->after('estimated_participants');
        });
    }

    public function down(): void
    {
        Schema::table('programs', function (Blueprint $table): void {
            $table->dropColumn('participation_points');
        });
    }
};
