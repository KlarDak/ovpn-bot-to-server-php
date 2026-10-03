<?php

namespace CNS\BotToServer\Node\OpenVPN;

use GuzzleHttp\Client;

class AccessContract {
    function __construct(public Client $client, public readonly string $code, public readonly string $subs_url) {}
    public function get(string $uuid) {

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