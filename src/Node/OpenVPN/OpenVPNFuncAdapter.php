<?php

namespace CNS\BotToServer\Node\OpenVPN;

use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Node\OpenVPN\Types\ServerResponse;

class OpenVPNFuncAdapter {
    private array $headers;

    function __construct(private HttpClientInterface $httpClient, string $JWTToken)
    {
        $this->headers = [
            "Authorization" => "Bearer " . $JWTToken
        ];
    }

    public function request(string $method, string $endpoint, ?array $options = null) : array{
        $requestResult = $this->httpClient->request($method, $endpoint, $options, $this->headers);

        return $requestResult->getJsonBody();
    }
}