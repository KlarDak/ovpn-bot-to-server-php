<?php

namespace KSD\OvpnBotToServer\Services;

use CNS\OvpnBotToServer\Databases\IDBConnector;
use CNS\OvpnBotToServer\Types\SubsResponse;

class SubsClient {
    private static IDBConnector $db;
    public static function setDB(IDBConnector $db) {
        self::$db = $db;
    }

    public static function createSubs(string $uuid, string $server, string $shortlink) : bool {
        if (empty($uuid) || empty($server) || empty($shortlink)) {
            return false;
        }

        $exec = self::$db->fetchOne("INSERT INTO subs (sub_uuid, sub_link, sub_server) VALUES (:uuid, :link, :server)", [
            ":uuid" => $uuid,
            ":link" => $shortlink,
            ":server" => $server
        ]);

        return $exec !== false;
    }
    public static function getSubs(string $uuid) : SubsResponse {
        $data = self::$db->fetchOne("SELECT * FROM subs WHERE sub_uuid = :uuid", [
            ":uuid" => $uuid
        ]);

        if (!$data) {
            throw new \Exception("Subscription not found");
        }

        return new SubsResponse($data);
    }
    public static function deleteSubs(string $uuid) : bool {
        if (empty($uuid)) {
            return false;
        }

        $exec = self::$db->fetchOne("DELETE FROM subs WHERE sub_uuid = :uuid", [
            ":uuid" => $uuid
        ]);

        return $exec !== false;
    }
}