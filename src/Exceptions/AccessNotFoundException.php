<?php

namespace CNS\BotToServer\Exceptions;

/**
 * Exception thrown when an access with the specified UUID
 * cannot be found.
 */
class AccessNotFoundException extends \Exception
{
    /**
     * @param string $uuid UUID of the access that was not found.
     */
    public function __construct(string $uuid)
    {
        parent::__construct("Access with UUID {$uuid} not found.");
    }
}