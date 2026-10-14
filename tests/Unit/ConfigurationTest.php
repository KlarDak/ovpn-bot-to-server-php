<?php
declare(strict_types=1);

namespace CNS\BotToServer\Tests\Unit;

use CNS\BotToServer\Types\Env;
use CNS\BotToServer\Config\BTSConfig;
use CNS\BotToServer\Contracts\OpenVPN\OpenVPNContract;
use CNS\BotToServer\Contracts\NullContract\NullContract;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ConfigurationTest extends TestCase
{
    private array $savedEnv;
    private string|false $savedProcess;
    private const KEY = 'BTS_UNIT_TEST_VALUE';

    protected function setUp(): void
    {
        $this->savedEnv = $_ENV;
        $this->savedProcess = getenv(self::KEY);
        unset($_ENV[self::KEY]);
        putenv(self::KEY);
    }

    protected function tearDown(): void
    {
        $_ENV = $this->savedEnv;
        putenv($this->savedProcess === false ? self::KEY : self::KEY . '=' . $this->savedProcess);
    }

    public function testMissingEnvironmentUsesDefaults(): void
    {
        self::assertSame('fallback', Env::getString(self::KEY, 'fallback'));
        self::assertSame(7, Env::getInt(self::KEY, 7));
        self::assertSame(1.5, Env::getFloat(self::KEY, 1.5));
        self::assertTrue(Env::getBool(self::KEY, true));
    }

    public function testArrayEnvironmentTakesPrecedenceOverProcess(): void
    {
        putenv(self::KEY . '=process');
        self::assertSame('process', Env::getString(self::KEY));
        $_ENV[self::KEY] = '';
        self::assertSame('', Env::getString(self::KEY, 'fallback'));
        $_ENV[self::KEY] = 'array';
        self::assertSame('array', Env::getString(self::KEY));
    }

    #[DataProvider('values')]
    public function testTypedEnvironment(string $method, string $raw, mixed $expected): void
    {
        $_ENV[self::KEY] = $raw;
        self::assertSame($expected, Env::$method(self::KEY));
    }

    public static function values(): array
    {
        return [['getInt', '0', 0], ['getInt', '-42', -42], ['getInt', '', 0],
            ['getFloat', '1.25', 1.25], ['getFloat', '1e3', 1000.0], ['getFloat', '', 0.0],
            ['getBool', 'true', true], ['getBool', 'yes', true], ['getBool', 'on', true], ['getBool', '1', true],
            ['getBool', 'false', false], ['getBool', 'off', false], ['getBool', 'no', false], ['getBool', '0', false], ['getBool', '', false]];
    }

    #[DataProvider('invalidValues')]
    public function testInvalidEnvironmentRejected(string $method, string $value): void
    {
        $_ENV[self::KEY] = $value;
        $this->expectException(\UnexpectedValueException::class);
        Env::$method(self::KEY);
    }

    public static function invalidValues(): array { return [['getInt', '1.5'], ['getInt', 'abc'], ['getFloat', 'abc'], ['getBool', 'maybe']]; }

    public function testConfigurationDefaultsFromEnvironmentAndMergesContracts(): void
    {
        foreach (['BTS_ENCRYPTION_KEY' => 'key', 'BTS_ROLE' => 'admin', 'BTS_AUD_CODE' => 'aud', 'CONFIGS_DIR' => 'configs', 'LOGS_DIR' => 'logs'] as $name => $value) { $_ENV[$name] = $value; }
        $config = new BTSConfig(contracts: ['custom' => NullContract::class]);
        self::assertSame('key', $config->encryptionKey);
        self::assertSame('admin', $config->role);
        self::assertSame('aud', $config->audCode);
        self::assertSame('configs', $config->configsDir);
        self::assertSame('logs', $config->logsDir);
        self::assertSame(['openvpn' => OpenVPNContract::class, 'custom' => NullContract::class], $config->contracts);
    }

    #[DataProvider('emptyFields')]
    public function testEmptyRequiredConfigurationRejected(int $index): void
    {
        $args = ['key', 'admin', 'aud', 'configs', 'logs'];
        $args[$index] = '';
        $this->expectException(\InvalidArgumentException::class);
        new BTSConfig(...$args);
    }

    public static function emptyFields(): array { return [[0], [1], [2], [3], [4]]; }
}
