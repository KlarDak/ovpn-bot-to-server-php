<?php

namespace CNS\BotToServer;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Resources\AccessesResource;
use CNS\BotToServer\Resources\AccessResource;
use CNS\BotToServer\Resources\ServerResource;
use CNS\BotToServer\Resources\ServersResource;
use CNS\BotToServer\Resources\UserResource;
use CNS\BotToServer\Resources\UsersResource;
use CNS\BotToServer\Types\AccessType;
use CNS\BotToServer\Types\UserType;

class BotToServer {
    private IDBConnector $dbConnector;
    private string|HttpClientInterface $httpClient;
    function __construct(
        IDBConnector $database,
        string|HttpClientInterface $http
    )
    {
        $this->dbConnector = $database;
        $this->httpClient = $http;
    }

    public function user(int $user_id, UserType $userType) : UserResource {
        return new UserResource($this->dbConnector, $user_id, $userType);
    }

    public function users() : UsersResource {
        return new UsersResource($this->dbConnector);
    }

    public function access(string $uuid) : AccessResource {
        return new AccessResource($this->dbConnector, $uuid);
    }

    public function accesses(int $user_id) : AccessesResource {
        return new AccessesResource($this->dbConnector, $user_id);
    }

    public function server(string $server_name, AccessType $accessType) : ServerResource {
        return new ServerResource($this->dbConnector, $this->httpClient, $server_name, $accessType);
    }

    public function servers() : ServersResource {
        return new ServersResource($this->dbConnector);
    }
}