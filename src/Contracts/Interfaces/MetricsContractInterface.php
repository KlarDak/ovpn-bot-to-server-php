<?php

namespace CNS\BotToServer\Contracts\Interfaces;

/**
 * Defines access to the metrics API of a server contract.
 */
interface MetricsContractInterface
{
    /**
     * Returns the metrics handler for the server.
     *
     * @return mixed The server metrics handler.
     */
    public function metrics(): mixed;
}