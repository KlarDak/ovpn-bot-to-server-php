<?php

namespace CNS\OvpnBotToServer\Adapters;

use CNS\OvpnBotToServer\Databases\IDBConnector;
use CNS\OvpnBotToServer\Exceptions\ConfigException;
use CNS\OvpnBotToServer\Types\Config;

class configAdapter {
    private IDBConnector $db;
    private string $uuid;

    function __construct(IDBConnector $db, string $uuid)
    {
        $this->db = $db;
        $this->uuid = $uuid;
    }

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

    public function createConfig(string $uuid, int $user_id, string $type, int $expired_at) {
        try {
            $query = "INSERT INTO configs (uuid, user_id, type, is_active) VALUES (:uuid, :user_id, :type, 1)";
            $params = [
                ":uuid" => $uuid,
                ":user_id" => $user_id,
                ":type" => $type
            ];

            return $this->db->execute($query, $params);
        }
        catch (\Exception $e) {
            // Handle exception (e.g., log it)
            return false;
        }

    }

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

    public function updateLocation(string $location) : bool {
        $query = "UPDATE configs SET location = :location WHERE uuid = :uuid AND is_dropped = 0";
        $params = [":location" => $location, ":uuid" => $this->uuid];

        return $this->db->execute($query, $params);
    }

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

    public function updateActiveStatus(string $status, ?int $blocked_at = null) : bool {
        try {
            if ($status == 0 && $blocked_at == null) {
                return false;
            }

            $query = "UPDATE configs SET status = :status" . ($status == 1) ? ", blocked_at = :time" : "" . " WHERE uuid = :uuid AND is_dropped = 0";
            $params = [":uuid" => $this->uuid, ":status" => $status, "blocked_at" => $blocked_at];

            return $this->db->execute($query, $params);
        }
        catch (ConfigException $error) {
            return false;
        }
    }
}