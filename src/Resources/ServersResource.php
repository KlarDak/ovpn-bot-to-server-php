<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Types\Server;
use CNS\BotToServer\Types\ServerStatusType;
use DateTimeImmutable;

/**
 * Provides methods for managing VPN server records.
 *
 * Supports creating, retrieving, filtering, updating,
 * changing server statuses, and soft-deleting servers.
 */
class ServersResource
{
    private IDBConnector $dbConnector;

    /**
     * List of server fields allowed for filtering.
     *
     * @var string[]
     */
    public array $allowedFilterBy = [
        "name",
        "host",
        "port",
        "api_endpoint",
        "type",
        "status"
    ];

    /**
     * Initializes the server resource.
     *
     * @param IDBConnector $dbConnector Database connector instance.
     */
    function __construct(IDBConnector $dbConnector)
    {
        $this->dbConnector = $dbConnector;
    }

    /**
     * Creates a new VPN server with an active status.
     *
     * @param string $code Unique server code.
     * @param string $name Server display name.
     * @param string $host Server hostname or IP address.
     * @param int $port Server API port.
     * @param string $api_endpoint Server API endpoint.
     * @param string $encrypt_secret_code Server encryption secret.
     * @param string $type VPN server type.
     * @param string|null $subs_url Optional subscription URL.
     *
     * @return int ID of the newly created server.
     */
    public function create(
        string $code,
        string $name,
        string $host,
        int $port,
        string $api_endpoint,
        string $encrypt_secret_code,
        string $type,
        ?string $subs_url = null,
    ) : int {
        $query = "INSERT INTO servers(code, name, host, port, api_endpoint, encrypt_secret_code, type, subs_url, status) VALUES (:code, :name, :host, :port, :api_endpoint, :encrypt_secret_code, :type, :subs_url, 'active')";
        $params = [
            ":code" => $code,
            ":name" => $name,
            ":host" => $host,
            ":port" => $port,
            ":api_endpoint" => $api_endpoint,
            ":encrypt_secret_code" => $encrypt_secret_code,
            ":type" => $type,
            ":subs_url" => $subs_url,
        ];

        return $this->dbConnector->insert($query, $params);
    }

    /**
     * Retrieves all servers that have not been dropped.
     *
     * @return Server[] List of server instances.
     */
    public function getAll() : array {
        $query = "SELECT * FROM servers WHERE is_dropped = 0";
        $result = $this->dbConnector->fetchAll($query, []);

        return array_map(fn($server) => new Server($server), $result);
    }

    /**
     * Retrieves non-dropped servers matching the specified conditions.
     *
     * Only fields listed in allowedFilterBy are applied.
     * Unsupported filter fields are ignored.
     * Multiple valid conditions are combined using AND.
     *
     * @param array<string, mixed> $conditions Server fields and their expected values.
     *
     * @return Server[] List of matching server instances.
     */
    public function filterBy(array $conditions) : array {
        $params = [];
        $query = [];

        foreach ($conditions as $column => $value) {
            if (!in_array($column, $this->allowedFilterBy, true)) {
                continue;
            }

            $query[] = "{$column} = :{$column}";
            $params[":{$column}"] = $value;
        }

        $query[] = 'is_dropped = 0';

        $where = implode(' AND ', $query);
        $query = "SELECT * FROM servers WHERE {$where}";

        $result = $this->dbConnector->fetchAll($query, $params);

        return array_map(
            static fn(array $server) => new Server($server),
            $result
        );
    }

    /**
     * Retrieves a server by its unique code.
     *
     * Servers marked as dropped are excluded.
     *
     * @param string $code Unique server code.
     *
     * @return Server|null The server instance, or null if not found.
     */
    public function get(string $code) : ?Server {
        $query = "SELECT * FROM servers WHERE code = :code AND is_dropped = 0";
        $result = $this->dbConnector->fetchOne($query, [":code" => $code]);
        return $result ? new Server($result) : null;
    }

    /**
     * Updates the specified fields of a non-dropped server.
     *
     * Only non-null parameters are included in the update.
     * Returns false when no fields are provided.
     *
     * @param string $code Unique server code.
     * @param string|null $name New server display name.
     * @param string|null $host New hostname or IP address.
     * @param int|null $port New server API port.
     * @param string|null $api_endpoint New server API endpoint.
     * @param string|null $encrypt_secret_code New encryption secret.
     * @param string|null $subs_url New subscription URL.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
    public function update(
        string $code,
        ?string $name = null,
        ?string $host = null,
        ?int $port = null,
        ?string $api_endpoint = null,
        ?string $encrypt_secret_code = null,
        ?string $subs_url = null
    ) : bool {
        $fields = array_filter([
            "name" => $name,
            "host" => $host,
            "port" => $port,
            "api_endpoint" => $api_endpoint,
            "encrypt_secret_code" => $encrypt_secret_code,
            "subs_url" => $subs_url
        ], fn($value) => $value !== null);

        if ($fields == []) {
            return false;
        }

        $set = implode(', ', array_map(
            fn($column) => "$column = :$column",
            array_keys($fields)
        ));

        $fields["code"] = $code;

        $query = "UPDATE servers SET $set WHERE code = :code AND is_dropped = 0";

        return $this->dbConnector->execute($query, $fields);
    }

    /**
     * Changes the status of a non-dropped server.
     *
     * Sets the disabling timestamp for DISABLED and MAINTENANCE
     * statuses, and clears it for ACTIVE and DRAINING.
     *
     * The DROPPED status cannot be assigned through this method.
     *
     * @param string $code Unique server code.
     * @param ServerStatusType $status New server status.
     *
     * @return bool True if the database operation succeeds,
     *              false if the status is DROPPED or the operation fails.
     */
    public function updateStatus(string $code, ServerStatusType $status) : bool {
        if ($status === ServerStatusType::DROPPED) {
            return false;
        }

        $query = "UPDATE servers SET status = :status, disabled_at = :disabled_at WHERE code = :code AND is_dropped = 0";

        $disabled_at = match($status) {
            ServerStatusType::DISABLED,
            ServerStatusType::MAINTENANCE => (new DateTimeImmutable)->format("Y-m-d H:i:s"),

            ServerStatusType::ACTIVE,
            ServerStatusType::DRAINING => null
        };

        $params = [":status" => $status->value, ":disabled_at" => $disabled_at, ":code" => $code];

        return $this->dbConnector->execute($query, $params);
    }

    /**
     * Soft-deletes a server by marking it as dropped.
     *
     * Sets the server status to DROPPED, records the disabling
     * timestamp, and excludes the server from future queries
     * without physically deleting its database record.
     *
     * @param string $code Unique server code.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
    public function drop(string $code) : bool {
        $query = "UPDATE servers SET status = :status, disabled_at = :disabled_at, is_dropped = 1 WHERE code = :code AND is_dropped = 0";
        $params = [
            ":status" => ServerStatusType::DROPPED,
            ":disabled_at" => (new DateTimeImmutable)->format("Y-m-d H:i:s"),
            ":code" => $code
        ];

        return $this->dbConnector->execute($query, $params);
    }
}