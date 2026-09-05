<?php

namespace Tests\Feature;

use App\Http\Middleware\RequireSessionRole;
use Closure;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class StudentProfileCompletionBypassTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('photo')->nullable();
            $table->string('email')->nullable();
            $table->string('semester')->nullable();
            $table->string('academic_session')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('religion')->nullable();
            $table->string('race')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('guardian_name')->nullable();
            $table->string('guardian_ic_no')->nullable();
            $table->text('guardian_address')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('mother_ic_no')->nullable();
            $table->decimal('family_income', 12, 2)->nullable();
            $table->boolean('profile_completion_bypass')->default(false);
            $table->boolean('is_blacklisted')->default(false);
        });

        Schema::create('student_scholarship_status_forms', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->timestamp('submitted_at')->nullable();
        });
    }

    public function test_only_the_enabled_student_can_bypass_profile_and_scholarship_onboarding(): void
    {
        DB::table('students')->insert([
            ['id' => 1, 'full_name' => 'Blocked Student', 'profile_completion_bypass' => false],
            ['id' => 2, 'full_name' => 'Exempt Student', 'profile_completion_bypass' => true],
        ]);

        $this->assertSame(302, $this->studentRequest(1)->getStatusCode());
        $this->assertSame(200, $this->studentRequest(2)->getStatusCode());
    }

    public function test_blacklisted_student_is_signed_out_before_reaching_the_system(): void
    {
        DB::table('students')->insert([
            'id' => 3,
            'full_name' => 'Blacklisted Student',
            'is_blacklisted' => true,
        ]);

        $this->assertSame(302, $this->studentRequest(3)->getStatusCode());
    }

    private function studentRequest(int $studentId): Response
    {
        $session = app('session')->driver();
        $session->put('auth_user', [
            'id' => $studentId,
            'role' => 'student',
            'name' => $studentId === 1 ? 'Blocked Student' : 'Exempt Student',
        ]);

        $request = Request::create('/student/dashboard');
        $request->setLaravelSession($session);

        return app(RequireSessionRole::class)->handle($request, Closure::fromCallable(fn () => new Response('allowed')), 'student');
    }
}
