<?php

namespace CNS\BotToServer\Exceptions;

/**
 * Exception thrown when attempting to access a user
 * who has been marked as dropped.
 */
final class UserDroppedException extends \Exception
{
    /**
     * @param int $user_id ID of the dropped user.
     */
    public function __construct(int $user_id)
    {
        parent::__construct("User with ID $user_id has been dropped.");
    }
}