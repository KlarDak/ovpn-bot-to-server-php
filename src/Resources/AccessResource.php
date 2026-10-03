<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Exceptions\AccessNotFoundException;
use CNS\BotToServer\Types\Access;
use CNS\BotToServer\Types\AccessType;
use CNS\BotToServer\Types\VPNType;

class AccessResource {
    private IDBConnector $dbConnector;
    private string $uuid;
    function __construct(IDBConnector $dbConnector, string $uuid)
    {
        $this->dbConnector = $dbConnector;
        $this->uuid = $uuid;
    }

    public function exists() : bool {
        $query = "SELECT COUNT(*) FROM accesses WHERE uuid = :uuid";
        $params = [':uuid' => $this->uuid];
        $count = $this->dbConnector->fetchOne($query, $params)['COUNT(*)'] ?? 0;
        return $count > 0;
    }

    public function get() : Access {
        $query = "SELECT * FROM accesses WHERE uuid = :uuid";
        $params = [':uuid' => $this->uuid];
        $result = $this->dbConnector->fetchOne($query, $params);
        return $result ? new Access($result) : throw new AccessNotFoundException($this->uuid);
    }

    public function create(int $userId, VPNType $VPNType, int $server_id, ?string $name = null) : bool {
        $query = "INSERT INTO accesses (user_id, uuid, type, server_id, name) VALUES (:user_id, :uuid, :type, :server_id, :name)";
        $params = [
            ':user_id' => $userId,
            ':uuid' => $this->uuid,
            ':type' => $VPNType->value,
            ':server_id' => $server_id,
            ':name' => $name
        ];

        return $this->dbConnector->execute($query, $params);
    }
    
    public function update(string $name) : bool {
        $query = "UPDATE accesses SET name = :name WHERE uuid = :uuid AND is_dropped = 0";
        $params = [
            ':name' => $name,
            ':uuid' => $this->uuid
        ];

        return $this->dbConnector->execute($query, $params);
    }
    public function block() : bool{
        $query = "UPDATE accesses SET is_active = 0 AND disabled_at = NOW() WHERE uuid = :uuid AND is_dropped = 0";
        $params = [':uuid' => $this->uuid];
        return $this->dbConnector->execute($query, $params);
    }
    public function unblock() : bool {
        $query = "UPDATE accesses SET is_active = 1 AND disabled_at = NULL WHERE uuid = :uuid AND is_dropped = 0";
        $params = [':uuid' => $this->uuid];
        return $this->dbConnector->execute($query, $params);
    }
    public function drop() : bool {
        $query = "UPDATE accesses SET is_active = 0, disabled_at = NOW(), is_dropped = 1 WHERE uuid = :uuid";
        $params = [':uuid' => $this->uuid];
        return $this->dbConnector->execute($query, $params);
    }
}