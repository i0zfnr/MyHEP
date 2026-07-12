<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fine_payment_applications', function (Blueprint $table) {
            if (!Schema::hasColumn('fine_payment_applications', 'receipt_path')) {
                $table->string('receipt_path')->nullable()->after('student_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('fine_payment_applications', function (Blueprint $table) {
            if (Schema::hasColumn('fine_payment_applications', 'receipt_path')) {
                $table->dropColumn('receipt_path');
            }
        });
    }
};
