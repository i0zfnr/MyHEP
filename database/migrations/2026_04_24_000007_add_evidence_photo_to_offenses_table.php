<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offenses', function (Blueprint $table) {
            if (!Schema::hasColumn('offenses', 'evidence_photo_path')) {
                $table->string('evidence_photo_path')->nullable()->after('place');
            }
        });
    }

    public function down(): void
    {
        Schema::table('offenses', function (Blueprint $table) {
            if (Schema::hasColumn('offenses', 'evidence_photo_path')) {
                $table->dropColumn('evidence_photo_path');
            }
        });
    }
};

