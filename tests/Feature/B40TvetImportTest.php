<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\ScholarshipController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class B40TvetImportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('matric_no')->nullable();
            $table->string('ic_no');
            $table->string('program');
            $table->string('phone')->nullable();
            $table->string('residence_status')->nullable();
            $table->timestamps();
        });

        Schema::create('scholarships', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('type');
            $table->string('provider_name')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('status');
            $table->string('proof_file')->nullable();
            $table->timestamps();
        });
    }

    public function test_b40_upload_updates_existing_student_instead_of_creating_a_duplicate(): void
    {
        DB::table('students')->insert([
            'id' => 131,
            'full_name' => 'DEENA A/L LOGANATHAN',
            'matric_no' => '34DIT25F1019',
            'ic_no' => '070425102015',
            'program' => 'DIT',
            'phone' => null,
            'residence_status' => 'inside_campus',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $controller = new ScholarshipController();
        $importRows = new ReflectionMethod($controller, 'importB40Rows');
        $result = $importRows->invoke($controller, [[
            'nama' => 'DEENA A/L LOGANATHAN',
            'no kad pengenalan' => '7.0425102015E10',
            'program' => 'DIPLOMA TEKNOLOGI MAKLUMAT',
            'institusi' => 'POLITEKNIK BESUT, TERENGGANU',
            'jumlah' => '5000',
        ]]);

        $this->assertSame(0, $result['students_created']);
        $this->assertSame(1, $result['students_updated']);
        $this->assertDatabaseCount('students', 1);
        $this->assertDatabaseHas('students', [
            'id' => 131,
            'matric_no' => '34DIT25F1019',
            'ic_no' => '070425102015',
            'program' => 'DIT',
        ]);
        $this->assertDatabaseHas('scholarships', [
            'student_id' => 131,
            'provider_name' => 'SCHOLARSHIP B40 TVET',
            'status' => 'confirmed',
        ]);
    }
}
