<?php

namespace CNS\OvpnBotToServer\Types;

use CNS\OvpnBotToServer\Exceptions\AppException;
use RuntimeException;

class Env
{
    public static function get(string $key) : string {
        $value = $_ENV[$key];

        if ($value === null || $value === '') {
            throw new AppException("An error with value of this keys: $key");
        }
        return $value;
    }
    
    public static function getToken() : string {
        return self::get("TOKEN");
    }

    public static function getSecretToken() : string {
        return self::get("SECRET_KEY");
    }

    public static function getConfigsDir() : string {
        return self::get("CONFIGS_DIR");
    }

    public static function getDatabaseKeys(): object {
        return (object) [
            "hostname" => self::get("DB_HOSTNAME"),
            "port" => self::get("DB_PORT"),
            "username" => self::get("DB_USERNAME"),
            "password" => self::get("DB_PASSWORD"),
            "dbname" => self::get("DB_DATABASE"),
        ];
    }

    public static function getAddressByID($id_name) :string {
        return self::get($id_name . "_SERVER_ADDRESS");
    }

    public static function getIndexByID($id_name) :string {
        return self::get($id_name . "_SERVER_INDEX");
    }

    public static function getSecretKeyByID($id_name) {
        return self::get($id_name . "_SECRET_KEY");
    }

    public static function getSubIndex(): string {
        return self::get("SUB_INDEX");
    }

    public static function getRedisKeys(): object {
        return (object) [
            "hostname" => self::get("REDIS_HOSTNAME"),
            "port" => self::get("REDIS_PORT")
        ];
    }

    public static function getRequiredServers() : array {
        return explode(",", self::get("REQUIRED_SERVERS"));
    }
}