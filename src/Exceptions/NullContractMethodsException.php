<?php

namespace CNS\BotToServer\Exceptions;

use Exception;
use Throwable;
use Override;

final class NullContractMethodsException extends Exception {
    public function __construct()
    {
        parent::__construct("No active connection contract was selected or the specified contract does not exist.");
    }
}