<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Types\Subs;
use Exception;

class SubsResource {
    function __construct(private IDBConnector $dbConnector) {}

    public function create(string $short_code, int $server_id, int $access_id) : bool {
        $query = "INSERT INTO subs (short_code, server_id, access_id) VALUES (:short_code, :server_id, :access_id)";
        $params = [
            ":short_code" => $short_code,
            ":server_id" => $server_id,
            ":access_id" => $access_id
        ];

        return $this->dbConnector->execute($query, $params);
    }

    public function get(string $short_code) : Subs {
        $query = "SELECT * FROM subs WHERE short_code = :short_code AND is_dropped = 0";
        $params = [
            ":short_code" => $short_code
        ];

        $result = $this->dbConnector->fetchOne($query, $params);
        # TODO: Change error type
        return ($result) ? new Subs($result) : throw new Exception("ERROR TEST");
    }

    public function update(string $old_short_code, string $new_short_code) : bool {
        $query = "UPDATE subs SET short_code = :new_short_code WHERE short_code = :old_short_code AND is_dropped = 0";
        $params = [
            ":old_short_code" => $old_short_code,
            ":new_short_code" => $new_short_code
        ];
        return $this->dbConnector->execute($query, $params);
    }

    public function updateUser(string $short_code) : bool {
        $query = "UPDATE subs SET is_used = 1, used_at = NOW() WHERE short_code = :short_code AND is_dropped = 0";
        $params = [
            ":short_code" => $short_code,
        ];
        return $this->dbConnector->execute($query, $params);
    }

    public function drop(string $short_code) : bool {
        $query = "UPDATE subs SET is_dropped = 1 WHERE short_code = :short_code";
        $params = [
            ":short_code" => $short_code
        ];

        return $this->dbConnector->execute($query, $params);
    }
}