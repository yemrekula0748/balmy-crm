<?php

namespace Tests\Unit;

use App\Models\User;
use Tests\TestCase;

class UserIdentityTest extends TestCase
{
    /** @dataProvider phoneProvider */
    public function test_it_normalizes_supported_turkish_phone_formats(string $input): void
    {
        $this->assertSame('905321234567', User::normalizeTurkishPhone($input));
    }

    public static function phoneProvider(): array
    {
        return [
            'leading zero' => ['0532 123 45 67'],
            'local number' => ['5321234567'],
            'country code' => ['+90 532 123 45 67'],
            'short country prefix' => ['+9 532 123 45 67'],
            'double zero prefix' => ['0090 532 123 45 67'],
        ];
    }

    public function test_it_rejects_invalid_identity_and_phone_values(): void
    {
        $this->assertNull(User::normalizeIdentityNumber('123'));
        $this->assertNull(User::normalizeTurkishPhone('12345'));
        $this->assertSame('12345678901', User::normalizeIdentityNumber('123 456 789 01'));
    }

    public function test_identity_number_is_hashed_deterministically_without_storing_the_raw_value(): void
    {
        config()->set('app.key', 'base64:' . base64_encode('test-application-key-32-bytes!'));

        $hash = User::identityHash('12345678901');

        $this->assertSame(64, strlen($hash));
        $this->assertSame($hash, User::identityHash('12345678901'));
        $this->assertStringNotContainsString('12345678901', $hash);
    }
}
