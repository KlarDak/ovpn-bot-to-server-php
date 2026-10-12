<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Config\BTSConfig;
use CNS\BotToServer\Contracts\NullContract\NullContract;
use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Types\Server;
use Exception;

class ServerResource {
    private string $code;
    function __construct(private IDBConnector $dbConnector, private string|HttpClientInterface $httpClient, private BTSConfig $btsConfig, string $code)
    {
        $this->dbConnector = $dbConnector;
        $this->httpClient = $httpClient;
        $this->code = $code;
    }

    public function contract(): mixed
    {
        $server = $this->info() ?? throw new Exception("Server not found");

        if (is_string($this->httpClient)) {
            $class = $this->httpClient;

            $this->httpClient = new $class(
                $server->host,
                $server->port,
                $server->api_endpoint
            );
        }

        $contractClass = $this->btsConfig->contracts[$server->type]
            ?? NullContract::class;

        return new $contractClass(
            $this->dbConnector,
            $this->httpClient,
            $this->btsConfig,
            $server
        );
    }

    public function info() : ?Server {
        $query = "SELECT * FROM servers WHERE code = :code AND is_dropped = 0";
        $params = [
            ':code' => $this->code
        ];
        $result = $this->dbConnector->fetchOne($query, $params);
        return $result ? new Server($result) : null;
    }
}