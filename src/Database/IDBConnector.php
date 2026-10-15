<?php

namespace CNS\BotToServer\Database;

interface IDBConnector {
    public function fetchOne(string $query, array $params = []) : ?array;

    public function fetchAll(string $query, array $params = []) : array;

    public function execute(string $query, array $params = []) : bool;

    public function insert(string $query, array $params = []) : int;

    public function beginTransaction() : bool;

    public function commit() : bool;

    public function rollBack() : bool;
}