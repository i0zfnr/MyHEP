<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AdminAiChatboxViewTest extends TestCase
{
    public function test_mobile_floating_chat_uses_safe_offsets_and_system_accent(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/partials/admin_ai_chatbox.blade.php');

        $this->assertStringContainsString('var(--se-primary-button-start)', $view);
        $this->assertStringContainsString('.admin-ai-fab { right:10px; bottom:16px; width:48px; height:48px;', $view);
        $this->assertStringContainsString('(display-mode:standalone)', $view);
        $this->assertStringContainsString('body.student-bottom-nav-eligible .admin-ai-fab { bottom:calc(86px + env(safe-area-inset-bottom,0px)); }', $view);
        $this->assertStringContainsString('width:calc(100vw - 16px); height:min(540px,calc(100dvh - 92px));', $view);
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
        $this->assertSame(3, substr_count($layout, "@include('partials.ai_helper_icon', ['class' => 'nav-icon'])"));
        $this->assertStringContainsString("@include('partials.ai_helper_icon')", $workspace);
        $this->assertSame(2, substr_count($chatbox, "partials.ai_helper_icon"));
        $this->assertStringNotContainsString('>✦</span>', $workspace.$chatbox);
    }
}
