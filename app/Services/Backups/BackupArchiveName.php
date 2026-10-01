<?php

namespace App\Services\Backups;

use Illuminate\Support\Str;
use InvalidArgumentException;

class BackupArchiveName
{
    public static function make(string $type): string
    {
        $prefix = match ($type) {
            'database' => 'db',
            'full' => 'full',
            default => throw new InvalidArgumentException('Unsupported backup type.'),
        };

        return sprintf(
            'myhep-%s-%s-%s.zip',
            $prefix,
            now()->format('Ymd-His'),
            Str::lower(Str::random(10)),
        );
    }
}
