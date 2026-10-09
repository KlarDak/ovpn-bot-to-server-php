<?php

namespace CNS\BotToServer\Types;

use DateTimeImmutable;

class Subs {
    public int $id;
    public string $short_code;
    public int $server_id;
    public int $accesses_id;
    public DateTimeImmutable $created_at;
    public bool $is_used = false;
    public ?DateTimeImmutable $used_at = null;
    public bool $is_dropped = false;

    function __construct(array $subsData)
    {
        $this->id = (int) $subsData["id"];
        $this->short_code = $subsData["short_code"];
        $this->server_id = (int) $subsData["server_id"];
        $this->accesses_id = (int) $subsData["accesses_id"];
        $this->created_at = $subsData["created_at"];
        $this->is_used = (bool) $subsData["is_used"];
        $this->used_at = $subsData["used_at"];
        $this->is_dropped = (bool) $subsData["is_dropped"];
    }
}