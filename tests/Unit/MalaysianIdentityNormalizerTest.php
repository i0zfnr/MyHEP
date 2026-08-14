<?php

namespace Tests\Unit;

use App\Support\MalaysianIdentityNormalizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class MalaysianIdentityNormalizerTest extends TestCase
{
    #[DataProvider('icValues')]
    public function test_it_normalizes_excel_and_existing_ic_values(string $input, string $expected): void
    {
        $this->assertSame($expected, MalaysianIdentityNormalizer::ic($input));
    }

    public static function icValues(): array
    {
        return [
            'formatted IC' => ['070425-10-2015', '070425102015'],
            'Excel scientific notation' => ['7.0425102015E10', '070425102015'],
            'Excel value without leading zero' => ['70425102015', '070425102015'],
            'value saved by old importer' => ['70425102015E10', '070425102015'],
            'ordinary 12 digit IC' => ['990101011234', '990101011234'],
        ];
    }
}
