<?php

namespace CNS\OvpnBotToServer\Tests;

use CNS\OvpnBotToServer\Services\ApiCronClient;
use CNS\OvpnBotToServer\Services\ApiUserClient;
use CNS\OvpnBotToServer\Utils\JwtGenerator;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response as HttpResponse;
use PHPUnit\Framework\TestCase;

class ApiCompatibilityTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV["TEST_SERVER_ADDRESS"] = "http://127.0.0.1:3000";
        $_ENV["TEST_SERVER_INDEX"] = "test_api_server";
        $_ENV["TEST_SECRET_KEY"] = "test-secret-key-with-at-least-32-bytes";
        $_ENV["SUB_INDEX"] = "test_bot_server";
    }

    public function testJwtPayloadMatchesApiVersion21(): void
    {
        $token = JwtGenerator::createToken("TEST", time() + 12, "site");
        $payload = (array) JWT::decode(
            $token,
            new Key($_ENV["TEST_SECRET_KEY"], "HS256")
        );

        $this->assertSame("test_bot_server", $payload["sub"]);
        $this->assertSame("test_api_server", $payload["aud"]);
        $this->assertSame("site", $payload["role"]);
        $this->assertArrayNotHasKey("type", $payload);
    }

    public function testPutUsesApiConfigUuidEndpoint(): void
    {
        $history = [];
        $client = new ApiUserClient("TEST");
        $client->client = $this->mockClient($history);

        $result = $client->putConfig(
            "41649438-8844-11f1-9c27-4f9a5cf82333",
            3600,
            "user"
        );

        $this->assertSame(200, $result->code);
        $this->assertSame(
            "/api/config/41649438-8844-11f1-9c27-4f9a5cf82333",
            $history[0]["request"]->getUri()->getPath()
        );
        $this->assertSame(
            ["time" => 3600, "type" => "user"],
            json_decode((string) $history[0]["request"]->getBody(), true)
        );
    }

    public function testActiveActionsUseAdminRoleAndJsonBody(): void
    {
        $history = [];
        $client = new ApiCronClient("TEST");
        $client->client = $this->mockClient($history);
        $uuid = "41649438-8844-11f1-9c27-4f9a5cf82333";

        $this->assertTrue($client->banUser($uuid));
        $this->assertSame("/api/active/ban", $history[0]["request"]->getUri()->getPath());
        $this->assertSame(
            ["uuid" => $uuid],
            json_decode((string) $history[0]["request"]->getBody(), true)
        );

        $authorization = $history[0]["request"]->getHeaderLine("Authorization");
        $token = substr($authorization, strlen("Bearer "));
        $payload = JWT::decode($token, new Key($_ENV["TEST_SECRET_KEY"], "HS256"));

        $this->assertSame("admin", $payload->role);
    }

    /**
     * @param array<int, array<string, mixed>> $history
     */
    private function mockClient(array &$history): Client
    {
        $mock = new MockHandler([
            new HttpResponse(
                200,
                ["Content-Type" => "application/json"],
                '{"code":200,"message":"OK","data":null}'
            )
        ]);
        $stack = HandlerStack::create($mock);
        $stack->push(Middleware::history($history));

        return new Client([
            "base_uri" => "http://127.0.0.1:3000",
            "handler" => $stack,
            "http_errors" => false
        ]);
    }
}
