<?php

namespace CNS\OvpnBotToServer\Adapters;

use CNS\OvpnBotToServer\Databases\IDBConnector;
use CNS\OvpnBotToServer\Types\User;

class usersAdapter {
    private IDBConnector $dbConnector;

    function __construct(IDBConnector $dbConnector) {
        $this->dbConnector = $dbConnector;
    }

    public function getAllUsers() : array {
        $query = "SELECT user_id, username FROM users WHERE is_dropped = 0";
        $allUsers = $this->dbConnector->fetchAll($query, []);

        return array_map(
            fn($user) => new User($user),
            $allUsers
        );
    }

    public function getExpiredUsers(string $time): array {
        $query = "SELECT * FROM users WHERE expired_at < :time AND is_active = 1 AND is_dropped = 0";
        $expiredUsers = $this->dbConnector->fetchAll($query, [":time" => $time]);

        return array_map(
            fn($user) => new User($user),
            $expiredUsers
        );
    }
}