<?php

namespace CNS\BotToServer\Contracts\Interfaces;

/**
 * Defines access to the system management API of a server contract.
 */
interface SystemContractInterface
{
    /**
     * Returns the system management handler for the server.
     *
     * @return mixed The system management handler.
     */
    public function system(): mixed;
}