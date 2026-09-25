<?php

namespace CNS\OvpnBotToServer\Types;

class SubsResponse {
    public int $id;

    public string $sub_uuid;
    public string $sub_link;
    public string $sub_server;

    public string $created_at;

    function __construct(array $responseData)
    {
        $this->id = $responseData["id"];
        $this->sub_uuid = $responseData["sub_uuid"];
        $this->sub_link = $responseData["sub_link"];
        $this->sub_server = $responseData["sub_server"];
        $this->created_at = $responseData["created_at"];
    }
}