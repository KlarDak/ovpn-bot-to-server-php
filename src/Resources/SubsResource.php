<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Types\Subs;

/**
 * Provides methods for managing VPN subscription records.
 *
 * Supports creating, retrieving, updating, marking as used,
 * and soft-deleting subscriptions identified by their short codes.
 */
class SubsResource
{
    /**
     * Initializes the subscription resource.
     *
     * @param IDBConnector $dbConnector Database connector instance.
     */
    function __construct(private IDBConnector $dbConnector) {}

    /**
     * Creates a new subscription associated with a server and an access.
     *
     * @param string $short_code Unique subscription short code.
     * @param int $server_id ID of the associated server.
     * @param int $access_id ID of the associated access.
     *
     * @return int ID of the newly created subscription.
     */
    public function create(string $short_code, int $server_id, int $access_id) : int {
        $query = "INSERT INTO subs (short_code, server_id, access_id) VALUES (:short_code, :server_id, :access_id)";
        $params = [
            ":short_code" => $short_code,
            ":server_id" => $server_id,
            ":access_id" => $access_id
        ];

        return $this->dbConnector->insert($query, $params);
    }

    /**
     * Retrieves a non-dropped subscription by its short code.
     *
     * @param string $short_code Subscription short code.
     *
     * @return Subs|null The subscription instance, or null if not found.
     */
    public function get(string $short_code) : ?Subs {
        $query = "SELECT * FROM subs WHERE short_code = :short_code AND is_dropped = 0";
        $params = [
            ":short_code" => $short_code
        ];

        $result = $this->dbConnector->fetchOne($query, $params);

        return ($result !== null) ? new Subs($result) : null;
    }

    /**
     * Updates the short code of a non-dropped subscription.
     *
     * @param string $old_short_code Current subscription short code.
     * @param string $new_short_code New subscription short code.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
    public function update(string $old_short_code, string $new_short_code) : bool {
        $query = "UPDATE subs SET short_code = :new_short_code WHERE short_code = :old_short_code AND is_dropped = 0";
        $params = [
            ":old_short_code" => $old_short_code,
            ":new_short_code" => $new_short_code
        ];
        return $this->dbConnector->execute($query, $params);
    }

    /**
     * Marks a non-dropped subscription as used.
     *
     * Sets the usage flag and records the current timestamp.
     *
     * @param string $short_code Subscription short code.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
    public function setUsed(string $short_code) : bool {
        $query = "UPDATE subs SET is_used = 1, used_at = NOW() WHERE short_code = :short_code AND is_dropped = 0";
        $params = [
            ":short_code" => $short_code,
        ];
        return $this->dbConnector->execute($query, $params);
    }

    /**
     * Soft-deletes a subscription by marking it as dropped.
     *
     * The subscription record remains in the database
     * but is excluded from subsequent retrieval operations.
     *
     * @param string $short_code Subscription short code.
     *
     * @return bool True if the database operation succeeds, false otherwise.
     */
    public function drop(string $short_code) : bool {
        $query = "UPDATE subs SET is_dropped = 1 WHERE short_code = :short_code";
        $params = [
            ":short_code" => $short_code
        ];

        return $this->dbConnector->execute($query, $params);
    }
}