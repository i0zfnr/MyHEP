<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table): void {
            if (! Schema::hasColumn('certificate_templates', 'page_width_mm')) {
                $table->decimal('page_width_mm', 8, 2)->default(297)->after('source_page');
            }

            if (! Schema::hasColumn('certificate_templates', 'page_height_mm')) {
                $table->decimal('page_height_mm', 8, 2)->default(210)->after('page_width_mm');
            }
        });
    }

    public function down(): void
    {
        Schema::table('certificate_templates', function (Blueprint $table): void {
            if (Schema::hasColumn('certificate_templates', 'page_height_mm')) {
                $table->dropColumn('page_height_mm');
            }

            if (Schema::hasColumn('certificate_templates', 'page_width_mm')) {
                $table->dropColumn('page_width_mm');
            }
        });
    }
};
