<?php

namespace CNS\BotToServer\Contracts\OpenVPN\DTO;

class DiskMetrics {
    public float $totalGB;
    public float $usedGB;
    public float $availableGB;
    public float $usagePercent;

    function __construct(array $diskMetricsData)
    {
        $this->totalGB = $diskMetricsData["total_gb"];
        $this->usedGB = $diskMetricsData["used_gb"];
        $this->availableGB = $diskMetricsData["available_gb"];
        $this->usagePercent = $diskMetricsData["usage_percent"];
    }
}