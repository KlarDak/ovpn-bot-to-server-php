<?php

namespace CNS\BotToServer\Contracts\OpenVPN\DTO;

class MemoryMetrics {
    public float $totalGB;
    public float $activeGB;
    public float $freeGB;
    public float $usagePercent;

    function __construct(array $memoryMetricsData)
    {
        $this->totalGB = $memoryMetricsData["total_gb"];
        $this->activeGB = $memoryMetricsData["active_gb"];
        $this->freeGB = $memoryMetricsData["free_gb"];
        $this->usagePercent = $memoryMetricsData["usage_percent"];
    }
}