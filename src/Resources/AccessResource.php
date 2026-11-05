<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Exceptions\AccessNotFoundException;
use CNS\BotToServer\Types\Access;

/**
 * Provides methods for managing a single VPN access record.
 *
 * Supports retrieving, creating, updating, blocking, unblocking,
 * and soft-deleting an access identified by its UUID.
 */
class AccessResource
{
    private IDBConnector $dbConnector;
    private string $uuid;

    /**
     * Initializes the resource for the specified access UUID.
     *
     * @param IDBConnector $dbConnector Database connector instance.
     * @param string $uuid Unique identifier of the access.
     */
    function __construct(IDBConnector $dbConnector, string $uuid)
    {
        $this->dbConnector = $dbConnector;
        $this->uuid = $uuid;
    }

    /**
     * Checks whether a non-dropped access with the specified UUID exists.
     *
     * @return bool True if the access exists, false otherwise.
     */
    public function exists(): bool {
        $query = "SELECT COUNT(*) FROM accesses WHERE uuid = :uuid AND is_dropped = 0";
        $params = [':uuid' => $this->uuid];
        $count = $this->dbConnector->fetchOne($query, $params)['COUNT(*)'] ?? 0;
        return $count > 0;
    }

    /**
     * Retrieves the access associated with the specified UUID.
     *
     * @return Access The access instance.
     *
     * @throws AccessNotFoundException If the access does not exist or has been dropped.
     */
    public function get(): Access {
        $query = "SELECT * FROM accesses WHERE uuid = :uuid AND is_dropped = 0";
        $params = [':uuid' => $this->uuid];
        $result = $this->dbConnector->fetchOne($query, $params);
        return $result ? new Access($result) : throw new AccessNotFoundException($this->uuid);
    }

    /**
     * Creates a new VPN access record.
     *
     * @param int $userId ID of the user who owns the access.
     * @param string $AccessType VPN protocol or access type.
     * @param int $server_id ID of the server associated with the access.
     * @param string|null $name Optional access name.
     *
     * @return int ID of the newly created access record.
     */
    public function create(int $userId, string $AccessType, int $server_id, ?string $name = null): int {
        $query = "INSERT INTO accesses (user_id, uuid, type, server_id, name) VALUES (:user_id, :uuid, :type, :server_id, :name)";
        $params = [
            ':user_id' => $userId,
            ':uuid' => $this->uuid,
            ':type' => $AccessType,
            ':server_id' => $server_id,
            ':name' => $name
        ];

        return $this->dbConnector->insert($query, $params);
    }

    /**
     * Updates the name of a non-dropped access.
     *
     * @param string $name New access name.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
    public function update(string $name): bool {
        $query = "UPDATE accesses SET name = :name WHERE uuid = :uuid AND is_dropped = 0";
        $params = [
            ':name' => $name,
            ':uuid' => $this->uuid
        ];

        return $this->dbConnector->execute($query, $params);
    }

    /**
     * Disables a non-dropped access and records the disabling timestamp.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
    public function block(): bool {
        $query = "UPDATE accesses SET is_active = 0, disabled_at = NOW() WHERE uuid = :uuid AND is_dropped = 0";
        $params = [':uuid' => $this->uuid];
        return $this->dbConnector->execute($query, $params);
    }

    /**
     * Enables a non-dropped access and clears its disabling timestamp.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
    public function unblock(): bool {
        $query = "UPDATE accesses SET is_active = 1, disabled_at = NULL WHERE uuid = :uuid AND is_dropped = 0";
        $params = [':uuid' => $this->uuid];
        return $this->dbConnector->execute($query, $params);
    }

    /**
     * Soft-deletes an access by marking it as dropped and inactive.
     *
     * Also records the disabling timestamp without removing
     * the access record from the database.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
    public function drop(): bool {
        $query = "UPDATE accesses SET is_active = 0, disabled_at = NOW(), is_dropped = 1 WHERE uuid = :uuid";
        $params = [':uuid' => $this->uuid];
        return $this->dbConnector->execute($query, $params);
    }
}