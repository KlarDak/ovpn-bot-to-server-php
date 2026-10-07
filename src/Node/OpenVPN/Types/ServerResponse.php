<?php

namespace CNS\BotToServer\Node\OpenVPN\Types;

class ServerResponse {
    public int $code;
    public string $message;
    public ?array $data = null;
    function __construct(array $serverResponse) 
    {
        $this->code = $serverResponse["code"];
        $this->message = $serverResponse["message"];
        $this->data = $serverResponse["data"];
    }
}