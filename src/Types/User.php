<?php

namespace CNS\BotToServer\Types;

/**
 * Represents a user record.
 *
 * Contains user identification, profile information,
 * subscription expiration and payment dates, activity timestamps,
 * and account activation and deletion states.
 */
final class User
{
    /** @var int Internal user ID. */
    public int $id;

    /** @var int|null Telegram user ID, if available. */
    public ?int $telegram_id = null;

    /** @var string|null User email address. */
    public ?string $email = null;

    /** @var string|null Optional username. */
    public ?string $username = null;

    /** @var string User's preferred language. */
    public string $language;

    /** @var \DateTimeImmutable User registration date and time. */
    public \DateTimeImmutable $created_at;

    /** @var \DateTimeImmutable|null Date and time of the user's last activity. */
    public ?\DateTimeImmutable $last_activity_at = null;

    /** @var \DateTimeImmutable|null Subscription expiration date and time. */
    public ?\DateTimeImmutable $expired_at = null;

    /** @var \DateTimeImmutable|null Date and time of the user's last payment. */
    public ?\DateTimeImmutable $last_payment_at = null;

    /** @var bool Whether the user account is currently enabled. */
    public bool $is_active = true;

    /** @var \DateTimeImmutable|null Date and time when the user was disabled. */
    public ?\DateTimeImmutable $disabled_at = null;

    /** @var bool Whether the user has been soft-deleted. */
    public bool $is_dropped = false;

    /**
     * Initializes a user instance from a database record.
     *
     * Maps database fields to object properties, converts numeric
     * and boolean values to their corresponding PHP types,
     * and parses timestamps into DateTimeImmutable instances.
     *
     * @param array<string, mixed> $userData Associative array containing user data.
     *
     * @throws \Exception If a timestamp cannot be parsed.
     */
    function __construct(array $userData)
    {
        $this->id = (int) $userData['id']; 
        $this->telegram_id = isset($userData['telegram_id']) ? (int) $userData['telegram_id'] : null;
        $this->email = isset($userData['email']) ? (string) $userData['email'] : null;
        $this->username = isset($userData['username']) ? (string) $userData['username'] : null;
        $this->language = (string) $userData['language'];
        $this->created_at = new \DateTimeImmutable($userData['created_at']);
        $this->last_activity_at = isset($userData['last_activity_at']) ? new \DateTimeImmutable($userData['last_activity_at']) : null;
        $this->expired_at = ($userData['expired_at'] !== null) ? new \DateTimeImmutable($userData['expired_at']) : null;
        $this->last_payment_at = ($userData['last_payment_at'] !== null) ? new \DateTimeImmutable($userData['last_payment_at']) : null;
        $this->is_active = (bool) $userData['is_active'];
        $this->disabled_at = ($userData['disabled_at'] !== null) ? new \DateTimeImmutable($userData['disabled_at']) : null;
        $this->is_dropped = (bool) $userData['is_dropped']; 
    }
}