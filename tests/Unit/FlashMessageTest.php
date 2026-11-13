<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Support\FlashMessage;
use PHPUnit\Framework\TestCase;

final class FlashMessageTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testMessageIsPulledOnce(): void
    {
        $flash = new FlashMessage();
        $flash->set(['type' => 'success', 'message' => 'login.demo']);

        self::assertSame(
            ['type' => 'success', 'message' => 'login.demo'],
            $flash->pull(),
        );
        self::assertNull($flash->pull());
    }

    public function testMissingMessageReturnsNull(): void
    {
        self::assertNull((new FlashMessage())->pull());
    }

    public function testInvalidPayloadIsDiscarded(): void
    {
        $_SESSION[FlashMessage::DEFAULT_SESSION_KEY] = 'not-an-array';

        self::assertNull((new FlashMessage())->pull());
        self::assertArrayNotHasKey(FlashMessage::DEFAULT_SESSION_KEY, $_SESSION);
    }

    public function testOnlyScalarStringEntriesAreReturned(): void
    {
        $flash = new FlashMessage();
        $flash->set(['type' => ['nested'], 'message' => 'login.demo', 'count' => 3]);

        self::assertSame(['message' => 'login.demo'], $flash->pull());
    }

    public function testCustomSessionKeyIsSupported(): void
    {
        $flash = new FlashMessage('_custom_flash');
        $flash->set(['message' => 'demo']);

        self::assertArrayHasKey('_custom_flash', $_SESSION);
        self::assertArrayNotHasKey(FlashMessage::DEFAULT_SESSION_KEY, $_SESSION);
        self::assertSame(['message' => 'demo'], $flash->pull());
    }
}
