<?php

namespace CNS\BotToServer\Node\OpenVPN\Security;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

final class JWTCipher {
    private static array $payloadFields = ["sub", "aud", "iat", "exp", "role"];

    function __construct() {}

    public static function decode(string $token, string $decrypt_secret_code) : object {
        return JWT::decode($token, new Key($decrypt_secret_code, "HS256"));
    } 

    public static function encode(string $sub_code, string $aud_code, string $role, string $decrypt_secret_code): string {
        $payload = self::payloadGenerator($sub_code, $aud_code, $role);

        return JWT::encode($payload, $decrypt_secret_code, "HS256");
    }

    private static function payloadGenerator(string $sub_code, string $aud_code, string $role): array {
        return [
            "sub" => $sub_code,
            "aud" => $aud_code,
            "iat" => time(),
            "exp" => time() + 12,
            "role" => $role
        ];
    }
}