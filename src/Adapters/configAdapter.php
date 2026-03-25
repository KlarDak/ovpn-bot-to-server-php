<?php

namespace CNS\OvpnBotToServer\Adapters;

use CNS\OvpnBotToServer\Databases\IDBConnector;
use CNS\OvpnBotToServer\Exceptions\ConfigException;
use CNS\OvpnBotToServer\Types\Config;
use CNS\OvpnBotToServer\Utils\Utils;

class configAdapter {
    /**
     * Database object variable
     * 
     * @var IDBConnector
     */
    private IDBConnector $db;

    /**
     * UUID of config's file
     * 
     * @var string
     */
    private string $uuid;


    /**
     * Construct of configAdapter class
     * 
     * @param IDBConnector $db Database object
     * @param string $uuid Config file UUID identifier
     */
    function __construct(IDBConnector $db, string $uuid)
    {
        $this->db = $db;
        $this->uuid = $uuid;
    }

    /**
     * Get config file record from table
     * 
     * @return Config|bool
     * @throws ConfigException
     */
    public function getConfig() : Config|bool {
        try {
            $query = "SELECT * FROM configs WHERE uuid = :uuid AND is_dropped = 0";
            $params = [":uuid" => $this->uuid];

            return new Config($this->db->fetchOne($query, $params));
        }
        catch (ConfigException $error) {
            return false;
        }
    }

    /**
     * Create a new config file record
     * 
     * @param string $uuid Config file UUID
     * @param string $user_id UserID of user in Telegram
     * @param string $config_name Name of config file
     * @param string $type Type of config file
     * @param string $location Location of new config file
     * 
     * @return bool
     * 
     * @throws ConfigException
     */
    public function createConfig(string $uuid, int $user_id, string $config_name, string $type, string $location) : bool
    {
        try {
            $query = "INSERT INTO configs (uuid, user_id, config_name, type, location) VALUES (:uuid, :user_id, :config_name, :type, :location)";
            $params = [
                ":uuid" => $uuid,
                ":user_id" => $user_id,
                ":config_name" => $config_name,
                ":type" => $type,
                ":location" => $location
            ];

            return $this->db->execute($query, $params);
        }
        catch (ConfigException $e) {
            return false;
        }

    }

    /**
     * Update config file name
     * 
     * @param string $name New name of config file
     * 
     * @return bool
     * 
     * @throws ConfigException
     */
     public function updateConfigName(string $name) : bool {
        try {
            $query = "UPDATE configs SET config_name = :name WHERE uuid = :uuid";
            $params = [":name" => $name, ":uuid" => $this->uuid];

            return $this->db->execute($query, $params);
        }
        catch (ConfigException $error) {
            return false;
        }
    }

    /**
     * Update config file location
     * 
     * @param string $location New location of config file
     * 
     * @return bool
     * 
     * @throws ConfigException
     */
    public function updateLocation(string $location) : bool {
        try {
            $query = "UPDATE configs SET location = :location WHERE uuid = :uuid AND is_dropped = 0";
            $params = [":location" => $location, ":uuid" => $this->uuid];

            return $this->db->execute($query, $params);
        }
        catch (ConfigException $error) {
            return false;
        }
    }

    /**
     * Update config file type
     * 
     * @param string $type New config file type
     * @return bool
     * 
     * @throws ConfigException
     */
    public function updateType(string $type) : bool {
        try {
            $query = "UPDATE configs SET type = :type WHERE uuid = :uuid";
            $params = [":type" => $type, ":uuid" => $this->uuid];

            return $this->db->execute($query, $params);
        }
        catch (ConfigException $error) {
            return false;
        }
    }

    /**
     * Update config file active status
     * 
     * @param int $status Numeric file activity status
     * @param int $blocked_at Block time in UNIX
     * 
     * @return bool
     * 
     * @throws ConfigException
     */
    public function updateActiveStatus(int $status, ?int $blocked_at = null) : bool {
        try {
            if ($status == 0 && $blocked_at == null) {
                return false;
            }

            $query = "UPDATE configs SET status = :status" . ($status == 1) ? ", blocked_at = :time" : "" . " WHERE uuid = :uuid AND is_dropped = 0";
            $params = [":uuid" => $this->uuid, ":status" => $status, ":time" => Utils::timeGenerator($blocked_at)];

            return $this->db->execute($query, $params);
        }
        catch (ConfigException $error) {
            return false;
        }
    }

    /**
     * Deactivate user's config file
     * 
     * @return bool
     * 
     * @throws ConfigException
     */
    public function deactivateConfigFile() : bool
    {
        try {
            $query = "UPDATE configs SET is_dropped = 1 WHERE uuid = :uuid";
            $params = [
                ":uuid" => $this->uuid
            ];

            return $this->db->execute($query, $params);
        }
        catch(ConfigException $error) {
            return false;
        }
    }
}