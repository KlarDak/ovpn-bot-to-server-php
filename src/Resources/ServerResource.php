<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Types\Server;
use CNS\BotToServer\Types\VPNType;

class ServerResource {
    private IDBConnector $dbConnector;
    private string $server_name;
    private VPNType $vpn_type;
    function __construct(IDBConnector $dbConnector, string $server_name, VPNType $vpn_type)
    {
        $this->dbConnector = $dbConnector;
        $this->server_name = $server_name;
        $this->vpn_type = $vpn_type;
    }

    public function contract() {}

    public function info() : Server {
        $query = "SELECT * FROM servers WHERE server_name = :server_name AND vpn_type = :vpn_type AND is_dropped = 0";
        $params = [
            ':server_name' => $this->server_name,
            ':vpn_type' => $this->vpn_type->value
        ];
        $result = $this->dbConnector->fetchOne($query, $params);
        return $result ? new Server($result) : throw new \Exception("Server not found.");
    }
}