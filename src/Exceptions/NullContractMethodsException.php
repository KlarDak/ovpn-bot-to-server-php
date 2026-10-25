<?php

namespace CNS\BotToServer\Exceptions;

use Exception;

/**
 * Exception thrown when no valid VPN node contract is available
 * or the selected contract does not exist.
 */
final class NullContractMethodsException extends Exception
{
    public function __construct()
    {
        parent::__construct(
            "No active connection contract was selected or the specified contract does not exist."
        );
    }
}