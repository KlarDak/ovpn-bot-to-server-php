<?php

namespace CNS\OvpnBotToServer;

use CNS\OvpnBotToServer\Databases\IDBConnector;

class BotToServer
{
    private IDBConnector $databaseConnector;
    private int $user_id;
    private array $db_connector_params = [];

    function __construct(IDBConnector $databaseConnector, ?int $user_id = null)
    {
        $this->databaseConnector = $databaseConnector;
        $this->user_id = $user_id ?? 0;
    }

    public function setUserID(int $user_id) : void {
        $this->user_id = $user_id;
    }

    public function getUserID() : int {
        return $this->user_id;
    }

    public function user(int $user_id) : Adapters\userAdapter
    {
        return new Adapters\userAdapter($this->databaseConnector, $user_id ?? $this->user_id);
    }

    public function config(string $uuid) : Adapters\configAdapter 
    {
        return new Adapters\configAdapter($this->databaseConnector, $uuid);
    }

    public function configs(int $user_id) : Adapters\configsAdapter
    {
        return new Adapters\configsAdapter($this->databaseConnector, $user_id);
    }

    public function apiClient(string $server_id) : Services\ApiUserClient
    {
        return new Services\ApiUserClient($server_id);
    }

    public function usersClient() : Adapters\usersAdapter 
    {
        return new Adapters\usersAdapter($this->databaseConnector);
    }

    public function apiCronClient(string $server_id) : Services\ApiCronClient
    {
        return new Services\ApiCronClient($server_id);
    }
}