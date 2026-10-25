<?php

namespace CNS\BotToServer\Exceptions;

/**
 * Exception thrown when attempting to create an access
 * with a UUID that already exists.
 */
class AccessAlreadyExistsException extends \Exception
{
    /**
     * @param string $uuid UUID of the existing access.
     */
    public function __construct(string $uuid)
    {
        parent::__construct("Access with UUID {$uuid} already exists.");
    }
}