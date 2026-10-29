<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

final class Translator
{
    public const SUPPORTED_LOCALES = ['pl', 'en'];

    /**
     * @param array<string, string> $messages
     */
    private function __construct(
        private readonly string $locale,
        private readonly array $messages,
    ) {
    }

    public static function fromDirectory(string $directory, string $locale): self
    {
        $locale = self::supports($locale) ? $locale : 'pl';
        $path = $directory . '/' . $locale . '.php';

        if (!is_file($path)) {
            throw new RuntimeException(sprintf('Translation file "%s" was not found.', $path));
        }

        $messages = require $path;

        if (!is_array($messages)) {
            throw new RuntimeException(sprintf('Translation file "%s" must return an array.', $path));
        }

        /** @var array<string, string> $messages */
        return new self($locale, $messages);
    }

    public static function supports(string $locale): bool
    {
        return in_array($locale, self::SUPPORTED_LOCALES, true);
    }

    public function locale(): string
    {
        return $this->locale;
    }

    /**
     * @param array<string, string|int|float> $parameters
     */
    public function translate(string $key, array $parameters = []): string
    {
        $message = $this->messages[$key] ?? $key;

        foreach ($parameters as $name => $value) {
            $message = str_replace(':' . $name, (string) $value, $message);
        }

        return $message;
    }
}
