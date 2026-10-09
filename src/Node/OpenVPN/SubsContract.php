<?php

namespace CNS\BotToServer\Node\OpenVPN;

use CNS\BotToServer\Config\BTSConfig;
use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Resources\SubsResource;
use CNS\BotToServer\Types\Subs;

class SubsContract {
    private SubsResource $subsResource;
    function __construct(IDBConnector $dbConnector, private BTSConfig $btsConfig) {
        $this->subsResource = new SubsResource($dbConnector);
    }

    public function getSubsLink(string $short_code) : Subs|null {
        $sub = $this->subsResource->get($short_code);

        return ($sub) ? ($this->btsConfig->subsUrl["OPENVPN"] . "/" . $sub->short_code) : null;
    }

    public function getSubs(string $short_code) : Subs|null {
        $sub = $this->subsResource->get($short_code);

        return $sub ?? null;
    }

    public function createSubsLink(string $short_code, int $server_id, int $access_id) : bool {
        return $this->subsResource->create($short_code, $server_id, $access_id);
    }
}