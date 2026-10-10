<?php

namespace CNS\BotToServer\Contracts\OpenVPN;

use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Contracts\OpenVPN\Types\ServerResponse;
use CNS\BotToServer\Types\Server;

class ActiveContract {
    function __construct(private OpenVPNFuncAdapter $openVPNAdapter) {}
    public function getAll() : array {
        return array_map(fn($item) => new ServerResponse($item), $this->openVPNAdapter->request("POST", "/api/active/list"));
    }

    public function get(string $uuid) {
        // IN DEVELOPING
    }

    public function kick(string $uuid) : ServerResponse{
        return new ServerResponse($this->openVPNAdapter->request("POST", "/api/active/kick", ["uuid" => $uuid]));
    }

    public function ban(string $uuid) : ServerResponse {
        return new ServerResponse($this->openVPNAdapter->request("POST", "/api/active/ban", ["uuid" => $uuid]));
    }

    public function pardon(string $uuid) : ServerResponse {
        return new ServerResponse($this->openVPNAdapter->request("POST", "/api/active/pardon", ["uuid" => $uuid]));
    }
}