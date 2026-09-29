<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;

class AccessResource {
    private IDBConnector $dbConnector;
    private string $uuid;
    function __construct(IDBConnector $dbConnector, string $uuid)
    {
        $this->dbConnector = $dbConnector;
        $this->uuid = $uuid;
    }

    public function exists() {}
    public function get() {}
    public function create() {}
    public function update() {}
    public function block(){}
    public function unblock(){}
    public function drop() {}
}