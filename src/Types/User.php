<?php

namespace CNS\BotToServer\Types;

final class User {
    public int $id;
    public ?int $telegram_id = null;
    public ?string $email = null;
    public ?string $username = null;
    public string $language;

    public \DateTimeImmutable $created_at;
    public ?\DateTimeImmutable $last_activity_at = null;
    public ?\DateTimeImmutable $expired_at = null;
    public ?\DateTimeImmutable $last_payment_at = null;
    public bool $is_active = true;
    public ?\DateTimeImmutable $disabled_at = null;
    public bool $is_dropped = false;

    function __construct(array $userData)
    {
        $this->id = (int) $userData['id']; 
        $this->telegram_id = isset($userData['telegram_id']) ? (int) $userData['telegram_id'] : null;
        $this->email = isset($userData['email']) ? (string) $userData['email'] : null;
        $this->username = isset($userData['username']) ? (string) $userData['username'] : null;
        $this->language = (string) $userData['language'];
        $this->created_at = new \DateTimeImmutable($userData['created_at']);
        $this->last_activity_at = isset($userData['last_activity_at']) ? new \DateTimeImmutable($userData['last_activity_at']) : null;
        $this->expired_at = isset($userData['expired_at']) ? new \DateTimeImmutable($userData['expired_at']) : null;
        $this->last_payment_at = isset($userData['last_payment_at']) ? new \DateTimeImmutable($userData['last_payment_at']) : null;
        $this->is_active = (bool) $userData['is_active'];
        $this->disabled_at = isset($userData['disabled_at']) ? new \DateTimeImmutable($userData['disabled_at']) : null;
        $this->is_dropped = (bool) $userData['is_dropped']; 
    }
}