<?php

namespace CNS\BotToServer\Node\OpenVPN;

use CNS\BotToServer\Http\Interfaces\HttpClientInterface;

class MetricsContract {
    function __construct(private HttpClientInterface $http_client, private string $code) {}
    public function server() {}

    public function node() {}

    public function vpn() {}
    
}