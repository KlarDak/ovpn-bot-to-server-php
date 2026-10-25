<?php

namespace CNS\BotToServer\Config;

use CNS\BotToServer\Contracts\OpenVPN\OpenVPNContract;
use CNS\BotToServer\Manager\Manager;
use CNS\BotToServer\Types\Env;

final class BTSConfig {
    /**
     * Encryption key used to secure sensitive data.
     */
    public string $encryptionKey;

    /**
     * Role assigned to the current instance.
     */
    public string $role;

    /**
     * Audience identifier used for authentication.
     */
    public string $audCode;

    /**
     * Directory containing configuration files.
     */
    public string $configsDir;

    /**
     * Directory containing application logs.
     */
    public string $logsDir;

    /**
     * Registered contracts.
     *
     * @var array<string, mixed>
     */
    public array $contracts;
    /**
     * Manages VPN nodes, users, and access permissions.
     * @var Manager
     */
    public Manager $manager;
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

        foreach (['encryptionKey', 'role', 'audCode', 'configsDir'] as $property) {
            if ($this->$property === '') {
                throw new \InvalidArgumentException(
                    "BTS configuration '{$property}' cannot be empty."
                );
            }
        }
    }
}