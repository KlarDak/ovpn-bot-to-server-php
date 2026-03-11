<?php

namespace CNS\OvpnBotToServer\Tests;

use CNS\OvpnBotToServer\Services\apiUserClient;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;

class apiClientTest extends TestCase {
    public static apiUserClient $client;
    public static string $uuid = "430a8e06-d9b6-11f0-a8db-38f3ab6d0b03"; 

    public static function setUpBeforeClass(): void
    {
        Dotenv::createImmutable(dirname(__DIR__))->load();
        self::$client = new apiUserClient("RU");

    }

    public function testPostConfig() {
        $result = self::$client->postConfig(self::$uuid, 3600, "user");

        $this->assertEquals(200, $result->code);
    }
   
    public function testGetConfig() {
        $result = self::$client->getConfig(self::$uuid);

        $this->assertEquals(200, $result->code);
    }

    public function testPutConfig() {
        $result = self::$client->putConfig(self::$uuid, 3600, "user");

        $this->assertEquals(200, $result->code);
    }

    public function testPatchConfig() {
        $result = self::$client->patchConfig(self::$uuid, time: 3600);

        $this->assertEquals(200, $result->code);
    }

    public function testDeleteConfig() {
        $result = self::$client->deleteConfig(self::$uuid);

        $this->assertEquals(200, $result->code);
    }
}