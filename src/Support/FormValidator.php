<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Small, reusable form validator.
 *
 * Rules store translation keys instead of messages so controllers never build
 * user-facing text. Rules skip empty values unless they are `required`, which
 * avoids reporting "invalid e-mail" for a field the user left blank.
 */
final class FormValidator
{
    /**
     * @var array<string, list<string>>
     */
    private array $errors = [];

    /**
     * @param array<string, mixed> $data
     */
    public function __construct(
        private readonly array $data,
    ) {
    }

    /**
     * @param string $message Translation key.
     */
    public function required(string $field, string $message): self
    {
        if ($this->value($field) === '') {
            $this->add($field, $message);
        }

        return $this;
    }

    /**
     * @param string $message Translation key.
     */
    public function email(string $field, string $message): self
    {
        $value = $this->value($field);

        if ($value !== '' && filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            $this->add($field, $message);
        }

        return $this;
    }

    /**
     * @param string $message Translation key.
     */
    public function minLength(string $field, int $minimum, string $message): self
    {
        $value = $this->value($field);

        if ($value !== '' && mb_strlen($value) < $minimum) {
            $this->add($field, $message);
        }

        return $this;
    }

    /**
     * @param string $message Translation key.
     */
    public function maxLength(string $field, int $maximum, string $message): self
    {
        $value = $this->value($field);

        if ($value !== '' && mb_strlen($value) > $maximum) {
            $this->add($field, $message);
        }

        return $this;
    }

    /**
     * Accepts only a real calendar date in Y-m-d form.
     *
     * @param string $message Translation key.
     */
    public function date(string $field, string $message): self
    {
        $value = $this->value($field);

        if ($value === '') {
            return $this;
        }

        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);

        if ($date === false || $date->format('Y-m-d') !== $value) {
            $this->add($field, $message);
        }

        return $this;
    }

    /**
     * Accepts 24-hour time in HH:MM form.
     *
     * @param string $message Translation key.
     */
    public function time(string $field, string $message): self
    {
        $value = $this->value($field);

        if ($value !== '' && preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $value) !== 1) {
            $this->add($field, $message);
        }

        return $this;
    }

    /**
     * Requires the end time to be later than the start time.
     *
     * Invalid or empty times are ignored here; the `time`/`required` rules
     * report them separately.
     *
     * @param string $message Translation key.
     */
    public function afterTime(string $startField, string $endField, string $message): self
    {
        $start = $this->value($startField);
        $end = $this->value($endField);

        if (!self::isTime($start) || !self::isTime($end)) {
            return $this;
        }

        if (strcmp($end, $start) <= 0) {
            $this->add($endField, $message);
        }

        return $this;
    }

    public function value(string $field): string
    {
        $value = $this->data[$field] ?? null;

        return is_string($value) ? trim($value) : '';
    }

    public function hasErrors(): bool
    {
        return $this->errors !== [];
    }

    /**
     * @return array<string, list<string>>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(string $field): ?string
    {
        return $this->errors[$field][0] ?? null;
    }

    private function add(string $field, string $message): void
    {
        if (!in_array($message, $this->errors[$field] ?? [], true)) {
            $this->errors[$field][] = $message;
        }
    }

    private static function isTime(string $value): bool
    {
        return preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $value) === 1;
    }
}
