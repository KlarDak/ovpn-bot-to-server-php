<?php
declare(strict_types=1);

namespace CNS\BotToServer\Tests\Unit;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Resources\{UserResource, AccessesResource, SubsResource, ServersResource};
use CNS\BotToServer\Types\UserType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ResourceReadsTest extends TestCase
{
    #[DataProvider('presence')]
    public function testUserGetUsesResolvedId(bool $found): void
    {
        $row = ['id' => '7', 'telegram_id' => '123', 'email' => 'test@example.invalid', 'username' => 'tester', 'language' => 'ru',
            'created_at' => '2026-01-01', 'last_activity_at' => '2026-01-02', 'expired_at' => '2026-02-01',
            'last_payment_at' => '2026-01-03', 'is_active' => 0, 'disabled_at' => '2026-01-04', 'is_dropped' => 1];
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::exactly(2))->method('fetchOne')->willReturnCallback(static function ($sql, $params) use ($found, $row): ?array {
            if ($sql === 'SELECT id FROM users WHERE telegram_id = :identifier LIMIT 1') {
                self::assertSame([':identifier' => 123], $params); return ['id' => '7'];
            }
            self::assertSame('SELECT * FROM users WHERE id = :id LIMIT 1', $sql);
            self::assertSame([':id' => 7], $params);
            return $found ? $row : null;
        });
        $user = (new UserResource($db, 123, UserType::TELEGRAM))->get();
        if (!$found) { self::assertNull($user); return; }
        self::assertSame(7, $user->id);
        self::assertSame(123, $user->telegram_id);
        self::assertSame('test@example.invalid', $user->email);
        self::assertSame('tester', $user->username);
        self::assertFalse($user->is_active);
        self::assertTrue($user->is_dropped);
        foreach (['created_at' => '2026-01-01', 'last_activity_at' => '2026-01-02', 'expired_at' => '2026-02-01', 'last_payment_at' => '2026-01-03', 'disabled_at' => '2026-01-04'] as $field => $date) {
            self::assertSame($date, $user->$field->format('Y-m-d'));
        }
    }

    public static function presence(): array { return [[false], [true]]; }

    #[DataProvider('userActions')]
    public function testUserStateChanges(string $method, string $sql): void
    {
        foreach ([true, false] as $result) {
            $db = $this->createMock(IDBConnector::class);
            $db->expects(self::once())->method('fetchOne')->willReturn(['id' => 7]);
            $db->expects(self::once())->method('execute')->with($sql, [':id' => 7])->willReturn($result);
            self::assertSame($result, (new UserResource($db, 123, UserType::TELEGRAM))->$method());
        }
    }

    public static function userActions(): array
    {
        return [
            ['touch', 'UPDATE users SET last_activity_at = NOW() WHERE id = :id AND is_dropped = 0'],
            ['block', 'UPDATE users SET is_active = 0, disabled_at = NOW() WHERE id = :id AND is_dropped = 0'],
            ['unblock', 'UPDATE users SET is_active = 1, disabled_at = NULL WHERE id = :id AND is_dropped = 0'],
            ['drop', 'UPDATE users SET is_active = 0, disabled_at = NOW(), is_dropped = 1 WHERE id = :id'],
        ];
    }

    #[DataProvider('countFilters')]
    public function testAccessCountPreservesAggregate(?string $type, string $sql, array $params): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->with($sql, $params)->willReturn(['COUNT(*)' => '3']);
        self::assertSame(['COUNT(*)' => '3'], (new AccessesResource($db, 7))->count($type));
    }

    public static function countFilters(): array
    {
        return [[null, 'SELECT COUNT(*) FROM accesses WHERE  user_id = :user_id AND is_dropped = 0', [':user_id' => 7]],
            ['openvpn', 'SELECT COUNT(*) FROM accesses WHERE type = :vpn_type AND user_id = :user_id AND is_dropped = 0', [':user_id' => 7, ':vpn_type' => 'openvpn']]];
    }

    public function testEmptyAccessList(): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchAll')->with('SELECT * FROM accesses WHERE user_id = :user_id AND is_dropped = 0', [':user_id' => 7])->willReturn([]);
        self::assertSame([], (new AccessesResource($db, 7))->list());
    }

    #[DataProvider('presence')]
    public function testSubscriptionGet(bool $found): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->with('SELECT * FROM subs WHERE short_code = :short_code AND is_dropped = 0', [':short_code' => 'abc'])->willReturn($found ? [
            'id' => '1', 'short_code' => 'abc', 'server_id' => '2', 'access_id' => '3', 'created_at' => '2026-01-01', 'is_used' => 0, 'used_at' => null, 'is_dropped' => 0,
        ] : null);
        $sub = (new SubsResource($db))->get('abc');
        if (!$found) { self::assertNull($sub); return; }
        self::assertSame(1, $sub->id);
        self::assertSame(2, $sub->server_id);
        self::assertSame(3, $sub->access_id);
        self::assertSame('abc', $sub->short_code);
        self::assertSame('2026-01-01', $sub->created_at->format('Y-m-d'));
        self::assertFalse($sub->is_used);
        self::assertNull($sub->used_at);
    }

    private static function serverRow(): array
    {
        return ['id' => 2, 'code' => 's', 'name' => 'Test', 'host' => 'example.invalid', 'port' => 443, 'encrypt_secret_code' => 'unused',
            'type' => 'openvpn', 'created_at' => '2026-01-01', 'status' => 'disabled', 'disabled_at' => '2026-01-02', 'is_dropped' => 0];
    }

    #[DataProvider('presence')]
    public function testServerGet(bool $found): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->with('SELECT * FROM servers WHERE code = :code AND is_dropped = 0', [':code' => 's'])->willReturn($found ? self::serverRow() : null);
        $server = (new ServersResource($db))->get('s');
        if (!$found) { self::assertNull($server); return; }
        self::assertSame('s', $server->code);
        self::assertSame(443, $server->port);
        self::assertNull($server->subs_url);
        self::assertNull($server->api_endpoint);
        self::assertSame('2026-01-02', $server->disabled_at->format('Y-m-d'));
    }

    #[DataProvider('serverLists')]
    public function testServerLists(string $method, array $args): void
    {
        foreach ([[], [self::serverRow()]] as $rows) {
            $db = $this->createMock(IDBConnector::class);
            $db->expects(self::once())->method('fetchAll')->with('SELECT * FROM servers WHERE is_dropped = 0', [])->willReturn($rows);
            $servers = (new ServersResource($db))->$method(...$args);
            self::assertCount(count($rows), $servers);
            if ($rows) { self::assertSame('s', $servers[0]->code); }
        }
    }

    public static function serverLists(): array { return [['getAll', []], ['filterBy', [[]]]]; }
}
