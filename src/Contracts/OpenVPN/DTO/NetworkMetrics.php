<?php

namespace CNS\BotToServer\Contracts\OpenVPN\DTO;

class NetworkMetrics {
    public float $incomingKBSec;
    public float $outgoingKBSec;
    public float $totalKBSec;

    function __construct(array $networkMetricsData)
    {
        $this->incomingKBSec = $networkMetricsData["incoming_kb_sec"];
        $this->outgoingKBSec = $networkMetricsData["outgoing_kb_sec"];
        $this->totalKBSec = $networkMetricsData["total_kb_sec"];
    }
}