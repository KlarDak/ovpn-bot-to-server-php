<?php

namespace CNS\BotToServer;

use CNS\BotToServer\Config\BTSConfig;
use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Resources\AccessesResource;
use CNS\BotToServer\Resources\AccessResource;
use CNS\BotToServer\Resources\ServerResource;
use CNS\BotToServer\Resources\ServersResource;
use CNS\BotToServer\Resources\SubsResource;
use CNS\BotToServer\Resources\TransactionResource;
use CNS\BotToServer\Resources\UserResource;
use CNS\BotToServer\Resources\UsersResource;
use CNS\BotToServer\Types\UserType;

/**
 * Main entry point and facade for the BTS library.
 *
 * Provides access to user, VPN access, subscription, server,
 * and transaction resources through a unified interface.
 *
 * Manages shared dependencies, including the database connector,
 * HTTP client, and library configuration.
 */
class BotToServer
{
    private IDBConnector $dbConnector;
    private string|HttpClientInterface $httpClient;
    private BTSConfig $btsConfig;

    // private ManagerInterface $manager;

    /**
     * Initializes the BTS library with the required dependencies.
     *
     * Uses the default BTSConfig instance when no custom
     * configuration is provided.
     *
     * @param IDBConnector $database Database connector instance.
     * @param string|HttpClientInterface $http HTTP client instance or class name.
     * @param BTSConfig|null $btsConfig Optional library configuration.
     */
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

    /**
     * Returns a resource for managing a specific user.
     *
     * @param int|string $userIdentifier User identifier.
     * @param UserType $userType Type of user identifier.
     *
     * @return UserResource User management resource.
     */
    public function user(int|string $userIdentifier, UserType $userType) : UserResource {
        return new UserResource($this->dbConnector, $userIdentifier, $userType);
    }

    /**
     * Returns a resource for querying user collections.
     *
     * @return UsersResource Users collection resource.
     */
    public function users() : UsersResource {
        return new UsersResource($this->dbConnector);
    }

    /**
     * Returns a resource for managing a specific VPN access.
     *
     * @param string $uuid Unique access identifier.
     *
     * @return AccessResource Access management resource.
     */
    public function access(string $uuid) : AccessResource {
        return new AccessResource($this->dbConnector, $uuid);
    }

    /**
     * Returns a resource for querying a user's VPN accesses.
     *
     * @param int $user_id Internal user ID.
     *
     * @return AccessesResource User accesses collection resource.
     */
    public function accesses(int $user_id) : AccessesResource {
        return new AccessesResource($this->dbConnector, $user_id);
    }

    /**
     * Returns a resource for managing VPN subscriptions.
     *
     * @return SubsResource Subscription management resource.
     */
    public function subs() : SubsResource {
        return new SubsResource($this->dbConnector);
    }

    /**
     * Returns a resource for accessing a specific VPN server.
     *
     * The resource can retrieve server information and resolve
     * the appropriate VPN node contract.
     *
     * @param string $code Unique server code.
     *
     * @return ServerResource Server management resource.
     */
    public function server(string $code) : ServerResource {
        return new ServerResource($this->dbConnector, $this->httpClient, $this->btsConfig, $code);
    }

    /**
     * Returns a resource for managing VPN server records.
     *
     * @return ServersResource Servers collection resource.
     */
    public function servers() : ServersResource {
        return new ServersResource($this->dbConnector);
    }

    /**
     * Returns a resource for managing payment transactions.
     *
     * @return TransactionResource Transaction management resource.
     */
    public function transaction() : TransactionResource {
        return new TransactionResource($this->dbConnector);
    }
}