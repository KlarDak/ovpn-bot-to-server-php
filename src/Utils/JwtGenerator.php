<?php

namespace CNS\OvpnBotToServer\Utils;

use CNS\OvpnBotToServer\Exceptions\TokenException;
use CNS\OvpnBotToServer\Types\Env;
use CNS\OvpnBotToServer\Types\Payload;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtGenerator {
    /**
     * Token payload validation fields
     * 
     * @var array
     */
    public static array $payloadFields = ["sub", "aud", "iat", "exp", "role"];
    
    /**
     * Decode of token
     * 
     * @param string $token Authorization token
     * @param string $server_id Server identify
     * @return object|false
     * @throws TokenException
     */
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

    /**
     * Create token function
     * 
     * @param string $server_id Server idenfity
     * @param int $exp Token expiration date
     * @param string $role Sender role
     * @return string|false
     * @throws TokenException
     */
    public static function createToken(string $server_id, int $exp, string $role = "bot"): string|false {
        try {
            if (time() > $exp) {
                throw new TokenException("Error: the exp-field must be greater than TIME()");
            }

            if (!in_array($role, ["admin", "bot", "site", "user"], true)) {
                throw new TokenException("Error: unsupported token role");
            }

            $payload = self::payloadGenerator($server_id, $exp, $role);
            $secret_key = Env::getSecretKeyByID($server_id);
            return self::encodeToken($secret_key, $payload);
        }
        catch (TokenException $error) {
            return false;
        }
    }

    /**
     * Encoding token
     * 
     * @param string $secret_key Secret key of recipient server
     * @param array $payload Payload of toke
     * @return string
     */
    public static function encodeToken(string $secret_key, array $payload): string {
        return JWT::encode($payload, $secret_key, "HS256");
    }

    /**
     * Payload generator
     * 
     * @param string $aud_id Index of recipient server
     * @param int $exp Token expiration date
     * @param string $role Sender role
     * @return array
     */
    public static function payloadGenerator(string $aud_id, int $exp, string $role = "bot"): array {
        return [
            "sub" => Env::getSubIndex(),
            "aud" => Env::getIndexByID($aud_id),
            "iat" => time(),
            "exp" => $exp,
            "role" => $role
        ];
    }
}
