<?php

namespace Tests\Feature;

use App\Mail\BugReportSubmitted;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BugReportNotificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('bug_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('reporter_name', 150);
            $table->string('reporter_email', 150);
            $table->string('category', 30);
            $table->string('subject', 200);
            $table->string('page_url', 500)->nullable();
            $table->text('description');
            $table->string('screenshot_path')->nullable();
            $table->string('status', 30);
            $table->timestamps();
        });
    }

    public function test_submission_is_saved_and_emails_the_configured_system_admin(): void
    {
        Mail::fake();
        config(['mail.system_admin_report_address' => 'systemadminse@gmail.com']);

        $this->post('/report-problem', [
            'reporter_name' => 'Student User',
            'reporter_email' => 'student@example.test',
            'category' => 'bug',
            'subject' => 'Dashboard card is not loading',
            'page_url' => 'https://studentedge.example/student/dashboard',
            'description' => 'The dashboard card remains blank after refreshing the page.',
        ])->assertRedirect('/report-problem')
            ->assertSessionHas('success');

        $this->assertDatabaseHas('bug_reports', [
            'reporter_email' => 'student@example.test',
            'subject' => 'Dashboard card is not loading',
            'status' => 'new',
        ]);

        Mail::assertSent(function (BugReportSubmitted $mail): bool {
            return $mail->hasTo('systemadminse@gmail.com')
                && $mail->report['subject'] === 'Dashboard card is not loading';
        });
    }
}
