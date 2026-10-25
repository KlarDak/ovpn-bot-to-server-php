<?php

namespace CNS\BotToServer\Contracts\Interfaces;

/**
 * Defines access to the active connection management API of a server contract.
 */
interface ActiveContractInterface
{
    /**
     * Returns the active connection management handler for the server.
     *
     * @return mixed The active connection management handler.
     */
    public function active(): mixed;
}