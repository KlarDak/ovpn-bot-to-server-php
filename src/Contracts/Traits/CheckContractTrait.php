<?php

namespace CNS\BotToServer\Contracts\Traits;

/**
 * Provides a method for identifying the current server contract.
 */
trait CheckContractTrait
{
    /**
     * Returns the fully qualified class name of the current contract.
     *
     * @return string The contract class name.
     */
    public function whichContract(): string
    {
        return self::class;
    }
}