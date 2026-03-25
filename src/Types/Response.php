<?php

namespace CNS\OvpnBotToServer\Types;

class Response {
    /**
     * HTTP response code
     * 
     * @var int
     */
    public int $code;

    /**
     * Message of response
     * 
     * @var string
     */
    public string $message;

    /**
     * Data of response
     * 
     * @var array|null
     */
    public array|null $data;

    function __construct(array $responseData)
    {
        $this->code = $responseData["code"];
        $this->message = $responseData["message"];
        $this->data = $responseData["data"];
    }
}