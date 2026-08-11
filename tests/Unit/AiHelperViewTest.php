<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AiHelperViewTest extends TestCase
{
    public function test_database_table_names_are_not_loaded_as_translation_groups(): void
    {
        $view = file_get_contents(__DIR__ . '/../../resources/views/admin/ai_helper/index.blade.php');

        $this->assertStringNotContainsString("__('students')", $view);
        $this->assertStringContainsString('<span>students</span>', $view);
    }

    public function test_admin_ai_helper_uses_accent_tokens_and_hides_native_file_control(): void
    {
        $view = file_get_contents(__DIR__ . '/../../resources/views/admin/ai_helper/index.blade.php');

        $this->assertStringContainsString('var(--se-primary-button-start)', $view);
        $this->assertStringContainsString('var(--se-primary-soft)', $view);
        $this->assertStringContainsString('.ai-upload-drop input { display:none !important;', $view);
    }

    public function test_admin_ai_helper_has_focused_composer_and_on_demand_tools(): void
    {
        $view = file_get_contents(__DIR__ . '/../../resources/views/admin/ai_helper/index.blade.php');

        $this->assertStringContainsString("What should we focus on?", $view);
        $this->assertStringContainsString('id="aiAddMenu"', $view);
        $this->assertStringContainsString('id="aiToolsPanel" aria-hidden="true"', $view);
        $this->assertStringContainsString('id="aiUploadShortcut"', $view);
    }

    public function test_shared_ai_view_has_a_two_column_student_composer(): void
    {
        $view = file_get_contents(__DIR__ . '/../../resources/views/admin/ai_helper/index.blade.php');

        $this->assertStringContainsString("'ai-admin--student'", $view);
        $this->assertStringContainsString('.ai-admin--student .ai-compose-row { grid-template-columns:minmax(0,1fr) 46px !important;', $view);
    }

    public function test_lecturer_ai_view_uses_its_own_routes_and_attachment_composer(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/admin/ai_helper/index.blade.php');

        $this->assertStringContainsString('$lecturerAiMode = $lecturerAiMode ?? false', $view);
        $this->assertStringContainsString("'lecturer.ai-helper.ask'", $view);
        $this->assertStringContainsString("'/lecturer/ai-helper/conversations'", $view);
        $this->assertStringContainsString('@unless($textOnlyAiMode)', $view);
        $this->assertStringContainsString("__('AI Helper (Lecturer)')", $view);
        $this->assertStringContainsString("__('Category summary')", $view);
        $this->assertStringContainsString('$canUploadAiFiles = ! $studentAiMode', $view);
        $this->assertStringContainsString('class="ai-compose-attachments" id="attachmentPreview"', $view);
        $this->assertStringContainsString('accept="application/pdf,image/jpeg,image/png,image/webp" multiple', $view);
        $this->assertStringContainsString('let selectedAttachments = [];', $view);
        $this->assertStringContainsString('selectedAttachments.length > 10', $view);
        $this->assertStringContainsString('selectedAttachments.push(file)', $view);
        $this->assertStringContainsString('selectedAttachments = selectedAttachments.filter', $view);
        $this->assertStringContainsString('bottom:calc(100% + 8px)', $view);
        $this->assertStringContainsString('<div class="ai-compose-frame">', $view);
        $this->assertStringContainsString('flex:0 0 104px', $view);
        $this->assertStringContainsString('min-width:25px !important', $view);
        $this->assertStringContainsString('border-radius:999px !important', $view);
        $this->assertStringContainsString('aspect-ratio:1/1', $view);
        $this->assertStringContainsString("requestBody.append('attachments[]', attachment)", $view);
        $this->assertStringContainsString("addMessage('user', message, '', sentAttachments)", $view);
        $this->assertStringContainsString("article.classList.add('has-attachments')", $view);
        $this->assertStringContainsString('className = \'ai-sent-attachment\'', $view);
        $this->assertStringContainsString('const clearComposerAttachments = () => {', $view);
        $this->assertStringContainsString("attachmentPreview?.classList.remove('is-visible')", $view);
        $this->assertStringContainsString("const sentAttachments = [...selectedAttachments];\n        clearComposerAttachments();\n        addMessage('user'", $view);
        $this->assertStringContainsString('.ai-admin--lecturer .ai-compose-row', $view);
    }

    public function test_admin_quick_actions_drawer_follows_system_accent_tokens(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/admin/ai_helper/index.blade.php');

        $this->assertStringContainsString('.ai-admin--admin > aside.ai-panel {', $view);
        $this->assertStringContainsString('.ai-admin--admin .ops-card {', $view);
        $this->assertStringContainsString('.ai-admin--admin .task-btn:hover', $view);
        $this->assertStringContainsString('background:var(--se-primary-soft) !important;', $view);
        $this->assertStringContainsString('border-color:var(--se-primary) !important;', $view);
    }

    public function test_admin_ai_workspace_uses_available_layout_height_without_page_scroll(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/admin/ai_helper/index.blade.php');

        $this->assertStringContainsString('body.admin-ai-helper-page .main-scroll-viewport { overflow:hidden !important; }', $view);
        $this->assertStringContainsString('body.admin-ai-helper-page .main-scroll-inner { height:100%; min-height:0; overflow:hidden; }', $view);
        $this->assertStringContainsString('body.admin-ai-helper-page .page-body { flex:1 1 auto; height:auto !important; min-height:0;', $view);
        $this->assertStringContainsString('min-height:0 !important; height:100% !important;', $view);
        $this->assertStringNotContainsString('height:calc(100vh - var(--topbar-h))', $view);
    }

    public function test_mobile_ai_workspace_keeps_composer_in_flow_and_messages_scrollable(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/admin/ai_helper/index.blade.php');
        $layout = file_get_contents(__DIR__.'/../../resources/views/layouts/app.blade.php');

        $this->assertStringContainsString("routeIs('admin.ai-helper.*', 'student.ai-helper.*', 'lecturer.ai-helper.*')", $layout);
        $this->assertStringContainsString('.ai-chat-log { min-height:0 !important; height:auto !important; overflow-y:auto !important;', $view);
        $this->assertStringContainsString('.ai-compose { position:relative !important; bottom:auto !important;', $view);
        $this->assertStringContainsString('.ai-admin--admin .ai-compose-row { grid-template-columns:42px minmax(0,1fr) 42px !important;', $view);
        $this->assertStringContainsString('.ai-format-pill { display:none !important; }', $view);
    }

    public function test_student_ai_history_and_chat_bubbles_use_accent_tokens(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/admin/ai_helper/index.blade.php');

        $this->assertStringContainsString('id="aiHistoryPanel" aria-hidden="true"', $view);
        $this->assertStringContainsString('id="aiNewConversation"', $view);
        $this->assertStringContainsString('id="aiDeleteAllHistory"', $view);
        $this->assertStringContainsString("requestBody.append('conversation_id', currentConversationId)", $view);
        $this->assertStringContainsString('.ai-admin .msg.user {', $view);
        $this->assertStringContainsString('var(--se-primary-button-start)', $view);
        $this->assertStringContainsString('.ai-admin .msg.ai {', $view);
        $this->assertStringContainsString('var(--se-primary-soft)', $view);
        $this->assertStringContainsString('body.student-bottom-nav-eligible.admin-ai-helper-page .page-body { padding-bottom:calc(5.8rem + env(safe-area-inset-bottom,0px)) !important; }', $view);
        $this->assertStringNotContainsString("(message.provider || 'AI').toUpperCase()", $view);
        $this->assertStringNotContainsString("(payload.provider || 'ai').toUpperCase()", $view);
    }

    public function test_reply_actions_use_compact_accent_toolbar(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/admin/ai_helper/index.blade.php');

        $this->assertStringContainsString('.ai-admin .ai-toolbar {', $view);
        $this->assertStringContainsString('border-radius:999px;', $view);
        $this->assertStringContainsString('.ai-admin .ai-toolbar .ai-btn svg', $view);
        $this->assertStringContainsString('id="aiCopyBtn" aria-label=', $view);
        $this->assertStringContainsString('id="aiClearBtn" aria-label=', $view);
        $this->assertStringContainsString('id="aiRegenerateBtn" aria-label=', $view);
    }

    public function test_chat_scrollbars_are_visible_and_follow_the_accent(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/css/design-system.css');

        $this->assertStringContainsString('scrollbar-gutter: auto;', $view);
        $this->assertStringContainsString('scrollbar-color: var(--se-primary)', $view);
        $this->assertStringContainsString('*::-webkit-scrollbar-thumb', $view);
        $this->assertStringContainsString('background: linear-gradient(180deg, var(--se-primary-muted), var(--se-primary));', $view);
        $this->assertStringContainsString('*::-webkit-scrollbar-button', $view);
        $this->assertStringContainsString('*::-webkit-scrollbar-button:vertical:start:decrement', $view);
    }

    public function test_ai_replies_render_as_safe_structured_reports(): void
    {
        $view = file_get_contents(__DIR__.'/../../resources/views/admin/ai_helper/index.blade.php');

        $this->assertStringContainsString("line.match(/^(#{1,4})", $view);
        $this->assertStringContainsString("metaGrid.className = 'report-meta'", $view);
        $this->assertStringContainsString("wrap.className = 'report-table-wrap'", $view);
        $this->assertStringContainsString("document.createTextNode(part)", $view);
        $this->assertStringNotContainsString('article.innerHTML = text', $view);
        $this->assertStringContainsString('.msg-rich h3 {', $view);
    }
}
