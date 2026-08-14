<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentDocumentCentreTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('student_documents');

        Schema::create('students', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('matric_no')->nullable();
            $table->string('photo')->nullable();
        });
        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('role');
            $table->string('photo')->nullable();
        });
        Schema::create('student_documents', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('source_type', 40)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('title', 150);
            $table->string('category', 32);
            $table->string('disk', 40)->default('student_documents');
            $table->string('path')->unique();
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->date('expiry_date')->nullable();
            $table->string('status', 20)->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('student_scholarship_status_forms', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('student_id')->unique();
            $table->string('has_scholarship', 10);
            $table->string('sponsor_name')->nullable();
            $table->decimal('monthly_amount', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();
        });
        Schema::create('scholarships', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->string('type');
            $table->string('provider_name')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('status');
            $table->string('proof_file')->nullable();
            $table->timestamps();
        });
        Schema::create('system_features', function (Blueprint $table): void {
            $table->id();
            $table->string('feature_key')->unique();
            $table->boolean('enabled');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });

        DB::table('students')->insert([
            ['id' => 1, 'full_name' => 'First Student', 'matric_no' => 'PB001', 'photo' => 'students/first.jpg'],
            ['id' => 2, 'full_name' => 'Second Student', 'matric_no' => 'PB002', 'photo' => 'students/second.jpg'],
        ]);
        DB::table('admins')->insert([
            ['id' => 10, 'full_name' => 'HEP Head', 'role' => 'student_affairs_head'],
            ['id' => 11, 'full_name' => 'Discipline Officer', 'role' => 'discipline_admin'],
            ['id' => 12, 'full_name' => 'System Admin', 'role' => 'system_admin'],
            ['id' => 13, 'full_name' => 'Scholarship Officer', 'role' => 'scholarship_admin'],
        ]);
    }

    public function test_scholarship_offer_letter_is_uploaded_privately_from_status_form(): void
    {
        $response = $this->studentSession(1)->post('/student/scholarship-status', [
            'has_scholarship' => 'yes',
            'sponsor_name' => 'MARA',
            'monthly_amount' => 500,
            'offer_letter' => UploadedFile::fake()->create('offer-letter.pdf', 128, 'application/pdf'),
        ]);

        $response->assertRedirect('/student/scholarships');
        $document = DB::table('student_documents')->first();
        $this->assertSame(1, (int) $document->student_id);
        $this->assertSame('pending', $document->status);
        $this->assertSame('scholarship_status', $document->source_type);
        $this->assertStringStartsWith('1/', $document->path);
        $this->assertStringNotContainsString('offer-letter', $document->path);
        Storage::disk('student_documents')->assertExists($document->path);
        Storage::disk('public')->assertMissing($document->path);
    }

    public function test_student_list_and_download_are_limited_to_owned_documents(): void
    {
        $ownId = $this->insertDocument(1, 'Own Private Letter', '1/own.pdf');
        $otherId = $this->insertDocument(2, 'Another Student Letter', '2/other.pdf');
        Storage::disk('student_documents')->put('1/own.pdf', 'private-content');
        Storage::disk('student_documents')->put('2/other.pdf', 'other-content');

        $this->studentSession(1)->get('/student/documents')
            ->assertOk()
            ->assertSee('Own Private Letter')
            ->assertDontSee('Another Student Letter');

        $download = $this->get("/student/documents/{$ownId}/download");
        $download->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringContainsString('private', (string) $download->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', (string) $download->headers->get('Cache-Control'));
        $this->get("/student/documents/{$otherId}/download")->assertNotFound();
    }

    public function test_student_can_upload_payment_document_but_cannot_delete_it_from_archive(): void
    {
        $this->studentSession(1)->post('/student/documents', [
            'title' => 'Insurance Payment 2026',
            'category' => 'insurance_payment',
            'document' => UploadedFile::fake()->create('insurance.pdf', 128, 'application/pdf'),
        ])->assertRedirect('/student/documents');

        $document = DB::table('student_documents')->where('title', 'Insurance Payment 2026')->first();
        $this->assertNotNull($document);
        $this->assertSame('insurance_payment', $document->category);
        $this->assertSame('pending', $document->status);
        Storage::disk('student_documents')->assertExists($document->path);

        $documentId = (int) $document->id;
        $this->delete("/student/documents/{$documentId}")->assertNotFound();
        $this->assertDatabaseHas('student_documents', ['id' => $documentId]);
    }

    public function test_only_authorized_admin_roles_can_access_document_review(): void
    {
        $insuranceId = $this->insertDocument(1, 'Insurance Receipt', '1/insurance.pdf', category: 'insurance_payment');
        $letterId = $this->insertDocument(1, 'Private Letter', '1/private.pdf');
        Storage::disk('student_documents')->put('1/insurance.pdf', 'insurance-content');
        Storage::disk('student_documents')->put('1/private.pdf', 'letter-content');

        $this->adminSession(11, 'discipline_admin', 'Discipline Officer')
            ->get('/admin/documents')
            ->assertOk()
            ->assertSee('Insurance Receipt')
            ->assertDontSee('Private Letter');
        $this->get("/admin/documents/{$insuranceId}/download")->assertOk();
        $this->get("/admin/documents/{$letterId}/download")->assertNotFound();

        $this->adminSession(10, 'student_affairs_head', 'HEP Head')
            ->get('/admin/documents')
            ->assertOk();
    }

    public function test_admin_can_review_pending_document_and_rejection_requires_note(): void
    {
        $documentId = $this->insertDocument(1, 'Review Me', '1/review.pdf');
        $this->adminSession(10, 'student_affairs_head', 'HEP Head');

        $this->patch("/admin/documents/{$documentId}/review", ['status' => 'rejected'])
            ->assertSessionHasErrors('review_note');
        $this->patch("/admin/documents/{$documentId}/review", [
            'status' => 'approved',
            'review_note' => 'Verified',
        ])->assertRedirect('/admin/documents');

        $this->assertDatabaseHas('student_documents', [
            'id' => $documentId,
            'status' => 'approved',
            'reviewed_by' => 10,
            'review_note' => 'Verified',
        ]);
        $this->patch("/admin/documents/{$documentId}/review", ['status' => 'rejected', 'review_note' => 'Changed'])
            ->assertSessionHasErrors('document');
    }

    public function test_system_admin_can_disable_document_centre_for_students(): void
    {
        $this->adminSession(12, 'system_admin', 'System Admin')
            ->patch('/admin/features/document_centre', ['enabled' => 0])
            ->assertRedirect('/admin/features');

        $this->studentSession(1)->get('/student/documents')
            ->assertStatus(503)
            ->assertSee('currently unavailable');
    }

    public function test_scholarship_admin_can_download_contextual_offer_letter(): void
    {
        $documentId = $this->insertDocument(1, 'Scholarship Offer Letter', '1/offer.pdf', 'scholarship_status', 50);
        Storage::disk('student_documents')->put('1/offer.pdf', 'offer-content');

        $this->adminSession(13, 'scholarship_admin', 'Scholarship Officer')
            ->get("/admin/student-scholarship-status/documents/{$documentId}/download")
            ->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    private function studentSession(int $id): static
    {
        return $this->withSession(['auth_user' => ['id' => $id, 'role' => 'student', 'name' => $id === 1 ? 'First Student' : 'Second Student']]);
    }

    private function adminSession(int $id, string $role, string $name): static
    {
        return $this->withSession(['auth_user' => ['id' => $id, 'role' => 'admin', 'admin_role' => $role, 'name' => $name]]);
    }

    private function insertDocument(int $studentId, string $title, string $path, ?string $sourceType = null, ?int $sourceId = null, ?string $category = null): int
    {
        return DB::table('student_documents')->insertGetId([
            'student_id' => $studentId,
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'title' => $title,
            'category' => $category ?? ($sourceType === 'scholarship_status' ? 'scholarship' : 'letters'),
            'disk' => 'student_documents',
            'path' => $path,
            'original_name' => basename($path),
            'mime_type' => 'application/pdf',
            'size_bytes' => 100,
            'expiry_date' => null,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
