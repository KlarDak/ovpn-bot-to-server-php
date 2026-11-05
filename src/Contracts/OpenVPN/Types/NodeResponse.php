<?php

namespace CNS\BotToServer\Contracts\OpenVPN\Types;

use CNS\BotToServer\Contracts\OpenVPN\DTO\NodeStatusMetrics;
use CNS\BotToServer\Contracts\OpenVPN\Types\ServerResponse;

class NodeResponse extends ServerResponse {
    public ?NodeStatusMetrics $node;
    function __construct(array $serverResponse)
    {
        parent::__construct($serverResponse);
        
        $this->node = new NodeStatusMetrics($serverResponse["data"]) ?? null;
    }
}