<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Types\Access;
use DateTimeImmutable;

class UsersResource {
    private IDBConnector $dbConnector;
    function __construct(IDBConnector $dbConnector)
    {
        $this->dbConnector = $dbConnector;
    }

    public function expiresBetween(DateTimeImmutable $start, DateTimeImmutable $end) : array {
        $query = "SELECT * FROM accesses WHERE expires_at BETWEEN :start AND :end AND is_dropped = 0";
        $params = [
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ];
        $results = $this->dbConnector->fetchAll($query, $params);
        return array_map(fn($result) => new Access($result), $results);
    }

    public function expiredBefore(DateTimeImmutable $date) : array {
        $query = "SELECT * FROM accesses WHERE expires_at < :date AND is_dropped = 0";
        $params = [':date' => $date->format('Y-m-d H:i:s')];
        $results = $this->dbConnector->fetchAll($query, $params);
        return array_map(fn($result) => new Access($result), $results);
    }

    public function getAll() : array {
        $query = "SELECT * FROM accesses WHERE is_dropped = 0";
        $results = $this->dbConnector->fetchAll($query, []);
        return array_map(fn($result) => new Access($result), $results);
    }

    public function dropped() : array {
        $query = "SELECT * FROM accesses WHERE is_dropped = 1";
        $results = $this->dbConnector->fetchAll($query, []);
        return array_map(fn($result) => new Access($result), $results);
    }
}