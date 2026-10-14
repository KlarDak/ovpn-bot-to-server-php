<?php
declare(strict_types=1);

namespace CNS\BotToServer\Tests\Unit;

use CNS\BotToServer\Security\SecretCipher;
use CNS\BotToServer\Contracts\OpenVPN\Security\JWTCipher;
use Firebase\JWT\{JWT, ExpiredException, SignatureInvalidException};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SecurityTest extends TestCase
{
    #[DataProvider('plaintexts')]
    public function testEncryptionRoundTrip(string $text): void
    {
        $key = SecretCipher::generateKey();
        self::assertSame(32, strlen(SecretCipher::decodeKey($key)));
        $encrypted = SecretCipher::encrypt($text, $key);
        self::assertNotSame($text, $encrypted);
        self::assertSame($text, SecretCipher::decrypt($encrypted, $key));
        self::assertNotSame($encrypted, SecretCipher::encrypt($text, $key));
    }

    public static function plaintexts(): array { return [[''], ['secret'], ['Привет 🔐'], ["binary\0value"]]; }

    #[DataProvider('invalidKeys')]
    public function testInvalidKeyRejected(string $key): void
    {
        $this->expectException(\InvalidArgumentException::class);
        SecretCipher::decodeKey($key);
    }

    public static function invalidKeys(): array { return [['!invalid!'], [''], [base64_encode(str_repeat('a', 31))], [base64_encode(str_repeat('a', 33))]]; }

    #[DataProvider('invalidCiphertexts')]
    public function testInvalidCiphertextRejected(string $text): void
    {
        $this->expectException(\RuntimeException::class);
        SecretCipher::decrypt($text, base64_encode(str_repeat('k', 32)));
    }

    public static function invalidCiphertexts(): array { return [['!invalid!'], [''], [base64_encode(str_repeat('a', 27))]]; }

    public function testWrongKeyRejected(): void
    {
        $encrypted = SecretCipher::encrypt('secret', base64_encode(str_repeat('a', 32)));
        $this->expectException(\RuntimeException::class);
        SecretCipher::decrypt($encrypted, base64_encode(str_repeat('b', 32)));
    }

    public function testTamperedCiphertextRejected(): void
    {
        $key = base64_encode(str_repeat('a', 32));
        $bytes = base64_decode(SecretCipher::encrypt('secret', $key), true);
        $bytes[28] = chr(ord($bytes[28]) ^ 1);
        $this->expectException(\RuntimeException::class);
        SecretCipher::decrypt(base64_encode($bytes), $key);
    }

    public function testJwtClaimsAndLifetime(): void
    {
        $key = str_repeat('test-key', 8);
        $before = time();
        $payload = JWTCipher::decode(JWTCipher::encode('sender', 'node', 'admin', $key), $key);
        self::assertSame('sender', $payload->sub);
        self::assertSame('node', $payload->aud);
        self::assertSame('admin', $payload->role);
        self::assertGreaterThanOrEqual($before, $payload->iat);
        self::assertLessThanOrEqual(time(), $payload->iat);
        self::assertGreaterThanOrEqual($before + 12, $payload->exp);
        self::assertLessThanOrEqual(time() + 12, $payload->exp);
    }

    public function testJwtWrongSignatureRejected(): void
    {
        $token = JWTCipher::encode('sender', 'node', 'admin', str_repeat('a', 64));
        $this->expectException(SignatureInvalidException::class);
        JWTCipher::decode($token, str_repeat('b', 64));
    }

    public function testExpiredJwtRejectedWithoutSleeping(): void
    {
        $key = str_repeat('a', 64);
        $token = JWT::encode(['sub' => 'sender', 'iat' => time() - 120, 'exp' => time() - 60], $key, 'HS256');
        $this->expectException(ExpiredException::class);
        JWTCipher::decode($token, $key);
    }
}
