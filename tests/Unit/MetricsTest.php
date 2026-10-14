<?php
declare(strict_types=1);

namespace CNS\BotToServer\Tests\Unit;

use CNS\BotToServer\Contracts\OpenVPN\{MetricsContract, OpenVPNFuncAdapter};
use PHPUnit\Framework\TestCase;

final class MetricsTest extends TestCase
{
    public function testServerMetricsMapsNestedValues(): void
    {
        $adapter = $this->createMock(OpenVPNFuncAdapter::class);
        $adapter->expects(self::once())->method('request')->with('GET', 'server/metrics')->willReturn([
            'code' => 200, 'message' => 'ok', 'data' => [
                'timestamp' => '2026-01-01T12:00:00+00:00', 'uptime_seconds' => 123.5,
                'cpu' => ['usage_percent' => 25.5, 'avg_load' => 1.2, 'cores' => 4],
                'memory' => ['total_gb' => 8.0, 'active_gb' => 3.0, 'free_gb' => 5.0, 'usage_percent' => 37.5],
                'disk' => ['total_gb' => 100.0, 'used_gb' => 40.0, 'available_gb' => 60.0, 'usage_percent' => 40.0],
                'network' => ['incoming_kb_sec' => 10.0, 'outgoing_kb_sec' => 20.0, 'total_kb_sec' => 30.0],
            ]]);
        $response = (new MetricsContract($adapter))->server();
        self::assertSame(200, $response->code);
        self::assertSame('ok', $response->message);
        $server = $response->server;
        self::assertSame('2026-01-01T12:00:00+00:00', $server->timestamp->format('c'));
        self::assertSame(123.5, $server->uptimeSeconds);
        self::assertSame(['usagePercent' => 25.5, 'avgLoad' => 1.2, 'cores' => 4], get_object_vars($server->cpu));
        self::assertSame(['totalGB' => 8.0, 'activeGB' => 3.0, 'freeGB' => 5.0, 'usagePercent' => 37.5], get_object_vars($server->memory));
        self::assertSame(['totalGB' => 100.0, 'usedGB' => 40.0, 'availableGB' => 60.0, 'usagePercent' => 40.0], get_object_vars($server->disk));
        self::assertSame(['incomingKBSec' => 10.0, 'outgoingKBSec' => 20.0, 'totalKBSec' => 30.0], get_object_vars($server->network));
    }

    public function testNodeStatusMapsFlagsAndIdentity(): void
    {
        $adapter = $this->createMock(OpenVPNFuncAdapter::class);
        $adapter->expects(self::once())->method('request')->with('GET', '/api/server/status')->willReturn([
            'code' => 200, 'message' => 'ok', 'data' => ['date' => '2026-01-01T00:00:00+00:00', 'server_number' => 2, 'server_code' => 'node',
                'isServerWorking' => true, 'isRedisRunning' => false, 'isConfigsDirExists' => true, 'isConfigsDBExists' => false, 'isOVPNActive' => true]]);
        $node = (new MetricsContract($adapter))->node()->node;
        self::assertSame(2, $node->serverNumber);
        self::assertSame('node', $node->serverCode);
        self::assertSame('2026-01-01T00:00:00+00:00', $node->date->format('c'));
        self::assertTrue($node->isServerWorking);
        self::assertFalse($node->isRedisRunning);
        self::assertTrue($node->isConfigsDirExists);
        self::assertFalse($node->isConfigsDBExists);
        self::assertTrue($node->isOVPNActive);
    }
}
