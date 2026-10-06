<?php

namespace CNS\BotToServer\Types;

use DateTimeImmutable;

class Server {
    public int $id;
    public string $code;
    public string $name;
    public string $host;
    public int $port;
    public ?string $api_endpoint = null;
    public string $encrypt_secret_code;
    public AccessType $type;
    public ?string $subs_url = null;
    public DateTimeImmutable $created_at;
    public string $status;
    public DateTimeImmutable $disabled_at;
    public bool $is_dropped = false;

    function __construct(array $serverData)
    {
        $this->id = $serverData['id'];
        $this->code = $serverData['code'];
        $this->name = $serverData['name'];
        $this->host = $serverData['host'];
        $this->port = $serverData['port'];
        $this->api_endpoint = $serverData['api_endpoint'] ?? null;
        $this->encrypt_secret_code = $serverData['encrypt_secret_code'];
        $this->type = AccessType::from($serverData['type']);
        $this->subs_url = $serverData['subs_url'] ?? null;
        $this->created_at = new DateTimeImmutable($serverData['created_at']);
        $this->status = $serverData['status'];
        $this->disabled_at = new DateTimeImmutable($serverData['disabled_at']);
        $this->is_dropped = (bool)$serverData['is_dropped'];
    }
}