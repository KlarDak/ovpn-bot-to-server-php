<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Types\AccessType;
use CNS\BotToServer\Types\Server;

class ServerResource {
    private IDBConnector $dbConnector;
    private string $server_name;
    private AccessType $access_type;
    function __construct(IDBConnector $dbConnector, string $server_name, AccessType $accessType)
    {
        $this->dbConnector = $dbConnector;
        $this->server_name = $server_name;
        $this->access_type = $accessType;
    }

    public function contract() : mixed {
        $query = "SELECT code, host, port, encrypt_secret_code, subs_url FROM servers WHERE server_name = :server_name AND type = :vpn_type AND is_dropped = 0";
        $params = [
            ':server_name' => $this->server_name,
            ':vpn_type' => $this->access_type->value
        ];
        $result = $this->dbConnector->fetchOne($query, $params);
        
        if (!$result) {
            throw new \Exception("Server not found.");
        }

        return match ($this->access_type) {
            AccessType::OPENVPN => new \CNS\BotToServer\Node\OpenVPN\OpenVPNContract($result['code'], $result['host'], (int)$result['port'], $result['encrypt_secret_code'], $result['subs_url']),
        };
    }

    public function info() : Server {
        $query = "SELECT * FROM servers WHERE server_name = :server_name AND type = :vpn_type AND is_dropped = 0";
        $params = [
            ':server_name' => $this->server_name,
            ':vpn_type' => $this->access_type->value
        ];
        $result = $this->dbConnector->fetchOne($query, $params);
        return $result ? new Server($result) : throw new \Exception("Server not found.");
    }
}