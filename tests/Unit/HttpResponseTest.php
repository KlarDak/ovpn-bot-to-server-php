<?php
declare(strict_types=1);

namespace CNS\BotToServer\Tests\Unit;

use CNS\BotToServer\Http\Types\HttpResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HttpResponseTest extends TestCase
{
    #[DataProvider('statuses')]
    public function testSuccessStatusBoundaries(int $status, bool $success): void
    {
        $response = new HttpResponse($status, [], '');
        self::assertSame($status, $response->getStatusCode());
        self::assertSame($success, $response->isSuccessful());
    }

    public static function statuses(): array { return [[199, false], [200, true], [204, true], [299, true], [300, false], [404, false], [500, false]]; }

    public function testBodyAndHeadersArePreserved(): void
    {
        $response = new HttpResponse(200, ['Content-Type' => 'application/json'], '{"ok":true,"items":[1,2]}');
        self::assertSame('{"ok":true,"items":[1,2]}', $response->getBody());
        self::assertSame(['ok' => true, 'items' => [1, 2]], $response->getJsonBody());
        self::assertSame(['Content-Type' => 'application/json'], $response->getHeaders());
        self::assertTrue($response->hasHeader('Content-Type'));
        self::assertSame('application/json', $response->getHeader('Content-Type'));
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        self::assertFalse($response->hasHeader('Missing'));
        self::assertSame('', $response->getHeader('Missing'));
    }
}
