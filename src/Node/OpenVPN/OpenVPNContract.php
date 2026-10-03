<?php

namespace CNS\BotToServer\Node\OpenVPN;

use CNS\BotToServer\Node\Interfaces\ActiveContractInterface;
use CNS\BotToServer\Node\Interfaces\AccessContractInterface;
use CNS\BotToServer\Node\Interfaces\MetricsContractInterface;
use CNS\BotToServer\Node\Interfaces\SubsContractInterface;
use CNS\BotToServer\Security\SecretCipher;
use GuzzleHttp\Client;

class OpenVPNContract implements AccessContractInterface, ActiveContractInterface, MetricsContractInterface, SubsContractInterface {
    public Client $client;
    function __construct(public readonly string $code, string $host, int $port, string $encrypt_secret_code, public readonly string $subs_url)
    {
        try {
            $decrypt_secret_code = SecretCipher::decrypt($encrypt_secret_code, $code);
            $this->client = new Client([
                'base_uri' => "http://$host:$port",
                'headers' => [
                    'Authorization' => "Bearer $decrypt_secret_code"
                ]
            ]);
        }
        catch (\Exception $e) {
            throw new \Exception("Failed to initialize OpenVPNContract: " . $e->getMessage());
        }
    }
    public function access() : AccessContract {
        return new AccessContract($this->client, $this->code, $this->subs_url);
    }
    public function active() : ActiveContract {
        return new ActiveContract();
    }
    public function metrics() : MetricsContract {
        return new MetricsContract();
    }
    public function subs() : SubsContract {
        return new SubsContract();
    }
}