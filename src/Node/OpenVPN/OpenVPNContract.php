<?php

namespace CNS\BotToServer\Node\OpenVPN;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Node\Interfaces\ActiveContractInterface;
use CNS\BotToServer\Node\Interfaces\AccessContractInterface;
use CNS\BotToServer\Node\Interfaces\MetricsContractInterface;
use CNS\BotToServer\Node\Interfaces\SubsContractInterface;
use CNS\BotToServer\Security\SecretCipher;

class OpenVPNContract implements AccessContractInterface, ActiveContractInterface, MetricsContractInterface, SubsContractInterface {
    function __construct(public readonly string $code, public readonly string $subs_url, private HttpClientInterface $httpClient, private IDBConnector $dbConnector) {}
    public function access() : AccessContract {
        return new AccessContract($this->httpClient, $this->code);
    }
    public function active() : ActiveContract {
        return new ActiveContract($this->httpClient, $this->code);
    }
    public function metrics() : MetricsContract {
        return new MetricsContract($this->httpClient, $this->code);
    }
    public function subs() : SubsContract {
        return new SubsContract($this->dbConnector, $this->subs_url);
    }
}