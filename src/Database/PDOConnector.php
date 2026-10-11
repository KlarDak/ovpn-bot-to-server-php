<?php

namespace CNS\BotToServer\Database;

use Override;

class PDOConnector implements IDBConnector {
    private \PDO $connection;

    function __construct(string $hostname, string $username, string $password, string $dbname)
    {
        try {
            $this->connection = new \PDO("mysql:host=$hostname;dbname=$dbname", $username, $password);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new \Exception("Connection failed: " . $e->getMessage());
        }
    }

    #[Override]
    public function fetchOne(string $query, array $params = []): ?array
    {
        try {
            $smtp = $this->connection->prepare($query);
            $smtp->execute($params);
            $result = $smtp->fetch(\PDO::FETCH_ASSOC) ?? null;
            return $result === false ? null : $result;
        }
        catch (\PDOException $e) {
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }

    #[Override]
    public function fetchAll(string $query, array $params = []): array
    {
        try {
            $smtp = $this->connection->prepare($query);
            $smtp->execute($params);
            $result = $smtp->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        }
        catch (\PDOException $e) {
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }

    #[Override]
    public function execute(string $query, array $params = []): bool
    {
        try {
            $smtp = $this->connection->prepare($query);
            return $smtp->execute($params);
        }
        catch (\PDOException $e) {
            throw new \Exception("Query failed: " . $e->getMessage());
        }
    }
}