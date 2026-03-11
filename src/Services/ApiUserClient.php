<?php

namespace CNS\OvpnBotToServer\Services;

use GuzzleHttp\Client;
use CNS\OvpnBotToServer\Types\Env;
use CNS\OvpnBotToServer\Types\Response;
use CNS\OvpnBotToServer\Utils\JwtGenerator;
use CNS\OvpnBotToServer\Utils\Utils;
use GuzzleHttp\Exception\GuzzleException;

class ApiUserClient {
    public Client $client;
    public array $endpoint = [
        "config" => "/v2.0/users/config",
        // "bot" => "/v2.0/bot/"
        "download" => "/v2.0/configs/download"
    ];
    public string $server_id;

    function __construct(string $server_id)
    {
        $this->server_id = $server_id;

        $this->client = new Client([
            "base_uri" => Env::getAddressByID($server_id),
            "timeout" => 10,
            "http_errors" => false
        ]);
    }

    public function getConfig(string $uuid) {
        return $this->queryConfig("get", $this->endpoint["config"] . "/$uuid", "get");
    }

    public function postConfig(string $uuid, int $time, string $type) : Response|false {
        if (!in_array($type, ["user", "admin", "unlimit"]) || $time < 0) {
            return false;
        }

        return $this->queryConfig("post", $this->endpoint["config"], "create", ["json" => [
            "uuid" => $uuid,
            "time" => $time,
            "type" => $type
        ]]);
    }

    public function putConfig(string $uuid, int $time, string $type) : Response|false {
        if (!in_array($type, ["user", "admin", "unlimit"]) || $time < 0) {
            return false;
        }

        return $this->queryConfig("put", $this->endpoint["config"], "recreate", ["json" => [
            "uuid" => $uuid,
            "time" => $time,
            "type" => $type
        ]]);
    }

    public function patchConfig(string $uuid, ?int $time = null, ?string $type = null) : Response|false {
        if (!in_array($type, ["user", "admin", "unlimit"]) && $time < 0) {
            return false;
        }

        return $this->queryConfig("patch", $this->endpoint["config"], "update", ["json" => [
            "uuid" => $uuid,
            "time" => $time,
            "type" => $type
        ]]);
    }

    public function deleteConfig(string $uuid) : Response|false {
        return $this->queryConfig("delete", $this->endpoint["config"] . "/$uuid", "delete");
    }

    public function downloadConfig(string $uuid, string $short_link) {
        $token = JwtGenerator::createToken($this->server_id, time() + 36000, "download", "user");

        $getConfig = $this->client->request("get", $this->endpoint["download"] . "/$short_link", [
            "headers" => [
                "Authorization" => "Bearer $token",
                "Accept" => "application/octet-stream",
                "Content-Type" => "application/json"
            ]
        ]);

        if ($getConfig->getStatusCode() !== 200) {
            return false;
        }

        Utils::saveOvpnFile($uuid, $getConfig->getBody()->getContents());

        return (Utils::isOvpnFileExists($uuid)) ? true : false;
    }

    public function queryConfig(string $method, string $endpoint, string $type, ?array $params = null, ?string $role = "user") : Response|false {
        try {
            $token = JwtGenerator::createToken($this->server_id, time() + 36000, $type, $role);
            $getResult = $this->client->request($method, $endpoint, array_merge($this->mergeHeaders($token), $params ?? []));
            $decoded_data = json_decode($getResult->getBody()->getContents(), true);
            return ($decoded_data === null) ? false : new Response($decoded_data);
        }
        catch (GuzzleException $error) {
            return false;
        }
    }

    private function mergeHeaders(string $token) {
        return [
            "headers" => [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => "Bearer $token"
            ]
        ];
    }
}