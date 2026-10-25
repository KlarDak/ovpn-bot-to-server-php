<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Types\User;
use DateTimeImmutable;

/**
 * Provides methods for retrieving collections of user records.
 *
 * Supports querying users by subscription expiration dates
 * and retrieving active or dropped user records.
 */
class UsersResource {
    private IDBConnector $dbConnector;

    /**
     * Initializes the users resource.
     *
     * @param IDBConnector $dbConnector Database connector instance.
     */
    function __construct(IDBConnector $dbConnector)
    {
        $this->dbConnector = $dbConnector;
    }

    /**
     * Retrieves non-dropped users whose subscriptions expire
     * within the specified date range, including both boundaries.
     *
     * @param DateTimeImmutable $start Start of the expiration date range.
     * @param DateTimeImmutable $end End of the expiration date range.
     *
     * @return User[] List of matching user instances.
     */
    public function expiresBetween(DateTimeImmutable $start, DateTimeImmutable $end) : array {
        $query = "SELECT * FROM users WHERE expired_at BETWEEN :start AND :end AND is_dropped = 0";
        $params = [
            ':start' => $start->format('Y-m-d H:i:s'),
            ':end' => $end->format('Y-m-d H:i:s')
        ];
        $results = $this->dbConnector->fetchAll($query, $params);
        return array_map(fn($result) => new User($result), $results);
    }

    /**
     * Retrieves non-dropped users whose subscriptions expired
     * before the specified date and time.
     *
     * @param DateTimeImmutable $date Expiration date threshold.
     *
     * @return User[] List of matching user instances.
     */
    public function expiredBefore(DateTimeImmutable $date) : array {
        $query = "SELECT * FROM users WHERE expired_at < :date AND is_dropped = 0";
        $params = [':date' => $date->format('Y-m-d H:i:s')];
        $results = $this->dbConnector->fetchAll($query, $params);
        return array_map(fn($result) => new User($result), $results);
    }

    /**
     * Retrieves all users that have not been dropped.
     *
     * Includes both enabled and disabled users.
     *
     * @return User[] List of user instances.
     */
    public function getAll() : array {
        $query = "SELECT * FROM users WHERE is_dropped = 0";
        $results = $this->dbConnector->fetchAll($query, []);
        return array_map(fn($result) => new User($result), $results);
    }

    /**
     * Retrieves all users marked as dropped.
     *
     * @return User[] List of dropped user instances.
     */
    public function dropped() : array {
        $query = "SELECT * FROM users WHERE is_dropped = 1";
        $results = $this->dbConnector->fetchAll($query, []);
        return array_map(fn($result) => new User($result), $results);
    }
}