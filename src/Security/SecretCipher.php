<?php

namespace CNS\BotToServer\Security;

final class SecretCipher {
    private function __construct() {}

    public static function encrypt(
        string $secret,
        string $key
    ): string {
        $nonce = random_bytes(
            SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES
        );

        $encrypted = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt(
            $secret,
            '',
            $nonce,
            $key
        );

        return base64_encode($nonce . $encrypted);
    }

    public static function decrypt(
        string $encryptedSecret,
        string $key
    ): string {
        $data = base64_decode($encryptedSecret, true);

        if ($data === false) {
            throw new \RuntimeException('Invalid encrypted secret.');
        }

        $nonceSize =
            SODIUM_CRYPTO_AEAD_XCHACHA20POLY1305_IETF_NPUBBYTES;

        $nonce = substr($data, 0, $nonceSize);
        $ciphertext = substr($data, $nonceSize);

        $secret = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt(
            $ciphertext,
            '',
            $nonce,
            $key
        );

        if ($secret === false) {
            throw new \RuntimeException(
                'Failed to decrypt secret.'
            );
        }

        return $secret;
    }
}