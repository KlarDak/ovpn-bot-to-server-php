<?php

namespace CNS\OvpnBotToServer\Adapters;

use CNS\OvpnBotToServer\Databases\IDBConnector;
use CNS\OvpnBotToServer\Exceptions\ConfigException;
use CNS\OvpnBotToServer\Types\Config;

class configsAdapter {
    private IDBConnector $db;
    private int $user_id;
    
    function __construct(IDBConnector $db, int $user_id)
    {
        $this->db = $db;
        $this->user_id = $user_id;    
    }

    public function getConfigsByUserID() : array {
        $query = "SELECT * FROM configs WHERE user_id = :user_id AND is_dropped = 0";
        $params = [":user_id" => $this->user_id];
        $configs = $this->db->fetchAll($query, $params);
        return array_map(
            fn($config) => new Config($config),
            $configs
        );
    }

    public function blockConfigsByUserID() : bool {
        $query = "UPDATE configs SET status = 0 WHERE user_id = :user_id AND is_dropped = 0";
        $params = [":user_id" => $this->user_id];

        return $this->db->execute($query, $params);
    }

    public function pardonConfigsByUserID() : bool {
        $query = "UPDATE configs SET status = 1 WHERE user_id = :user_id AND is_dropped = 0";
        $params = [":user_id" => $this->user_id];

        return $this->db->execute($query, $params);
    }
}