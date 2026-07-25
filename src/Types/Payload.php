<?php

namespace CNS\OvpnBotToServer\Types;

class Payload {
    /**
     * Sender server index
     * 
     * @var string
     */
    public string $sub;

    /**
     * Recipient server index
     * 
     * @var string
     */
    public string $aud;

    /**
     * Token creation date
     * 
     * @var int
     */
    public int $iat;

    /**
     * Token expiration date
     * 
     * @var int
     */
    public int $exp;

    /**
     * Role of sender
     * 
     * @var string
     */
    public string $role;

    function __construct(array $payloadData)
    {
        $this->sub = $payloadData["sub"];
        $this->aud = $payloadData["aud"];
        $this->iat = $payloadData["iat"];
        $this->exp = $payloadData["exp"];
        $this->role = $payloadData["role"];
    }
}
