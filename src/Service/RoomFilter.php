<?php

declare(strict_types=1);

namespace App\Service;

use App\Model\Room;
use App\Support\HttpException;

/**
 * Validated representation of the `/rooms` query string.
 *
 * The filter is built once per request so controllers and views never read
 * raw query parameters, and every invalid value is rejected with a 400.
 */
final readonly class RoomFilter
{
    public const SORT_NAME = 'name';
    public const SORT_CAPACITY = 'capacity';

    private const SORTS = [self::SORT_NAME, self::SORT_CAPACITY];
    private const MAX_TEXT_LENGTH = 80;
    private const MAX_CAPACITY = 1000;

    private function __construct(
        public string $query,
        public ?string $kind,
        public ?int $minimumCapacity,
        public string $sort,
    ) {
    }

    /**
     * @param array<string, mixed> $query
     */
    public static function fromQuery(array $query): self
    {
        $search = self::text($query, 'q');
        $kind = self::text($query, 'kind');
        $capacity = self::text($query, 'min_capacity');
        $sort = self::text($query, 'sort');

        if ($kind !== '' && !in_array($kind, Room::KINDS, true)) {
            throw HttpException::badRequest('The room kind filter is invalid.');
        }

        if ($sort !== '' && !in_array($sort, self::SORTS, true)) {
            throw HttpException::badRequest('The sort parameter is invalid.');
        }

        return new self(
            $search,
            $kind === '' ? null : $kind,
            self::minimumCapacity($capacity),
            $sort === '' ? self::SORT_NAME : $sort,
        );
    }

    /**
     * @param list<Room> $rooms
     *
     * @return list<Room>
     */
    public function apply(array $rooms): array
    {
        $matches = array_values(array_filter($rooms, fn (Room $room): bool => $this->matches($room)));

        usort($matches, $this->sort === self::SORT_CAPACITY
            ? static fn (Room $left, Room $right): int => [$left->capacity, $left->name] <=> [$right->capacity, $right->name]
            : static fn (Room $left, Room $right): int => strnatcasecmp($left->name, $right->name));

        return $matches;
    }

    public function isActive(): bool
    {
        return $this->query !== ''
            || $this->kind !== null
            || $this->minimumCapacity !== null
            || $this->sort !== self::SORT_NAME;
    }

    private function matches(Room $room): bool
    {
        if ($this->kind !== null && $room->kind !== $this->kind) {
            return false;
        }

        if ($this->minimumCapacity !== null && $room->capacity < $this->minimumCapacity) {
            return false;
        }

        if ($this->query === '') {
            return true;
        }

        $pattern = '/' . preg_quote($this->query, '/') . '/iu';

        return preg_match($pattern, $room->name) === 1
            || preg_match($pattern, $room->location) === 1;
    }

    private static function minimumCapacity(string $value): ?int
    {
        if ($value === '') {
            return null;
        }

        if (preg_match('/^[1-9][0-9]{0,3}$/', $value) !== 1 || (int) $value > self::MAX_CAPACITY) {
            throw HttpException::badRequest('The capacity filter is invalid.');
        }

        return (int) $value;
    }

    /**
     * Reads a single, trimmed query parameter and rejects values that cannot be
     * used safely, such as arrays, broken UTF-8 or overly long strings.
     *
     * @param array<string, mixed> $query
     */
    private static function text(array $query, string $name): string
    {
        $value = $query[$name] ?? null;

        if ($value === null) {
            return '';
        }

        if (!is_string($value) || preg_match('//u', $value) !== 1) {
            throw HttpException::badRequest(sprintf('The "%s" parameter is invalid.', $name));
        }

        $value = trim($value);

        if (preg_match_all('/./u', $value) > self::MAX_TEXT_LENGTH) {
            throw HttpException::badRequest(sprintf('The "%s" parameter is too long.', $name));
        }

        return $value;
    }
}
