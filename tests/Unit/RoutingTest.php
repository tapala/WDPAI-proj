<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Routing;
use App\Support\HttpException;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RoutingTest extends TestCase
{
    public function testDispatchesStaticRoute(): void
    {
        $calls = [];
        $router = new Routing();
        $router->get('/', static function () use (&$calls): void {
            $calls[] = 'home';
        });

        $router->dispatch('GET', '/');

        self::assertSame(['home'], $calls);
    }

    public function testPassesPathParameterToHandler(): void
    {
        $received = null;
        $router = new Routing();
        $router->get('/rooms/{id:\d+}', static function (string $id) use (&$received): void {
            $received = $id;
        });

        $router->dispatch('GET', '/rooms/42');

        self::assertSame('42', $received);
    }

    public function testIgnoresQueryStringWhenMatching(): void
    {
        $received = null;
        $router = new Routing();
        $router->get('/rooms', static function () use (&$received): void {
            $received = 'rooms';
        });

        $router->dispatch('GET', '/rooms?q=sala&sort=capacity');

        self::assertSame('rooms', $received);
    }

    public function testSupportsCustomRegexAndMultipleParameters(): void
    {
        $received = [];
        $router = new Routing();
        $router->get('/posts/{slug:[a-z0-9-]+}/{year:\d{4}}', static function (string $slug, string $year) use (&$received): void {
            $received = [$slug, $year];
        });

        $router->dispatch('GET', '/posts/roomly-news/2026');

        self::assertSame(['roomly-news', '2026'], $received);
    }

    public function testNormalizesTrailingSlash(): void
    {
        $received = null;
        $router = new Routing();
        $router->get('/rooms/{id:\d+}', static function (string $id) use (&$received): void {
            $received = $id;
        });

        $router->dispatch('GET', '/rooms/7/');

        self::assertSame('7', $received);
    }

    public function testMatchesRoutesByHttpMethod(): void
    {
        $router = new Routing();
        $router->patch('/rooms/{id:\d+}', static function (): void {
            self::fail('The PATCH handler must not run for a GET request.');
        });

        try {
            $router->dispatch('GET', '/rooms/7');
            self::fail('A method mismatch must raise an HTTP exception.');
        } catch (HttpException $exception) {
            self::assertSame(404, $exception->statusCode());
        }
    }

    #[DataProvider('invalidUris')]
    public function testRejectsInvalidParameterFormatWithBadRequest(string $uri): void
    {
        $router = new Routing();
        $router->get('/rooms/{id:\d+}', static function (): void {
            self::fail('The handler must not run for an invalid parameter.');
        });
        $router->get('/rooms/{id:\d+}/edit', static function (): void {
            self::fail('The handler must not run for an invalid parameter.');
        });

        try {
            $router->dispatch('GET', $uri);
            self::fail('An invalid parameter must raise an HTTP exception.');
        } catch (HttpException $exception) {
            self::assertSame(400, $exception->statusCode());
        }
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidUris(): array
    {
        return [
            'non-numeric id' => ['/rooms/abc'],
            'id with a suffix' => ['/rooms/7abc'],
        ];
    }

    public function testUnknownRouteReturnsNotFound(): void
    {
        $router = new Routing();
        $router->get('/', static function (): void {
        });

        try {
            $router->dispatch('GET', '/missing');
            self::fail('An unknown route must raise an HTTP exception.');
        } catch (HttpException $exception) {
            self::assertSame(404, $exception->statusCode());
        }
    }

    public function testDuplicateParameterIsRejectedAtRegistration(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Routing())->get('/rooms/{id}/{id}', static function (): void {
        });
    }

    public function testPatternWithoutLeadingSlashIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Routing())->get('rooms', static function (): void {
        });
    }

    public function testUnclosedParameterIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Routing())->get('/rooms/{id', static function (): void {
        });
    }
}
