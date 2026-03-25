<?php

namespace CNS\OvpnBotToServer\Databases;

interface IDBConnector
{
    /**
     * Execute command with bool response
     * 
     * @param string $query Database query
     * @param array @params Query parameters
     * 
     * @return bool
     */
    public function execute(string $query, array $params = []): bool;

    /**
     * Execute command with one-dimensional array response
     * 
     * @param string $query Database query
     * @param array $params Query parameters
     * 
     * @return array|null
     */
    public function fetchOne(string $query, array $params = []): array|null;

    /**
     * Execute command with multidimensional array in response
     * 
     * @param string $query Database query
     * @param array $params Query parameters
     * 
     * @return array|null
     */
    public function fetchAll(string $query, array $params = []): array|null;

    /**
     * Get ID of last inserted row
     * 
     * @return string 
     */
    public function lastInsertId(): string;

    public function __serialize(): array;

    public function __unserialize(array $serialized_data): void;
}