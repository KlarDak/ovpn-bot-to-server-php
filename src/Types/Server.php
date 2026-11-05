<?php

namespace CNS\BotToServer\Types;

use DateTimeImmutable;

/**
 * Represents a VPN server record.
 *
 * Contains server identification, connection settings,
 * VPN protocol type, subscription URL, and operational status.
 */
class Server
{
    /** @var int Internal server ID. */
    public int $id;

    /** @var string Unique server code. */
    public string $code;

    /** @var string Server display name. */
    public string $name;

    /** @var string Server hostname or IP address. */
    public string $host;

    /** @var int Server connection port. */
    public int $port;

    /** @var string|null Optional server API endpoint. */
    public ?string $api_endpoint = null;

    /** @var string Server encryption secret. */
    public string $encrypt_secret_code;

    /** @var string VPN server type. */
    public string $type;

    /** @var string|null Optional subscription URL. */
    public ?string $subs_url = null;

    /** @var DateTimeImmutable Server creation date and time. */
    public DateTimeImmutable $created_at;

    /** @var string Current server operational status. */
    public string $status;

    /** @var DateTimeImmutable|null Date and time when the server was disabled. */
    public ?DateTimeImmutable $disabled_at = null;

    /** @var bool Whether the server has been soft-deleted. */
    public bool $is_dropped = false;

    /**
     * Initializes a server instance from a database record.
     *
     * Maps database fields to object properties and converts
     * timestamp values into DateTimeImmutable instances.
     *
     * @param array<string, mixed> $serverData Associative array containing server data.
     *
     * @throws \Exception If a timestamp cannot be parsed.
     */
    function __construct(array $serverData)
    {
        $this->id = $serverData['id'];
        $this->code = $serverData['code'];
        $this->name = $serverData['name'];
        $this->host = $serverData['host'];
        $this->port = $serverData['port'];
        $this->api_endpoint = $serverData['api_endpoint'] ?? null;
        $this->encrypt_secret_code = $serverData['encrypt_secret_code'];
        $this->type = $serverData['type'];
        $this->subs_url = $serverData['subs_url'] ?? null;
        $this->created_at = new DateTimeImmutable($serverData['created_at']);
        $this->status = $serverData['status'];
        $this->disabled_at = ($serverData["disabled_at"] !== null) ? new DateTimeImmutable($serverData['disabled_at']) : null;
        $this->is_dropped = (bool) $serverData['is_dropped'];
    }
}