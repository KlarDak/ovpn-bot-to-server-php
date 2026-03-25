<?php

namespace CNS\OvpnBotToServer;

use CNS\OvpnBotToServer\Databases\IDBConnector;

class BotToServer
{
    /**
     * Database object variable
     * 
     * @var IDBConnector
     */
    private IDBConnector $databaseConnector;
    /**
     * UserID of user in Telegram
     * 
     * @var int
     */
    private int $user_id;

    function __construct(IDBConnector $databaseConnector, ?int $user_id = null)
    {
        $this->databaseConnector = $databaseConnector;
        $this->user_id = $user_id ?? 0;
    }

    /**
     * Set new userID
     * 
     * @param int $user_id UserID of user in Telegram
     * 
     * @return void
     */
    public function setUserID(int $user_id) : void {
        $this->user_id = $user_id;
    }

    /**
     * Getter of UserID
     * 
     * @return int
     */
    public function getUserID() : int {
        return $this->user_id;
    }

    /**
     * Get object of class usersAdapter
     * 
     * @param int $user_id UserID of user
     * @return usersAdapter
     */
    public function user(int $user_id) : Adapters\userAdapter
    {
        return new Adapters\userAdapter($this->databaseConnector, $user_id ?? $this->user_id);
    }

    /**
     * Get object of class configAdapter
     * 
     * @param string $uuid UUID identifier
     * @return configAdapter
     */
    public function config(string $uuid) : Adapters\configAdapter 
    {
        return new Adapters\configAdapter($this->databaseConnector, $uuid);
    }

    /**
     * Get object of class configAdapter
     * 
     * @param int $user_id UserID of user
     * @return configAdapter
     */
    public function configs(int $user_id) : Adapters\configsAdapter
    {
        return new Adapters\configsAdapter($this->databaseConnector, $user_id);
    }

    /**
     * Get object of class ApiUserClient
     * 
     * @param string $server_id Index of selected server
     * @return ApiUserClient
     */
    public function apiClient(string $server_id) : Services\ApiUserClient
    {
        return new Services\ApiUserClient($server_id);
    }

    /**
     * Get object of class usersAdapter
     * 
     * @return usersAdapter
     */
    public function usersClient() : Adapters\usersAdapter 
    {
        return new Adapters\usersAdapter($this->databaseConnector);
    }

    /**
     * Get object of class apiCronClient
     * 
     * @param string $server_id Index of selected server
     * @return ApiCronClient
     */
    public function apiCronClient(string $server_id) : Services\ApiCronClient
    {
        return new Services\ApiCronClient($server_id);
    }
}