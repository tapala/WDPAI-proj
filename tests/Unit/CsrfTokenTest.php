<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Security\CsrfToken;
use PHPUnit\Framework\TestCase;

final class CsrfTokenTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    public function testTokenIsStableAndWellFormed(): void
    {
        $csrf = new CsrfToken();
        $token = $csrf->token();

        self::assertSame(64, strlen($token));
        self::assertTrue(ctype_xdigit($token));
        self::assertSame($token, $csrf->token());
    }

    public function testGeneratedTokenIsValid(): void
    {
        $csrf = new CsrfToken();

        self::assertTrue($csrf->isValid($csrf->token()));
    }

    public function testDifferentTokenIsRejected(): void
    {
        $csrf = new CsrfToken();
        $csrf->token();

        self::assertFalse($csrf->isValid(str_repeat('a', 64)));
        self::assertFalse($csrf->isValid(''));
        self::assertFalse($csrf->isValid(null));
        self::assertFalse($csrf->isValid([]));
    }

    public function testTokenFromAnotherSessionIsRejected(): void
    {
        $csrf = new CsrfToken();
        $token = $csrf->token();

        $_SESSION = [];

        self::assertFalse($csrf->isValid($token));
    }

    public function testCustomSessionKeyIsSupported(): void
    {
        $csrf = new CsrfToken('_custom_token');
        $token = $csrf->token();

        self::assertArrayHasKey('_custom_token', $_SESSION);
        self::assertArrayNotHasKey(CsrfToken::DEFAULT_SESSION_KEY, $_SESSION);
        self::assertTrue($csrf->isValid($token));
    }
}
