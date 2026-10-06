<?php

namespace CNS\BotToServer\Node\OpenVPN;

use CNS\BotToServer\Http\Interfaces\HttpClientInterface;

class ActiveContract {
    function __construct(HttpClientInterface $httpClient, private string $code) {}
    public function getAll() {

    }

    public function get(string $uuid) {

    }

    public function kick(string $uuid) {

    }

    public function ban(string $uuid) {

    }

    public function pardon(string $uuid) {

    }
}