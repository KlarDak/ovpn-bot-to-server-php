<?php

namespace CNS\BotToServer\Http\Interfaces;

use CNS\BotToServer\Http\Types\HttpResponse;

interface HttpClientInterface {
    public function request(string $method,string $endpoint, array $headers = [], array $options = []): HttpResponse;
    public function stream(string $method, string $endpoint, array $headers = [], array $options = []): array;
}