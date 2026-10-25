<?php

namespace CNS\BotToServer\Exceptions;

/**
 * Exception thrown when a user with the specified ID
 * cannot be found.
 */
final class UserNotFoundException extends \Exception
{
    /**
     * @param int $user_id ID of the user that was not found.
     */
    public function __construct(int $user_id)
    {
        parent::__construct("User with ID $user_id not found.");
    }
}