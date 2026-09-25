<?php

namespace Tests\Unit;

use App\Support\DynamicQrToken;
use Tests\TestCase;

class DynamicQrTokenTest extends TestCase
{
    public function test_movement_checkpoint_qr_defaults_to_fifteen_second_rotation(): void
    {
        config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

        $token = DynamicQrToken::generateForCheckpoint(12);

        $this->assertSame(15, $token['expires_in']);
        $this->assertTrue(DynamicQrToken::verifyForCheckpoint($token['token'], 12));
        $this->assertFalse(DynamicQrToken::verifyForCheckpoint($token['token'], 13));
    }

    public function test_program_attendance_qr_keeps_its_thirty_second_rotation(): void
    {
        config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);

        $token = DynamicQrToken::generate(12);

        $this->assertSame(30, $token['expires_in']);
        $this->assertTrue(DynamicQrToken::verify($token['token'], 12));
    }

    public function test_movement_checkpoint_qr_expires_after_rotation_and_grace_window(): void
    {
        config(['app.key' => 'base64:'.base64_encode(str_repeat('k', 32))]);
        $payload = rtrim(strtr(base64_encode(json_encode([
            'cid' => 12,
            't' => time() - 31,
            'n' => 'oldtoken',
        ], JSON_UNESCAPED_SLASHES)), '+/', '-_'), '=');
        $token = $payload.'.'.hash_hmac('sha256', $payload, (string) config('app.key'));

        $this->assertFalse(DynamicQrToken::verifyForCheckpoint($token, 12));
    }
}
