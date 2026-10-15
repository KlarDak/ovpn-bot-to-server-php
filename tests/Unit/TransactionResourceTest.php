<?php
declare(strict_types=1);

namespace CNS\BotToServer\Tests\Unit;

use CNS\BotToServer\Database\IDBConnector;
use CNS\BotToServer\Resources\TransactionResource;
use CNS\BotToServer\Types\Transaction;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TransactionResourceTest extends TestCase
{
    #[DataProvider('creationCases')]
    public function testCreate(array $options, array $expected): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('insert')->with(
            self::callback(static fn(string $sql): bool => preg_replace('/\s+/', ' ', trim($sql)) ===
                'INSERT INTO transactions ( user_id, provider, provider_payment_id, amount, currency, duration_days, description, metadata ) VALUES ( :user_id, :provider, :provider_payment_id, :amount, :currency, :duration_days, :description, :metadata )'),
            $expected
        )->willReturn(42);
        self::assertSame(42, (new TransactionResource($db))->create(7, 'test', '123456789.01', ...$options));
    }

    public static function creationCases(): array
    {
        $defaults = ['user_id' => 7, 'provider' => 'test', 'provider_payment_id' => null, 'amount' => '123456789.01', 'currency' => 'RUB', 'duration_days' => null, 'description' => null, 'metadata' => null];
        return [
            'defaults' => [[], $defaults],
            'all options' => [['USD', 'payment-1', 30, 'Subscription', ['plan' => 'monthly', 'tags' => [1, true]]], array_replace($defaults, ['currency' => 'USD', 'provider_payment_id' => 'payment-1', 'duration_days' => 30, 'description' => 'Subscription', 'metadata' => '{"plan":"monthly","tags":[1,true]}'])],
            'empty values preserved' => [['RUB', '', 0, '', []], array_replace($defaults, ['provider_payment_id' => '', 'duration_days' => 0, 'description' => '', 'metadata' => '[]'])],
        ];
    }

    #[DataProvider('lookups')]
    public function testLookupMapsTransaction(string $method, array $args, string $sql, array $params): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->with($sql, $params)->willReturn(self::row());
        $transaction = (new TransactionResource($db))->$method(...$args);
        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertSame(42, $transaction->id);
        self::assertSame(7, $transaction->user_id);
        self::assertSame('test', $transaction->provider);
        self::assertSame('payment-1', $transaction->provider_payment_id);
        self::assertSame('123456789.01', $transaction->amount);
        self::assertSame('RUB', $transaction->currency);
        self::assertSame('succeeded', $transaction->status);
        self::assertSame(30, $transaction->duration_days);
        self::assertSame('Subscription', $transaction->description);
        self::assertSame(['plan' => 'monthly'], $transaction->metadata);
        self::assertSame('2026-01-01 12:00:00', $transaction->created_at->format('Y-m-d H:i:s'));
        self::assertSame('2026-01-02 12:00:00', $transaction->updated_at->format('Y-m-d H:i:s'));
        self::assertSame('2026-01-03 12:00:00', $transaction->paid_at->format('Y-m-d H:i:s'));
    }

    #[DataProvider('lookups')]
    public function testMissingLookupReturnsNull(string $method, array $args, string $sql, array $params): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->with($sql, $params)->willReturn(null);
        self::assertNull((new TransactionResource($db))->$method(...$args));
    }

    public static function lookups(): array
    {
        return [
            ['get', [42], 'SELECT * FROM transactions WHERE id = :id', [':id' => 42]],
            ['getByProviderPayment', ['test', 'payment-1'], 'SELECT * FROM transactions WHERE provider = :provider AND provider_payment_id = :provider_payment_id', [':provider' => 'test', 'provider_payment_id' => 'payment-1']],
        ];
    }

    public function testNullableProviderPaymentIdCanBeRead(): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->with('SELECT * FROM transactions WHERE id = :id', [':id' => 42])
            ->willReturn(array_replace(self::row(), ['provider_payment_id' => null]));
        // create() accepts null; reading the resulting row must also support it.
        self::assertNull((new TransactionResource($db))->get(42)->provider_payment_id);
    }

    public function testNullableOptionalFields(): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchOne')->willReturn(array_replace(self::row(), [
            'duration_days' => null, 'description' => null, 'metadata' => null, 'updated_at' => null, 'paid_at' => null,
        ]));
        $transaction = (new TransactionResource($db))->get(42);
        foreach (['duration_days', 'description', 'metadata', 'updated_at', 'paid_at'] as $field) { self::assertNull($transaction->$field); }
    }

    #[DataProvider('userRows')]
    public function testUserTransactions(array $rows, array $ids): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('fetchAll')->with('SELECT * FROM transactions WHERE user_id = :user_id', [':user_id' => 7])->willReturn($rows);
        $transactions = (new TransactionResource($db))->getByUserId(7);
        self::assertSame($ids, array_map(static fn(Transaction $item): int => $item->id, $transactions));
    }

    public static function userRows(): array { return [[[], []], [[self::row(), array_replace(self::row(), ['id' => 43])], [42, 43]]]; }

    public function testEmptyUpdateDoesNotWrite(): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::never())->method('execute');
        self::assertFalse((new TransactionResource($db))->update(42));
    }

    #[DataProvider('updates')]
    public function testUpdate(array $args, string $set, array $params): void
    {
        foreach ([true, false] as $result) {
            $db = $this->createMock(IDBConnector::class);
            $db->expects(self::once())->method('execute')->with('UPDATE transactions SET ' . $set . ' WHERE id = :id', ['id' => 42] + $params)->willReturn($result);
            self::assertSame($result, (new TransactionResource($db))->update(42, ...$args));
        }
    }

    public static function updates(): array
    {
        return [
            [['provider_payment_id' => 'p'], 'provider_payment_id = :provider_payment_id', ['provider_payment_id' => 'p']],
            [['description' => 'text'], 'description = :description', ['description' => 'text']],
            [['metadata' => ['a' => 1]], 'metadata = :metadata', ['metadata' => '{"a":1}']],
            [['provider_payment_id' => '', 'description' => '', 'metadata' => []], 'provider_payment_id = :provider_payment_id, description = :description, metadata = :metadata', ['provider_payment_id' => '', 'description' => '', 'metadata' => '[]']],
        ];
    }

    #[DataProvider('invalidMetadata')]
    public function testInvalidMetadataFailsBeforeWriting(string $method, array $metadata): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::never())->method('insert');
        $db->expects(self::never())->method('execute');
        $resource = new TransactionResource($db);
        $this->expectException(\JsonException::class);
        if ($method === 'create') { $resource->create(7, 'test', '10.00', metadata: $metadata); }
        else { $resource->update(42, metadata: $metadata); }
    }

    public static function invalidMetadata(): iterable
    {
        foreach (['create', 'update'] as $method) {
            yield $method . ' invalid UTF-8' => [$method, ['text' => "\xB1\x31"]];
            yield $method . ' infinite number' => [$method, ['number' => INF]];
        }
    }

    #[DataProvider('statuses')]
    public function testStatusChanges(string $method, string $status, bool $result): void
    {
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('execute')->with('UPDATE transactions SET status = :status WHERE id = :id', [':id' => 42, ':status' => $status])->willReturn($result);
        self::assertSame($result, (new TransactionResource($db))->$method(42));
    }

    public static function statuses(): iterable
    {
        foreach (['cancel' => 'canceled', 'failed' => 'failed', 'refunded' => 'refunded'] as $method => $status) {
            foreach ([true, false] as $result) { yield [$method, $status, $result]; }
        }
    }

    #[DataProvider('booleans')]
    public function testSuccessSetsPaidTimestamp(bool $result): void
    {
        $before = date('Y-m-d H:i:s');
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method('execute')->with('UPDATE transactions SET status = :status, paid_at = :paid_at WHERE id = :id',
            self::callback(static function (array $params) use ($before): bool {
                self::assertSame(42, $params[':id']);
                self::assertSame('succeeded', $params[':status']);
                self::assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $params[':paid_at']);
                self::assertGreaterThanOrEqual($before, $params[':paid_at']);
                self::assertLessThanOrEqual(date('Y-m-d H:i:s'), $params[':paid_at']);
                return true;
            }))->willReturn($result);
        self::assertSame($result, (new TransactionResource($db))->success(42));
    }

    public static function booleans(): array { return [[true], [false]]; }

    #[DataProvider('databaseFailures')]
    public function testDatabaseExceptionsPropagate(string $method, array $args, string $dbMethod): void
    {
        $failure = new \RuntimeException('Database unavailable');
        $db = $this->createMock(IDBConnector::class);
        $db->expects(self::once())->method($dbMethod)->willThrowException($failure);
        $this->expectExceptionObject($failure);
        (new TransactionResource($db))->$method(...$args);
    }

    public static function databaseFailures(): array
    {
        return [['create', [7, 'test', '10.00'], 'insert'], ['get', [42], 'fetchOne'],
            ['getByProviderPayment', ['test', 'p'], 'fetchOne'], ['getByUserId', [7], 'fetchAll'],
            ['update', [42, 'p'], 'execute'], ['success', [42], 'execute'],
            ['cancel', [42], 'execute'], ['failed', [42], 'execute'], ['refunded', [42], 'execute']];
    }

    private static function row(): array
    {
        return ['id' => 42, 'user_id' => 7, 'provider' => 'test', 'provider_payment_id' => 'payment-1',
            'amount' => '123456789.01', 'currency' => 'RUB', 'status' => 'succeeded', 'duration_days' => '30',
            'description' => 'Subscription', 'metadata' => '{"plan":"monthly"}',
            'created_at' => '2026-01-01 12:00:00', 'updated_at' => '2026-01-02 12:00:00', 'paid_at' => '2026-01-03 12:00:00'];
    }
}
