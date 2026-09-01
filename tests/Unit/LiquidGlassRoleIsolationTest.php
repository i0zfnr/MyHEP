<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class LiquidGlassRoleIsolationTest extends TestCase
{
    public function test_layout_exposes_explicit_student_and_system_admin_roots(): void
    {
        $layout = file_get_contents(__DIR__.'/../../resources/views/layouts/app.blade.php');

        $this->assertStringContainsString("'role-student student-mobile-shell'", $layout);
        $this->assertStringContainsString("'role-system-admin system-admin-shell '", $layout);
    }

    public function test_liquid_stylesheet_loads_after_the_shared_design_system(): void
    {
        $layout = file_get_contents(__DIR__.'/../../resources/views/layouts/app.blade.php');
        $vite = file_get_contents(__DIR__.'/../../vite.config.js');

        $this->assertStringContainsString("'resources/css/design-system.css', 'resources/css/liquid-glass.css'", $layout);
        $this->assertStringContainsString("'resources/css/design-system.css', 'resources/css/liquid-glass.css'", $vite);
    }

    public function test_liquid_design_is_role_scoped_and_uses_safe_area_tokens(): void
    {
        $styles = file_get_contents(__DIR__.'/../../resources/css/liquid-glass.css');

        $this->assertStringContainsString('body.role-student', $styles);
        $this->assertStringContainsString('body.role-system-admin', $styles);
        $this->assertStringNotContainsString('body.role-lecturer', $styles);
        $this->assertStringContainsString('--safe-top: env(safe-area-inset-top, 0px);', $styles);
        $this->assertStringContainsString('--safe-bottom: env(safe-area-inset-bottom, 0px);', $styles);
        $this->assertStringContainsString('bottom: max(var(--student-liquid-nav-gap), var(--safe-bottom))', $styles);
        $this->assertStringContainsString('body.role-student .mobile-bottom-nav {', $styles);
        $this->assertStringNotContainsString('body.role-student .app-layout .mobile-bottom-nav', $styles);
        $this->assertStringContainsString('@media (prefers-reduced-motion: reduce)', $styles);
        $this->assertStringContainsString('@media (prefers-reduced-transparency: reduce), (prefers-contrast: more)', $styles);
    }

    public function test_reusable_liquid_materials_cannot_leak_to_other_roles(): void
    {
        $styles = file_get_contents(__DIR__.'/../../resources/css/liquid-glass.css');

        $this->assertStringContainsString('body.role-student :is(.liquid-glass-surface, .liquid-glass-card)', $styles);
        $this->assertStringContainsString('body.role-system-admin :is(.liquid-glass-surface, .liquid-glass-card)', $styles);
        $this->assertDoesNotMatchRegularExpression('/^\.liquid-glass-(?:surface|card)/m', $styles);
    }

    public function test_student_drawer_and_dynamic_island_spacing_use_inset_sheet_geometry(): void
    {
        $styles = file_get_contents(__DIR__.'/../../resources/css/liquid-glass.css');

        $this->assertStringContainsString('body.role-student .app-layout .sidebar {', $styles);
        $this->assertStringContainsString('top: max(.5rem, var(--safe-top)) !important;', $styles);
        $this->assertStringContainsString('bottom: max(.5rem, var(--safe-bottom)) !important;', $styles);
        $this->assertStringContainsString('padding-top: 0 !important;', $styles);
        $this->assertStringContainsString('border-radius: 28px !important;', $styles);
        $this->assertStringContainsString('body.role-student .sidebar :is(.nav-link.active, .nav-group.active > .nav-group-toggle)::before', $styles);
        $this->assertStringContainsString('min-height: calc(58px + var(--safe-top)) !important;', $styles);
    }

    public function test_student_hero_contrast_and_scan_qr_share_the_unified_navigation_geometry(): void
    {
        $styles = file_get_contents(__DIR__.'/../../resources/css/liquid-glass.css');

        $this->assertStringContainsString('body.role-student .sdash .hero.liquid-glass-hero::before', $styles);
        $this->assertStringContainsString('body.role-student .sdash :is(.hero-badge-value, .hero-meta-value)', $styles);
        $this->assertStringContainsString('color: var(--liquid-text) !important;', $styles);
        $this->assertStringContainsString('body.role-student .mobile-bottom-nav .mobile-scan-tab', $styles);
        $this->assertStringContainsString('--student-liquid-nav-height: 64px;', $styles);
        $this->assertStringContainsString('min-height: 48px !important;', $styles);
        $this->assertStringNotContainsString('min-height: 62px !important;', $styles);
    }

    public function test_student_refinement_uses_an_opaque_sheet_and_interaction_only_purple(): void
    {
        $styles = file_get_contents(__DIR__.'/../../resources/css/liquid-glass.css');

        $this->assertStringContainsString('--student-sheet-surface: rgba(249, 248, 246, .93);', $styles);
        $this->assertStringContainsString('--student-sheet-surface: rgba(29, 28, 27, .93);', $styles);
        $this->assertStringContainsString('var(--student-sheet-surface) !important;', $styles);
        $this->assertStringContainsString('--student-interaction: #6754a6;', $styles);
        $this->assertStringContainsString('transform: translateX(2px) !important;', $styles);
        $this->assertStringContainsString('transform: scale(.96) !important;', $styles);
    }

    public function test_student_hero_keeps_academic_context_without_a_live_clock_or_nric(): void
    {
        $dashboard = file_get_contents(__DIR__.'/../../resources/views/dashboard/student.blade.php');

        $this->assertStringContainsString("{{ __('Semester') }}", $dashboard);
        $this->assertStringContainsString("{{ __('Sesi') }}", $dashboard);
        $this->assertStringContainsString("{{ __('Kelas') }}", $dashboard);
        $this->assertStringNotContainsString('heroTodayDate', $dashboard);
        $this->assertStringNotContainsString('heroClock', $dashboard);
        $this->assertStringNotContainsString('maskIdentityNumber', $dashboard);
        $this->assertStringNotContainsString('setInterval(updateClock', $dashboard);
    }

    public function test_only_the_standalone_student_shell_hides_its_footer(): void
    {
        $styles = file_get_contents(__DIR__.'/../../resources/css/liquid-glass.css');

        $this->assertStringContainsString('body.role-student.pwa-standalone .app-footer', $styles);
        $this->assertStringContainsString('body.role-student.student-bottom-nav-eligible:not(.pwa-standalone) .app-footer', $styles);
        $this->assertStringNotContainsString('@media (display-mode: standalone)', $styles);
    }

    public function test_student_navigation_uses_one_gold_active_capsule_and_a_full_width_environment(): void
    {
        $styles = file_get_contents(__DIR__.'/../../resources/css/liquid-glass.css');

        $this->assertStringContainsString('border-radius: 999px !important;', $styles);
        $this->assertStringContainsString('#mobileMoreToggle[aria-expanded="true"]', $styles);
        $this->assertStringContainsString('color: var(--liquid-accent) !important;', $styles);
        $this->assertStringContainsString('body.role-student .app-layout .page-body {', $styles);
        $this->assertStringContainsString("body.role-student .app-layout .page-body {\n    background: transparent !important;", $styles);
    }

    public function test_selected_student_tab_uses_a_compact_neutral_glass_bubble_in_both_themes(): void
    {
        $styles = file_get_contents(__DIR__.'/../../resources/css/liquid-glass.css');

        $this->assertStringContainsString('width: min(62px, calc(100% - 2px));', $styles);
        $this->assertStringContainsString('height: 52px;', $styles);
        $this->assertStringContainsString('border-radius: 22px;', $styles);
        $this->assertStringContainsString(':is(a, button).active::before', $styles);
        $this->assertStringContainsString('#mobileMoreToggle[aria-expanded="true"]::before', $styles);
        $this->assertStringContainsString('body.role-student[data-theme="dark"] .mobile-bottom-nav--student :is(a, button).active::before', $styles);
        $this->assertStringContainsString('transform: translate(-50%, -50%) scale(1);', $styles);
        $this->assertStringContainsString('rgb(255 253 249 / var(--student-nav-active-alpha));', $styles);
        $this->assertStringContainsString('box-shadow: 0 4px 11px rgba(77, 59, 39, .10), inset 0 1px 0 rgba(255,255,255,.66);', $styles);
        $this->assertStringContainsString('rgb(65 62 58 / var(--student-nav-active-alpha));', $styles);
        $this->assertStringContainsString('mobile-bottom-nav.mobile-bottom-nav--student :is(a, button).active', $styles);
        $this->assertStringContainsString('body.role-student:is(.student-bottom-nav-eligible, .has-student-bottom-nav) .app-layout .mobile-bottom-nav.mobile-bottom-nav--student :is(a, button).active', $styles);
    }

    public function test_student_mobile_active_tab_neutralizes_the_legacy_dark_surface_in_browser_and_standalone_modes(): void
    {
        $legacyStyles = file_get_contents(__DIR__.'/../../resources/css/design-system.css');
        $liquidStyles = file_get_contents(__DIR__.'/../../resources/css/liquid-glass.css');

        $this->assertStringContainsString(
            'body[data-theme="dark"].has-student-bottom-nav:not(.role-student) .mobile-bottom-nav :is(a, button).active:not(.mobile-scan-tab)',
            $legacyStyles
        );
        $this->assertStringNotContainsString(
            'body[data-theme="dark"].has-student-bottom-nav .mobile-bottom-nav :is(a, button).active:not(.mobile-scan-tab)',
            $legacyStyles
        );
        $this->assertStringContainsString(
            'body.role-student:is(.student-bottom-nav-eligible, .has-student-bottom-nav) .app-layout .mobile-bottom-nav.mobile-bottom-nav--student :is(a, button).active',
            $liquidStyles
        );
        $this->assertStringContainsString('background-color: transparent !important;', $liquidStyles);
        $this->assertStringContainsString('background-image: none !important;', $liquidStyles);
        $this->assertStringContainsString('border: 0 !important;', $liquidStyles);
        $this->assertStringContainsString('border-color: transparent !important;', $liquidStyles);
        $this->assertStringContainsString('box-shadow: none !important;', $liquidStyles);
    }
}
