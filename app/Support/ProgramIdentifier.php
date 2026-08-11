<?php

namespace App\Support;

class ProgramIdentifier
{
    private const COURSE_CODES = [
        'DF007' => 'DDT',
        'DF008' => 'DIT',
        'DF009' => 'DIT',
        'DV010' => 'DDC',
        'DV011' => 'DBF',
    ];

    public static function from(?string $matricNo, ?string $value): string
    {
        $matricNo = strtoupper(trim((string) $matricNo));
        if (preg_match('/(?:DIT|DDT|DDC|DBF)/', $matricNo, $matches)) {
            return $matches[0];
        }

        $value = strtoupper(trim((string) $value));

        if (preg_match('/(?:DIT|DDT|DDC|DBF)/', $value, $matches)) {
            return $matches[0];
        }

        return self::COURSE_CODES[$value] ?? $value;
    }
}
