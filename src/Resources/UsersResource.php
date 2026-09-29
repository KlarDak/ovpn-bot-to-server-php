<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;

class UsersResource {
    private IDBConnector $dbConnector;
    function __construct(IDBConnector $dbConnector)
    {
        $this->dbConnector = $dbConnector;
    }

    public function expired() {}

    public function list() {}

    public function dropped() {}
}