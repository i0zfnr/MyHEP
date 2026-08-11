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
}
