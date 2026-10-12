<?php

namespace CNS\BotToServer\Contracts\OpenVPN;

use CNS\BotToServer\Config\BTSConfig;
use CNS\BotToServer\Contracts\Traits\CheckContractTrait;
use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Contracts\Interfaces\ActiveContractInterface;
use CNS\BotToServer\Contracts\Interfaces\AccessContractInterface;
use CNS\BotToServer\Contracts\Interfaces\MetricsContractInterface;
use CNS\BotToServer\Contracts\Interfaces\SubsContractInterface;
use CNS\BotToServer\Contracts\OpenVPN\Security\JWTCipher;
use CNS\BotToServer\Security\SecretCipher;
use CNS\BotToServer\Types\Server;

class OpenVPNContract implements AccessContractInterface, ActiveContractInterface, MetricsContractInterface, SubsContractInterface {
    use CheckContractTrait;
    private OpenVPNFuncAdapter $openVPNFuncAdapter;
    private string $subs_url;

    function __construct(
        private IDBConnector $dbConnector, 
        private HttpClientInterface $httpClient, 
        private BTSConfig $btsConfig, 
        Server $server) {
        
        $secret = SecretCipher::decrypt($server->encrypt_secret_code, $this->btsConfig->encryptionKey);
        $this->openVPNFuncAdapter = new OpenVPNFuncAdapter(
            $this->httpClient,
            fn(): string => JWTCipher::encode($this->btsConfig->audCode, $server->code, $this->btsConfig->role, $secret)
        );
        $this->subs_url = $server->subs_url ?? "";
    }
    public function access() : AccessContract {
        return new AccessContract($this->openVPNFuncAdapter);
    }
    public function active() : ActiveContract {
        return new ActiveContract($this->openVPNFuncAdapter);
    }
    public function metrics() : MetricsContract {
        return new MetricsContract($this->openVPNFuncAdapter);
    }
    public function subs() : SubsContract {
        return new SubsContract($this->dbConnector, $this->subs_url);
    }
}