<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_sticker_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('vehicle_sticker_applications', 'license_card_path')) {
                $table->string('license_card_path')->nullable()->after('vehicle_type');
            }
            if (!Schema::hasColumn('vehicle_sticker_applications', 'parent_permission_path')) {
                $table->string('parent_permission_path')->nullable()->after('license_card_path');
            }
            if (!Schema::hasColumn('vehicle_sticker_applications', 'vehicle_photo_path')) {
                $table->string('vehicle_photo_path')->nullable()->after('parent_permission_path');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_sticker_applications', function (Blueprint $table) {
            $drops = [];
            if (Schema::hasColumn('vehicle_sticker_applications', 'license_card_path')) {
                $drops[] = 'license_card_path';
            }
            if (Schema::hasColumn('vehicle_sticker_applications', 'parent_permission_path')) {
                $drops[] = 'parent_permission_path';
            }
            if (Schema::hasColumn('vehicle_sticker_applications', 'vehicle_photo_path')) {
                $drops[] = 'vehicle_photo_path';
            }
            if (!empty($drops)) {
                $table->dropColumn($drops);
            }
        });
    }
};

