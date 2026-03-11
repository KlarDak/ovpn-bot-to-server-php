<?php

namespace CNS\OvpnBotToServer\Databases;
use CNS\OvpnBotToServer\Databases\IDBConnector;

class PDOConnector implements IDBConnector{
    private \PDO $pdo;

    function __construct(string $hostname, string $port, string $username, string $password, string $dbname)
    {
        $this->pdo = new \PDO("mysql:host=$hostname;port=$port;dbname=$dbname", $username, $password);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function execute(string $query, array $params = []): bool
    {
        $stmt = $this->pdo->prepare($query);
        return $stmt->execute($params);
    }

    public function fetchOne(string $query, array $params = []): array|null
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result === false ? null : $result;
    }

    public function fetchAll(string $query, array $params = []): array
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
}