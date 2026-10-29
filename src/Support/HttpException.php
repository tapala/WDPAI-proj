<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

final class HttpException extends RuntimeException
{
    private function __construct(
        private readonly int $statusCode,
        string $message,
    ) {
        parent::__construct($message);
    }

    public static function badRequest(string $message = 'Bad request.'): self
    {
        return new self(400, $message);
    }

    public static function notFound(string $message = 'Not found.'): self
    {
        return new self(404, $message);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }
}
