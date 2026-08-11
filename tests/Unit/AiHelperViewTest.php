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
}
