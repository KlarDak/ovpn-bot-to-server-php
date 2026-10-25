<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Config\BTSConfig;
use CNS\BotToServer\Contracts\Interfaces\VPNNodeContractInterface;
use CNS\BotToServer\Contracts\NullContract\NullContract;
use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Types\Server;
use Exception;

/**
 * Provides access to server information and its VPN node contract.
 *
 * Resolves server configuration from the database and initializes
 * the appropriate contract for communicating with the remote node.
 */
class ServerResource
{
    private string $code;

    /**
     * Initializes the resource for the specified server.
     *
     * @param IDBConnector $dbConnector Database connector instance.
     * @param string|HttpClientInterface $httpClient HTTP client instance or class name.
     * @param BTSConfig $btsConfig Library configuration instance.
     * @param string $code Unique server code.
     */
    function __construct(
        private IDBConnector $dbConnector,
        private string|HttpClientInterface $httpClient,
        private BTSConfig $btsConfig,
        string $code
    ) {
        $this->code = $code;
    }

    /**
     * Resolves and initializes the VPN node contract for the server.
     *
     * If an HTTP client class name is provided, the client is instantiated
     * using the server's host, port, and API endpoint.
     *
     * Uses NullContract when no contract is registered for the server type.
     *
     * @return mixed The initialized VPN node contract instance.
     *
     * @throws Exception If the server cannot be found.
     */
    public function contract(): VPNNodeContractInterface
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

    /**
     * Retrieves information about the specified server.
     *
     * Excludes servers marked as dropped.
     *
     * @return Server|null The server instance, or null if not found.
     */
    public function info(): ?Server {
        $query = "SELECT * FROM servers WHERE code = :code AND is_dropped = 0";
        $params = [
            ':code' => $this->code
        ];
        $result = $this->dbConnector->fetchOne($query, $params);
        return $result ? new Server($result) : null;
    }
}