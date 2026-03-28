<?php

namespace CNS\OvpnBotToServer;

use CNS\OvpnBotToServer\Databases\IDBConnector;

class BTSStatic
{
    /**
     * Database object variable
     * 
     * @var IDBConnector
     */
    private static IDBConnector $databaseConnector;
    /**
     * UserID of user in Telegram
     * 
     * @var int
     */
    private static int $user_id;

    public static function setDBConnection(IDBConnector $databaseConnector) {
        self::$databaseConnector = $databaseConnector;
    }

    /**
     * Set new userID
     * 
     * @param int $user_id UserID of user in Telegram
     * 
     * @return void
     */
    public static function setUserID(int $user_id) : void {
        self::$user_id = $user_id;
    }

    /**
     * Getter of UserID
     * 
     * @return int
     */
    public static function getUserID() : int {
        return self::$user_id;
    }

    /**
     * Get object of class usersAdapter
     * 
     * @param int $user_id UserID of user
     * @return usersAdapter
     */
    public static function user(int $user_id) : Adapters\userAdapter
    {
        return new Adapters\userAdapter(self::$databaseConnector, $user_id);
    }

    /**
     * Get object of class configAdapter
     * 
     * @param string $uuid UUID identifier
     * @return configAdapter
     */
    public static function config(string $uuid) : Adapters\configAdapter 
    {
        return new Adapters\configAdapter(self::$databaseConnector, $uuid);
    }

    /**
     * Get object of class configAdapter
     * 
     * @param int $user_id UserID of user
     * @return configAdapter
     */
    public static function configs(int $user_id) : Adapters\configsAdapter
    {
        return new Adapters\configsAdapter(self::$databaseConnector, $user_id);
    }

    /**
     * Get object of class ApiUserClient
     * 
     * @param string $server_id Index of selected server
     * @return ApiUserClient
     */
    public static function apiClient(string $server_id) : Services\ApiUserClient
    {
        return new Services\ApiUserClient($server_id);
    }

    /**
     * Get object of class usersAdapter
     * 
     * @return usersAdapter
     */
    public static function usersClient() : Adapters\usersAdapter 
    {
        return new Adapters\usersAdapter(self::$databaseConnector);
    }

    /**
     * Get object of class apiCronClient
     * 
     * @param string $server_id Index of selected server
     * @return ApiCronClient
     */
    public static function apiCronClient(string $server_id) : Services\ApiCronClient
    {
        return new Services\ApiCronClient($server_id);
    }
}