<?php

namespace CNS\BotToServer\Node\OpenVPN;

use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Node\OpenVPN\Types\ServerResponse;

class MetricsContract {
    function __construct(private OpenVPNFuncAdapter $openVPNFuncAdapter) {}
    public function server() {
        // IN DEVELOPING
    }

    public function node() : ServerResponse {
        return new ServerResponse($this->openVPNFuncAdapter->request("GET", "/api/server/status"));
    }

    public function vpn() {
        // IN DEVELOPING
    }
    
}