<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Types\Access;
use CNS\BotToServer\Types\AccessType;
use CNS\BotToServer\Types\VPNType;

class AccessesResource {
    private IDBConnector $dbConnector;
    private int $user_id;
    function __construct(IDBConnector $dbConnector, int $user_id)
    {
        $this->dbConnector = $dbConnector;
        $this->user_id = $user_id;
    }

    public function list() : array {
        $query = "SELECT * FROM accesses WHERE user_id = :user_id AND is_dropped = 0";
        $params = [':user_id' => $this->user_id];
        $results = $this->dbConnector->fetchAll($query, $params);
        return array_map(fn($result) => new Access($result), $results);
    }

    public function count(?VPNType $vpn_type = null) : array {
        $query = "SELECT COUNT(*) FROM accesses WHERE ". ($vpn_type ? "type = :vpn_type AND" : "") ." user_id = :user_id AND is_dropped = 0";
        $params = [':user_id' => $this->user_id];
        if ($vpn_type) {
            $params[':vpn_type'] = $vpn_type->value;
        }
        return $this->dbConnector->fetchOne($query, $params);
    }
}