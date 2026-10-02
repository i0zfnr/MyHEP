<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('backup_settings', function (Blueprint $table): void {
            $table->id();
            $table->longText('google_drive_client_id')->nullable();
            $table->longText('google_drive_client_secret')->nullable();
            $table->longText('google_drive_refresh_token')->nullable();
            $table->longText('google_drive_folder_id')->nullable();
            $table->longText('archive_password')->nullable();
            $table->string('notification_email')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('backup_settings');
    }
};
