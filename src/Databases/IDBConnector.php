<?php

namespace CNS\OvpnBotToServer\Databases;

interface IDBConnector
{
    public function execute(string $query, array $params = []): bool;

    public function fetchOne(string $query, array $params = []): array|null;

    public function fetchAll(string $query, array $params = []): array;

    public function lastInsertId(): string;

    public function __serialize(): array;

    public function __unserialize(array $serialized_data): void;
}