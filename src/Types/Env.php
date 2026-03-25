<?php

namespace CNS\OvpnBotToServer\Types;

use CNS\OvpnBotToServer\Exceptions\AppException;
use RuntimeException;

class Env
{
    /**
     * Filter keys and output variable values
     * 
     * @param string $key Key of variable
     * @return string
     * @throws AppException 
     */
    public static function get(string $key) : string {
        $value = $_ENV[$key];

        if ($value === null || $value === '') {
            throw new AppException("An error with value of this keys: $key");
        }
        return $value;
    }
    
    /**
     * Return value of Telegram token
     * 
     * @return string
     */
    public static function getToken() : string {
        return self::get("TOKEN");
    }

    /**
     * Return secret key of server
     * 
     * @return string
     */
    public static function getSecretToken() : string {
        return self::get("SECRET_KEY");
    }

    /**
     * Return path of configs dir
     * 
     * @return string
     */
    public static function getConfigsDir() : string {
        return self::get("CONFIGS_DIR");
    }

    /**
     * Return Databases key
     * 
     * @return object
     */
    public static function getDatabaseKeys(): object {
        return (object) [
            "hostname" => self::get("DB_HOSTNAME"),
            "port" => self::get("DB_PORT"),
            "username" => self::get("DB_USERNAME"),
            "password" => self::get("DB_PASSWORD"),
            "dbname" => self::get("DB_DATABASE"),
        ];
    }

    /**
     * Return selected server address
     * 
     * @param string $id_name Identify of selected server
     * @return string 
     */
    public static function getAddressByID($id_name): string {
        return self::get($id_name . "_SERVER_ADDRESS");
    }

    /**
     * Return index of selected server
     * 
     * @param string $id_name Identify of selected server
     * @return string
     */
    public static function getIndexByID($id_name): string {
        return self::get($id_name . "_SERVER_INDEX");
    }

    /**
     * Return secret key of server
     * 
     * @param string $id_name Identify of selected server
     * @return string
     */
    public static function getSecretKeyByID($id_name): string {
        return self::get($id_name . "_SECRET_KEY");
    }

    /**
     * Return index of server
     * 
     * @return string
     */
    public static function getSubIndex(): string {
        return self::get("SUB_INDEX");
    }

    /**
     * Return Redis keys
     * 
     * @return object
     */
    public static function getRedisKeys(): object {
        return (object) [
            "hostname" => self::get("REDIS_HOSTNAME"),
            "port" => self::get("REDIS_PORT")
        ];
    }

    /**
     * Not used now
     */
    public static function getRequiredServers() : array {
        return explode(",", self::get("REQUIRED_SERVERS"));
    }
}