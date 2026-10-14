<?php
declare(strict_types=1);

namespace CNS\BotToServer\Tests\Unit;

use CNS\BotToServer\BotToServer;
use CNS\BotToServer\Config\BTSConfig;
use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Http\Interfaces\HttpClientInterface;
use CNS\BotToServer\Types\UserType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class BotToServerTest extends TestCase
{
    #[DataProvider('resources')]
    public function testFacadePassesIdentifiersAndConnector(string $factory, array $args, string $operation, string $dbMethod, string $sql, array $params, mixed $dbResult, mixed $expected): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method($dbMethod)->with($sql, $params)->willReturn($dbResult);
        $http = $this->createMock(HttpClientInterface::class);
        $http->expects(self::never())->method('request');
        $bot = new BotToServer($db, $http, new BTSConfig('key', 'admin', 'aud', 'configs', 'logs'));
        self::assertSame($expected, $bot->$factory(...$args)->$operation());
    }

    public static function resources(): array
    {
        return [
            ['user', [123, UserType::TELEGRAM], 'exists', 'fetchOne', 'SELECT id FROM users WHERE telegram_id = :identifier LIMIT 1', [':identifier' => 123], ['id' => 7], true],
            ['users', [], 'getAll', 'fetchAll', 'SELECT * FROM users WHERE is_dropped = 0', [], [], []],
            ['access', ['a'], 'exists', 'fetchOne', 'SELECT COUNT(*) FROM accesses WHERE uuid = :uuid AND is_dropped = 0', [':uuid' => 'a'], ['COUNT(*)' => 1], true],
            ['accesses', [7], 'list', 'fetchAll', 'SELECT * FROM accesses WHERE user_id = :user_id AND is_dropped = 0', [':user_id' => 7], [], []],
            ['server', ['s'], 'info', 'fetchOne', 'SELECT * FROM servers WHERE code = :code AND is_dropped = 0', [':code' => 's'], null, null],
            ['servers', [], 'getAll', 'fetchAll', 'SELECT * FROM servers WHERE is_dropped = 0', [], [], []],
        ];
    }

    public function testSubscriptionFactoryPassesConnector(): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->with('SELECT * FROM subs WHERE short_code = :short_code AND is_dropped = 0', [':short_code' => 'abc'])->willReturn(null);
        $bot = new BotToServer($db, $this->createStub(HttpClientInterface::class), new BTSConfig('key', 'admin', 'aud', 'configs', 'logs'));
        self::assertNull($bot->subs()->get('abc'));
    }
}
