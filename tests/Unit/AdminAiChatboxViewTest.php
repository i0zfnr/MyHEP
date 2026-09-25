<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AdminAiChatboxViewTest extends TestCase
{
    public function test_mobile_floating_chat_uses_safe_offsets_and_system_accent(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/partials/admin_ai_chatbox.blade.php');
        $styles = file_get_contents(__DIR__.'/../../resources/css/site-shared.css');

        $this->assertStringContainsString('var(--se-primary-button-start', $styles);
        $this->assertStringContainsString('right: 10px !important;', $styles);
        $this->assertStringContainsString('bottom: calc(16px + env(safe-area-inset-bottom, 0px)) !important;', $styles);
        $this->assertStringContainsString('(display-mode: standalone)', $styles);
        $this->assertStringContainsString('width: calc(100vw - 16px);', $styles);
        $this->assertStringContainsString('height: min(540px, calc(100dvh - 92px - env(safe-area-inset-bottom, 0px)));', $styles);
        $this->assertStringContainsString("document.body.classList.toggle('admin-ai-chat-open',open)", $view);
        $this->assertStringContainsString("@include('partials.ai_helper_icon', ['class' => 'admin-ai-fab-icon'])", $view);
    }

    public function test_all_ai_surfaces_use_the_shared_chat_icon(): void
    {
        $icon = file_get_contents(__DIR__.'/../../resources/views/partials/ai_helper_icon.blade.php');
        $layout = file_get_contents(__DIR__.'/../../resources/views/layouts/app.blade.php');
        $workspace = file_get_contents(__DIR__.'/../../resources/views/admin/ai_helper/index.blade.php');
        $chatbox = file_get_contents(__DIR__.'/../../resources/views/partials/admin_ai_chatbox.blade.php');

        $this->assertStringContainsString('M5.5 18.2 3.8 21l4.2-1.2', $icon);
        $this->assertStringContainsString('m12 8.7 1.05 2.25L15.3 12', $icon);
        $this->assertGreaterThanOrEqual(3, substr_count($layout, "@include('partials.ai_helper_icon', ['class' => 'nav-icon'])"));
        $this->assertStringContainsString("@include('partials.ai_helper_icon')", $workspace);
        $this->assertSame(2, substr_count($chatbox, "partials.ai_helper_icon"));
        $this->assertStringNotContainsString('>✦</span>', $workspace.$chatbox);
    }
}
