<?php

namespace CNS\OvpnBotToServer\Utils;

use CNS\OvpnBotToServer\Exceptions\TokenException;
use CNS\OvpnBotToServer\Types\Env;
use CNS\OvpnBotToServer\Types\Payload;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtGenerator {
    public static array $payloadFields = ["sub", "aud", "iat", "exp", "role", "type"];
    
    public static function decodeToken(string $token, string $server_id) : object|false {
        try {
            $server_key = Env::getSecretKeyByID($server_id);

            $decode = JWT::decode($token, new Key($server_key, "HS256"));

            return $decode;
        }
        catch (TokenException $error) {
            return false;
        }
    } 

    public static function createToken(string $server_id, int $exp, string $type, string $role) {
        try {
            if (time() > $exp) {
                throw new TokenException("Error: the exp-field must be greater than TIME()");
            }

            $payload = self::payloadGenerator($server_id, $exp, $type, $role);
            $secret_key = Env::getSecretKeyByID($server_id);
            return self::encodeToken($secret_key, $payload);
        }
        catch (TokenException $error) {
            return false;
        }
    }

    public static function encodeToken(string $secret_key, array $payload) {
        return JWT::encode($payload, $secret_key, "HS256");
    }

    public static function payloadGenerator(string $aud_id, int $exp, string $type, string $role) {
        return [
            "sub" => Env::getSubIndex(),
            "aud" => Env::getIndexByID($aud_id),
            "iat" => time(),
            "exp" => $exp,
            "type" => $type,
            "role" => $role
        ];
    }
}