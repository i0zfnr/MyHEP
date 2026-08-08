<?php

namespace Tests\Unit;

use App\Mail\PasswordResetCode;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PasswordResetCodeMailTest extends TestCase
{
    public function test_password_reset_email_has_branded_html_and_plain_text_content(): void
    {
        $mail = new PasswordResetCode(
            recipientName: 'Test Student',
            code: '482913',
            reference: 'd846c0d5-28ad-47df-a395-42db6fd53671',
            expiresAt: Carbon::parse('2026-08-09 12:15:00'),
        );

        $mail->assertHasSubject('Your StudentEdge password reset code');
        $mail->assertSeeInHtml('Test Student');
        $mail->assertSeeInHtml('482913');
        $mail->assertSeeInHtml('Keep this code private.');
        $mail->assertSeeInText('482913');
        $mail->assertSeeInText('D846C0D5');
    }
}
