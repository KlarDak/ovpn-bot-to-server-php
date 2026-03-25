<?php

namespace CNS\OvpnBotToServer\Services;

use CNS\OvpnBotToServer\Exceptions\ApiException;
use GuzzleHttp\Client;
use CNS\OvpnBotToServer\Types\Env;
use CNS\OvpnBotToServer\Utils\JwtGenerator;
use CNS\OvpnBotToServer\Types\Response;
use GuzzleHttp\Exception\GuzzleException;

class ApiCronClient {
    /**
     * Connection variable
     * 
     * @var Client
     */
    public Client $client;
    
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
        $this->client = new Client([
            "base_uri" => Env::getAddressByID($server_id),
            "timeout" => 5.0,
            "http_errors" => false
        ]);
    }

    /**
     * Ban user on the server
     * 
     * @param string $uuid UUID identifier of config file
     * @return bool
     * @throws ApiException
     */
    public function banUser(string $uuid) : bool {
        try {
            $banUser = $this->queryConfig("ban", $uuid);

            return ($banUser->code === 200) ? true : false;
        }
        catch(ApiException $error) {
            return false;
        }
    }

    /**
     * Pardon user on the server
     * 
     * @param string $uuid UUID identifier of config file
     * @return bool
     * @throws ApiException
     */
    public function pardonUser(string $uuid) : bool {
        try {
            $pardonUser = $this->queryConfig("pardon", $uuid);

            return ($pardonUser->code === 200) ? true : false;
        }
        catch(ApiException $error) {
            return false;
        }
    }

    /**
     * API query configurator
     * 
     * @param string $endpoint Endpoint of query in the server
     * @param string $uuid UUID identifier of config file
     * @return Response|false
     * @throws GuzzleException
     */
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

    /**
     * Headers generator
     * 
     * @param string $token Authorization token
     * @return array
     */
    private function mergeHeaders(string $token) : array {
        return [
            "headers" => [
                "Accept" => "application/json",
                "Content-Type" => "application/json",
                "Authorization" => "Bearer $token"
            ]
        ];
    }
}