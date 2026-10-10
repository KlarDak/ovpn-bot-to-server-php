<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Config\BTSConfig;
use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Node\OpenVPN\OpenVPNContract;
use CNS\BotToServer\Types\AccessType;
use CNS\BotToServer\Types\Server;

class ServerResource {
    private IDBConnector $dbConnector;
    private string $server_name;
    private AccessType $access_type;
    private string|HttpClientInterface $httpClient;
    private BTSConfig $btsConfig;
    function __construct(IDBConnector $dbConnector, string|HttpClientInterface $httpClient, string $server_name, AccessType $accessType, BTSConfig $bTSConfig)
    {
        $this->dbConnector = $dbConnector;
        $this->httpClient = $httpClient;
        $this->server_name = $server_name;
        $this->access_type = $accessType;
        $this->btsConfig = $bTSConfig;
    }

    public function contract() : mixed {
        try {
            $query = "SELECT code, host, port, api_endpoint, encrypt_secret_code, subs_url FROM servers WHERE server_name = :server_name AND type = :vpn_type AND is_dropped = 0";
            $params = [
                ':server_name' => $this->server_name,
                ':vpn_type' => $this->access_type->value
            ];
            $result = $this->dbConnector->fetchOne($query, $params);
            
            if (!$result) {
                throw new \Exception("Server not found.");
            }

            if (gettype($this->httpClient) == "string") {
                $this->httpClient = new $this->httpClient($result['host'], $result['port'], $result["api_endpoint"] ?? "/api");
            }

            return match ($this->access_type) {
                AccessType::OPENVPN => new OpenVPNContract($this->httpClient, $this->dbConnector, $this->btsConfig, $result["code"], $result["encrypt_secret_code"], $result["subs_url"]),
            };
        }
        catch (\Exception $e) {
            throw new \Exception("Error creating contract: " . $e->getMessage());
        }
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