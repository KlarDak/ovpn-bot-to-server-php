<?php

namespace CNS\BotToServer\Manager\Interfaces;

use CNS\BotToServer\BotToServer;
use CNS\BotToServer\Config\BTSConfig;

interface ManagerInterface {
    function __construct(BotToServer $botToServer);
}