<?php

namespace CNS\BotToServer\Contracts\Interfaces;

/**
 * Defines access to the access management API of a server contract.
 */
interface AccessContractInterface
{
    /**
     * Returns the access management handler for the server.
     *
     * @return mixed The access management handler.
     */
    public function access(): mixed;
}