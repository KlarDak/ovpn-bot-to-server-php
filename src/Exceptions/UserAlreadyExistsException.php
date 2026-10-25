<?php

namespace CNS\BotToServer\Exceptions;

/**
 * Exception thrown when attempting to create a user
 * with an identifier that already exists.
 */
final class UserAlreadyExistsException extends \Exception
{
    /**
     * @param string $identifier Identifier of the existing user.
     */
    public function __construct(string $identifier)
    {
        parent::__construct("User with identifier {$identifier} already exists.");
    }
}