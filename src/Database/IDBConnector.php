<?php

namespace CNS\BotToServer\Database;

/**
 * Defines the database operations required by the BTS library.
 *
 * Allows custom database connectors to be implemented
 * independently of the underlying database driver.
 */
interface IDBConnector
{
    /**
     * Fetches a single row from the database.
     *
     * @param string $query SQL query to execute.
     * @param array $params Parameters to bind to the query.
     *
     * @return array|null The fetched row as an associative array,
     *                    or null if no matching row exists.
     */
    public function fetchOne(string $query, array $params = []): ?array;

    /**
     * Fetches all rows matching the given query.
     *
     * @param string $query SQL query to execute.
     * @param array $params Parameters to bind to the query.
     *
     * @return array An array of associative arrays,
     *               or an empty array if no rows are found.
     */
    public function fetchAll(string $query, array $params = []): array;

    /**
     * Executes a database statement without fetching results.
     *
     * @param string $query SQL statement to execute.
     * @param array $params Parameters to bind to the statement.
     *
     * @return bool True if the statement was executed successfully,
     *              false otherwise.
     */
    public function execute(string $query, array $params = []): bool;

    /**
     * Inserts a new record into the database.
     *
     * @param string $query SQL INSERT statement to execute.
     * @param array $params Parameters to bind to the statement.
     *
     * @return int The ID of the newly inserted record.
     */
    public function insert(string $query, array $params = []): int;

    /**
     * Starts a new database transaction.
     *
     * @return bool True if the transaction was started successfully,
     *              false otherwise.
     */
    public function beginTransaction(): bool;

    /**
     * Commits the current database transaction.
     *
     * @return bool True if the transaction was committed successfully,
     *              false otherwise.
     */
    public function commit(): bool;

    /**
     * Rolls back the current database transaction.
     *
     * @return bool True if the transaction was rolled back successfully,
     *              false otherwise.
     */
    public function rollBack(): bool;
}