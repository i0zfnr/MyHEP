<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('ic_no');
            $table->string('role');
            $table->string('photo')->nullable();
            $table->timestamps();
        });

        DB::table('admins')->insert([
            'id' => 1,
            'full_name' => 'Discipline Lecturer',
            'ic_no' => '800101010101',
            'role' => 'discipline_admin',
            'photo' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_every_admin_role_can_open_its_own_profile(): void
    {
        $this->withSession([
            'auth_user' => [
                'id' => 1,
                'role' => 'admin',
                'admin_role' => 'discipline_admin',
                'name' => 'Discipline Lecturer',
            ],
        ])->get('/admin/profile')
            ->assertOk()
            ->assertSee('Discipline Lecturer')
            ->assertSee('800101010101');
    }

    public function test_admin_photo_is_stored_in_the_admin_profile_folder(): void
    {
        Storage::fake('public');

        $response = $this->withSession([
            'auth_user' => [
                'id' => 1,
                'role' => 'admin',
                'admin_role' => 'discipline_admin',
                'name' => 'Discipline Lecturer',
            ],
        ])->post('/admin/profile/photo', [
            'profile_photo' => UploadedFile::fake()->createWithContent(
                'lecturer.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='),
            ),
        ]);

        $response->assertRedirect('/admin/profile');

        $photoPath = DB::table('admins')->where('id', 1)->value('photo');
        $this->assertStringStartsWith('admins/profile_photos/', $photoPath);
        Storage::disk('public')->assertExists($photoPath);
    }
}
