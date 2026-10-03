<?php

namespace CNS\BotToServer\Exceptions;

class AccessAlreadyExistsException extends \Exception {
    public function __construct(string $uuid)
    {
        parent::__construct("Access with UUID {$uuid} already exists.");
    }
}