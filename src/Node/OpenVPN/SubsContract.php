<?php

namespace CNS\BotToServer\Node\OpenVPN;

use CNS\BotToServer\Database\IDBConnector;

class SubsContract {
    function __construct(private IDBConnector $dbConnector, private readonly string $subs_url) {}
}