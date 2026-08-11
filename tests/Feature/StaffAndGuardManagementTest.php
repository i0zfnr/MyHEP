<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StaffAndGuardManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('ic_no')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password');
            $table->string('role');
            $table->string('staff_category')->nullable();
            $table->string('staff_department')->nullable();
            $table->string('position')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('photo')->nullable();
            $table->timestamps();
        });
        Schema::create('lecturer_page_access', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('page_key');
            $table->boolean('enabled')->default(true);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->unique(['admin_id', 'page_key']);
        });

        DB::table('admins')->insert([
            $this->account(1, 'System Admin', 'system_admin'),
            $this->account(2, 'Head Student Affairs', 'student_affairs_head'),
            $this->account(3, 'Discipline Admin', 'discipline_admin'),
            $this->account(4, 'Cik Lan', 'lecturer', 'discipline'),
            $this->account(5, 'Scholarship Lecturer', 'lecturer', 'scholarship'),
        ]);
        DB::table('lecturer_page_access')->insert([
            'admin_id' => 4, 'page_key' => 'guard_management', 'enabled' => true,
            'updated_by' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_head_and_system_admin_can_manage_staff_but_only_system_admin_can_manage_admins(): void
    {
        $this->signIn(2, 'student_affairs_head')->get('/admin/staff')->assertOk()->assertSee('Staff Management');
        $this->signIn(1, 'system_admin')->get('/admin/staff')->assertOk();
        $this->signIn(2, 'student_affairs_head')->get('/admin/admin-users')->assertForbidden();
        $this->signIn(1, 'system_admin')->get('/admin/admin-users')->assertOk();
    }

    public function test_head_can_create_discipline_staff_with_selected_page_access(): void
    {
        $response = $this->signIn(2, 'student_affairs_head')->post('/admin/staff', [
            'full_name' => 'New Discipline Lecturer', 'ic_no' => '900101-01-5555',
            'email' => 'discipline@example.test', 'staff_category' => 'discipline',
            'is_active' => '1', 'password' => 'SecurePass123',
            'lecturer_pages' => ['offense_register', 'guard_management'],
        ]);

        $response->assertRedirect('/admin/staff');
        $id = (int) DB::table('admins')->where('ic_no', '900101015555')->value('id');
        $this->assertSame('lecturer', DB::table('admins')->where('id', $id)->value('role'));
        $this->assertSame('discipline', DB::table('admins')->where('id', $id)->value('staff_category'));
        $this->assertDatabaseHas('lecturer_page_access', ['admin_id' => $id, 'page_key' => 'guard_management', 'enabled' => 1]);
    }

    public function test_system_admin_can_create_an_admin_with_a_formatted_new_nric(): void
    {
        $this->signIn(1, 'system_admin')->post('/admin/admin-users', [
            'full_name' => 'New System Guard',
            'ic_no' => '920101-01-1234',
            'email' => 'new.guard@example.test',
            'role' => 'guard',
            'password' => 'SecurePass123',
        ])->assertRedirect('/admin/admin-users');

        $this->assertDatabaseHas('admins', [
            'full_name' => 'New System Guard',
            'ic_no' => '920101011234',
            'email' => 'new.guard@example.test',
            'role' => 'guard',
        ]);
    }

    public function test_canonical_duplicate_nric_is_rejected_for_another_account(): void
    {
        DB::table('admins')->insert(array_replace($this->account(9, 'Existing NRIC', 'guard'), ['ic_no' => '900101011234']));

        $this->signIn(1, 'system_admin')->post('/admin/admin-users', [
            'full_name' => 'Duplicate Account',
            'ic_no' => '900101-01-1234',
            'email' => 'duplicate@example.test',
            'role' => 'guard',
            'password' => 'SecurePass123',
        ])->assertSessionHasErrors('ic_no');
    }

    public function test_guard_management_is_available_to_authorized_operational_roles_only(): void
    {
        $this->signIn(1, 'system_admin')->get('/admin/guards')->assertOk();
        $this->signIn(2, 'student_affairs_head')->get('/admin/guards')->assertOk();
        $this->signIn(3, 'discipline_admin')->get('/admin/guards')->assertOk();
        $this->signIn(4, 'lecturer', 'discipline')->get('/admin/guards')->assertOk();
        $this->signIn(5, 'lecturer', 'scholarship')->get('/admin/guards')->assertForbidden();
    }

    public function test_discipline_lecturer_can_create_guard_when_individually_authorized(): void
    {
        $this->signIn(4, 'lecturer', 'discipline')->post('/admin/guards', [
            'full_name' => 'Night Guard', 'ic_no' => '880101015555', 'email' => 'night.guard@example.test',
            'is_active' => '1', 'password' => 'GuardPass123',
        ])->assertRedirect('/admin/guards');

        $this->assertDatabaseHas('admins', ['ic_no' => '880101015555', 'role' => 'guard', 'is_active' => 1]);
    }

    public function test_inactive_staff_cannot_login_with_shared_admin_login(): void
    {
        DB::table('admins')->where('id', 4)->update(['is_active' => false]);

        $this->post('/login', ['role' => 'admin', 'username' => 'user4@example.test', 'password' => 'Password123'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('username');
        $this->assertFalse(session()->has('auth_user'));
    }

    public function test_admin_logs_in_with_email_and_default_nric_password(): void
    {
        DB::table('admins')->where('id', 1)->update(['password' => Hash::make('IC1')]);

        $this->post('/login', [
            'role' => 'admin',
            'username' => 'USER1@EXAMPLE.TEST',
            'password' => 'IC1',
        ])->assertRedirect('/admin/dashboard');

        $this->assertSame(1, session('auth_user.id'));
        $this->assertSame('admin', session('auth_user.role'));
    }

    public function test_admin_cannot_use_nric_as_the_login_identifier(): void
    {
        DB::table('admins')->where('id', 1)->update(['password' => Hash::make('IC1')]);

        $this->post('/login', [
            'role' => 'admin',
            'username' => 'IC1',
            'password' => 'IC1',
        ])->assertRedirect('/login')->assertSessionHasErrors('username');

        $this->assertFalse(session()->has('auth_user'));
    }

    public function test_lecturer_mobile_shell_contains_staff_bottom_navigation(): void
    {
        $this->signIn(4, 'lecturer', 'discipline')->get('/admin/guards')
            ->assertOk()
            ->assertSee('aria-label="Staff mobile navigation"', false)
            ->assertSee('Scan QR')
            ->assertSee('student-bottom-nav-eligible', false);
    }

    public function test_staff_csv_import_creates_accounts_in_the_matching_departments(): void
    {
        $csv = "Bahagian,Nama,No IC,Jawatan,Email\nJTMK,Nur Aina,900101-01-1001,Pensyarah,nur.aina@example.test\nUnit Pengurusan Kewangan,Siti Aminah,900202-02-2002,Pegawai Kewangan,siti.aminah@example.test\n";
        $file = UploadedFile::fake()->createWithContent('staff.csv', $csv);

        $this->signIn(1, 'system_admin')->post('/admin/staff/import', ['staff_file' => $file])
            ->assertRedirect('/admin/staff')
            ->assertSessionHas('success', 'Staff import completed: 2 created, 0 updated, 0 skipped.');

        $this->assertDatabaseHas('admins', [
            'full_name' => 'NUR AINA', 'ic_no' => '900101011001', 'role' => 'lecturer',
            'staff_department' => 'jtmk', 'position' => 'Pensyarah',
        ]);
        $this->assertDatabaseHas('admins', [
            'full_name' => 'SITI AMINAH', 'staff_department' => 'unit_pengurusan_kewangan',
        ]);
    }

    public function test_official_style_xlsx_department_headings_place_staff_in_the_correct_sections(): void
    {
        $file = $this->officialStyleStaffWorkbook();

        $this->signIn(1, 'system_admin')->post('/admin/staff/import', ['staff_file' => $file])
            ->assertRedirect('/admin/staff')
            ->assertSessionHas('success', 'Staff import completed: 2 created, 0 updated, 0 skipped.');

        $this->assertDatabaseHas('admins', [
            'full_name' => 'ALI BIN AHMAD', 'staff_department' => 'pejabat_pengarah', 'position' => 'Pengarah',
        ]);
        $this->assertDatabaseHas('admins', [
            'full_name' => 'LIM MEI LING', 'staff_department' => 'jrkv', 'position' => 'Pensyarah',
        ]);
    }

    private function signIn(int $id, string $role, ?string $category = null): static
    {
        return $this->withSession(['auth_user' => ['id' => $id, 'role' => 'admin', 'admin_role' => $role, 'staff_category' => $category, 'name' => "Account {$id}"]]);
    }

    private function account(int $id, string $name, string $role, ?string $category = null): array
    {
        return ['id' => $id, 'full_name' => $name, 'ic_no' => "IC{$id}", 'email' => "user{$id}@example.test",
            'password' => Hash::make('Password123'), 'role' => $role, 'staff_category' => $category,
            'is_active' => true, 'photo' => null, 'created_at' => now(), 'updated_at' => now()];
    }

    private function officialStyleStaffWorkbook(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'staff-xlsx-');
        $zip = new \ZipArchive();
        $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Staff" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>');
        $zip->addFromString('xl/worksheets/sheet1.xml', '<?xml version="1.0"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetData><row r="1"><c r="A1" t="inlineStr"><is><t>BIL</t></is></c><c r="B1" t="inlineStr"><is><t>NAMA</t></is></c><c r="E1" t="inlineStr"><is><t>JAWATAN</t></is></c><c r="F1" t="inlineStr"><is><t>NO IC</t></is></c><c r="G1" t="inlineStr"><is><t>EMAIL</t></is></c></row><row r="2"><c r="A2" t="inlineStr"><is><t>PEJABAT PENGARAH</t></is></c></row><row r="3"><c r="A3"><v>1</v></c><c r="B3" t="inlineStr"><is><t>Ali Bin Ahmad</t></is></c><c r="E3" t="inlineStr"><is><t>Pengarah</t></is></c><c r="F3"><v>800101011111</v></c><c r="G3" t="inlineStr"><is><t>ali@example.test</t></is></c></row><row r="4"><c r="A4" t="inlineStr"><is><t>JABATAN REKA BENTUK &amp; KOMUNIKASI VISUAL (JRKV)</t></is></c></row><row r="5"><c r="A5"><v>2</v></c><c r="B5" t="inlineStr"><is><t>Lim Mei Ling</t></is></c><c r="E5" t="inlineStr"><is><t>Pensyarah</t></is></c><c r="F5"><v>810202022222</v></c><c r="G5" t="inlineStr"><is><t>lim@example.test</t></is></c></row></sheetData></worksheet>');
        $zip->close();

        return new UploadedFile($path, 'staf-polibesut.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);
    }
}
