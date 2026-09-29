<?php

namespace CNS\BotToServer\Exceptions;

final class UserUpdateException extends \Exception {
    public function __construct(int $user_id)
    {
        parent::__construct("Failed to update user with ID $user_id.");
    }
}