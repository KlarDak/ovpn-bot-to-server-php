<?php

namespace CNS\BotToServer\Exceptions;

final class UserDroppedException extends \Exception {
    public function __construct(int $user_id)
    {
        parent::__construct("User with ID $user_id has been dropped.");
    }
}