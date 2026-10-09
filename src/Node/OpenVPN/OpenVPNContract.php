<?php

namespace CNS\BotToServer\Node\OpenVPN;

use CNS\BotToServer\Config\BTSConfig;
use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Node\Interfaces\ActiveContractInterface;
use CNS\BotToServer\Node\Interfaces\AccessContractInterface;
use CNS\BotToServer\Node\Interfaces\MetricsContractInterface;
use CNS\BotToServer\Node\Interfaces\SubsContractInterface;
use CNS\BotToServer\Node\OpenVPN\Security\JWTCipher;
use CNS\BotToServer\Security\SecretCipher;

class OpenVPNContract implements AccessContractInterface, ActiveContractInterface, MetricsContractInterface, SubsContractInterface {
    private OpenVPNFuncAdapter $openVPNFuncAdapter;
    function __construct(private HttpClientInterface $httpClient, private IDBConnector $dbConnector, private BTSConfig $btsConfig, string $code, string $encrypt_secret_code) {
        $this->openVPNFuncAdapter = new OpenVPNFuncAdapter(
            $this->httpClient,
            JWTCipher::encode($this->btsConfig->audCode, $code, $this->btsConfig->role, SecretCipher::decrypt($encrypt_secret_code, $this->btsConfig->encryptionKey))
        );
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
        return new SubsContract($this->dbConnector, $this->btsConfig);
    }
}