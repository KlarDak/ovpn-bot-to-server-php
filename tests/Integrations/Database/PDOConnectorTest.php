<?php
declare(strict_types=1);

namespace CNS\BotToServer\Tests\Integrations\Database;

use PHPUnit\Framework\Attributes\DataProvider;

final class PDOConnectorTest extends MySqlTestCase
{
    public function testInsertReturnsGeneratedIdAndFetchOneAssociativeRow(): void
    {
        $id = $this->db->insert('INSERT INTO connector_probe (label, optional_value) VALUES (:label, :value)', [':label' => 'first', ':value' => null]);
        self::assertGreaterThan(0, $id);
        $row = $this->db->fetchOne('SELECT * FROM connector_probe WHERE id = :id', [':id' => $id]);
        self::assertSame($id, (int) $row['id']);
        self::assertSame('first', $row['label']);
        self::assertNull($row['optional_value']);
        self::assertArrayNotHasKey(0, $row);
    }

    public function testNoRowsReturnsNullOrEmptyArray(): void
    {
        self::assertNull($this->db->fetchOne('SELECT * FROM connector_probe'));
        self::assertSame([], $this->db->fetchAll('SELECT * FROM connector_probe'));
    }

    public function testFetchAllReturnsOrderedAssociativeRows(): void
    {
        $first = $this->db->insert('INSERT INTO connector_probe (label) VALUES (:label)', ['label' => 'first']);
        $second = $this->db->insert('INSERT INTO connector_probe (label) VALUES (:label)', ['label' => 'second']);
        self::assertGreaterThan($first, $second);
        $rows = $this->db->fetchAll('SELECT label FROM connector_probe ORDER BY id');
        self::assertSame([['label' => 'first'], ['label' => 'second']], $rows);
    }

    public function testExecuteUpdatesAndDeletes(): void
    {
        $id = $this->db->insert("INSERT INTO connector_probe (label) VALUES ('first')");
        self::assertTrue($this->db->execute('UPDATE connector_probe SET label = :label WHERE id = :id', ['label' => 'updated', 'id' => $id]));
        self::assertSame('updated', $this->db->fetchOne('SELECT label FROM connector_probe')['label']);
        self::assertTrue($this->db->execute('DELETE FROM connector_probe WHERE id = :id', ['id' => $id]));
        self::assertNull($this->db->fetchOne('SELECT * FROM connector_probe'));
    }

    public function testExecuteNoMatchingRowsStillSucceeds(): void
    {
        self::assertTrue($this->db->execute('UPDATE connector_probe SET label = :label WHERE id = :id', ['label' => 'unused', 'id' => 999]));
        self::assertSame([], $this->db->fetchAll('SELECT * FROM connector_probe'));
    }

    public function testParametersAreDataNotSql(): void
    {
        $label = "quote ' OR 1=1; -- Привет";
        $this->db->execute('SET NAMES utf8mb4');
        $this->db->insert('INSERT INTO connector_probe (label) VALUES (:label)', ['label' => $label]);
        $this->db->insert('INSERT INTO connector_probe (label) VALUES (:label)', ['label' => 'other']);
        self::assertSame([['label' => $label]], $this->db->fetchAll('SELECT label FROM connector_probe WHERE label = :label', ['label' => $label]));
        self::assertSame(2, (int) $this->db->fetchOne('SELECT COUNT(*) AS total FROM connector_probe')['total']);
    }

    #[DataProvider('queryMethods')]
    public function testSqlErrorsAreReported(string $method): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Query failed:');
        $this->db->$method('THIS IS NOT VALID SQL');
    }

    public static function queryMethods(): array { return [['fetchOne'], ['fetchAll'], ['execute'], ['insert']]; }

    public function testDuplicateUniqueValueIsRejected(): void
    {
        $this->db->insert("INSERT INTO connector_probe (label) VALUES ('duplicate')");
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Query failed:');
        $this->db->insert("INSERT INTO connector_probe (label) VALUES ('duplicate')");
    }

    public function testInsertWithoutGeneratedIdIsRejected(): void
    {
        $this->db->execute('CREATE TEMPORARY TABLE connector_no_identity (label VARCHAR(100))');
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Insert succeeded, but no generated ID was returned.');
        $this->db->insert("INSERT INTO connector_no_identity (label) VALUES ('value')");
    }
}
