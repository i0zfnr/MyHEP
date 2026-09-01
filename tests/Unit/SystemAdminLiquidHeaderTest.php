<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SystemAdminLiquidHeaderTest extends TestCase
{
    public function test_liquid_header_is_limited_to_system_admin_shell(): void
    {
        $layout = file_get_contents(__DIR__.'/../../resources/views/layouts/app.blade.php');
        $styles = file_get_contents(__DIR__.'/../../resources/css/liquid-glass.css');

        $this->assertStringContainsString("\$isAdmin && \$adminScope === 'system_admin' ? ' page-header--system-liquid' : ''", $layout);
        $this->assertStringContainsString('body.role-system-admin:not(.admin-liquid-disabled) .page-header--system-liquid', $styles);
        $this->assertStringContainsString('backdrop-filter: blur(var(--liquid-blur)) saturate(170%)', $styles);
        $this->assertStringContainsString('body.role-student .topbar', $styles);
        $this->assertStringContainsString('width: auto !important;', $styles);
        $this->assertStringContainsString('box-sizing: border-box;', $styles);
        $this->assertStringNotContainsString('body.role-lecturer', $styles);
    }
}
