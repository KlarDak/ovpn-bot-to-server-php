<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Types\AccessType;
use CNS\BotToServer\Types\Server;
use CNS\BotToServer\Types\ServerStatusType;
use DateTimeImmutable;
use Exception;

class ServersResource {
    private IDBConnector $dbConnector;
    public array $allowedFilterBy = [
        "name",
        "hort",
        "port",
        "api_endpoint",
        "type",
        "status"
    ];

    function __construct(IDBConnector $dbConnector)
    {
        $this->dbConnector = $dbConnector;
    }

    public function create(
        string $code,
        string $name,
        string $host,
        int $port,
        string $api_endpoint,
        string $encrypt_secret_code,
        AccessType $type,
        ?string $subs_url = null,
    ) : bool {
        $query = "INSERT INTO server(code, name, host, port, api_endpoint, encrypt_secret_code, type, subs_url) VALUES (:code, :name, :host, :port, :api_enpoint, :encrypt_secret_code, :type, :subs_url)";
        $params = [
            ":code" => $code,
            ":name" => $name,
            ":host" => $host,
            ":port" => $port,
            ":api_endpoint" => $api_endpoint,
            ":encrypt_secret_code" => $encrypt_secret_code,
            ":type" => $type->value,
            ":subs_url" => $subs_url,
        ];

        return $this->dbConnector->execute($query, $params);
    }
    public function getAll() : array {
        $query = "SELECT * FROM server WHERE is_dropped = 0";
        $result = $this->dbConnector->fetchAll($query, []);

        return array_map(fn($server) => new Server($server), $result);
    }

    public function filterBy(array $conditions) : array {
        $conditions = ['is_dropped = 0'];
        $params = [];

        foreach ($this->allowedFilterBy as $column => $value) {
            if (!in_array($column, $this->allowedFilterBy, true)) {
                throw new \InvalidArgumentException(
                    "Unsupported filter: {$column}"
                );
            }

            if ($value instanceof \BackedEnum) {
                $value = $value->value;
            }

            $conditions[] = "{$column} = :{$column}";
            $params[":{$column}"] = $value;
        }

        $where = implode(' AND ', $conditions);
        $query = "SELECT * FROM servers WHERE {$where}";

        $result = $this->dbConnector->fetchAll($query, $params);

        return array_map(
            static fn(array $server) => new Server($server),
            $result
        );
    }

    public function get(string $code) : Server {
        $query = "SELECT * FROM servers WHERE code = :code AND is_dropped = 0";
        $result = $this->dbConnector->fetchOne($query, [":code" => $code]);
        return $result ? new Server($result) : throw new Exception("Server not found.");
    }
    public function update(
        string $code,
        ?string $name = null,
        ?string $host = null,
        ?int $port = null,
        ?string $api_endpoint = null,
        ?string $encrypt_secret_code = null,
        ?string $subs_url = null
    ) : bool {
        $fields = array_filter([
            "name" => $name,
            "host" => $host,
            "port" => $port,
            "api_endpoint" => $api_endpoint,
            "encrypt_secret_code" => $encrypt_secret_code,
            "subs_url" => $subs_url
        ], fn($value) => $value !== null);

        if ($fields == []) {
            return false;
        }

        $set = implode(', ', array_map(
            fn($column) => "$column = :$column",
            array_keys($fields)
        ));

        $fields["code"] = $code;

        $query = "UPDATE servers SET $set WHERE code = :code AND is_dropped = 0";

        return $this->dbConnector->execute($query, $fields);
    }

    public function updateStatus(string $code, ServerStatusType $status) : bool {
        if ($status === ServerStatusType::DROPPED) {
            return false;
        }

        $query = "UPDATE servers SET status = :status, disabled_at = :disabled_at WHERE code = :code AND is_dropped = 0";
        
        $disabled_at = match($status) {
            ServerStatusType::DISABLED,
            ServerStatusType::MAINTENANCE => (new DateTimeImmutable)->format("Y-m-d H:i:s"),
            
            ServerStatusType::ACTIVE,
            ServerStatusType::DRAINING => null
        };

        $params = [":status" => $status->value, ":disabled_at" => $disabled_at, ":code" => $code];

        return $this->dbConnector->execute($query, $params);
    }

    public function drop(string $code) : bool {
        $query = "UPDATE servers SET status = :status, disabled_at = :disabled_at, is_dropped = 1 WHERE code = :code AND is_dropped = 0";
        $params = [
            ":status" => ServerStatusType::DROPPED,
            ":disabled_at" => (new DateTimeImmutable)->format("Y-m-d H:i:s"),
            ":code" => $code
        ];

        return $this->dbConnector->execute($query, $params);
    }
}