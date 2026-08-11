<?php

namespace Tests\Unit;

use Tests\TestCase;

class BackToTopViewTest extends TestCase
{
    public function test_admin_and_lecturer_layout_has_an_accessible_back_to_top_control(): void
    {
        $layout = file_get_contents(__DIR__.'/../../resources/views/layouts/app.blade.php');
        $styles = file_get_contents(__DIR__.'/../../resources/css/design-system.css');
        $script = file_get_contents(__DIR__.'/../../resources/js/app.js');

        $this->assertStringContainsString('@if($isAdmin && !$isGuardAdmin)', $layout);
        $this->assertStringContainsString('id="seBackToTop"', $layout);
        $this->assertStringContainsString("{{ __('Back to top') }}", $layout);
        $this->assertStringContainsString('.se-back-to-top.is-visible', $styles);
        $this->assertStringContainsString('body.student-bottom-nav-eligible .se-back-to-top', $styles);
        $this->assertStringContainsString("document.querySelector('[data-lenis-main]')", $script);
        $this->assertStringContainsString("viewport.scrollTo({", $script);
        $this->assertStringContainsString("prefers-reduced-motion: reduce", $script);
    }
}
