<?php

namespace Tests\Feature;

use App\Mail\PasswordResetCode;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PasswordResetEmailTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('matric_no')->unique();
            $table->string('email')->nullable();
        });
        Schema::create('password_reset_codes', function (Blueprint $table): void {
            $table->id();
            $table->uuid('ref')->unique();
            $table->string('role', 20);
            $table->unsignedBigInteger('target_id');
            $table->string('email', 150);
            $table->string('code_hash');
            $table->timestamp('expires_at');
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });

        DB::table('students')->insert([
            'id' => 10,
            'full_name' => 'Test Student',
            'matric_no' => '34DIT24F1011',
            'email' => 'student@example.test',
        ]);
        RateLimiter::clear('password-reset:test');
    }

    public function test_matching_student_receives_the_branded_password_reset_email(): void
    {
        Mail::fake();

        $this->post('/password/forgot', [
            'role' => 'student',
            'identifier' => '34DIT24F1011',
            'email' => 'student@example.test',
        ])->assertRedirect();

        Mail::assertSent(function (PasswordResetCode $mail): bool {
            return $mail->hasTo('student@example.test')
                && $mail->recipientName === 'Test Student'
                && preg_match('/^\d{6}$/', $mail->code) === 1;
        });

        $this->assertDatabaseHas('password_reset_codes', [
            'role' => 'student',
            'target_id' => 10,
            'email' => 'student@example.test',
            'used_at' => null,
        ]);
    }
}
