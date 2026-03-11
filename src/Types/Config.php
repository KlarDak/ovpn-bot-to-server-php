<?php

namespace CNS\OvpnBotToServer\Types;

class Config
{
    public int $id;
    public string $uuid;
    public string $user_id;
    public string $type;
    public string $location;
    public string $created_at;
    public bool $status;
    public string $blocked_at;
    // -------------------------
    public bool $is_dropped;

    function __construct(array $configData)
    {
        $this->id = $configData['id'];
        $this->uuid = $configData['uuid'];
        $this->user_id = $configData['user_id'];
        $this->type = $configData['type'];
        $this->location = $configData['location'];
        $this->created_at = $configData['created_at'];
        $this->status = $configData['status'];
        $this->blocked_at = $configData['blocked_at'];
    }
}