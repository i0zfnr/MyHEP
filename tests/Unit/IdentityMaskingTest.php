<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class IdentityMaskingTest extends TestCase
{
    #[DataProvider('identityNumbers')]
    public function test_it_masks_identity_numbers(?string $identity, string $expected): void
    {
        $this->assertSame($expected, maskIdentityNumber($identity));
    }

    public static function identityNumbers(): array
    {
        return [
            'null' => [null, '-'],
            'empty' => ['', '-'],
            'whitespace' => ['   ', '-'],
            'standard nric' => ['800101010101', '********0101'],
            'formatted nric' => ['800101-01-0101', '**********0101'],
            'trimmed nric' => [' 800101010101 ', '********0101'],
            'four characters' => ['1234', '****'],
            'short value' => ['12', '**'],
        ];
    }
}
