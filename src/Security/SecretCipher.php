<?php

namespace CNS\BotToServer\Security;

/**
 * Provides authenticated encryption and decryption using AES-256-GCM.
 *
 * Uses a randomly generated 12-byte initialization vector (IV)
 * and a 16-byte authentication tag for each encryption operation.
 *
 * Encrypted data is encoded in Base64 using the following structure:
 * IV (12 bytes) + authentication tag (16 bytes) + ciphertext.
 *
 * Encryption keys must be 256-bit (32-byte) values encoded in Base64.
 */
final class SecretCipher
{
    /**
     * OpenSSL cipher algorithm used for encryption and decryption.
     */
    private const CIPHER = 'aes-256-gcm';

    /**
     * Prevents instantiation of this utility class.
     */
    private function __construct() {}

    /**
     * Encrypts plaintext using AES-256-GCM.
     *
     * Generates a random initialization vector and returns
     * the encrypted payload encoded in Base64.
     *
     * @param string $plaintext Data to encrypt.
     * @param string $key Base64-encoded 256-bit encryption key.
     *
     * @return string Base64-encoded encrypted payload.
     *
     * @throws \InvalidArgumentException If the encryption key is invalid.
     * @throws \RuntimeException If encryption fails.
     * @throws \Random\RandomException If secure random byte generation fails.
     */
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

    /**
     * Decrypts an AES-256-GCM encrypted payload.
     *
     * Extracts the initialization vector, authentication tag,
     * and ciphertext from the Base64-encoded payload.
     *
     * Authentication ensures that modified data or an incorrect
     * encryption key causes decryption to fail.
     *
     * @param string $encrypted Base64-encoded encrypted payload.
     * @param string $key Base64-encoded 256-bit encryption key.
     *
     * @return string Decrypted plaintext.
     *
     * @throws \InvalidArgumentException If the encryption key is invalid.
     * @throws \RuntimeException If the payload is invalid or decryption fails.
     */
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
     * Generates a cryptographically secure 256-bit encryption key.
     *
     * @return string Base64-encoded 32-byte encryption key.
     *
     * @throws \Random\RandomException If secure random byte generation fails.
     */
    public static function generateKey(): string
    {
        return base64_encode(random_bytes(32));
    }

    /**
     * Decodes and validates a Base64-encoded encryption key.
     *
     * The decoded key must contain exactly 32 bytes (256 bits).
     *
     * @param string $key Base64-encoded encryption key.
     *
     * @return string Raw 32-byte encryption key.
     *
     * @throws \InvalidArgumentException If the key is invalid or has an incorrect length.
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