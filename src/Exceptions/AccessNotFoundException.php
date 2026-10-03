<?php

namespace CNS\BotToServer\Exceptions;

class AccessNotFoundException extends \Exception {
    public function __construct(string $uuid)
    {
        parent::__construct("Access with UUID {$uuid} not found.");
    }
}