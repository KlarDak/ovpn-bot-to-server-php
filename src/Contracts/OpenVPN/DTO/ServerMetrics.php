<?php

namespace CNS\BotToServer\Contracts\OpenVPN\DTO;

use DateTimeImmutable;

class ServerMetrics {
    public DateTimeImmutable $timestamp;
    public CpuMetrics $cpu;
    public MemoryMetrics $memory;
    public DiskMetrics $disk;
    public NetworkMetrics $network;
    public float $uptimeSeconds;

    function __construct(array $responseData)
    {
        $this->timestamp = new DateTimeImmutable($responseData["timestamp"]);
        $this->cpu = new CpuMetrics($responseData["cpu"]);
        $this->memory = new MemoryMetrics($responseData["memory"]);
        $this->disk = new DiskMetrics($responseData["disk"]);
        $this->network = new NetworkMetrics($responseData["network"]);
        $this->uptimeSeconds = $responseData["uptime_seconds"];
    }
}