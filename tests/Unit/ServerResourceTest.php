<?php

declare(strict_types=1);

namespace CNS\BotToServer\Tests\Unit;

use CNS\BotToServer\Config\BTSConfig;
use CNS\BotToServer\Contracts\NullContract\NullContract;
use CNS\BotToServer\Contracts\OpenVPN\{OpenVPNContract, AccessContract, ActiveContract, MetricsContract, SubsContract};
use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Resources\ServerResource;
use CNS\BotToServer\Security\SecretCipher;
use PHPUnit\Framework\TestCase;

final class ServerResourceTest extends TestCase
{
    private function config(): BTSConfig
    {
        return new BTSConfig(base64_encode(str_repeat('t', 32)), 'admin', 'test', 'unused-configs', 'unused-logs');
    }

    private function row(string $type): array
    {
        return ['id' => 2, 'code' => 's', 'name' => 'Test', 'host' => 'example.invalid', 'port' => 443,
            'api_endpoint' => '/api', 'encrypt_secret_code' => SecretCipher::encrypt('test-secret', $this->config()->encryptionKey),
            'type' => $type, 'subs_url' => 'https://example.invalid/subs', 'created_at' => '2026-01-01',
            'status' => 'active', 'disabled_at' => null, 'is_dropped' => 0];
    }

    public function testMissingInfoReturnsNull(): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->with('SELECT * FROM servers WHERE code = :code AND is_dropped = 0', [':code' => 's'])->willReturn(null);
        self::assertNull((new ServerResource($db, $this->createStub(HttpClientInterface::class), $this->config(), 's'))->info());
    }

    public function testMissingServerCannotCreateContract(): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->willReturn(null);
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Server not found');
        (new ServerResource($db, $this->createStub(HttpClientInterface::class), $this->config(), 's'))->contract();
    }

    public function testUnknownTypeUsesNullContractWithoutNetwork(): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->willReturn($this->row('unknown'));
        $http = $this->createMock(HttpClientInterface::class);
        $http->expects(self::never())->method('request');
        $http->expects(self::never())->method('stream');
        $contract = (new ServerResource($db, $http, $this->config(), 's'))->contract();
        self::assertInstanceOf(NullContract::class, $contract);
        self::assertSame(NullContract::class, $contract->whichContract());
        $this->expectException(\Exception::class);
        $contract->unsupported();
    }

    public function testOpenVpnContractExposesSubcontractsWithoutNetwork(): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->willReturn($this->row('openvpn'));
        $http = $this->createMock(HttpClientInterface::class);
        $http->expects(self::never())->method('request');
        $http->expects(self::never())->method('stream');
        $contract = (new ServerResource($db, $http, $this->config(), 's'))->contract();
        self::assertInstanceOf(OpenVPNContract::class, $contract);
        self::assertSame(OpenVPNContract::class, $contract->whichContract());
        self::assertInstanceOf(AccessContract::class, $contract->access());
        self::assertInstanceOf(ActiveContract::class, $contract->active());
        self::assertInstanceOf(MetricsContract::class, $contract->metrics());
        self::assertInstanceOf(SubsContract::class, $contract->subs());
    }

    public function testConfiguredContractOverridesDefault(): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->willReturn($this->row('openvpn'));
        $config = $this->config();
        $config->contracts['openvpn'] = NullContract::class;
        self::assertInstanceOf(NullContract::class, (new ServerResource($db, $this->createStub(HttpClientInterface::class), $config, 's'))->contract());
    }
}
