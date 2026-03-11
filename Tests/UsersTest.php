<?php

namespace CNS\OvpnBotToServer\Tests;

use PHPUnit\Framework\TestCase;
use CNS\OvpnBotToServer\Adapters\usersAdapter;
use CNS\OvpnBotToServer\Databases\PDOConnector;
use CNS\OvpnBotToServer\Types\Env;
use Dotenv\Dotenv;

class UsersTest extends TestCase {
    private static $userAdapter;
    private static $db;

    public static function setUpBeforeClass(): void
    {
        Dotenv::createImmutable(dirname(__DIR__))->load();
        self::$db = new PDOConnector(Env::getDatabaseKeys()->hostname, Env::getDatabaseKeys()->port, Env::getDatabaseKeys()->username, Env::getDatabaseKeys()->password, Env::getDatabaseKeys()->dbname);
        self::$userAdapter = new usersAdapter(self::$db, 1);
    }

    public function testAddUser() {
        $test = self::$userAdapter->createUser("test");
        self::assertTrue($test);
    }

    public function testUpdateUsername() {
        $test = self::$userAdapter->updateUsername("newtest");
        self::assertTrue($test);
    }

    public function testUpdatePaymentInfo() {
        $test = self::$userAdapter->updatePaymentInfo(time() + 36000, time());
        self::assertTrue($test);
    }

    public function testUpdateConfigsCount() {
        $test_plus = self::$userAdapter->updateConfigsCount(2);
        self::assertTrue($test_plus);

        $test_minus = self::$userAdapter->updateConfigsCount(-1);
        self::assertTrue($test_minus);
    }

    public function testUpdateActiveStatus() {
        $test = self::$userAdapter->updateActiveStatus(false, time());

        self::assertTrue($test);
    }

    public function testGetUser() {
        $test = self::$userAdapter->getUserByID();
        self::assertEquals(1, $test->user_id);
        self::assertEquals("newtest", $test->username);
        self::assertFalse($test->is_active);
        self::assertNotNull($test->disabled_at);
        self::assertNotNull($test->expired_at);
        self::assertNotNull($test->last_payment_at);
    }

    public static function tearDownAfterClass(): void
    {
        self::$db->execute("DELETE FROM users WHERE user_id = 1", []);
    }
}