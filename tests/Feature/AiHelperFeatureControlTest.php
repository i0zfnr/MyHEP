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
            $table->string('matric_no')->nullable();
            $table->string('program')->nullable();
            $table->string('class_name')->nullable();
            $table->unsignedInteger('semester')->nullable();
            $table->string('academic_session')->nullable();
        });
        Schema::create('student_ai_conversations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('student_id')->index();
            $table->string('title', 120);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });
        Schema::create('student_ai_messages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('conversation_id')->index();
            $table->string('role', 16);
            $table->text('content');
            $table->string('provider', 40)->nullable();
            $table->string('model', 120)->nullable();
            $table->timestamps();
        });
        Schema::create('admin_ai_conversations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('admin_id')->index();
            $table->string('title', 120);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();
        });
        Schema::create('admin_ai_messages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('conversation_id')->index();
            $table->string('role', 16);
            $table->text('content');
            $table->string('provider', 40)->nullable();
            $table->string('model', 120)->nullable();
            $table->timestamps();
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
            ['id' => 3, 'full_name' => 'Test Lecturer', 'role' => 'lecturer'],
        ]);
        DB::table('students')->insert([
            ['id' => 99, 'full_name' => 'Test Student', 'matric_no' => 'TEST99', 'program' => 'DIT'],
            ['id' => 100, 'full_name' => 'Other Student', 'matric_no' => 'TEST100', 'program' => 'DIT'],
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

    public function test_lecturer_ai_helper_has_an_independent_system_admin_control(): void
    {
        $this->actingAsSystemAdmin()->patch('/admin/features/lecturer_ai_helper', ['enabled' => 0])
            ->assertRedirect('/admin/features');

        $this->assertDatabaseHas('system_features', ['feature_key' => 'lecturer_ai_helper', 'enabled' => false]);
        $this->actingAsLecturer()->get('/lecturer/ai-helper')->assertStatus(503);

        $this->actingAsSystemAdmin()->patch('/admin/features/lecturer_ai_helper', ['enabled' => 1]);
        $this->actingAsSystemAdmin()->get('/lecturer/ai-helper')->assertForbidden();
        $this->actingAsLecturer()->get('/lecturer/ai-helper')
            ->assertOk()->assertSee('AI Helper (Lecturer)')->assertSee('Upload PDF or image');
    }

    public function test_lecturer_ai_helper_accepts_report_sources_and_persists_private_history(): void
    {
        config(['services.gemini.key' => 'test-key', 'services.gemini.url' => 'https://example.test', 'services.gemini.model' => 'gemini-test']);
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => 'Lecturer summary']]]]]])]);

        $attachments = collect(range(1, 10))->map(fn (int $number) =>
            UploadedFile::fake()->create("source-{$number}.pdf", 1, 'application/pdf')
        )->all();
        $response = $this->actingAsLecturer()->post('/lecturer/ai-helper', [
            'message' => 'Prepare an anonymized summary from this file',
            'attachments' => $attachments,
        ], ['Accept' => 'application/json'])->assertOk()
            ->assertJsonPath('answer', 'Lecturer summary')
            ->assertJsonPath('attachment_name', 'source-1.pdf')
            ->assertJsonCount(10, 'attachment_names');
        $conversationId = (int) $response->json('conversation.id');

        $this->assertDatabaseHas('admin_ai_conversations', ['id' => $conversationId, 'admin_id' => 3]);
        $this->actingAsLecturer()->getJson("/lecturer/ai-helper/conversations/{$conversationId}")
            ->assertOk()->assertJsonCount(2, 'messages');
        $this->actingAsRegularAdmin()->getJson("/lecturer/ai-helper/conversations/{$conversationId}")->assertForbidden();
        Http::assertSent(fn ($request): bool => str_contains(
            (string) data_get($request->data(), 'contents.0.parts.0.text'),
            'Attached report sources: source-1.pdf'
        ));

        $tooMany = collect(range(1, 11))->map(fn (int $number) =>
            UploadedFile::fake()->create("extra-{$number}.pdf", 1, 'application/pdf')
        )->all();
        $this->actingAsLecturer()->post('/lecturer/ai-helper', [
            'message' => 'Review these files',
            'attachments' => $tooMany,
        ], ['Accept' => 'application/json'])->assertStatus(422)->assertJsonValidationErrors('attachments');
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

    public function test_student_ai_conversations_are_persisted_and_owner_scoped(): void
    {
        config([
            'services.gemini.key' => 'test-key',
            'services.gemini.url' => 'https://generativelanguage.googleapis.com/v1beta',
            'services.gemini.model' => 'gemini-test',
        ]);
        Http::fake(['*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'Your scholarship is pending.']]]]],
        ])]);

        $created = $this->actingAsStudent()->post('/student/ai-helper', [
            'message' => 'What is my scholarship status?',
        ], ['Accept' => 'application/json'])->assertOk();

        $conversationId = (int) $created->json('conversation.id');
        $this->assertGreaterThan(0, $conversationId);
        $this->assertDatabaseHas('student_ai_conversations', ['id' => $conversationId, 'student_id' => 99]);
        $this->assertDatabaseHas('student_ai_messages', ['conversation_id' => $conversationId, 'role' => 'user']);
        $this->assertDatabaseHas('student_ai_messages', ['conversation_id' => $conversationId, 'role' => 'assistant']);

        $this->actingAsStudent()->getJson("/student/ai-helper/conversations/{$conversationId}")
            ->assertOk()->assertJsonCount(2, 'messages');

        $this->withSession(['auth_user' => ['id' => 100, 'role' => 'student', 'name' => 'Other Student']])
            ->getJson("/student/ai-helper/conversations/{$conversationId}")->assertNotFound();
    }

    public function test_student_can_rename_and_delete_only_their_ai_conversation(): void
    {
        $conversationId = DB::table('student_ai_conversations')->insertGetId([
            'student_id' => 99,
            'title' => 'Old title',
            'last_message_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAsStudent()->patchJson("/student/ai-helper/conversations/{$conversationId}", [
            'title' => 'Scholarship question',
        ])->assertOk()->assertJsonPath('conversation.title', 'Scholarship question');

        $this->actingAsStudent()->deleteJson("/student/ai-helper/conversations/{$conversationId}")
            ->assertOk()->assertJsonPath('deleted', true);
        $this->assertDatabaseMissing('student_ai_conversations', ['id' => $conversationId]);
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

    public function test_admin_ai_conversations_are_persisted_and_owner_scoped(): void
    {
        config(['services.gemini.key' => 'test-key', 'services.gemini.url' => 'https://example.test', 'services.gemini.model' => 'gemini-test']);
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => 'Admin report']]]]]])]);

        $response = $this->actingAsSystemAdmin()->postJson('/admin/ai-helper', ['message' => 'Prepare the monthly report'])->assertOk();
        $conversationId = (int) $response->json('conversation.id');

        $this->assertDatabaseHas('admin_ai_conversations', ['id' => $conversationId, 'admin_id' => 1]);
        $this->assertDatabaseCount('admin_ai_messages', 2);
        $this->actingAsSystemAdmin()->getJson("/admin/ai-helper/conversations/{$conversationId}")
            ->assertOk()->assertJsonCount(2, 'messages');
        $this->actingAsRegularAdmin()->getJson("/admin/ai-helper/conversations/{$conversationId}")->assertNotFound();
    }

    public function test_inactive_ai_conversations_are_pruned_for_all_roles(): void
    {
        config(['ai.conversation_retention_days' => 30]);
        foreach ([['student_ai_conversations', 'student_id', 99], ['admin_ai_conversations', 'admin_id', 1]] as [$table, $owner, $id]) {
            DB::table($table)->insert([
                $owner => $id, 'title' => 'Expired', 'last_message_at' => now()->subDays(31),
                'created_at' => now()->subDays(31), 'updated_at' => now()->subDays(31),
            ]);
            DB::table($table)->insert([
                $owner => $id, 'title' => 'Active', 'last_message_at' => now()->subDays(2),
                'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2),
            ]);
        }

        $this->artisan('ai:prune-conversations')->assertSuccessful();

        $this->assertDatabaseMissing('student_ai_conversations', ['title' => 'Expired']);
        $this->assertDatabaseMissing('admin_ai_conversations', ['title' => 'Expired']);
        $this->assertDatabaseHas('student_ai_conversations', ['title' => 'Active']);
        $this->assertDatabaseHas('admin_ai_conversations', ['title' => 'Active']);
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

    private function actingAsLecturer(): static
    {
        return $this->withSession(['auth_user' => [
            'id' => 3,
            'role' => 'admin',
            'admin_role' => 'lecturer',
            'name' => 'Test Lecturer',
        ]]);
    }
}
