<?php

namespace CNS\BotToServer\Database;

interface IDBConnector {
    public function fetchOne(string $query, array $params = []) : ?array;

    public function fetchAll(string $query, array $params = []) : array;

    public function execute(string $query, array $params = []) : bool;
}