<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class LiquidGlassRoleIsolationTest extends TestCase
{
    public function test_layout_keeps_explicit_student_and_system_admin_roots(): void
    {
        $layout = file_get_contents(__DIR__.'/../../resources/views/layouts/app.blade.php');

        $this->assertStringContainsString("'role-student student-mobile-shell'", $layout);
        $this->assertStringContainsString("'role-system-admin system-admin-shell '", $layout);
        $this->assertStringContainsString("'resources/css/design-system.css', 'resources/css/liquid-glass.css'", $layout);
    }

    public function test_student_liquid_css_is_limited_to_navigation_and_safe_area_behaviour(): void
    {
        $styles = file_get_contents(__DIR__.'/../../resources/css/liquid-glass.css');

        $this->assertStringContainsString('--safe-top: env(safe-area-inset-top, 0px);', $styles);
        $this->assertStringContainsString('--safe-bottom: env(safe-area-inset-bottom, 0px);', $styles);
        $this->assertStringContainsString('body.role-student .mobile-bottom-nav.mobile-bottom-nav--student', $styles);
        $this->assertStringContainsString('body.role-student.pwa-standalone .app-footer', $styles);
        $this->assertStringNotContainsString('body.role-student .sdash', $styles);
        $this->assertStringNotContainsString('body.role-student .sidebar', $styles);
        $this->assertStringNotContainsString('body.role-student .topbar', $styles);
        $this->assertStringNotContainsString('body.role-student :is(.liquid-glass-surface, .liquid-glass-card)', $styles);
    }

    public function test_student_dashboard_restores_the_published_markup(): void
    {
        $dashboard = file_get_contents(__DIR__.'/../../resources/views/dashboard/student.blade.php');

        $this->assertStringContainsString("font-family:'DM Sans',system-ui,sans-serif", $dashboard);
        $this->assertStringContainsString('<div class="hero">', $dashboard);
        $this->assertStringContainsString('heroTodayDate', $dashboard);
        $this->assertStringContainsString('heroClock', $dashboard);
        $this->assertStringContainsString('maskIdentityNumber', $dashboard);
        $this->assertStringNotContainsString('liquid-glass-card', $dashboard);
        $this->assertStringNotContainsString('liquid-glass-hero', $dashboard);
    }

    public function test_selected_student_tab_uses_one_neutral_glass_bubble_in_both_themes(): void
    {
        $styles = file_get_contents(__DIR__.'/../../resources/css/liquid-glass.css');
        $legacyStyles = file_get_contents(__DIR__.'/../../resources/css/design-system.css');

        $this->assertStringContainsString('width: min(62px, calc(100% - 2px));', $styles);
        $this->assertStringContainsString('height: 52px;', $styles);
        $this->assertStringContainsString('border-radius: 22px;', $styles);
        $this->assertStringContainsString(':is(a, button).active::before', $styles);
        $this->assertStringContainsString('background-color: transparent !important;', $styles);
        $this->assertStringContainsString('background-image: none !important;', $styles);
        $this->assertStringContainsString('box-shadow: none !important;', $styles);
        $this->assertStringContainsString('body[data-theme="dark"].has-student-bottom-nav:not(.role-student)', $legacyStyles);
        $this->assertStringNotContainsString('body[data-theme="dark"].has-student-bottom-nav .mobile-bottom-nav :is(a, button).active:not(.mobile-scan-tab)', $legacyStyles);
    }

    public function test_transparency_control_only_updates_student_navigation_material_tokens(): void
    {
        $script = file_get_contents(__DIR__.'/../../resources/js/app.js');
        $bootstrap = file_get_contents(__DIR__.'/../../resources/views/partials/theme_bootstrap.blade.php');

        $this->assertStringContainsString("root.style.setProperty('--student-nav-material-alpha'", $script);
        $this->assertStringNotContainsString("root.style.setProperty('--glass-opacity'", $script);
        $this->assertStringContainsString("document.documentElement.style.setProperty('--student-nav-active-alpha'", $bootstrap);
        $this->assertStringNotContainsString("document.documentElement.style.setProperty('--glass-opacity'", $bootstrap);
    }
}
