<?php

namespace App\Services\Backups;

use Illuminate\Support\Facades\DB;
use RuntimeException;
use SplFileInfo;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use FilesystemIterator;

class BackupCapacity
{
    public function assertEnoughSpace(string $type): void
    {
        $freeBytes = disk_free_space(storage_path('app'));
        if ($freeBytes === false) {
            throw new RuntimeException('Unable to determine free space for the temporary backup archive.');
        }

        $databaseBytes = $this->estimateDatabaseSize();
        $fileBytes = $type === 'full' ? $this->estimateFullFileSize() : 0;
        $requiredBytes = (int) ceil(($databaseBytes + $fileBytes) * 2.0)
            + ((int) config('myhep-backups.minimum_free_disk_mb', 512) * 1024 * 1024);

        if ($freeBytes < $requiredBytes) {
            $requiredMb = (int) ceil($requiredBytes / 1024 / 1024);
            $availableMb = (int) floor($freeBytes / 1024 / 1024);
            throw new RuntimeException("Insufficient free disk space for a safe backup (need about {$requiredMb} MB; {$availableMb} MB available).");
        }
    }

    private function estimateDatabaseSize(): int
    {
        $connection = DB::connection();
        if (! in_array($connection->getDriverName(), ['mysql', 'mariadb'], true)) {
            throw new RuntimeException('Automatic backups require a MySQL or MariaDB connection.');
        }

        $databaseName = $connection->getDatabaseName();
        $result = $connection->selectOne(
            'SELECT COALESCE(SUM(data_length + index_length), 0) AS total_bytes FROM information_schema.tables WHERE table_schema = ?',
            [$databaseName],
        );

        return max(0, (int) ($result->total_bytes ?? 0));
    }

    private function estimateFullFileSize(): int
    {
        $roots = [
            storage_path('app/private'),
            storage_path('app/public'),
            storage_path('app/certificate-templates'),
        ];
        $excluded = array_map('strtolower', [
            storage_path('app/private/backups'),
            storage_path('app/private/docx-template-qa'),
            storage_path('app/private/report-template-qa'),
        ]);

        $total = 0;
        foreach ($roots as $root) {
            if (! is_dir($root)) {
                continue;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY,
            );

            foreach ($iterator as $file) {
                if (! $file instanceof SplFileInfo || ! $file->isFile()) {
                    continue;
                }

                $path = strtolower($file->getPathname());
                foreach ($excluded as $excludedPath) {
                    if ($path === $excludedPath || str_starts_with($path, rtrim($excludedPath, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR)) {
                        continue 2;
                    }
                }

                $total += max(0, $file->getSize());
            }
        }

        return $total;
    }
}
