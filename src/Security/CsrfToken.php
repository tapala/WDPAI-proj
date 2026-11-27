<?php

declare(strict_types=1);

namespace App\Security;

/**
 * Session-backed CSRF token.
 *
 * The token is created once per session and compared with hash_equals so the
 * check is not vulnerable to timing attacks. The session is started by the
 * front controller; this class only reads and writes $_SESSION.
 */
final class CsrfToken
{
    public const DEFAULT_SESSION_KEY = '_csrf_token';

    private const TOKEN_BYTES = 32;

    public function __construct(
        private readonly string $sessionKey = self::DEFAULT_SESSION_KEY,
    ) {
    }

    public function token(): string
    {
        $token = $_SESSION[$this->sessionKey] ?? null;

        if (!is_string($token) || $token === '') {
            $token = bin2hex(random_bytes(self::TOKEN_BYTES));
            $_SESSION[$this->sessionKey] = $token;
        }

        return $token;
    }

    public function isValid(mixed $token): bool
    {
        $expected = $_SESSION[$this->sessionKey] ?? null;

        return is_string($token)
            && is_string($expected)
            && $expected !== ''
            && hash_equals($expected, $token);
    }
}
