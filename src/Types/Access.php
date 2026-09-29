<?php

namespace CNS\BotToServer\Types;

final class Access {
    public int $id;
    public int $user_id;
    public string $uuid;
    public ?string $name = null;
    public string $type;
    public int $server_id;
    public \DateTimeImmutable $created_at;
    public bool $is_active = true;
    public ?\DateTimeImmutable $disabled_at = null;
    public bool $is_dropped = false;

    function __construct(array $accessData)
    {
        $this->id = (int) $accessData['id'];
        $this->user_id = (int) $accessData['user_id'];
        $this->uuid = (string) $accessData['uuid'];
        $this->name = isset($accessData['name']) ? (string) $accessData['name'] : null;
        $this->type = (string) $accessData['type'];
        $this->server_id = (int) $accessData['server_id'];
        $this->created_at = new \DateTimeImmutable($accessData['created_at']);
        $this->is_active = (bool) $accessData['is_active'];
        $this->disabled_at = isset($accessData['disabled_at']) ? new \DateTimeImmutable($accessData['disabled_at']) : null;
        $this->is_dropped = (bool) $accessData['is_dropped'];
    }
}