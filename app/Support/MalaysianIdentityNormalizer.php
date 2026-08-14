<?php

namespace App\Support;

use Illuminate\Support\Str;

class MalaysianIdentityNormalizer
{
    public static function ic(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $scientificValue = str_replace([',', ' '], '', $value);
        if (preg_match('/^\d+\.\d+[eE][+-]?\d+$/', $scientificValue) === 1) {
            $value = number_format((float) $scientificValue, 0, '.', '');
        }

        $identity = Str::upper(preg_replace('/[^A-Za-z0-9]/', '', $value) ?? $value);

        // Repair values produced by the old XLSX importer from cells such as
        // 7.0425102015E10, which it previously stored as 70425102015E10.
        if (preg_match('/^(\d{11})E10$/', $identity, $matches) === 1) {
            $identity = $matches[1];
        }

        // Excel removes the leading zero when a Malaysian IC is stored as a number.
        if (ctype_digit($identity) && strlen($identity) === 11) {
            $identity = '0'.$identity;
        }

        return $identity;
    }
}
