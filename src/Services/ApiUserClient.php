<?php

namespace CNS\OvpnBotToServer\Services;

use CNS\OvpnBotToServer\Exceptions\ApiException;
use GuzzleHttp\Client;
use CNS\OvpnBotToServer\Types\Env;
use CNS\OvpnBotToServer\Types\Response;
use CNS\OvpnBotToServer\Utils\JwtGenerator;
use CNS\OvpnBotToServer\Utils\Utils;
use GuzzleHttp\Exception\GuzzleException;
use InvalidArgumentException;
use UnexpectedValueException;

class ApiUserClient {
    /**
     * Connection variable
     * 
     * @var Client
     */
    public Client $client;
    /**
     * Available endpoints
     * 
     * @var array
     */
    public array $endpoint = [
        "config" => "/v2.0/users/config",
        "download" => "/v2.0/configs/download"
    ];
    /**
     * Server identifier variable
     * 
     * @var string
     */
    public string $server_id;

    /**
     * Constructor of ApiCronClient class
     * 
     * @param string $server_id Server identifier
     */
    function __construct(string $server_id)
    {
        $this->server_id = $server_id;

        $this->client = new Client([
            "base_uri" => Env::getAddressByID($server_id),
            "timeout" => 10,
            "http_errors" => false
        ]);
    }

    /**
     * Get config file
     * 
     * @param string $uuid UUID identifier of config file
     * @return Response|false
     * @throws ApiException
     */
    public function getConfig(string $uuid) : Response|false
    {
        try {
            return $this->queryConfig("get", $this->endpoint["config"] . "/$uuid", "get");
        }
        catch(ApiException $error) {
            return false;
        }
    }

    /**
     * Create config file
     * 
     * @param string $uuid UUID identifier of config file
     * @param int $time Config file validity period (in seconds)
     * @return Response|false
     * @throws ApiException
     */
    public function postConfig(string $uuid, int $time, string $type) : Response|false {
        try {
            if (!in_array($type, ["user", "admin", "unlimit"]) || $time < 0) {
                return false;
            }

            return $this->queryConfig("post", $this->endpoint["config"], "create", ["json" => [
                "uuid" => $uuid,
                "time" => $time,
                "type" => $type
            ]]);
        }
        catch(ApiException $error) {
            return false;
        }
    }

    /**
     * Reissue config file and update metadata on server
     * 
     * @param string $uuid UUID identifier of config file
     * @param int $time Config file validity period (in seconds)
     * @return Response|false
     * @throws ApiException
     */
    public function putConfig(string $uuid, int $time, string $type) : Response|false {
        try {
            if (!in_array($type, ["user", "admin", "unlimit", "trial"]) || $time < 0) {
                return false;
            }

            return $this->queryConfig("put", $this->endpoint["config"], "recreate", ["json" => [
                "uuid" => $uuid,
                "time" => $time,
                "type" => $type
            ]]);
        }
        catch(ApiException $error) {
            return false;
        }
    }

    /**
     * Update metadata of config file on the server
     * 
     * @param string $uuid UUID identifier of config file
     * @param int $time Config file validity period (in seconds)
     * @return Response|false
     * @throws ApiException
     */
    public function patchConfig(string $uuid, ?int $time = null, ?string $type = null) : Response|false {
        try {
            if (!in_array($type, ["user", "admin", "unlimit", "trial"]) && $time < 0) {
                return false;
            }

            return $this->queryConfig("patch", $this->endpoint["config"], "update", ["json" => [
                "uuid" => $uuid,
                "time" => $time,
                "type" => $type
            ]]);
        }
        catch(ApiException $error) {
            return false;
        }
    }

    /**
     * Delete config file
     * 
     * @param string $uuid UUID identifier of config file
     * @return Response|false
     * @throws ApiException
     */
    public function deleteConfig(string $uuid) : Response|false {
        try {
            return $this->queryConfig("delete", $this->endpoint["config"] . "/$uuid", "delete");
        }
        catch(ApiException $error) {
            return false;
        }
    }

    /**
     * Download config file
     * 
     * @param string $uuid UUID identifier of config file
     * @param string $short_link Short link for download config file
     * @return bool
     * @throws GuzzleException
     */
    public function downloadConfig(string $uuid, string $short_link) : bool
    {
        try {
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
        catch(GuzzleException $error) {
            return false;
        }
    }

    /**
     * API query configurator
     * 
     * @param string $method HTTP method of query
     * @param string $endpoint Endpoint of query to the server
     * @param string $type Type of query
     * @param string $params Parameters in request body
     * @return Response|false
     * @throws GuzzleException
     */
    public function queryConfig(string $method, string $endpoint, string $type, ?array $params = null, ?string $role = "user") : Response|false {
        try {
            $token = JwtGenerator::createToken($this->server_id, time() + 36000, $type, $role);
            $getResult = $this->client->request($method, $endpoint, array_merge($this->mergeHeaders($token), (is_array($params)) ? $params : []));
            $decoded_data = json_decode($getResult->getBody()->getContents(), true);
            return ($decoded_data == null) ? false : new Response($decoded_data);
        }
        catch (GuzzleException $error) {
            return false;
        }
    }

    /**
     * Headers generator
     * 
     * @param string $token Authorization token
     * @return array
     */
    private function mergeHeaders(string $token) {
        return [
            "headers" => [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => "Bearer $token"
            ]
        ];
    }

    // ???????
    
    public function __serialize(): array
    {
        return [
            "endpoints" => $this->endpoint
        ];
    }

    public function __unserialize(array $data): void
    {
        if (empty($data["endpoints"]) || !is_array($data["endpoints"])) {
            throw new UnexpectedValueException("Error with arguments in unserialize method");
        }

        $this->endpoint = $data["endpoints"];
        $required_methods = ["config", "download"];
        
        foreach ($required_methods as $key) {
            if (!array_key_exists($key, $this->endpoint)) {
                throw new InvalidArgumentException("Missing endpoint argument was excepted: $key");
            }
        }
    }
}