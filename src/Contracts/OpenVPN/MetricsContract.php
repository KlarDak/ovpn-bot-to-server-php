<?php

namespace CNS\BotToServer\Contracts\OpenVPN;

use CNS\BotToServer\Contracts\OpenVPN\Types\MetricsResponse;
use CNS\BotToServer\Contracts\OpenVPN\Types\NodeResponse;
use CNS\BotToServer\Contracts\OpenVPN\Types\ServerResponse;

class MetricsContract {
    function __construct(private OpenVPNFuncAdapter $openVPNFuncAdapter) {}
    public function server() {
        return new MetricsResponse($this->openVPNFuncAdapter->request("GET", "server/metrics"));
    }

    public function node() : ServerResponse {
        return new NodeResponse($this->openVPNFuncAdapter->request("GET", "/api/server/status"));
    }

    public function vpn() {
        // IN DEVELOPING
    }
    
}