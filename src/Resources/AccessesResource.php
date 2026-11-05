<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Types\Access;

/**
 * Provides access to a user's VPN access records.
 *
 * Supports retrieving and counting non-dropped accesses,
 * optionally filtered by VPN type.
 */
class AccessesResource
{
    private IDBConnector $dbConnector;
    private int $user_id;

    /**
     * Initializes the resource for the specified user.
     *
     * @param IDBConnector $dbConnector Database connector instance.
     * @param int $user_id ID of the user whose accesses are managed.
     */
    function __construct(IDBConnector $dbConnector, int $user_id)
    {
        $this->dbConnector = $dbConnector;
        $this->user_id = $user_id;
    }

    /**
     * Retrieves all non-dropped accesses belonging to the user.
     *
     * Results can optionally be filtered by VPN type.
     *
     * @param string|null $vpn_type VPN type to filter by, or null for all types.
     *
     * @return Access[] List of access instances.
     */
    public function list(?string $vpn_type = null): array
    {
        $query = "SELECT * FROM accesses WHERE user_id = :user_id AND is_dropped = 0";

        $params = [':user_id' => $this->user_id];

        if ($vpn_type !== null) {
            $query .= " AND type = :type";
            $params[':type'] = $vpn_type;
        }

        $results = $this->dbConnector->fetchAll($query, $params);

        return array_map(
            static fn(array $result) => new Access($result),
            $results
        );
    }

    /**
     * Counts non-dropped accesses belonging to the user.
     *
     * Results can optionally be filtered by VPN type.
     *
     * @param string|null $vpn_type VPN type to filter by, or null for all types.
     *
     * @return int Database row containing the access count.
     */
    public function count(?string $vpn_type = null): int {
        $query = "SELECT COUNT(*) FROM accesses WHERE ". ($vpn_type ? "type = :vpn_type AND" : "") ." user_id = :user_id AND is_dropped = 0";
        $params = [':user_id' => $this->user_id];
        if ($vpn_type) {
            $params[':vpn_type'] = $vpn_type;
        }
        $result = $this->dbConnector->fetchOne($query, $params);

        return (int) ($result['total'] ?? 0);
    }
}