<?php

namespace CNS\BotToServer\Contracts\Interfaces;

use CNS\BotToServer\Config\BTSConfig;
use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Types\Server;

interface VPNNodeContractInterface {
    function __construct(
        IDBConnector $dbConnector,
        HttpClientInterface $httpClient,
        BTSConfig $btsConfig,
        Server $server
    );
}