<?php

namespace CNS\BotToServer\Contracts\OpenVPN\DTO;

use CNS\BotToServer\Contracts\OpenVPN\DTO\ServerMetrics;
use CNS\BotToServer\Contracts\OpenVPN\Types\ServerResponse;
use DateTimeImmutable;

class NodeStatusMetrics {
    public ?DateTimeImmutable $date;
    public int $serverNumber;
    public string $serverCode;
    public bool $isServerWorking;
    public bool $isRedisRunning;
    public bool $isConfigsDirExists;
    public bool $isConfigsDBExists;
    public bool $isOVPNActive;
    function __construct(array $serverResponse)
    {        
        $this->date = new DateTimeImmutable($serverResponse["date"]);
        $this->serverNumber = $serverResponse["server_number"];
        $this->serverCode = $serverResponse["server_code"];
        $this->isServerWorking = $serverResponse["isServerWorking"];
        $this->isRedisRunning = $serverResponse["isRedisRunning"];
        $this->isConfigsDirExists = $serverResponse["isConfigsDirExists"];
        $this->isConfigsDBExists = $serverResponse["isConfigsDBExists"];
        $this->isOVPNActive = $serverResponse["isOVPNActive"];
    }
}