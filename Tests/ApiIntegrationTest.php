<?php

namespace CNS\OvpnBotToServer\Tests;

use CNS\OvpnBotToServer\Services\ApiCronClient;
use CNS\OvpnBotToServer\Services\ApiUserClient;
use CNS\OvpnBotToServer\Types\Response;
use CNS\OvpnBotToServer\Utils\Utils;
use Dotenv\Dotenv;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

final class ApiIntegrationTest extends TestCase
{
    private const SERVER_ID = "NL";

    public static function setUpBeforeClass(): void
    {
        Dotenv::createImmutable(dirname(__DIR__))->safeLoad();

        foreach ([
            "NL_SERVER_ADDRESS",
            "NL_SERVER_INDEX",
            "NL_SECRET_KEY",
            "SUB_INDEX",
            "CONFIGS_DIR"
        ] as $key) {
            if (!isset($_ENV[$key]) || trim((string) $_ENV[$key]) === "") {
                self::markTestSkipped("Integration environment variable $key is not configured.");
            }
        }
    }

    public function testCreateGetAndDeleteConfig(): void
    {
        $this->withTemporaryConfig(function (
            string $uuid,
            ApiUserClient $userClient,
            Response $create
        ): void {
            $this->assertSame($uuid, $create->data["uuid"] ?? null);

            $get = $userClient->getConfig($uuid);
            $this->assertSuccessfulResponse($get, "get config");
            $this->assertSame($uuid, $get->data["uuid"] ?? null);
        });
    }

    public function testPatchConfig(): void
    {
        $this->withTemporaryConfig(function (
            string $uuid,
            ApiUserClient $userClient
        ): void {
            $patch = $userClient->patchConfig($uuid, 900, "unblocked");
            $this->assertSuccessfulResponse($patch, "patch config");
        });
    }

    public function testReissueAndDownloadConfig(): void
    {
        $this->withTemporaryConfig(function (
            string $uuid,
            ApiUserClient $userClient
        ): void {
            $put = $userClient->putConfig($uuid, 1200, "user");
            $this->assertSuccessfulResponse($put, "reissue config");
            $downloadLink = (string) ($put->data["link"] ?? "");
            $this->assertMatchesRegularExpression("/^[A-Za-z0-9]{6}$/", $downloadLink);

            $this->assertTrue(
                $userClient->downloadConfig($uuid, $downloadLink),
                "The reissued OpenVPN config could not be downloaded."
            );
            $this->assertFileExists(Utils::getOvpnPath($uuid));
            $this->assertStringContainsString(
                "<ca>",
                (string) file_get_contents(Utils::getOvpnPath($uuid))
            );
        });
    }

    public function testActiveUsersAndModerationActions(): void
    {
        $cronClient = new ApiCronClient(self::SERVER_ID);
        $activeUsers = $cronClient->getActiveUsers();
        $this->assertSuccessfulResponse($activeUsers, "get active users");
        $this->assertIsArray($activeUsers->data);

        $this->withTemporaryConfig(function (
            string $uuid
        ) use ($cronClient): void {
            $this->assertTrue($cronClient->banUser($uuid), "Ban request failed.");
            $this->assertTrue($cronClient->pardonUser($uuid), "Pardon request failed.");
        });
    }

    /**
     * @param callable(string, ApiUserClient, Response): void $test
     */
    private function withTemporaryConfig(callable $test): void
    {
        $uuid = Uuid::uuid4()->toString();
        $userClient = new ApiUserClient(self::SERVER_ID);
        $created = false;

        try {
            $create = $userClient->postConfig($uuid, 600, "user");
            $this->assertSuccessfulResponse($create, "create config");
            $created = true;
            $this->assertMatchesRegularExpression(
                "/^[A-Za-z0-9]{6}$/",
                (string) ($create->data["link"] ?? "")
            );
            $test($uuid, $userClient, $create);
        } finally {
            if (Utils::isOvpnFileExists($uuid)) {
                Utils::removeOvpnFile($uuid);
            }

            if ($created) {
                $delete = $userClient->deleteConfig($uuid);
                $this->assertSuccessfulResponse($delete, "delete config during cleanup");
            }
        }
    }

    private function assertSuccessfulResponse(Response|false $response, string $operation): void
    {
        $this->assertInstanceOf(
            Response::class,
            $response,
            ucfirst($operation) . " returned no JSON response."
        );
        $this->assertSame(
            200,
            $response->code,
            ucfirst($operation) . " failed: " . $response->message
        );
    }
}
