<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class AdminSidebarNavigationTest extends TestCase
{
    public function test_student_list_is_a_single_top_level_item_outside_discipline(): void
    {
        $layout = file_get_contents(__DIR__.'/../../resources/views/layouts/app.blade.php');
        $studentList = strpos($layout, 'data-sidebar-student-list');
        $disciplineGroup = strpos($layout, 'class="nav-group {{ $adminOnDiscipline');

        $this->assertNotFalse($studentList);
        $this->assertNotFalse($disciplineGroup);
        $this->assertLessThan($disciplineGroup, $studentList);
        $this->assertSame(1, substr_count($layout, 'data-sidebar-student-list'));
        $this->assertSame(1, substr_count($layout, "route('admin.students.index')"));
    }
}
