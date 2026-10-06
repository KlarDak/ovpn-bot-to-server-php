<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Types\AccessType;
use CNS\BotToServer\Types\Server;
use Exception;

class ServersResource {
    private IDBConnector $dbConnector;

    function __construct(IDBConnector $dbConnector)
    {
        $this->dbConnector = $dbConnector;
    }

    public function create(
        string $code,
        string $name,
        string $host,
        string $port,
        ?string $api_endpoint = null,
        string $encrypt_secret_code,
        AccessType $type,
        ?string $subs_url = null,
        string $status = "active"
    ) : bool {
        $query = "INSERT INTO server(code, name, host, port, api_endpoint, encrypt_secret_code, type, subs_url, status) VALUES (:code, :name, :host, :port, :api_enpoint, :encrypt_secret_code, :type, :subs_url, :status)";
        $params = [
            ":code" => $code,
            ":name" => $name,
            ":host" => $host,
            ":port" => $port,
            ":api_endpoint" => $api_endpoint,
            ":encrypt_secret_code" => $encrypt_secret_code,
            ":type" => $type->value,
            ":subs_url" => $subs_url,
            ":status" => $status
        ];

        return $this->dbConnector->execute($query, $params);
    }
    public function getAll() : array {
        $query = "SELECT * FROM server WHERE is_dropped = 0";
        $result = $this->dbConnector->fetchAll($query, []);

        return array_map(fn($server) => new Server($server), $result);
    }
    public function get(string $code) : Server {
        $query = "SELECT * FROM servers WHERE code = :code AND is_dropped = 0";
        $result = $this->dbConnector->fetchOne($query, [":code" => $code]);
        return $result ? new Server($result) : throw new Exception("Server not found.");
    }
    public function update() {}
    public function stop() {}
    public function start() {}
    public function block() {}
    public function pardon() {}


}