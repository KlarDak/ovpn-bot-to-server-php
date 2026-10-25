<?php

namespace CNS\BotToServer\Contracts\Interfaces;

/**
 * Defines access to the subscription management API of a server contract.
 */
interface SubsContractInterface
{
    /**
     * Returns the subscription management handler for the server.
     *
     * @return mixed The subscription management handler.
     */
    public function subs(): mixed;
}