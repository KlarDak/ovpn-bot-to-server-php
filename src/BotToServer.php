<?php

namespace CNS\BotToServer;

use CNS\BotToServer\Config\BTSConfig;
use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Manager\Interfaces\ManagerInterface;
use CNS\BotToServer\Manager\Manager;
use CNS\BotToServer\Resources\AccessesResource;
use CNS\BotToServer\Resources\AccessResource;
use CNS\BotToServer\Resources\ServerResource;
use CNS\BotToServer\Resources\ServersResource;
use CNS\BotToServer\Resources\SubsResource;
use CNS\BotToServer\Resources\TransactionResource;
use CNS\BotToServer\Resources\UserResource;
use CNS\BotToServer\Resources\UsersResource;
use CNS\BotToServer\Types\Transaction;
use CNS\BotToServer\Types\UserType;

class BotToServer {
    private IDBConnector $dbConnector;
    private string|HttpClientInterface $httpClient;
    private BTSConfig $btsConfig;
    // private ManagerInterface $manager;
    function __construct(
        IDBConnector $database,
        string|HttpClientInterface $http,
        ?BTSConfig $btsConfig = null,
        // ?ManagerInterface $manager = null
    )
    {
        $this->dbConnector = $database;
        $this->httpClient = $http;
        $this->btsConfig = $btsConfig ?? new BTSConfig();
        // $this->manager = $manager ?? new Manager($this);
    }

    public function user(int|string $userIdentifier, UserType $userType) : UserResource {
        return new UserResource($this->dbConnector, $userIdentifier, $userType);
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

    public function subs() : SubsResource {
        return new SubsResource($this->dbConnector);
    }

    public function server(string $code) : ServerResource {
        return new ServerResource($this->dbConnector, $this->httpClient, $this->btsConfig, $code);
    }

    public function servers() : ServersResource {
        return new ServersResource($this->dbConnector);
    }

    public function transaction() : TransactionResource {
        return new TransactionResource($this->dbConnector);
    }
}