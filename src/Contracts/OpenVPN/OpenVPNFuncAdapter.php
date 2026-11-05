<?php

namespace CNS\BotToServer\Contracts\OpenVPN;

use CNS\BotToServer\Http\Interfaces\HttpClientInterface;

class OpenVPNFuncAdapter {
    function __construct(private HttpClientInterface $httpClient, private \Closure $tokenFactory) {}

    public function request(string $method, string $endpoint, ?array $options = []) : array{
        $requestResult = $this->httpClient->request($method, $endpoint, 
            [
                "json" => $options
            ], 
            headers: [
                "Authorization" => "Bearer " . ($this->tokenFactory)()
            ]
        );

        return $requestResult->getJsonBody();
    }
}