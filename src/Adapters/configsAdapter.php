<?php

namespace CNS\OvpnBotToServer\Adapters;

use CNS\OvpnBotToServer\Databases\IDBConnector;
use CNS\OvpnBotToServer\Exceptions\ConfigException;
use CNS\OvpnBotToServer\Types\Config;

class configsAdapter {
    /**
     * Database object variable
     * 
     * @var IDBConnector
     */
    private IDBConnector $db;
    /**
     * UserID of user in Telegram
     * 
     * @var int
     */
    private int $user_id;
    
    /**
     * Constructor of configsAdapter class
     * 
     * @param IDBConnector $db Database object
     * @param int $user_id UserID of user in Telegram
     */
    function __construct(IDBConnector $db, int $user_id)
    {
        $this->db = $db;
        $this->user_id = $user_id;    
    }

    /**
     * Get list of all non-deleted users
     * 
     * @return array
     * 
     * @throws ConfigException
     */
    public function getConfigsByUserID() : array {
        try {
            $query = "SELECT * FROM configs WHERE user_id = :user_id AND is_dropped = 0";
            $params = [":user_id" => $this->user_id];
            $configs = $this->db->fetchAll($query, $params);
            return array_map(
                fn($config) => new Config($config),
                $configs
            );
        }
        catch (ConfigException $error) {
            return [];
        }
    }

    /**
     * Block all user config files
     * 
     * @return bool
     * 
     * @throws ConfigException
     */
    public function blockConfigsByUserID() : bool {
        $query = "UPDATE configs SET status = 0 WHERE user_id = :user_id AND is_dropped = 0";
        $params = [":user_id" => $this->user_id];

        return $this->db->execute($query, $params);
    }

    /**
     * Pardon all user config files
     * 
     * @return bool
     * 
     * @throws ConfigException
     */
    public function pardonConfigsByUserID() : bool {
        $query = "UPDATE configs SET status = 1 WHERE user_id = :user_id AND is_dropped = 0";
        $params = [":user_id" => $this->user_id];

        return $this->db->execute($query, $params);
    }
}