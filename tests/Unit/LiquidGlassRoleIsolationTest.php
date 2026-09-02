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

    public function test_student_liquid_css_is_limited_to_transient_navigation_and_drawer_surfaces(): void
    {
        $styles = file_get_contents(__DIR__.'/../../resources/css/liquid-glass.css');

        $this->assertStringContainsString('--safe-top: env(safe-area-inset-top, 0px);', $styles);
        $this->assertStringContainsString('--safe-bottom: env(safe-area-inset-bottom, 0px);', $styles);
        $this->assertStringContainsString('--student-nav-bottom-offset: max(', $styles);
        $this->assertStringContainsString('bottom: var(--student-nav-bottom-offset) !important;', $styles);
        $this->assertStringContainsString('body:is(.role-student, .role-qr-staff)', $styles);
        $this->assertStringContainsString('body.role-qr-staff .mobile-bottom-nav.mobile-bottom-nav--staff', $styles);
        $this->assertStringContainsString('body.role-student .mobile-bottom-nav.mobile-bottom-nav--student', $styles);
        $this->assertStringContainsString('body:is(.role-student, .role-qr-staff) .app-footer', $styles);
        $this->assertStringContainsString('body.role-student:is(.student-bottom-nav-eligible, .has-student-bottom-nav) .mobile-more-sheet', $styles);
        $this->assertStringContainsString('left: max(.65rem, var(--safe-left)) !important;', $styles);
        $this->assertStringContainsString('transform: translate3d(0, 0, 0) scale(1) !important;', $styles);
        $this->assertStringContainsString('stroke: currentColor !important;', $styles);
        $this->assertStringNotContainsString('body.role-student .sdash', $styles);
        $this->assertStringContainsString('body.role-student .app-layout .sidebar.is-open', $styles);
        $this->assertStringContainsString('backdrop-filter: blur(30px) saturate(145%) !important;', $styles);
        $this->assertStringContainsString('rgb(17 15 13 / max(.78, var(--glass-opacity))) !important;', $styles);
        $this->assertStringNotContainsString('body.role-student .topbar', $styles);
        $this->assertStringNotContainsString('body.role-student :is(.liquid-glass-surface, .liquid-glass-card)', $styles);
        $this->assertStringContainsString('+ 2rem)', $styles);
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
        $this->assertStringContainsString('border: 0;', $styles);
        $this->assertStringContainsString('color-mix(in srgb, var(--se-primary) 18%', $styles);
        $this->assertStringContainsString('color-mix(in srgb, var(--se-primary) 22%', $styles);
        $this->assertStringContainsString('width: 64px !important;', $styles);
        $this->assertStringContainsString('height: 64px !important;', $styles);
        $this->assertStringContainsString('border-radius: 50% !important;', $styles);
        $this->assertStringContainsString('margin: -1.42rem auto -.1rem !important;', $styles);
        $this->assertStringContainsString('content: none !important;', $styles);
        $this->assertStringContainsString('.mobile-bottom-nav:is(.mobile-bottom-nav--student, .mobile-bottom-nav--staff) .mobile-scan-tab', $styles);
        $this->assertStringContainsString('.mobile-scan-tab .mobile-nav-icon', $styles);
        $this->assertStringContainsString('position: static !important;', $styles);
        $this->assertStringNotContainsString('.app-layout .mobile-bottom-nav .mobile-scan-tab', $styles);
        $this->assertStringContainsString('--student-nav-accent: var(--se-primary-strong);', $styles);
        $this->assertStringContainsString('--student-nav-accent: var(--se-primary);', $styles);
        $this->assertStringContainsString('body[data-theme="dark"].has-student-bottom-nav:not(.role-student)', $legacyStyles);
        $this->assertStringContainsString('body.student-bottom-nav-eligible:not(.role-student) .app-layout .mobile-bottom-nav', $legacyStyles);
        $this->assertStringNotContainsString('body[data-theme="dark"].has-student-bottom-nav .mobile-bottom-nav :is(a, button).active:not(.mobile-scan-tab)', $legacyStyles);
    }

    public function test_qr_capable_staff_navigation_is_role_scoped_and_uses_authorized_destinations(): void
    {
        $layout = file_get_contents(__DIR__.'/../../resources/views/layouts/app.blade.php');

        $this->assertStringContainsString("\$showStaffBottomNav = \$isAdmin && \$adminScope !== 'system_admin' && \$canUseLaptops;", $layout);
        $this->assertStringContainsString("(\$showStaffBottomNav ? 'role-qr-staff '", $layout);
        $this->assertStringContainsString("'guard' => [route('admin.movements.index')", $layout);
        $this->assertStringContainsString("'student_affairs_head' => [route('admin.students.index')", $layout);
        $this->assertStringContainsString('mobile-bottom-nav--staff', $layout);
    }

    public function test_student_dashboard_accent_refinement_is_role_scoped_and_keeps_status_surfaces_neutral(): void
    {
        $styles = file_get_contents(__DIR__.'/../../resources/css/design-system.css');

        $this->assertStringContainsString('body.role-student .sdash .hero', $styles);
        $this->assertStringContainsString('body.role-student .sdash .hero-name', $styles);
        $this->assertStringContainsString('body.role-student .sdash :is(.stat-card, .portal-card)', $styles);
        $this->assertStringContainsString('body.role-student .sdash :is(.stat-icon.sand, .stat-icon.gold, .portal-card-icon.gold, .portal-card-icon.sand)', $styles);
        $this->assertStringNotContainsString('body.role-lecturer .sdash .hero', $styles);

        $studentModules = file_get_contents(__DIR__.'/../../resources/css/student-modules.css');
        $this->assertStringContainsString('body.role-student.student-dashboard-mobile-sidebar:is(.student-bottom-nav-eligible, .has-student-bottom-nav) .sdash', $studentModules);
        $this->assertStringContainsString('padding-bottom: .8rem;', $studentModules);
    }

    public function test_transparency_control_updates_shared_and_student_navigation_material_tokens(): void
    {
        $script = file_get_contents(__DIR__.'/../../resources/js/app.js');
        $bootstrap = file_get_contents(__DIR__.'/../../resources/views/partials/theme_bootstrap.blade.php');

        $this->assertStringContainsString("root.style.setProperty('--student-nav-material-alpha'", $script);
        $this->assertStringContainsString("root.style.setProperty('--glass-opacity'", $script);
        $this->assertStringContainsString('?? document.documentElement.dataset.glassTransparency', $script);
        $this->assertStringContainsString('selectedGlassTransparency !== undefined', $script);
        $this->assertStringContainsString("document.documentElement.style.setProperty('--student-nav-active-alpha'", $bootstrap);
        $this->assertStringContainsString("document.documentElement.style.setProperty('--glass-opacity'", $bootstrap);
    }
}
