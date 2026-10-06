<?php

namespace CNS\BotToServer\Node\OpenVPN;

use CNS\BotToServer\Http\Interfaces\HttpClientInterface;

class AccessContract {
    function __construct(private readonly HttpClientInterface $httpClient, private readonly string $code) {}
    public function get(string $uuid) {
        // $this->
    }

    public function create(string $uuid, string $type, int $time) {

    }

    public function recreate(string $uuid, string $type, int $time) {

    }

    public function update(string $type, int $time) {

    }

    public function drop(string $uuid) {
        
    }

    
}