<?php

namespace CNS\OvpnBotToServer\Types;

class Config
{
    /**
     * Record ID
     * 
     * @var int
     */
    public int $id;
    
    /**
     * UUID of config file
     * 
     * @var string
     */
    public string $uuid;
    
    /**
     * UserID of user in Telegram
     * 
     * @var string
     */
    public string $user_id;

    /**
     * Type of config file
     * 
     * @var string
     */
    public string $type;

    /**
     * Server location of config file
     * 
     * @var string
     */
    public string $location;

    /**
     * Record creation date
     * 
     * @var string
     */
    public string $created_at;

    /**
     * Status of config file
     * 
     * @var bool
     */
    public bool $status;

    /**
     * Record blocking date
     * 
     * @var string|null
     */
    public string|null $blocked_at;
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