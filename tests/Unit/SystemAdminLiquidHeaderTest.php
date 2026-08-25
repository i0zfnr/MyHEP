<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class SystemAdminLiquidHeaderTest extends TestCase
{
    public function test_liquid_header_is_limited_to_system_admin_shell(): void
    {
        $layout = file_get_contents(__DIR__.'/../../resources/views/layouts/app.blade.php');
        $styles = file_get_contents(__DIR__.'/../../resources/css/layout.css');

        $this->assertStringContainsString("\$isAdmin && \$adminScope === 'system_admin' ? ' page-header--system-liquid' : ''", $layout);
        $this->assertStringContainsString('body.system-admin-shell:not(.admin-liquid-disabled) .page-header--system-liquid', $styles);
        $this->assertStringContainsString('backdrop-filter: blur(calc(var(--glass-blur) + 6px)) saturate(155%);', $styles);
        $this->assertStringContainsString("background: var(--surface);", $styles);
    }
}
