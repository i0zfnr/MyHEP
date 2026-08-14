<?php

namespace App\Support;

class ProgramIdentifier
{
    public const LABELS = [
        'DIT' => 'Diploma Teknologi Maklumat',
        'DDT' => 'Diploma Teknologi Maklumat (Teknologi Digital)',
        'DDC' => 'Diploma Rekabentuk Kraf',
        'DBF' => 'Diploma Rekabentuk Fesyen Batik',
    ];

    private const COURSE_CODES = [
        'DF007' => 'DDT',
        'DF008' => 'DIT',
        'DF009' => 'DIT',
        'DV010' => 'DDC',
        'DV011' => 'DBF',
    ];

    private const PROGRAM_NAMES = [
        'DIPLOMA TEKNOLOGI MAKLUMAT' => 'DIT',
        'DIPLOMA IN INFORMATION TECHNOLOGY' => 'DIT',
        'DIPLOMA TEKNOLOGI MAKLUMAT (TEKNOLOGI DIGITAL)' => 'DDT',
        'DIPLOMA IN INFORMATION TECHNOLOGY (DIGITAL TECHNOLOGY)' => 'DDT',
        'DIPLOMA REKA BENTUK KRAF' => 'DDC',
        'DIPLOMA REKABENTUK KRAF' => 'DDC',
        'DIPLOMA REKA BENTUK FESYEN BATIK' => 'DBF',
        'DIPLOMA REKABENTUK FESYEN BATIK' => 'DBF',
    ];

    public static function from(?string $matricNo, ?string $value): string
    {
        $matricNo = strtoupper(trim((string) $matricNo));
        if (preg_match('/(?:DIT|DDT|DDC|DBF)/', $matricNo, $matches)) {
            return $matches[0];
        }

        $value = strtoupper(trim((string) $value));

        if (isset(self::PROGRAM_NAMES[$value])) {
            return self::PROGRAM_NAMES[$value];
        }

        if (preg_match('/(?:DIT|DDT|DDC|DBF)/', $value, $matches)) {
            return $matches[0];
        }

        return self::COURSE_CODES[$value] ?? $value;
    }

    public static function label(?string $value): string
    {
        $identifier = self::from(null, $value);

        return self::LABELS[$identifier] ?? $identifier;
    }
}
