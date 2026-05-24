<?php

namespace CNS\OvpnBotToServer\Adapters;

use CNS\OvpnBotToServer\Databases\IDBConnector;
use CNS\OvpnBotToServer\Exceptions\UserException;
use CNS\OvpnBotToServer\Types\User;

class usersAdapter {
    /**
     * Database object variable
     * 
     * @var IDBConnector
     */
    private IDBConnector $dbConnector;

    /**
     * Constructor of usersAdapter class
     * 
     * @param IDBConnector $dbConnector Database object variable
     */
    function __construct(IDBConnector $dbConnector) {
        $this->dbConnector = $dbConnector;
    }

    /**
     * Get all non-blocked users
     * 
     * @return array
     * @throws UserException
     */
    public function getAllUsers() : array {
        try {
            $query = "SELECT * FROM users WHERE is_dropped = 0";
            $allUsers = $this->dbConnector->fetchAll($query, []);

            return array_map(
                fn($user) => new User($user),
                $allUsers
            );
        }
        catch (UserException $error) {
            return [];
        }
    }
    
    /**
     * Get all expired users
     * 
     * @param string $time DATETIME format of actual time
     * 
     * @return array
     * @throws UserException
     */
    public function getExpiredUsers(string $time): array {
        try {
            $query = "SELECT * FROM users WHERE expired_at < :time AND is_active = 1 AND is_dropped = 0";
            $expiredUsers = $this->dbConnector->fetchAll($query, [":time" => $time]);

            return array_map(
                fn($user) => new User($user),
                $expiredUsers
            );
        }
        catch (UserException $error) {
            return [];
        }
    }
}