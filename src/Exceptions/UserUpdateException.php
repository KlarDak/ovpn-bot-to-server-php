<?php

namespace CNS\BotToServer\Exceptions;

/**
 * Exception thrown when an attempt to update a user fails.
 */
final class UserUpdateException extends \Exception
{
    /**
     * @param int $user_id ID of the user whose update failed.
     */
    public function __construct(int $user_id)
    {
        parent::__construct("Failed to update user with ID $user_id.");
    }
}