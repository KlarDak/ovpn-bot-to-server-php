<?php

namespace CNS\BotToServer\Contracts\NullContract;

use CNS\BotToServer\Config\BTSConfig;
use CNS\BotToServer\Contracts\Traits\CheckContractTrait;
use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Exceptions\NullContractMethodsException;
use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Types\Server;

/**
 * Null object implementation used when no valid VPN node contract is available.
 *
 * Throws an exception when an undefined contract method is called.
 */
final class NullContract
{
    use CheckContractTrait;

    /**
     * Initializes the null contract with the standard contract dependencies.
     *
     * @param IDBConnector $dbConnector Database connector instance.
     * @param HttpClientInterface $httpClient HTTP client instance.
     * @param BTSConfig $btsConfig Library configuration instance.
     * @param Server $server Server associated with the contract.
     */
    public function __construct(
        IDBConnector $dbConnector,
        HttpClientInterface $httpClient,
        BTSConfig $btsConfig,
        Server $server
    ) {}

    /**
     * Handles calls to undefined contract methods.
     *
     * @param string $name Name of the called method.
     * @param array $args Arguments passed to the method.
     *
     * @throws NullContractMethodsException Always thrown.
     */
    public function __call($name, $args)
    {
        throw new NullContractMethodsException();
    }
}