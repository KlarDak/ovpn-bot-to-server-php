<?php

namespace CNS\OvpnBotToServer\Services;

use CNS\OvpnBotToServer\Databases\IDBConnector;
use CNS\OvpnBotToServer\Types\SubsResponse;

class SubsClient {
    private IDBConnector $db;
    function __construct(IDBConnector $db) {
        $this->db = $db;
    }

    public function createSubs(string $uuid, string $server, string $shortlink) : bool {
        if (empty($uuid) || empty($server) || empty($shortlink)) {
            return false;
        }

        $exec = $this->db->fetchOne("INSERT INTO subs (sub_uuid, sub_link, sub_server) VALUES (:uuid, :link, :server)", [
            ":uuid" => $uuid,
            ":link" => $shortlink,
            ":server" => $server
        ]);

        return $exec !== false;
    }
    public function getSubs(string $uuid) : SubsResponse {
        $data = $this->db->fetchOne("SELECT * FROM subs WHERE sub_uuid = :uuid", [
            ":uuid" => $uuid
        ]);

        if (!$data) {
            throw new \Exception("Subscription not found");
        }

        return new SubsResponse($data);
    }
    public function deleteSubs(string $uuid) : bool {
        if (empty($uuid)) {
            return false;
        }

        $exec = $this->db->fetchOne("DELETE FROM subs WHERE sub_uuid = :uuid", [
            ":uuid" => $uuid
        ]);

        return $exec !== false;
    }
}