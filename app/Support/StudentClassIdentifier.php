<?php

namespace App\Support;

class StudentClassIdentifier
{
    public static function normalize(?string $value): ?string
    {
        $className = strtoupper(preg_replace('/\s+/', '', trim((string) $value)));

        return $className !== '' ? $className : null;
    }

    public static function semester(?string $value): ?string
    {
        $className = self::normalize($value);

        if ($className && preg_match('/[A-Z]+(\d+)/', $className, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
