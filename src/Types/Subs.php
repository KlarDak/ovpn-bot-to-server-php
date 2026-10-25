<?php

namespace CNS\BotToServer\Types;

use DateTimeImmutable;

/**
 * Represents a VPN subscription record.
 *
 * Contains the subscription short code, associated server
 * and access identifiers, creation timestamp, usage status,
 * and soft-deletion state.
 */
class Subs
{
    /** @var int Internal subscription ID. */
    public int $id;

    /** @var string Unique subscription short code. */
    public string $short_code;

    /** @var int ID of the associated server. */
    public int $server_id;

    /** @var int ID of the associated access. */
    public int $access_id;

    /** @var DateTimeImmutable Subscription creation date and time. */
    public DateTimeImmutable $created_at;

    /** @var bool Whether the subscription has been used. */
    public bool $is_used = false;

    /** @var DateTimeImmutable|null Date and time when the subscription was used. */
    public ?DateTimeImmutable $used_at = null;

    /** @var bool Whether the subscription has been soft-deleted. */
    public bool $is_dropped = false;

    /**
     * Initializes a subscription instance from a database record.
     *
     * Maps database fields to object properties and converts
     * the creation timestamp into a DateTimeImmutable instance.
     *
     * @param array<string, mixed> $subsData Associative array containing subscription data.
     *
     * @throws \Exception If the creation timestamp cannot be parsed.
     */
    function __construct(array $subsData)
    {
        $this->id = (int) $subsData["id"];
        $this->short_code = $subsData["short_code"];
        $this->server_id = (int) $subsData["server_id"];
        $this->access_id = (int) $subsData["access_id"];
        $this->created_at = new DateTimeImmutable($subsData["created_at"]);
        $this->is_used = (bool) $subsData["is_used"];
        $this->used_at = $subsData["used_at"];
        $this->is_dropped = (bool) $subsData["is_dropped"];
    }
}