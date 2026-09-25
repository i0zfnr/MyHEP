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
            ->assertHeaderMissing('X-Frame-Options')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(self), geolocation=(self), microphone=()');

        $this->assertStringContainsString("frame-ancestors 'self' https://portfolio.ryz.my.id", (string) $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("object-src 'none'", (string) $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString('https://static.cloudflareinsights.com', (string) $response->headers->get('Content-Security-Policy'));
    }

    public function test_untrusted_forwarded_host_is_not_reflected_in_generated_urls(): void
    {
        $response = $this->withHeaders([
            'X-Forwarded-Host' => 'attacker.example',
            'X-Forwarded-Proto' => 'https',
        ])->withServerVariables(['REMOTE_ADDR' => '192.0.2.10'])->get('/login');

        $response->assertOk()->assertDontSee('attacker.example', false);
    }

    public function test_local_environment_allows_vite_assets_and_hmr(): void
    {
        $this->app->detectEnvironment(fn () => 'local');

        $response = $this->get('/login');

        $response->assertOk();
        $policy = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('http://127.0.0.1:5173', $policy);
        $this->assertStringContainsString('ws://127.0.0.1:5173', $policy);
    }

    public function test_production_environment_does_not_allow_vite_dev_assets(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $response = $this->get('/login');

        $response->assertOk();
        $policy = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringNotContainsString('127.0.0.1:5173', $policy);
        $this->assertStringNotContainsString('localhost:5173', $policy);
    }

}
