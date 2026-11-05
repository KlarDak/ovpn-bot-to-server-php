<?php

declare(strict_types=1);

namespace CNS\BotToServer\Tests\Unit;

use CNS\BotToServer\Contracts\OpenVPN\{AccessContract, ActiveContract, OpenVPNFuncAdapter, SubsContract};
use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Http\Types\HttpResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContractsTest extends TestCase
{
    #[DataProvider('requests')]
    public function testContractRequest(string $class, string $method, array $args, string $verb, string $endpoint, array $options): void
    {
        $adapter = $this->createMock(OpenVPNFuncAdapter::class);
        $adapter->expects(self::once())->method('request')->with($verb, $endpoint, $options)
            ->willReturn(['code' => 200, 'message' => 'ok', 'data' => ['uuid' => 'a']]);
        $response = (new $class($adapter))->$method(...$args);
        self::assertSame(200, $response->code);
        self::assertSame('ok', $response->message);
        self::assertSame(['uuid' => 'a'], $response->data);
    }

    public static function requests(): iterable
    {
        yield 'get' => [AccessContract::class, 'get', ['a'], 'GET', 'config/a', []];
        yield 'create' => [AccessContract::class, 'create', ['a', 'tcp', 3600], 'POST', 'config', ['uuid' => 'a', 'type' => 'tcp', 'time' => 3600]];
        yield 'recreate' => [AccessContract::class, 'recreate', ['a', 'udp', 60], 'PUT', 'config/a', ['time' => 60, 'type' => 'udp']];
        yield 'update type' => [AccessContract::class, 'update', ['a', 'tcp'], 'PATCH', 'config/a', ['type' => 'tcp']];
        yield 'update ttl' => [AccessContract::class, 'update', ['a', null, 60], 'PATCH', 'config/a', ['time' => 60]];
        yield 'update both' => [AccessContract::class, 'update', ['a', 'tcp', 60], 'PATCH', 'config/a', ['type' => 'tcp', 'time' => 60]];
        yield 'delete' => [AccessContract::class, 'delete', ['a'], 'DELETE', 'config/a', []];
        yield 'update all' => [AccessContract::class, 'updateAll', [['a', 'b'], 'tcp', 60], 'PATCH', 'configs/update', ['uuids' => ['a', 'b'], 'type' => 'tcp', 'time' => 60]];
        yield 'delete all' => [AccessContract::class, 'deleteAll', [['a', 'b']], 'DELETE', 'configs/delete', ['uuids' => ['a', 'b']]];
        foreach (['kick', 'ban', 'pardon'] as $method) {
            yield $method => [ActiveContract::class, $method, ['a'], 'POST', 'active/' . $method, ['uuid' => 'a']];
        }
    }

    public function testEmptyAccessUpdateDoesNotRequest(): void
    {
        $adapter = $this->createMock(OpenVPNFuncAdapter::class);
        $adapter->expects(self::never())->method('request');
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Error with arguments');
        (new AccessContract($adapter))->update('a');
    }

    public function testActiveListMapsResponses(): void
    {
        $adapter = $this->createMock(OpenVPNFuncAdapter::class);
        $adapter->expects(self::once())->method('request')->with('POST', '/api/active/list')->willReturn([
            ['code' => 200, 'message' => 'first', 'data' => ['uuid' => 'a']],
            ['code' => 200, 'message' => 'second', 'data' => ['uuid' => 'b']],
        ]);
        $items = (new ActiveContract($adapter))->getAll();
        self::assertCount(2, $items);
        self::assertSame('first', $items[0]->message);
        self::assertSame(['uuid' => 'b'], $items[1]->data);
    }

    public function testAdapterBuildsJsonAndFreshAuthorizationForEveryRequest(): void
    {
        $http = $this->createMock(HttpClientInterface::class);
        $tokens = 0;
        $calls = [];
        $http->expects(self::exactly(2))->method('request')->willReturnCallback(
            static function ($method, $endpoint, $options, $headers) use (&$calls): HttpResponse {
                $calls[] = [$method, $endpoint, $options, $headers];
                return new HttpResponse(200, [], '{"code":200,"message":"ok","data":null}');
            }
        );
        $http->expects(self::never())->method('stream');
        $adapter = new OpenVPNFuncAdapter($http, static function () use (&$tokens): string { return 'test-token-' . ++$tokens; });
        self::assertSame(['code' => 200, 'message' => 'ok', 'data' => null], $adapter->request('POST', 'config', ['uuid' => 'a']));
        $adapter->request('GET', 'config/a');
        self::assertSame(2, $tokens);
        self::assertSame([
            ['POST', 'config', ['json' => ['uuid' => 'a']], ['Authorization' => 'Bearer test-token-1']],
            ['GET', 'config/a', ['json' => []], ['Authorization' => 'Bearer test-token-2']],
        ], $calls);
    }

    public function testAdapterPropagatesTransportFailure(): void
    {
        $http = $this->createMock(HttpClientInterface::class);
        $failure = new \RuntimeException('transport failed');
        $http->expects(self::once())->method('request')->willThrowException($failure);
        $this->expectExceptionObject($failure);
        (new OpenVPNFuncAdapter($http, static fn(): string => 'test'))->request('GET', 'config/a');
    }

    #[DataProvider('subscriptionRows')]
    public function testSubscriptionLink(?array $row, ?string $expected): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->with('SELECT * FROM subs WHERE short_code = :short_code AND is_dropped = 0', [':short_code' => 'short'])->willReturn($row);
        self::assertSame($expected, (new SubsContract($db, 'https://example.invalid/subs'))->getSubsLink('short'));
    }

    public static function subscriptionRows(): array
    {
        return [[null, null], [['id' => '1', 'short_code' => 'short', 'server_id' => '2', 'access_id' => '3', 'created_at' => '2026-01-01', 'is_used' => 0, 'used_at' => null, 'is_dropped' => 0], 'https://example.invalid/subs/short']];
    }

    #[DataProvider('subscriptionInserts')]
    public function testCreateSubscriptionLink(int $id, ?string $expected): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('insert')->with('INSERT INTO subs (short_code, server_id, access_id) VALUES (:short_code, :server_id, :access_id)', [':short_code' => 'short', ':server_id' => 2, ':access_id' => 3])->willReturn($id);
        self::assertSame($expected, (new SubsContract($db, 'https://example.invalid/subs'))->createSubsLink('short', 2, 3));
    }

    public static function subscriptionInserts(): array { return [[0, null], [5, 'https://example.invalid/subs/short']]; }
}
