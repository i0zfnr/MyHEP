<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'program') && ! $this->hasIndex('students', 'idx_students_program')) {
                $table->index('program', 'idx_students_program');
            }
            if (Schema::hasColumn('students', 'created_at') && ! $this->hasIndex('students', 'idx_students_created_at')) {
                $table->index('created_at', 'idx_students_created_at');
            }
        });

        if (Schema::hasTable('scholarships')) {
            Schema::table('scholarships', function (Blueprint $table) {
                if (Schema::hasColumns('scholarships', ['status', 'type']) && ! $this->hasIndex('scholarships', 'idx_scholarships_status_type')) {
                    $table->index(['status', 'type'], 'idx_scholarships_status_type');
                }
                if (Schema::hasColumn('scholarships', 'created_at') && ! $this->hasIndex('scholarships', 'idx_scholarships_created_at')) {
                    $table->index('created_at', 'idx_scholarships_created_at');
                }
            });
        }

        if (Schema::hasTable('offenses')) {
            Schema::table('offenses', function (Blueprint $table) {
                if (Schema::hasColumns('offenses', ['status', 'created_at']) && ! $this->hasIndex('offenses', 'idx_offenses_status_created_at')) {
                    $table->index(['status', 'created_at'], 'idx_offenses_status_created_at');
                }
            });
        }

        if (Schema::hasTable('fine_payment_applications')) {
            Schema::table('fine_payment_applications', function (Blueprint $table) {
                if (Schema::hasColumns('fine_payment_applications', ['status', 'created_at']) && ! $this->hasIndex('fine_payment_applications', 'idx_fine_apps_status_created_at')) {
                    $table->index(['status', 'created_at'], 'idx_fine_apps_status_created_at');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            if ($this->hasIndex('students', 'idx_students_program')) {
                $table->dropIndex('idx_students_program');
            }
            if ($this->hasIndex('students', 'idx_students_created_at')) {
                $table->dropIndex('idx_students_created_at');
            }
        });

        if (Schema::hasTable('scholarships')) {
            Schema::table('scholarships', function (Blueprint $table) {
                if ($this->hasIndex('scholarships', 'idx_scholarships_status_type')) {
                    $table->dropIndex('idx_scholarships_status_type');
                }
                if ($this->hasIndex('scholarships', 'idx_scholarships_created_at')) {
                    $table->dropIndex('idx_scholarships_created_at');
                }
            });
        }

        if (Schema::hasTable('offenses')) {
            Schema::table('offenses', function (Blueprint $table) {
                if ($this->hasIndex('offenses', 'idx_offenses_status_created_at')) {
                    $table->dropIndex('idx_offenses_status_created_at');
                }
            });
        }

        if (Schema::hasTable('fine_payment_applications')) {
            Schema::table('fine_payment_applications', function (Blueprint $table) {
                if ($this->hasIndex('fine_payment_applications', 'idx_fine_apps_status_created_at')) {
                    $table->dropIndex('idx_fine_apps_status_created_at');
                }
            });
        }
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $indexes = Schema::getIndexes($table);
        foreach ($indexes as $index) {
            if (($index['name'] ?? null) === $indexName) {
                return true;
            }
        }
        return false;
    }
};
