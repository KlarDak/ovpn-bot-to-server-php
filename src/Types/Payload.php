<?php

namespace CNS\OvpnBotToServer\Types;

/**
 * 
 */
class Payload {
    public string $sub;
    public string $aud;
    public int $iat;
    public int $exp;
    public string $role;
    public string $type;
    
    function __construct(string $payloadData)
    {
        $this->sub = $payloadData["sub"];
        $this->aud = $payloadData["aud"];
        $this->iat = $payloadData["iat"];
        $this->exp = $payloadData["exp"];
        $this->role = $payloadData["role"];
        $this->type = $payloadData["type"];
    }
}