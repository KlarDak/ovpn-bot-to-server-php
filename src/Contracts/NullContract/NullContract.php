<?php

namespace CNS\BotToServer\Contracts\NullContract;

use CNS\BotToServer\Config\BTSConfig;
use CNS\BotToServer\Contracts\Traits\CheckContractTrait;
use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Types\Server;
use Exception;

final class NullContract {
    use CheckContractTrait;
    function __construct(
        private IDBConnector $dbConnector, 
        private HttpClientInterface $httpClient, 
        private BTSConfig $btsConfig, 
        Server $server) {}
    public function __call($name, $args) {
        # TODO: Change error type
        throw new Exception("ERROR TEST");
    }
}