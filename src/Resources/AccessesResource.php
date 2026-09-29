<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;

class AccessesResource {
    private IDBConnector $dbConnector;
    private int $user_id;
    function __construct(IDBConnector $dbConnector, int $user_id)
    {
        $this->dbConnector = $dbConnector;
        $this->user_id = $user_id;
    }

    public function list() {}

    public function count(?string $vpn_type) {}

    public function exists(?string $vpn_type) {}
}