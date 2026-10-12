<?php

namespace CNS\BotToServer\Contracts\OpenVPN\Types;

use CNS\BotToServer\Contracts\OpenVPN\DTO\ServerMetrics;
use CNS\BotToServer\Contracts\OpenVPN\Types\ServerResponse;

class MetricsResponse extends ServerResponse {
    public ?ServerMetrics $server;
    function __construct(array $serverResponse)
    {
        parent::__construct($serverResponse);
        
        $this->server = new ServerMetrics($serverResponse["data"]) ?? null;
    }
}