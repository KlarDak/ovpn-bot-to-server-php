<?php

declare(strict_types=1);

namespace CNS\BotToServer\Tests\Unit;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Exceptions\AccessNotFoundException;
use CNS\BotToServer\Exceptions\UserAlreadyExistsException;
use CNS\BotToServer\Resources\{AccessResource, AccessesResource, UserResource, UsersResource, SubsResource, ServersResource};
use CNS\BotToServer\Types\{Access, User, UserType, Subs, Server, ServerStatusType};
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ResourcesTest extends TestCase
{
    #[DataProvider('accessCounts')]
    public function testAccessExists(?array $row, bool $expected): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->with(
            'SELECT COUNT(*) FROM accesses WHERE uuid = :uuid AND is_dropped = 0', [':uuid' => 'access-1']
        )->willReturn($row);
        self::assertSame($expected, (new AccessResource($db, 'access-1'))->exists());
    }

    public static function accessCounts(): array
    {
        return [[null, false], [[], false], [['COUNT(*)' => '0'], false], [['COUNT(*)' => '1'], true]];
    }

    public function testMissingAccessThrows(): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->with(
            'SELECT * FROM accesses WHERE uuid = :uuid AND is_dropped = 0', [':uuid' => 'missing']
        )->willReturn(null);
        $this->expectException(AccessNotFoundException::class);
        (new AccessResource($db, 'missing'))->get();
    }

    #[DataProvider('accessFilters')]
    public function testAccessListFiltersAndMaps(?string $type): void
    {
        $db = $this->createMock(IDBConnector::class);
        $sql = 'SELECT * FROM accesses WHERE user_id = :user_id AND is_dropped = 0';
        $params = [':user_id' => 7];
        if ($type !== null) { $sql .= ' AND type = :type'; $params[':type'] = $type; }
        $db->expects(self::once())->method('fetchAll')->with($sql, $params)->willReturn([self::accessRow()]);
        $items = (new AccessesResource($db, 7))->list($type);
        self::assertCount(1, $items);
        self::assertInstanceOf(Access::class, $items[0]);
        self::assertSame('access-1', $items[0]->uuid);
        self::assertSame(7, $items[0]->user_id);
    }

    public static function accessFilters(): array { return [[null], ['openvpn'], ['']]; }

    public function testAccessGetMapsRow(): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->with(
            'SELECT * FROM accesses WHERE uuid = :uuid AND is_dropped = 0', [':uuid' => 'access-1']
        )->willReturn(self::accessRow());
        $access = (new AccessResource($db, 'access-1'))->get();
        self::assertSame(3, $access->id);
        self::assertTrue($access->is_active);
        self::assertNull($access->disabled_at);
    }

    #[DataProvider('writes')]
    public function testResourceWrites(string $class, array $constructor, string $method, array $args, string $dbMethod, string $sql, array $params): void
    {
        foreach (($dbMethod === 'insert' ? [42, 0] : [true, false]) as $result) {
            $db = $this->createMock(IDBConnector::class);
            $db->expects(self::once())->method($dbMethod)->with($sql, $params)->willReturn($result);
            $resource = new $class($db, ...$constructor);
            self::assertSame($result, $resource->$method(...$args));
        }
    }

    public static function writes(): iterable
    {
        yield 'create access' => [AccessResource::class, ['a'], 'create', [7, 'openvpn', 2], 'insert',
            'INSERT INTO accesses (user_id, uuid, type, server_id, name) VALUES (:user_id, :uuid, :type, :server_id, :name)',
            [':user_id' => 7, ':uuid' => 'a', ':type' => 'openvpn', ':server_id' => 2, ':name' => null]];
        yield 'rename access' => [AccessResource::class, ['a'], 'update', ['phone'], 'execute',
            'UPDATE accesses SET name = :name WHERE uuid = :uuid AND is_dropped = 0', [':name' => 'phone', ':uuid' => 'a']];
        foreach (['block' => 'is_active = 0, disabled_at = NOW()', 'unblock' => 'is_active = 1, disabled_at = NULL', 'drop' => 'is_active = 0, disabled_at = NOW(), is_dropped = 1'] as $method => $set) {
            yield $method . ' access' => [AccessResource::class, ['a'], $method, [], 'execute',
                "UPDATE accesses SET $set WHERE uuid = :uuid" . ($method === 'drop' ? '' : ' AND is_dropped = 0'), [':uuid' => 'a']];
        }
        yield 'create subscription' => [SubsResource::class, [], 'create', ['short', 2, 3], 'insert',
            'INSERT INTO subs (short_code, server_id, access_id) VALUES (:short_code, :server_id, :access_id)', [':short_code' => 'short', ':server_id' => 2, ':access_id' => 3]];
        yield 'rename subscription' => [SubsResource::class, [], 'update', ['old', 'new'], 'execute',
            'UPDATE subs SET short_code = :new_short_code WHERE short_code = :old_short_code AND is_dropped = 0', [':old_short_code' => 'old', ':new_short_code' => 'new']];
        yield 'use subscription' => [SubsResource::class, [], 'setUsed', ['short'], 'execute',
            'UPDATE subs SET is_used = 1, used_at = NOW() WHERE short_code = :short_code AND is_dropped = 0', [':short_code' => 'short']];
        yield 'drop subscription' => [SubsResource::class, [], 'drop', ['short'], 'execute',
            'UPDATE subs SET is_dropped = 1 WHERE short_code = :short_code', [':short_code' => 'short']];
        yield 'create server' => [ServersResource::class, [], 'create', ['s', 'Test', 'localhost', 443, '/api', 'encrypted', 'openvpn'], 'insert',
            "INSERT INTO servers(code, name, host, port, api_endpoint, encrypt_secret_code, type, subs_url, status) VALUES (:code, :name, :host, :port, :api_endpoint, :encrypt_secret_code, :type, :subs_url, 'active')",
            [':code' => 's', ':name' => 'Test', ':host' => 'localhost', ':port' => 443, ':api_endpoint' => '/api', ':encrypt_secret_code' => 'encrypted', ':type' => 'openvpn', ':subs_url' => null]];
        yield 'update server preserves empty and zero' => [ServersResource::class, [], 'update', ['s', '', null, 0], 'execute',
            'UPDATE servers SET name = :name, port = :port WHERE code = :code AND is_dropped = 0', ['name' => '', 'port' => 0, 'code' => 's']];
    }

    #[DataProvider('userIdentifiers')]
    public function testNewUserCreatedByIdentifier(UserType $type, int|string $identifier): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->with("SELECT id FROM users WHERE {$type->value} = :identifier LIMIT 1", [':identifier' => $identifier])->willReturn(null);
        $db->expects(self::once())->method('insert')->with(
            "INSERT INTO users ({$type->value}, language, username) VALUES (:identifier, :language, :username)",
            [':identifier' => $identifier, ':language' => 'ru', ':username' => null]
        )->willReturn(7);
        $user = new UserResource($db, $identifier, $type);
        self::assertFalse($user->exists());
        self::assertSame(7, $user->create('ru'));
    }

    public static function userIdentifiers(): array { return [[UserType::TELEGRAM, 123], [UserType::EMAIL, 'test@example.invalid']]; }

    public function testDuplicateUserDoesNotInsert(): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->willReturn(['id' => '7']);
        $db->expects(self::never())->method('insert');
        $user = new UserResource($db, 123, UserType::TELEGRAM);
        self::assertTrue($user->exists());
        $this->expectException(UserAlreadyExistsException::class);
        $user->create('ru');
    }

    public function testEmptyUserUpdateDoesNotWrite(): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->willReturn(['id' => 7]);
        $db->expects(self::never())->method('execute');
        self::assertFalse((new UserResource($db, 123, UserType::TELEGRAM))->update());
    }

    public function testUserUpdatePreservesEmptyStringsAndOmitsNull(): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->willReturn(['id' => 7]);
        $db->expects(self::once())->method('execute')->with(
            'UPDATE users SET username = :username, language = :language WHERE id = :id AND is_dropped = 0',
            [':id' => 7, ':username' => '', ':language' => 'ru']
        )->willReturn(false);
        self::assertFalse((new UserResource($db, 123, UserType::TELEGRAM))->update(username: '', language: 'ru'));
    }

    public function testPaymentTouchesActivityBeforeUpdatingDates(): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->willReturn(['id' => 7]);
        $calls = [];
        $db->expects(self::exactly(2))->method('execute')->willReturnCallback(static function ($sql, $params) use (&$calls): bool {
            $calls[] = [$sql, $params]; return true;
        });
        self::assertTrue((new UserResource($db, 123, UserType::TELEGRAM))->updatePayment(new \DateTimeImmutable('2026-01-01 12:00:00'), new \DateTimeImmutable('2026-02-01 12:00:00')));
        self::assertSame([
            ['UPDATE users SET last_activity_at = NOW() WHERE id = :id AND is_dropped = 0', [':id' => 7]],
            ['UPDATE users SET expired_at = :expiry_date, last_payment_at = :payment_date WHERE id = :id AND is_dropped = 0', [':id' => 7, ':payment_date' => '2026-01-01 12:00:00', ':expiry_date' => '2026-02-01 12:00:00']],
        ], $calls);
    }

    #[DataProvider('userLists')]
    public function testUserLists(string $method, array $args, string $sql, array $params): void
    {
        foreach ([[], [self::userRow()]] as $rows) {
            $db = $this->createMock(IDBConnector::class);
            $db->expects(self::once())->method('fetchAll')->with($sql, $params)->willReturn($rows);
            $users = (new UsersResource($db))->$method(...$args);
            self::assertCount(count($rows), $users);
            if ($rows) { self::assertInstanceOf(User::class, $users[0]); self::assertSame(7, $users[0]->id); }
        }
    }

    public static function userLists(): iterable
    {
        yield ['getAll', [], 'SELECT * FROM users WHERE is_dropped = 0', []];
        yield ['dropped', [], 'SELECT * FROM users WHERE is_dropped = 1', []];
        $date = new \DateTimeImmutable('2026-01-01 12:34:56');
        yield ['expiredBefore', [$date], 'SELECT * FROM users WHERE expired_at < :date AND is_dropped = 0', [':date' => '2026-01-01 12:34:56']];
        yield ['expiresBetween', [$date, $date], 'SELECT * FROM users WHERE expired_at BETWEEN :start AND :end AND is_dropped = 0', [':start' => '2026-01-01 12:34:56', ':end' => '2026-01-01 12:34:56']];
    }

    public function testServerFiltersIgnoreUnapprovedColumns(): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchAll')->with('SELECT * FROM servers WHERE type = :type AND is_dropped = 0', [':type' => 'openvpn'])->willReturn([]);
        self::assertSame([], (new ServersResource($db))->filterBy(['type' => 'openvpn', 'is_dropped' => 1, 'name OR 1=1' => 'bad']));
    }

    public function testEmptyServerUpdateAndDroppedStatusDoNotWrite(): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::never())->method('execute');
        $servers = new ServersResource($db);
        self::assertFalse($servers->update('s'));
        self::assertFalse($servers->updateStatus('s', ServerStatusType::DROPPED));
    }

    #[DataProvider('statuses')]
    public function testServerStatusTimestamp(ServerStatusType $status, bool $disabled): void
    {
        $db = $this->createMock(IDBConnector::class);
        $before = date('Y-m-d H:i:s');
        $db->expects(self::once())->method('execute')->with(
            'UPDATE servers SET status = :status, disabled_at = :disabled_at WHERE code = :code AND is_dropped = 0',
            self::callback(static function (array $params) use ($status, $disabled, $before): bool {
                self::assertSame($status->value, $params[':status']);
                self::assertSame('s', $params[':code']);
                if ($disabled) { self::assertGreaterThanOrEqual($before, $params[':disabled_at']); self::assertLessThanOrEqual(date('Y-m-d H:i:s'), $params[':disabled_at']); }
                else { self::assertNull($params[':disabled_at']); }
                return true;
            })
        )->willReturn(true);
        self::assertTrue((new ServersResource($db))->updateStatus('s', $status));
    }

    public static function statuses(): array { return [[ServerStatusType::ACTIVE, false], [ServerStatusType::DRAINING, false], [ServerStatusType::DISABLED, true], [ServerStatusType::MAINTENANCE, true]]; }

    public static function accessRow(): array { return ['id' => '3', 'user_id' => '7', 'uuid' => 'access-1', 'type' => 'openvpn', 'server_id' => '2', 'created_at' => '2026-01-01', 'is_active' => 1, 'disabled_at' => null, 'is_dropped' => 0]; }
    public static function userRow(): array { return ['id' => '7', 'language' => 'ru', 'created_at' => '2026-01-01', 'expired_at' => null, 'last_payment_at' => null, 'is_active' => 1, 'disabled_at' => null, 'is_dropped' => 0]; }
}
