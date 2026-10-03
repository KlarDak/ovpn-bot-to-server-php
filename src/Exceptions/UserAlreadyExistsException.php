<?php

namespace CNS\BotToServer\Exceptions;

final class UserAlreadyExistsException extends \Exception {
    public function __construct(string $identifier)
    {
        parent::__construct("User with identifier {$identifier} already exists.");
    }
}