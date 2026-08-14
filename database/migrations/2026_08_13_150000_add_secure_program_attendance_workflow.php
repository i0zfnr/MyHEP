<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('programs', function (Blueprint $table): void {
            $table->string('attendance_status', 20)->default('closed')->after('status');
            $table->timestamp('attendance_opened_at')->nullable()->after('attendance_status');
            $table->timestamp('attendance_closed_at')->nullable()->after('attendance_opened_at');
        });

        Schema::table('program_attendances', function (Blueprint $table): void {
            $table->string('validation_status', 30)->default('invalid_location')->after('geofence_valid');
            $table->decimal('distance_m', 10, 2)->nullable()->after('validation_status');
            $table->decimal('location_accuracy_m', 10, 2)->nullable()->after('distance_m');
            $table->timestamp('location_captured_at')->nullable()->after('location_accuracy_m');
            $table->unique(['program_id', 'attendee_type', 'identifier'], 'pa_program_attendee_identifier_unique');
        });
    }

    public function down(): void
    {
        Schema::table('program_attendances', function (Blueprint $table): void {
            $table->dropUnique('pa_program_attendee_identifier_unique');
            $table->dropColumn(['validation_status', 'distance_m', 'location_accuracy_m', 'location_captured_at']);
        });

        Schema::table('programs', function (Blueprint $table): void {
            $table->dropColumn(['attendance_status', 'attendance_opened_at', 'attendance_closed_at']);
        });
    }
};
