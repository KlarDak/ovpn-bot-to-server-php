<?php

namespace CNS\OvpnBotToServer\Services;

use GuzzleHttp\Client;
use CNS\OvpnBotToServer\Types\Env;
use CNS\OvpnBotToServer\Utils\JwtGenerator;
use CNS\OvpnBotToServer\Types\Response;
use GuzzleHttp\Exception\GuzzleException;

class ApiCronClient {
    public Client $client;
    public string $server_id;

    function __construct(string $server_id)
    {
        $this->client = new Client([
            "base_uri" => Env::getAddressByID($server_id),
            "timeout" => 5.0,
            "http_errors" => false
        ]);
    }

    public function banUser(string $uuid) : bool {
        $banUser = $this->queryConfig("ban", $uuid);

        return ($banUser === 200) ? true : false;
    }

    public function pardonUser(string $uuid) : bool {
        $pardonUser = $this->queryConfig("pardon", $uuid);

        return ($pardonUser === 200) ? true : false;
    }

    public function queryConfig(string $endpoint, string $uuid) : Response|false {
        try {
            $token = JwtGenerator::createToken($this->server_id, time() + 36000, "active", "bot");
            $getResult = $this->client->request("post", "/v2.0/bot/active/$endpoint", array_merge($this->mergeHeaders($token), [
                "uuid" => $uuid
            ]));
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