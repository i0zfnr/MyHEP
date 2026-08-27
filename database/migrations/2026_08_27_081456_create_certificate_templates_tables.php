<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificate_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 150)->unique();
            $table->string('disk', 30)->default('local');
            $table->string('file_path');
            $table->string('original_filename');
            $table->unsignedInteger('page_count')->default(1);
            $table->unsignedInteger('source_page')->default(1);
            $table->decimal('page_width_mm', 8, 2)->default(297);
            $table->decimal('page_height_mm', 8, 2)->default(210);
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });

        Schema::create('certificate_template_fields', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('certificate_template_id')->index();
            $table->string('field_key', 60);
            $table->string('label', 120);
            $table->unsignedInteger('page_number')->default(1);
            $table->decimal('x_mm', 8, 2);
            $table->decimal('y_mm', 8, 2);
            $table->decimal('width_mm', 8, 2)->default(160);
            $table->decimal('height_mm', 8, 2)->default(10);
            $table->unsignedInteger('font_size')->default(18);
            $table->string('font_weight', 20)->default('regular');
            $table->string('text_color', 20)->default('#1f1a16');
            $table->string('alignment', 10)->default('C');
            $table->boolean('cover_background')->default(false);
            $table->string('cover_color', 20)->nullable();
            $table->timestamps();
            $table->unique(['certificate_template_id', 'field_key'], 'cert_tpl_fields_template_field_unique');
        });

        Schema::table('programs', function (Blueprint $table): void {
            $table->unsignedBigInteger('certificate_template_id')->nullable()->after('certificate_template')->index();
        });

        Schema::table('program_certificates', function (Blueprint $table): void {
            $table->unsignedBigInteger('certificate_template_id')->nullable()->after('template_key')->index();
        });
    }

    public function down(): void
    {
        Schema::table('program_certificates', function (Blueprint $table): void {
            $table->dropColumn('certificate_template_id');
        });

        Schema::table('programs', function (Blueprint $table): void {
            $table->dropColumn('certificate_template_id');
        });

        Schema::dropIfExists('certificate_template_fields');
        Schema::dropIfExists('certificate_templates');
    }
};
