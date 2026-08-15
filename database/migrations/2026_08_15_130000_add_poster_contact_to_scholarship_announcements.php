<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scholarship_announcements', function (Blueprint $table) {
            $table->string('poster_image', 500)->nullable()->after('link_label');
            $table->string('contact_email', 200)->nullable()->after('poster_image');
            $table->string('contact_phone', 30)->nullable()->after('contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('scholarship_announcements', function (Blueprint $table) {
            $table->dropColumn(['poster_image', 'contact_email', 'contact_phone']);
        });
    }
};
