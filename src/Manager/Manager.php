<?php

namespace CNS\BotToServer\Manager;

use CNS\BotToServer\BotToServer;
use CNS\BotToServer\Manager\Interfaces\ManagerInterface;
use Exception;
use Override;

final class Manager implements ManagerInterface{
    function __construct(private BotToServer $botToServer) {
        throw new Exception("Manager was not added in this version. Try again later.");        
    }
}