<?php
declare(strict_types=1);

namespace CNS\BotToServer\Tests\Integrations\Database;

use CNS\BotToServer\Database\PDOConnector;
use PHPUnit\Framework\TestCase;

abstract class MySqlTestCase extends TestCase
{
    protected PDOConnector $db;

    protected function setUp(): void
    {
        parent::setUp();
        if (!defined('BTS_MYSQL_INTEGRATION_BOOTSTRAP') || self::setting('BTS_RUN_DB_TESTS') !== '1') {
            self::markTestSkipped('MySQL tests disabled. Configure .env.integration.local and use phpunit.integration.xml.');
        }
        if (!extension_loaded('pdo_mysql')) {
            self::fail('Enabled MySQL tests require the pdo_mysql extension.');
        }
        $host = self::setting('BTS_TEST_DB_HOST');
        $name = self::setting('BTS_TEST_DB_NAME');
        $user = self::setting('BTS_TEST_DB_USER');
        $password = self::setting('BTS_TEST_DB_PASSWORD');
        $port = filter_var(self::setting('BTS_TEST_DB_PORT') ?? '3306', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
        if ($host === null || !preg_match('/\A[a-zA-Z0-9.:-]+\z/', $host)
            || $name === null || !preg_match('/\A[a-zA-Z0-9_]+_test\z/', $name)
            || $user === null || $user === '' || $password === null || $port === false) {
            self::fail('Set valid BTS_TEST_DB_* values; database name must end with _test. Application DB_* settings are never used.');
        }
        $this->db = new PDOConnector($host, $user, $password, $name, $port);
        // Every table is connection-local. No DROP/TRUNCATE of persistent tables.
        $schema = file_get_contents(__DIR__ . '/schema.sql');
        foreach (explode(';', $schema) as $statement) {
            if (trim($statement) !== '') { $this->db->execute($statement); }
        }
    }

    protected function tearDown(): void
    {
        // Closing the connection automatically removes its temporary tables.
        unset($this->db);
        parent::tearDown();
    }

    private static function setting(string $name): ?string
    {
        $value = $_ENV[$name] ?? getenv($name);
        return $value === false ? null : (string) $value;
    }
}
