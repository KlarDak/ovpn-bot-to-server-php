<?php

namespace CNS\BotToServer\Node\OpenVPN;

use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Node\OpenVPN\Types\ServerResponse;
use Exception;

class AccessContract {
    function __construct(private OpenVPNFuncAdapter $OpenVPNAdapter) {}
    public function get(string $uuid) : ServerResponse {
        return new ServerResponse($this->OpenVPNAdapter->request("GET", "/config/$uuid"));
    }

    public function create(string $uuid, string $type, int $ttl) : ServerResponse {
        return new ServerResponse($this->OpenVPNAdapter->request("POST", "/config", [
            "uuid" => $uuid,
            "type" => $type,
            "time" => $ttl
        ]));
    }

    public function recreate(string $uuid, string $type, int $ttl) : ServerResponse {
        return new ServerResponse($this->OpenVPNAdapter->request("PUT", "/config/$uuid", [
            "time" => $ttl,
            "type" => $type
        ]));
    }

    public function update(string $uuid, ?string $type = null, ?int $ttl = null) : ServerResponse {
        if ($type == null && $ttl == null) {
            throw new Exception("Error with arguments");
        }

        $options = [];

        if ($type != null) {
            $options["type"] = $type;
        }

        if ($ttl != null) {
            $options["time"] = $ttl;
        }
        
        return new ServerResponse($this->OpenVPNAdapter->request("PATCH", "/config/$uuid", $options));
    }

    public function delete(string $uuid) : ServerResponse {
        return new ServerResponse($this->OpenVPNAdapter->request("DELETE", "/config/$uuid"));
    }

    public function updateAll(array $uuids, string $type, int $ttl) {
        if ($type == null && $ttl == null) {
            throw new Exception("Error with arguments");
        }

        $options = [
            "uuids" => $uuids
        ];

        if ($type != null) {
            $options["type"] = $type;
        }

        if ($ttl != null) {
            $options["time"] = $ttl;
        }
        
        return new ServerResponse($this->OpenVPNAdapter->request("PATCH", "/configs/update", $options));
    }

    public function deleteAll(array $uuids) : ServerResponse {
        return new ServerResponse($this->OpenVPNAdapter->request("DELETE", "/configs/delete", [
            "uuids" => $uuids
        ]));
    }
}