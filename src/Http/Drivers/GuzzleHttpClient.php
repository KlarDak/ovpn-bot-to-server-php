<?php

namespace CNS\BotToServer\Http\Drivers;

use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Http\Types\HttpResponse;
use GuzzleHttp\Client;

class GuzzleHttpClient implements HttpClientInterface {
    private Client $client;

    function __construct(string $host, int $port, string $secret_key, string $api_endpoint = "/api") {
        try {
            $this->client = new Client([
                'base_uri' => $host . ':' . $port . $api_endpoint,
                'headers' => [
                    'Authorization' => 'Bearer ' . $secret_key,
                    'Content-Type' => 'application/json',
                ],
                'http_errors' => false,
            ]);
        }
        catch (\Exception $e) {
            throw new \RuntimeException('Failed to initialize GuzzleHttpClient: ' . $e->getMessage());
        }
    }

    public function request(string $method, string $endpoint, array $headers = [], array $options = []): HttpResponse {
        try {
            $response = $this->client->request($method, $endpoint, array_merge(['headers' => $headers], $options));
            
            return new HttpResponse(
                $response->getStatusCode(),
                $response->getHeaders(),
                (string) $response->getBody()
            );
        }
        catch (\Exception $e) {
            throw new \RuntimeException('Failed to make request: ' . $e->getMessage());
        }
    }

    public function stream(string $method, string $endpoint, array $headers = [], array $options = []): array {
        try {
            $response = $this->client->request($method, $endpoint, array_merge(['headers' => $headers], $options));
            return [
                'status' => $response->getStatusCode(),
                'headers' => $response->getHeaders(),
                'body' => (string) $response->getBody()
            ];
        }
        catch (\Exception $e) {
            throw new \RuntimeException('Failed to make stream request: ' . $e->getMessage());
        }
    }
}