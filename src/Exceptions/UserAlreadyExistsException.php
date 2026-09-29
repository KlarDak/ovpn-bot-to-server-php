<?php

namespace CNS\BotToServer\Exceptions;

final class UserNotFoundException extends \Exception {
    public function __construct(int $user_id)
    {
        parent::__construct("User with ID $user_id not found.");
    }
}