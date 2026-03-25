<?php

namespace CNS\OvpnBotToServer\Types;

class User {
    /**
     * Record ID
     * 
     * @var int
     */
    public int $id;
    
    /**
     * UserID of user in Telegram
     * 
     * @var string
     */
    public string $user_id;

    /**
     * Username of user in Telegram
     * 
     * @var string
     */
    public string $username;

    /**
     * Language of user
     * 
     * @var string
     */
    public string $language;

    /**
     * User's config count
     * 
     * @var int
     */
    public int $configs_count;

    /**
     * Date creation of user
     * 
     * @var string
     */
    public string $created_at;

    /**
     * Date expiration of user
     * 
     * @var string|null
     */
    public string|null $expired_at;

    /**
     * Last payment of user
     * 
     * @var string|null
     */
    public string|null $last_payment_at;

    /**
     * Is user active
     * 
     * @var bool
     */
    public bool $is_active;

    /**
     * Account deactivation date
     * 
     * @var string|null
     */
    public string|null $disabled_at;

    function __construct(array $userData)
    {
        $this->id = $userData['id'];
        $this->user_id = $userData['user_id'];
        $this->username = $userData['username'];
        $this->language = $userData['language'];
        $this->configs_count = $userData['configs_count'];
        $this->created_at = $userData['created_at'];
        $this->expired_at = $userData['expired_at'];
        $this->last_payment_at = $userData['last_payment_at'];
        $this->is_active = $userData['is_active'];
        $this->disabled_at = $userData['disabled_at'];
    }
}