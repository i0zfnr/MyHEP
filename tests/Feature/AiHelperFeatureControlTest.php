<?php

namespace Tests\Feature;

use App\Support\SystemFeatures;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class AiHelperFeatureControlTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('role');
        });
        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
        });
        Schema::create('system_features', function (Blueprint $table): void {
            $table->id();
            $table->string('feature_key')->unique();
            $table->boolean('enabled');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
        DB::table('admins')->insert([
            ['id' => 1, 'full_name' => 'System Admin', 'role' => 'system_admin'],
            ['id' => 2, 'full_name' => 'Discipline Admin', 'role' => 'discipline_admin'],
        ]);
        DB::table('students')->insert([
            'id' => 99,
            'full_name' => 'Test Student',
        ]);
    }

    public function test_admin_ai_helper_control_blocks_regular_admin_but_not_system_admin(): void
    {
        $this->actingAsSystemAdmin()
            ->patch('/admin/features/admin_ai_helper', ['enabled' => 0])
            ->assertRedirect('/admin/features');

        $this->assertDatabaseHas('system_features', [
            'feature_key' => 'admin_ai_helper',
            'enabled' => false,
        ]);

        $this->actingAsSystemAdmin()->get('/admin/ai-helper')
            ->assertOk()->assertSee('AI Helper');
        $this->actingAsRegularAdmin()->get('/admin/ai-helper')->assertStatus(503);
    }

    public function test_student_ai_helper_has_an_independent_control(): void
    {
        $this->actingAsSystemAdmin()
            ->patch('/admin/features/student_ai_helper', ['enabled' => 0])
            ->assertRedirect('/admin/features');

        $this->assertDatabaseHas('system_features', [
            'feature_key' => 'student_ai_helper',
            'enabled' => false,
        ]);
    }

    public function test_student_ai_helper_uses_admin_workspace_without_upload_controls(): void
    {
        $this->actingAsStudent()->get('/student/ai-helper')
            ->assertOk()
            ->assertSee('What should we focus on?')
            ->assertSee('My scholarship')
            ->assertDontSee('id="reportAttachment"', false)
            ->assertDontSee('Upload PDF or image');
    }

    public function test_student_ai_helper_rejects_uploads_and_image_generation_requests(): void
    {
        Http::fake();

        $this->actingAsStudent()->post('/student/ai-helper', [
            'message' => 'Please explain this document.',
            'attachment' => UploadedFile::fake()->create('records.pdf', 20, 'application/pdf'),
        ], ['Accept' => 'application/json'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('attachment');

        $this->actingAsStudent()->post('/student/ai-helper', [
            'message' => 'Generate an image for my scholarship.',
        ], ['Accept' => 'application/json'])->assertStatus(422)
            ->assertJsonPath('message', 'Student AI Helper provides text guidance only. Image generation requests are not supported.');

        Http::assertNothingSent();
    }

    public function test_admin_can_attach_an_image_for_gemini_report_generation(): void
    {
        config([
            'services.gemini.key' => 'test-key',
            'services.gemini.url' => 'https://generativelanguage.googleapis.com/v1beta',
            'services.gemini.model' => 'gemini-test',
        ]);
        Http::fake([
            '*' => Http::response([
                'candidates' => [['content' => ['parts' => [['text' => 'Generated attachment report']]]]],
            ]),
        ]);

        $this->actingAsSystemAdmin()->post('/admin/ai-helper', [
            'message' => 'Generate a report from this image.',
            'filters' => ['output_format' => 'formal_report'],
            'attachment' => UploadedFile::fake()->createWithContent(
                'evidence.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=')
            ),
        ])->assertOk()->assertJsonPath('answer', 'Generated attachment report');

        Http::assertSent(function ($request): bool {
            $parts = data_get($request->data(), 'contents.0.parts', []);

            $hasImage = collect($parts)->contains(fn (array $part): bool =>
                data_get($part, 'inlineData.mimeType') === 'image/png'
                && filled(data_get($part, 'inlineData.data'))
            );
            $prompt = (string) data_get($parts, '0.text');

            return $hasImage
                && str_contains($prompt, 'Return a formal report with title')
                && str_contains($prompt, 'inspect an attached image strictly as evidence');
        });
    }

    public function test_admin_ai_helper_rejects_image_generation_requests(): void
    {
        Http::fake();

        $this->actingAsSystemAdmin()->post('/admin/ai-helper', [
            'message' => 'Create an image poster for the monthly report.',
        ])->assertStatus(422)
            ->assertJsonPath('message', 'AI Helper generates written reports only. Image generation requests are not supported.');

        Http::assertNothingSent();
    }

    public function test_system_admin_can_disable_liquid_design_for_other_administrators(): void
    {
        $this->actingAsSystemAdmin()
            ->patch('/admin/features/admin_liquid_design', ['enabled' => 0])
            ->assertRedirect('/admin/features');

        $this->assertDatabaseHas('system_features', [
            'feature_key' => 'admin_liquid_design',
            'enabled' => false,
        ]);

        $features = app(SystemFeatures::class);
        $this->assertFalse($features->adminLiquidDesignEnabled('discipline_admin'));
        $this->assertFalse($features->adminLiquidDesignEnabled('scholarship_admin'));
        $this->assertFalse($features->adminLiquidDesignEnabled('student_affairs_head'));
        $this->assertTrue($features->adminLiquidDesignEnabled('system_admin'));
    }

    private function actingAsSystemAdmin(): static
    {
        return $this->withSession(['auth_user' => [
            'id' => 1,
            'role' => 'admin',
            'admin_role' => 'system_admin',
            'name' => 'System Admin',
        ]]);
    }

    private function actingAsStudent(): static
    {
        return $this->withSession(['auth_user' => [
            'id' => 99,
            'role' => 'student',
            'name' => 'Test Student',
        ]]);
    }

    private function actingAsRegularAdmin(): static
    {
        return $this->withSession(['auth_user' => [
            'id' => 2,
            'role' => 'admin',
            'admin_role' => 'discipline_admin',
            'name' => 'Discipline Admin',
        ]]);
    }
}
