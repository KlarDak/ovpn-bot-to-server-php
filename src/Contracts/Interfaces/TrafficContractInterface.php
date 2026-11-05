<?php

namespace CNS\BotToServer\Contracts\Interfaces;

/**
 * Defines access to the traffic management API of a server contract.
 */
interface TrafficContractInterface
{
    /**
     * Returns the traffic management handler for the server.
     *
     * @return mixed The traffic management handler.
     */
    public function traffic(): mixed;
}