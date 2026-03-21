<?php

namespace CNS\OvpnBotToServer\Types;

class User {
    public int $id;
    public string $user_id;
    public string $username;
    public string $language;
    public int $configs_count;
    public string $created_at;
    public string|null $expired_at;
    public string|null $last_payment_at;
    public bool $is_active;
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