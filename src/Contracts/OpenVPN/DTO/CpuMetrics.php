<?php

namespace CNS\BotToServer\Contracts\OpenVPN\DTO;

class CpuMetrics {
    public float $usagePercent;
    public float $avgLoad;
    public int $cores;

    function __construct(array $cpuMetricsData)
    {
        $this->usagePercent = $cpuMetricsData["usage_percent"];
        $this->avgLoad = $cpuMetricsData["avg_load"];
        $this->cores = $cpuMetricsData["cores"];
    }
}