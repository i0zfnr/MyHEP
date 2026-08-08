<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bug_reports', function (Blueprint $table): void {
            $table->string('email_notification_status', 20)->default('pending')->after('status');
            $table->text('email_notification_error')->nullable()->after('email_notification_status');
            $table->timestamp('email_notification_attempted_at')->nullable()->after('email_notification_error');
        });
    }

    public function down(): void
    {
        Schema::table('bug_reports', function (Blueprint $table): void {
            $table->dropColumn([
                'email_notification_status',
                'email_notification_error',
                'email_notification_attempted_at',
            ]);
        });
    }
};
