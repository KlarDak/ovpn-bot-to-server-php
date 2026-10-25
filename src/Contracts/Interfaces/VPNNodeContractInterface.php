<?php

namespace CNS\BotToServer\Contracts\Interfaces;

use CNS\BotToServer\Config\BTSConfig;
use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Types\Server;

/**
 * Defines the base contract for interacting with a remote VPN node.
 */
interface VPNNodeContractInterface
{
    /**
     * Initializes the VPN node contract with the required dependencies.
     *
     * @param IDBConnector $dbConnector Database connector instance.
     * @param HttpClientInterface $httpClient HTTP client for communicating with the remote node.
     * @param BTSConfig $btsConfig Library configuration instance.
     * @param Server $server Server instance associated with this contract.
     */
    public function __construct(
        IDBConnector $dbConnector,
        HttpClientInterface $httpClient,
        BTSConfig $btsConfig,
        Server $server
    );

    /**
     * Returns the identifier of the current VPN node contract.
     *
     * @return string The contract identifier.
     */
    public function whichContract(): string;
}