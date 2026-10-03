<?php

namespace Tests\Feature;

use App\Jobs\RunMyHepBackup;
use App\Models\BackupRun;
use App\Models\BackupSetting;
use App\Services\Backups\BackupArchiveName;
use App\Services\Backups\BackupArchiveRunner;
use App\Services\Backups\BackupCapacity;
use App\Services\Backups\BackupDispatcher;
use App\Services\Backups\BackupHealthService;
use App\Services\Backups\BackupSettings;
use App\Services\Backups\GoogleDriveConnection;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\Config\Config as BackupPackageConfig;
use Spatie\Backup\Tasks\Backup\BackupJob;
use Spatie\Backup\Tasks\Backup\DbDumperFactory;
use Spatie\DbDumper\Databases\MySql;
use Tests\TestCase;
use ZipArchive;

class MyHepBackupSystemTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('admins', function (Blueprint $table): void {
            $table->id();
            $table->string('full_name');
            $table->string('role');
            $table->timestamps();
        });

        DB::table('admins')->insert([
            ['id' => 1, 'full_name' => 'System Admin', 'role' => 'system_admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'full_name' => 'Discipline Admin', 'role' => 'discipline_admin', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('backup_runs', function (Blueprint $table): void {
            $table->id();
            $table->string('type', 16);
            $table->string('triggered_by', 16)->default('scheduler');
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('status', 16);
            $table->string('destination', 64)->nullable();
            $table->string('remote_path', 512)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });

        Schema::create('backup_settings', function (Blueprint $table): void {
            $table->id();
            $table->text('google_drive_client_id')->nullable();
            $table->text('google_drive_client_secret')->nullable();
            $table->text('google_drive_refresh_token')->nullable();
            $table->text('google_drive_folder_id')->nullable();
            $table->text('archive_password')->nullable();
            $table->string('notification_email')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_backup_dashboard_requires_a_system_admin(): void
    {
        $this->get('/admin/backups')->assertRedirect(route('login'));

        $this->withSession([
            'auth_user' => ['id' => 2, 'role' => 'admin', 'admin_role' => 'discipline_admin', 'name' => 'Discipline Admin'],
        ])->get('/admin/backups')->assertForbidden();

        $this->withSession([
            'auth_user' => ['id' => 1, 'role' => 'admin', 'admin_role' => 'system_admin', 'name' => 'System Admin'],
        ])->get('/admin/backups')
            ->assertOk()
            ->assertSee(__('backup.title'))
            ->assertSee(__('backup.health_title'))
            ->assertDontSee('GOOGLE_DRIVE_REFRESH_TOKEN')
            ->assertDontSee('BACKUP_ARCHIVE_PASSWORD');
    }

    public function test_manual_backup_requires_configuration_then_queues_for_a_system_admin(): void
    {
        $this->from('/admin/backups')->withSession([
            'auth_user' => ['id' => 1, 'role' => 'admin', 'admin_role' => 'system_admin', 'name' => 'System Admin'],
        ])->post('/admin/backups', ['type' => 'full'])
            ->assertRedirect('/admin/backups')
            ->assertSessionHas('error');

        $this->configureBackupSecrets();
        Storage::fake('google_drive');
        Bus::fake();

        $this->from('/admin/backups')->withSession([
            'auth_user' => ['id' => 1, 'role' => 'admin', 'admin_role' => 'system_admin', 'name' => 'System Admin'],
        ])->post('/admin/backups', ['type' => 'database'])
            ->assertRedirect('/admin/backups')
            ->assertSessionHas('success');

        Bus::assertDispatched(RunMyHepBackup::class);
        $this->assertDatabaseHas('backup_runs', [
            'type' => 'database',
            'triggered_by' => 'admin',
            'admin_id' => 1,
            'status' => 'queued',
        ]);
    }

    public function test_dispatcher_blocks_duplicate_backup_jobs(): void
    {
        $this->configureBackupSecrets();
        Bus::fake();

        $dispatcher = app(BackupDispatcher::class);
        $first = $dispatcher->enqueue('database', 'scheduler');
        $second = $dispatcher->enqueue('full', 'scheduler');

        $this->assertNull($first['reason']);
        $this->assertSame('already_running', $second['reason']);
        $this->assertDatabaseCount('backup_runs', 1);
        Bus::assertDispatchedTimes(RunMyHepBackup::class, 1);
    }

    public function test_backup_job_records_verified_remote_metadata_and_releases_its_lock(): void
    {
        $this->configureBackupSecrets();
        $disk = Storage::fake('google_drive');
        $run = BackupRun::query()->create([
            'type' => 'database', 'triggered_by' => 'admin', 'status' => 'queued', 'destination' => 'google_drive',
        ]);
        $lock = Cache::lock(config('myhep-backups.lock_name'), config('myhep-backups.lock_ttl_seconds'));
        $this->assertTrue($lock->get());

        $capacity = new class extends BackupCapacity
        {
            public function assertEnoughSpace(string $type): void {}
        };
        $runner = new class extends BackupArchiveRunner
        {
            public function run(string $type, string $filename): void
            {
                Storage::disk('google_drive')->put('Database/'.$filename, 'synthetic encrypted archive');
            }
        };

        (new RunMyHepBackup($run->id, $lock->owner()))
            ->handle(app(BackupSettings::class), $capacity, $runner);

        $run->refresh();
        $this->assertSame('successful', $run->status, (string) $run->error_message);
        $this->assertSame('google_drive', $run->destination);
        $this->assertStringStartsWith('Database/myhep-db-', $run->remote_path);
        $this->assertGreaterThan(0, $run->size_bytes);
        $this->assertTrue($disk->exists($run->remote_path));

        $nextLock = Cache::lock(config('myhep-backups.lock_name'), config('myhep-backups.lock_ttl_seconds'));
        $this->assertTrue($nextLock->get());
        $nextLock->release();
    }

    public function test_backup_job_records_a_sanitized_google_failure(): void
    {
        $this->configureBackupSecrets();
        Storage::fake('google_drive');
        $run = BackupRun::query()->create([
            'type' => 'database', 'triggered_by' => 'scheduler', 'status' => 'queued', 'destination' => 'google_drive',
        ]);
        $lock = Cache::lock(config('myhep-backups.lock_name'), config('myhep-backups.lock_ttl_seconds'));
        $this->assertTrue($lock->get());
        $capacity = new class extends BackupCapacity
        {
            public function assertEnoughSpace(string $type): void {}
        };
        $runner = new class extends BackupArchiveRunner
        {
            public function run(string $type, string $filename): void
            {
                throw new \RuntimeException('Google Drive rejected refresh_token: synthetic-refresh-token');
            }
        };

        (new RunMyHepBackup($run->id, $lock->owner()))
            ->handle(app(BackupSettings::class), $capacity, $runner);

        $run->refresh();
        $this->assertSame('failed', $run->status);
        $this->assertStringNotContainsString('synthetic-refresh-token', $run->error_message);
        $this->assertStringContainsString('[redacted]', $run->error_message);
    }

    public function test_backup_filename_is_unique_and_identifies_its_type(): void
    {
        Carbon::setTestNow('2026-10-01 14:00:00');

        $first = BackupArchiveName::make('database');
        $second = BackupArchiveName::make('database');

        $this->assertMatchesRegularExpression('/^myhep-db-20261001-140000-[a-z0-9]{10}\.zip$/', $first);
        $this->assertNotSame($first, $second);
        $this->assertStringStartsWith('myhep-full-', BackupArchiveName::make('full'));
    }

    public function test_google_drive_connection_reports_failure_without_leaking_credentials(): void
    {
        $this->configureBackupSecrets();
        Storage::shouldReceive('disk')
            ->once()
            ->with('google_drive')
            ->andThrow(new \RuntimeException('OAuth refresh_token: refresh-secret failed'));

        Log::shouldReceive('warning')->once();

        $status = app(GoogleDriveConnection::class)->status();

        $this->assertFalse($status['connected']);
        $this->assertStringNotContainsString('refresh-secret', $status['message']);
        $this->assertStringContainsString('[redacted]', $status['message']);
    }

    public function test_backup_health_detects_overdue_and_failed_runs(): void
    {
        $this->configureBackupSecrets();
        Carbon::setTestNow('2026-10-01 14:00:00');

        BackupRun::query()->create([
            'type' => 'database', 'triggered_by' => 'scheduler', 'status' => 'successful',
            'finished_at' => now()->subHours(3),
        ]);
        BackupRun::query()->create([
            'type' => 'full', 'triggered_by' => 'scheduler', 'status' => 'successful',
            'finished_at' => now()->subHours(30),
        ]);

        $health = app(BackupHealthService::class)->snapshot(true);
        $this->assertSame('overdue', $health['health']['key']);

        BackupRun::query()->create([
            'type' => 'database', 'triggered_by' => 'scheduler', 'status' => 'failed',
            'error_message' => 'Synthetic test failure', 'finished_at' => now(),
        ]);
        BackupRun::query()->create([
            'type' => 'full', 'triggered_by' => 'scheduler', 'status' => 'successful',
            'finished_at' => now(),
        ]);

        $health = app(BackupHealthService::class)->snapshot(true);
        $this->assertSame('failed', $health['health']['key']);
        $this->assertSame('Synthetic test failure', $health['failed_run']->error_message);

        BackupRun::query()->create([
            'type' => 'database', 'triggered_by' => 'scheduler', 'status' => 'successful',
            'finished_at' => now(),
        ]);
        $health = app(BackupHealthService::class)->snapshot(true);
        $this->assertSame('healthy', $health['health']['key']);
    }

    public function test_mysql_dump_configuration_uses_transactional_unlocked_dump_options(): void
    {
        config([
            'database.connections.mysql' => [
                'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => 3306,
                'database' => 'synthetic_test', 'username' => 'unused', 'password' => 'unused',
                'dump' => [
                    'dump_binary_path' => '', 'timeout' => 3600,
                    'use_single_transaction' => null, 'skip_lock_tables' => null,
                ],
            ],
        ]);

        $dumper = DbDumperFactory::createFromConnection('mysql');
        $this->assertInstanceOf(MySql::class, $dumper);

        $this->assertTrue($this->protectedProperty($dumper, 'useSingleTransaction'));
        $this->assertTrue($this->protectedProperty($dumper, 'skipLockTables'));
        $this->assertSame(3600, $this->protectedProperty($dumper, 'timeout'));
    }

    public function test_synthetic_database_dump_is_encrypted_and_uploaded_to_fake_drive(): void
    {
        $this->configureBackupSecrets();
        $disk = Storage::fake('google_drive');
        $fixtureRoot = storage_path('framework/testing/myhep-backup-fixture');
        $temporaryRoot = storage_path('framework/testing/myhep-backup-temp');
        File::deleteDirectory($fixtureRoot);
        File::deleteDirectory($temporaryRoot);
        File::ensureDirectoryExists($fixtureRoot);
        File::ensureDirectoryExists($temporaryRoot);
        File::put($fixtureRoot.'/synthetic-upload.txt', 'synthetic upload fixture; no student data');

        $packageArray = config('backup');
        $packageArray['backup']['name'] = 'Full';
        $packageArray['backup']['source']['files']['include'] = [$fixtureRoot];
        $packageArray['backup']['source']['files']['exclude'] = [];
        $packageArray['backup']['source']['databases'] = ['mysql'];
        $packageArray['backup']['temporary_directory'] = $temporaryRoot;
        $packageArray['backup']['password'] = 'synthetic-test-only-password-with-enough-length';
        $packageArray['backup']['encryption'] = 'aes256';
        $packageArray['backup']['verify_backup'] = true;

        $databasePackageArray = $packageArray;
        $databasePackageArray['backup']['name'] = 'Database';
        $databasePackageArray['backup']['source']['files']['include'] = [];
        $databasePackageArray['backup']['source']['files']['exclude'] = [];
        config([
            'backup' => $packageArray,
            'backup_database' => $databasePackageArray,
        ]);

        $syntheticDumper = new class extends MySql
        {
            public function dumpToFile(string $dumpFile): void
            {
                File::ensureDirectoryExists(dirname($dumpFile));
                file_put_contents($dumpFile, "CREATE TABLE synthetic_students (id INTEGER);\nINSERT INTO synthetic_students VALUES (1);\n");
            }
        };
        $syntheticDumper->setDbName('synthetic_myhep');

        $runner = new class($syntheticDumper) extends BackupArchiveRunner
        {
            public function __construct(private readonly MySql $syntheticDumper) {}

            protected function makeBackupJob(BackupPackageConfig $config): BackupJob
            {
                return parent::makeBackupJob($config)
                    ->setDbDumpers(collect(['mysql' => $this->syntheticDumper]));
            }
        };

        try {
            $runner->run('database', 'myhep-test-encrypted.zip');

            $archivePath = 'Database/myhep-test-encrypted.zip';
            $this->assertTrue($disk->exists($archivePath));
            $this->assertGreaterThan(0, $disk->size($archivePath));

            $zip = new ZipArchive;
            $this->assertTrue($zip->open($disk->path($archivePath)));
            $sqlEntry = null;
            foreach (range(0, $zip->numFiles - 1) as $index) {
                $name = $zip->getNameIndex($index);
                if (is_string($name) && str_ends_with($name, '.sql')) {
                    $sqlEntry = $name;
                    break;
                }
            }
            $this->assertNotNull($sqlEntry, 'The archive should contain the synthetic SQL dump.');
            $metadata = $zip->statName($sqlEntry);
            $this->assertSame(ZipArchive::EM_AES_256, $metadata['encryption_method'] ?? null);
            $this->assertTrue($zip->setPassword('synthetic-test-only-password-with-enough-length'));
            $stream = $zip->getStream($sqlEntry);
            $this->assertIsResource($stream);
            $this->assertStringContainsString('synthetic_students', stream_get_contents($stream));
            fclose($stream);
            $zip->close();
        } finally {
            File::deleteDirectory($fixtureRoot);
            File::deleteDirectory($temporaryRoot);
        }
    }

    public function test_cleanup_retention_never_removes_the_newest_recovery_point(): void
    {
        $this->configureBackupSecrets();
        $disk = Storage::fake('google_drive');
        Carbon::setTestNow('2026-10-01 14:00:00');

        $disk->put('Database/2026-09-20-01-00-00.zip', 'old same-day archive');
        $disk->put('Database/2026-09-20-02-00-00.zip', 'newer same-day archive');
        $disk->put('Database/2026-10-01-14-00-00.zip', 'newest archive');

        $this->artisan('myhep:backup-clean')->assertExitCode(0);

        $this->assertFalse($disk->exists('Database/2026-09-20-01-00-00.zip'));
        $this->assertTrue($disk->exists('Database/2026-09-20-02-00-00.zip'));
        $this->assertTrue($disk->exists('Database/2026-10-01-14-00-00.zip'));
    }

    private function configureBackupSecrets(): void
    {
        BackupSetting::query()->updateOrCreate(['id' => 1], [
            'google_drive_client_id' => 'synthetic-client-id',
            'google_drive_client_secret' => 'synthetic-client-secret',
            'google_drive_refresh_token' => 'synthetic-refresh-token',
            'google_drive_folder_id' => 'synthetic-folder-id',
            'archive_password' => 'synthetic-archive-password-for-testing-only-2026',
        ]);
        app(BackupSettings::class)->applyStoredConfig();
    }

    private function protectedProperty(object $object, string $property): mixed
    {
        $reflection = new \ReflectionObject($object);
        while (! $reflection->hasProperty($property) && ($reflection = $reflection->getParentClass())) {
            // Search parent dumper properties.
        }

        return $reflection->getProperty($property)->getValue($object);
    }
}
