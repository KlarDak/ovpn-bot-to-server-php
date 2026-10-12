<?php

namespace CNS\BotToServer\Contracts\Traits;

trait CheckContractTrait {
    public function whichContract() : string {
        return self::class;
    }
}