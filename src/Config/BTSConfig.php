<?php

namespace CNS\BotToServer\Config;

use CNS\BotToServer\Contracts\OpenVPN\OpenVPNContract;
use CNS\BotToServer\Types\Env;

final class BTSConfig {
    public string $encryptionKey;
    public string $role;
    public string $audCode;
    public string $configsDir;
    public string $logsDir;
    public array $contracts;
    function __construct(?string $encryptionKey = null, ?string $role = null, ?string $audCode = null, ?string $configsDir = null, ?string $logsDir = null, ?array $contracts = [])
    {
        $this->encryptionKey = $encryptionKey ?? Env::getString("BTS_ENCRYPTION_KEY");
        $this->role = $role ?? Env::getString("BTS_ROLE");
        $this->audCode = $audCode ?? Env::getString("BTS_AUD_CODE");
        $this->configsDir = $configsDir ?? Env::getString("CONFIGS_DIR");
        $this->logsDir = $logsDir ?? Env::getString("LOGS_DIR");
        $this->contracts = array_replace([
            "openvpn" => OpenVPNContract::class
        ], $contracts ?? []);

        foreach (['encryptionKey', 'role', 'audCode', 'configsDir', 'logsDir'] as $property) {
            if ($this->$property === '') {
                throw new \InvalidArgumentException(
                    "BTS configuration '{$property}' cannot be empty."
                );
            }
        }
    }
}