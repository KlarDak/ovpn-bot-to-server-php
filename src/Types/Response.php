<?php

namespace CNS\OvpnBotToServer\Types;

class Response {
    public int $code;
    public string $message;
    public array|null $data;

    function __construct(array $responseData)
    {
        $this->code = $responseData["code"];
        $this->message = $responseData["message"];
        $this->data = $responseData["data"];
    }
}