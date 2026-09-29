<?php

namespace CNS\BotToServer\Resources;

use CNS\BotToServer\Database\IDBConnector;

class UserResource {
    private IDBConnector $dbConnector;
    private int $user_id;
    function __construct(IDBConnector $dbConnector, int $user_id)
    {
        $this->dbConnector = $dbConnector;
        $this->user_id = $user_id;
    }

    public function exist() {}

    public function get() {}

    public function create() {}

    public function update() {}

    public function updatePayment() {}
    
    public function touch() {}

    public function block() {}

    public function unblock() {}

    public function drop() {}
}