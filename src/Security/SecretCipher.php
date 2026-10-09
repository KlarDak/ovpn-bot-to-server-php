<?php

namespace CNS\BotToServer\Security;

final class SecretCipher
{
    private const CIPHER = 'aes-256-gcm';

    private function __construct() {}

    public static function encrypt(string $plaintext, string $key): string
    {
        $key = self::decodeKey($key);
        $iv = random_bytes(12);

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed.');
        }

        return base64_encode($iv . $tag . $ciphertext);
    }

    public static function decrypt(string $encrypted, string $key): string
    {
        $key = self::decodeKey($key);

        $data = base64_decode($encrypted, true);

        if ($data === false || strlen($data) < 28) {
            throw new \RuntimeException('Invalid encrypted data.');
        }

        $iv = substr($data, 0, 12);
        $tag = substr($data, 12, 16);
        $ciphertext = substr($data, 28);

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plaintext === false) {
            throw new \RuntimeException(
                'Decryption failed. Invalid key or corrupted data.'
            );
        }

        return $plaintext;
    }

    /**
     * Generates a random 256-bit encryption key encoded in Base64.
     */
    public static function generateKey(): string
    {
        return base64_encode(random_bytes(32));
    }

    /**
     * Decodes and validates a Base64-encoded encryption key.
     */
    public static function decodeKey(string $key): string
    {
        $decoded = base64_decode($key, true);

        if ($decoded === false || strlen($decoded) !== 32) {
            throw new \InvalidArgumentException(
                'Encryption key must be a Base64-encoded 32-byte key.'
            );
        }

        return $decoded;
    }
}