<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_web_responses_include_security_headers(): void
    {
        $response = $this->get('/login');

        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(self), geolocation=(self), microphone=()');

        $this->assertStringContainsString("frame-ancestors 'none'", (string) $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("object-src 'none'", (string) $response->headers->get('Content-Security-Policy'));
    }

    public function test_untrusted_forwarded_host_is_not_reflected_in_generated_urls(): void
    {
        $response = $this->withHeaders([
            'X-Forwarded-Host' => 'attacker.example',
            'X-Forwarded-Proto' => 'https',
        ])->get('/login');

        $response->assertOk()->assertDontSee('attacker.example', false);
    }
}
