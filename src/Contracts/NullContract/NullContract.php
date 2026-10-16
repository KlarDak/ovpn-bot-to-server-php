<?php

namespace CNS\BotToServer\Contracts\NullContract;

use CNS\BotToServer\Config\BTSConfig;
use CNS\BotToServer\Contracts\Traits\CheckContractTrait;
use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Exceptions\NullContractMethodsException;
use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Types\Server;
use Exception;

final class NullContract {
    use CheckContractTrait;
    function __construct(
        IDBConnector $dbConnector, 
        HttpClientInterface $httpClient, 
        BTSConfig $btsConfig, 
        Server $server) {}
    public function __call($name, $args) {
        throw new NullContractMethodsException();
    }
}