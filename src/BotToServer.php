<?php

namespace CNS\BotToServer;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Types\AccessType;
use CNS\BotToServer\Types\UserType;
use CNS\BotToServer\Types\VPNType;

class BotToServer {
    private IDBConnector $dbConnector;
    function __construct(IDBConnector $dbConnector)
    {
        $this->dbConnector = $dbConnector;
    }

    public function user(int $user_id, UserType $userType) {
        return new Resources\UserResource($this->dbConnector, $user_id, $userType);
    }

    public function users() {
        return new Resources\UsersResource($this->dbConnector);
    }

    public function access(string $uuid) {
        return new Resources\AccessResource($this->dbConnector, $uuid);
    }

    public function accesses(int $user_id) {
        return new Resources\AccessesResource($this->dbConnector, $user_id);
    }

    public function server(string $server_name, AccessType $accessType) {
        return new Resources\ServerResource($this->dbConnector, $server_name, $accessType);
    }
}