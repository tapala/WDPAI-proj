<?php

declare(strict_types=1);

namespace App\Support;

/**
 * One-shot session message used after a Post/Redirect/Get cycle.
 *
 * The payload is deliberately small: a type (for styling) and a translation
 * key (for the actual text). Pulling removes the message so a refresh does not
 * show it twice.
 */
final class FlashMessage
{
    public const DEFAULT_SESSION_KEY = '_flash_message';

    public function __construct(
        private readonly string $sessionKey = self::DEFAULT_SESSION_KEY,
    ) {
    }

    /**
     * @param array<string, string> $message
     */
    public function set(array $message): void
    {
        $_SESSION[$this->sessionKey] = $message;
    }

    /**
     * @return array<string, string>|null
     */
    public function pull(): ?array
    {
        $message = $_SESSION[$this->sessionKey] ?? null;
        unset($_SESSION[$this->sessionKey]);

        if (!is_array($message)) {
            return null;
        }

        $result = [];

        foreach ($message as $key => $value) {
            if (is_string($key) && is_string($value)) {
                $result[$key] = $value;
            }
        }

        return $result === [] ? null : $result;
    }
}
