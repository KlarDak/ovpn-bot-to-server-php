<?php

namespace CNS\BotToServer\Types;

/**
 * Represents a VPN access record.
 *
 * Contains access identification, ownership, server association,
 * creation timestamp, and current activation and deletion states.
 */
final class Access
{
    /** @var int Internal access ID. */
    public int $id;

    /** @var int ID of the user who owns the access. */
    public int $user_id;

    /** @var string Unique access identifier. */
    public string $uuid;

    /** @var string|null Optional access name. */
    public ?string $name = null;

    /** @var string VPN protocol or access type. */
    public string $type;

    /** @var int ID of the associated server. */
    public int $server_id;

    /** @var \DateTimeImmutable Access creation date and time. */
    public \DateTimeImmutable $created_at;

    /** @var bool Whether the access is currently enabled. */
    public bool $is_active = true;

    /** @var \DateTimeImmutable|null Date and time when the access was disabled. */
    public ?\DateTimeImmutable $disabled_at = null;

    /** @var bool Whether the access has been soft-deleted. */
    public bool $is_dropped = false;

    /**
     * Initializes an access instance from a database record.
     *
     * Converts database values into their corresponding PHP types,
     * including DateTimeImmutable instances for timestamp fields.
     *
     * @param array<string, mixed> $accessData Associative array containing access data.
     *
     * @throws \Exception If a timestamp cannot be parsed.
     */
    function __construct(array $accessData)
    {
        $this->id = (int) $accessData['id'];
        $this->user_id = (int) $accessData['user_id'];
        $this->uuid = (string) $accessData['uuid'];
        $this->name = isset($accessData['name']) ? (string) $accessData['name'] : null;
        $this->type = $accessData['type'];
        $this->server_id = (int) $accessData['server_id'];
        $this->created_at = new \DateTimeImmutable($accessData['created_at']);
        $this->is_active = (bool) $accessData['is_active'];
        $this->disabled_at = ($accessData['disabled_at'] !== null) ? new \DateTimeImmutable($accessData['disabled_at']) : null;
        $this->is_dropped = (bool) $accessData['is_dropped'];
    }
}