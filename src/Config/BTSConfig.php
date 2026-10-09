<?php

namespace CNS\BotToServer\Config;

use CNS\BotToServer\Types\Env;

final class BTSConfig {
    public string $encryptionKey;
    public string $role;
    public string $audCode;
    public array $subsUrl;
    public string $configsDir;
    public string $logsDir;
    function __construct(?string $encryptionKey = null, ?string $role = null, ?array $subsUrl = null, ?string $audCode = null, ?string $configsDir = null, ?string $logsDir = null)
    {
        $this->encryptionKey = $encryptionKey ?? Env::getString("BTS_ENCRYPTION_KEY");
        $this->role = $role ?? Env::getString("BTS_ROLE");
        $this->audCode = $audCode ?? Env::getString("BTS_AUD_CODE");
        $this->subsUrl = $subsUrl ?? Env::getSubsUrls();
        $this->configsDir = $configsDir ?? Env::getString("CONFIGS_DIR");
        $this->logsDir = $logsDir ?? Env::getString("LOGS_DIR");

        foreach (['encryptionKey', 'role', 'audCode'] as $property) {
            if ($this->$property === '') {
                throw new \InvalidArgumentException(
                    "BTS configuration '{$property}' cannot be empty."
                );
            }
        }
    }
}