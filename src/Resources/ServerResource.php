<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;

class ServerResource {
    private IDBConnector $dbConnector;
    private string $server_name;
    private string $vpn_type;
    function __construct(IDBConnector $dbConnector, string $server_name, string $vpn_type)
    {
        $this->dbConnector = $dbConnector;
        $this->server_name = $server_name;
        $this->vpn_type = $vpn_type;
    }

    public function contract() {}

    public function info() {}
}