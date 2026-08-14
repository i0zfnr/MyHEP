<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SystemAdminVisualizationStyleTest extends TestCase
{
    public function test_system_admin_analytics_uses_the_shared_non_neon_design(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/dashboard/partials/admin_analytics.blade.php');

        $this->assertStringContainsString('/* Unified analytics system */', $view);
        $this->assertStringContainsString('background:var(--se-surface)', $view);
        $this->assertStringContainsString('/* Warm monitoring visual language shared with System Performance. */', $view);
        $this->assertStringContainsString('color-mix(in srgb,var(--se-primary) 30%,var(--se-border))', $view);
        $this->assertStringNotContainsString('--an-neon:', $view);
        $this->assertStringNotContainsString('System Admin-only neon command centre', $view);
        $this->assertStringNotContainsString('linear-gradient(160deg,#0b1018', $view);
        $this->assertStringContainsString('animation:none!important', $view);
    }
}
