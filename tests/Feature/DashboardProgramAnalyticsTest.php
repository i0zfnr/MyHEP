<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\DashboardController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ReflectionMethod;
use Tests\TestCase;

class DashboardProgramAnalyticsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->string('program')->nullable();
            $table->string('semester')->nullable();
        });
        Cache::forget('myhep.dashboard.analytics.programs');
    }

    public function test_program_chart_merges_full_course_names_and_counts_students_without_a_semester(): void
    {
        DB::table('students')->insert([
            ['program' => 'DIT', 'semester' => '1'],
            ['program' => 'DIPLOMA TEKNOLOGI MAKLUMAT', 'semester' => null],
            ['program' => 'DIPLOMA TEKNOLOGI MAKLUMAT (TEKNOLOGI DIGITAL)', 'semester' => '6'],
            ['program' => 'DIPLOMA REKA BENTUK FESYEN BATIK', 'semester' => null],
        ]);

        $method = new ReflectionMethod(DashboardController::class, 'programSemesterStacked');
        $chart = $method->invoke(new DashboardController(), ['#111111', '#222222']);
        $series = collect($chart['series'])->keyBy('label');

        $this->assertSame(['DBF', 'DDT', 'DIT'], $series->keys()->sort()->values()->all());
        $this->assertSame(2, $series['DIT']['total']);
        $this->assertSame(1, $series['DDT']['total']);
        $this->assertSame(1, $series['DBF']['total']);
        $this->assertContains(__('Not set'), collect($series['DIT']['segments'])->pluck('label')->all());
    }
}
