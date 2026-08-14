<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            if (!Schema::hasColumn('students', 'oku_status')) {
                $table->string('oku_status', 10)->nullable()->after('family_income');
            }
            if (!Schema::hasColumn('students', 'oku_registration_no')) {
                $table->string('oku_registration_no', 50)->nullable()->after('oku_status');
            }
            if (!Schema::hasColumn('students', 'oku_category')) {
                $table->string('oku_category', 100)->nullable()->after('oku_registration_no');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $columns = array_values(array_filter(
                ['oku_status', 'oku_registration_no', 'oku_category'],
                fn (string $column): bool => Schema::hasColumn('students', $column)
            ));
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
