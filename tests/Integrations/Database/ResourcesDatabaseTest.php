<?php
declare(strict_types=1);

namespace CNS\BotToServer\Tests\Integrations\Database;

use CNS\BotToServer\Resources\{UserResource, UsersResource, AccessResource, AccessesResource, ServersResource, SubsResource};
use CNS\BotToServer\Types\{UserType, ServerStatusType};
use CNS\BotToServer\Exceptions\AccessNotFoundException;

final class ResourcesDatabaseTest extends MySqlTestCase
{
    public function testUserLifecycleAndDateQueries(): void
    {
        $new = new UserResource($this->db, 123456, UserType::TELEGRAM);
        self::assertFalse($new->exists());
        $id = $new->create('ru', 'tester');
        // Re-resolve after creation: UserResource caches the ID in its constructor.
        $user = new UserResource($this->db, 123456, UserType::TELEGRAM);
        self::assertTrue($user->exists());
        self::assertSame($id, $user->get()->id);
        self::assertTrue($user->update(username: 'updated', email: 'test@example.invalid'));
        self::assertSame('updated', $user->get()->username);
        self::assertTrue($user->updatePayment(new \DateTimeImmutable('2026-01-01'), new \DateTimeImmutable('2026-02-01')));
        self::assertSame('2026-02-01', $user->get()->expired_at->format('Y-m-d'));
        self::assertNotNull($user->get()->last_activity_at);
        $users = new UsersResource($this->db);
        self::assertCount(1, $users->expiresBetween(new \DateTimeImmutable('2026-01-31'), new \DateTimeImmutable('2026-02-02')));
        self::assertCount(1, $users->expiredBefore(new \DateTimeImmutable('2026-02-02')));
        self::assertTrue($user->block());
        self::assertFalse($user->get()->is_active);
        self::assertNotNull($user->get()->disabled_at);
        self::assertTrue($user->unblock());
        self::assertTrue($user->get()->is_active);
        self::assertNull($user->get()->disabled_at);
        self::assertTrue($user->drop());
        self::assertSame([], $users->getAll());
        self::assertCount(1, $users->dropped());
    }

    public function testAccessLifecycleAndOwnerTypeFiltering(): void
    {
        $access = new AccessResource($this->db, '11111111-1111-4111-8111-111111111111');
        self::assertFalse($access->exists());
        $id = $access->create(7, 'openvpn', 2, 'phone');
        self::assertTrue($access->exists());
        self::assertSame($id, $access->get()->id);
        (new AccessResource($this->db, '22222222-2222-4222-8222-222222222222'))->create(8, 'openvpn', 2);
        (new AccessResource($this->db, '33333333-3333-4333-8333-333333333333'))->create(7, 'other', 2);
        $list = new AccessesResource($this->db, 7);
        self::assertCount(2, $list->list());
        self::assertCount(1, $list->list('openvpn'));
        self::assertSame(1, (int) $list->count('openvpn')['COUNT(*)']);
        self::assertTrue($access->update('laptop'));
        self::assertSame('laptop', $access->get()->name);
        self::assertTrue($access->block());
        self::assertFalse($access->get()->is_active);
        self::assertNotNull($access->get()->disabled_at);
        self::assertTrue($access->unblock());
        self::assertTrue($access->get()->is_active);
        self::assertNull($access->get()->disabled_at);
        self::assertTrue($access->drop());
        self::assertFalse($access->exists());
        self::assertSame([], $list->list('openvpn'));
        $this->expectException(AccessNotFoundException::class);
        $access->get();
    }

    public function testServerCreateUpdateAndStatusFilters(): void
    {
        $servers = new ServersResource($this->db);
        $id = $servers->create('test-node', 'Test', 'example.invalid', 443, '/api', 'unused', 'openvpn');
        self::assertSame($id, $servers->get('test-node')->id);
        self::assertTrue($servers->update('test-node', name: 'Renamed', port: 8443));
        self::assertSame('Renamed', $servers->get('test-node')->name);
        self::assertSame(8443, $servers->get('test-node')->port);
        self::assertCount(1, $servers->filterBy(['type' => 'openvpn', 'port' => 8443]));
        self::assertSame([], $servers->filterBy(['type' => 'other']));
        self::assertTrue($servers->updateStatus('test-node', ServerStatusType::MAINTENANCE));
        self::assertSame('maintenance', $servers->get('test-node')->status);
        self::assertNotNull($servers->get('test-node')->disabled_at);
        self::assertTrue($servers->updateStatus('test-node', ServerStatusType::ACTIVE));
        self::assertNull($servers->get('test-node')->disabled_at);
        self::assertFalse($servers->updateStatus('test-node', ServerStatusType::DROPPED));
    }

    public function testSubscriptionWritesPersistAndDroppedRowsAreHidden(): void
    {
        $subs = new SubsResource($this->db);
        $id = $subs->create('first', 2, 3);
        self::assertSame($id, $subs->get('first')->id);
        self::assertTrue($subs->update('first', 'second'));
        self::assertNull($subs->get('first'));
        self::assertSame('second', $subs->get('second')->short_code);
        self::assertTrue($subs->setUsed('second'));
        // Check persistence directly: mapping non-null used_at is a separate DTO issue.
        $row = $this->db->fetchOne('SELECT is_used, used_at FROM subs WHERE id = :id', ['id' => $id]);
        self::assertSame(1, (int) $row['is_used']);
        self::assertNotNull($row['used_at']);
        self::assertTrue($subs->drop('second'));
        self::assertNull($subs->get('second'));
    }
}
