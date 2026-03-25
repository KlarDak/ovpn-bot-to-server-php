<?php

namespace CNS\OvpnBotToServer\Databases;
use CNS\OvpnBotToServer\Databases\IDBConnector;
use InvalidArgumentException;
use UnexpectedValueException;

class PDOConnector implements IDBConnector{
    private \PDO $pdo;
    private array $db_connector_params;

    function __construct(string $hostname, string $port, string $username, string $password, string $dbname)
    {
        $this->db_connector_params = get_defined_vars();
        
        $this->getConnect();
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

    public function getConnect(): void
    {
        $host = "mysql:host=".$this->db_connector_params["hostname"].";port=".$this->db_connector_params["port"].";dbname=".$this->db_connector_params["dbname"];
        $this->pdo = new \PDO($host, $this->db_connector_params["username"], $this->db_connector_params["password"]);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    public function __serialize(): array {
        return [
            "db_connector_params" => $this->db_connector_params
        ];
    }

    public function __unserialize(array $data): void
    {
        if (!empty($data["db_connector_params"]) || !is_array($data["db_connector_params"])) {
            throw new UnexpectedValueException("Invalid or missing 'connect' data in unserialized data.");
        }
        
        $this->db_connector_params = $data["db_connector_params"];
        
        $required_field = ["hostname", "port", "dbname", "username", "password"];

        foreach ($required_field as $key) {
            if (!array_key_exists($key, $this->db_connector_params)) {
                throw new InvalidArgumentException("Missing connection argument: $key");
            }
        }

        $this->getConnect();
    }
}