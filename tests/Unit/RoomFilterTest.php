<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Model\Room;
use App\Service\RoomFilter;
use App\Support\HttpException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class RoomFilterTest extends TestCase
{
    /**
     * @var list<Room>
     */
    private array $rooms;

    protected function setUp(): void
    {
        $this->rooms = [
            new Room(1, 'Sala Konferencyjna A', 'Budynek Główny', Room::KIND_ROOM, 12, 'rooms.demo.1.description'),
            new Room(2, 'Sala Cicha', 'Budynek B', Room::KIND_ROOM, 4, 'rooms.demo.2.description'),
            new Room(3, 'Stanowisko 01', 'Budynek B', Room::KIND_WORKSTATION, 1, 'rooms.demo.3.description'),
        ];
    }

    public function testEmptyQueryReturnsEveryRoomSortedByName(): void
    {
        $filter = RoomFilter::fromQuery([]);

        self::assertSame([2, 1, 3], self::ids($filter->apply($this->rooms)));
        self::assertFalse($filter->isActive());
    }

    public function testSearchMatchesNameCaseInsensitively(): void
    {
        $filter = RoomFilter::fromQuery(['q' => 'KONFERENCYJNA']);

        self::assertSame([1], self::ids($filter->apply($this->rooms)));
        self::assertTrue($filter->isActive());
    }

    public function testSearchMatchesLocation(): void
    {
        $filter = RoomFilter::fromQuery(['q' => 'budynek b']);

        self::assertSame([2, 3], self::ids($filter->apply($this->rooms)));
    }

    public function testSearchTrimsWhitespace(): void
    {
        $filter = RoomFilter::fromQuery(['q' => '  cicha  ']);

        self::assertSame([2], self::ids($filter->apply($this->rooms)));
    }

    public function testFiltersByKind(): void
    {
        $filter = RoomFilter::fromQuery(['kind' => Room::KIND_WORKSTATION]);

        self::assertSame([3], self::ids($filter->apply($this->rooms)));
    }

    public function testFiltersByMinimumCapacity(): void
    {
        $filter = RoomFilter::fromQuery(['min_capacity' => '4']);

        self::assertSame([2, 1], self::ids($filter->apply($this->rooms)));
        self::assertSame(4, $filter->minimumCapacity);
    }

    public function testSortsByCapacityAscending(): void
    {
        $filter = RoomFilter::fromQuery(['sort' => RoomFilter::SORT_CAPACITY]);

        self::assertSame([3, 2, 1], self::ids($filter->apply($this->rooms)));
    }

    public function testCombinesSearchAndKindFilters(): void
    {
        $filter = RoomFilter::fromQuery([
            'q' => 'budynek b',
            'kind' => Room::KIND_ROOM,
        ]);

        self::assertSame([2], self::ids($filter->apply($this->rooms)));
    }

    public function testReturnsEmptyListWhenNothingMatches(): void
    {
        $filter = RoomFilter::fromQuery(['q' => 'brak takiej sali']);

        self::assertSame([], $filter->apply($this->rooms));
    }

    /**
     * @param array<string, mixed> $query
     */
    #[DataProvider('invalidQueries')]
    public function testRejectsInvalidQueryWithBadRequest(array $query): void
    {
        try {
            RoomFilter::fromQuery($query);
            self::fail('An invalid query must be rejected.');
        } catch (HttpException $exception) {
            self::assertSame(400, $exception->statusCode());
        }
    }

    /**
     * @return array<string, array{array<string, mixed>}>
     */
    public static function invalidQueries(): array
    {
        return [
            'unknown kind' => [['kind' => 'garage']],
            'unknown sort' => [['sort' => 'price']],
            'zero capacity' => [['min_capacity' => '0']],
            'negative capacity' => [['min_capacity' => '-3']],
            'capacity with letters' => [['min_capacity' => '4a']],
            'capacity above the limit' => [['min_capacity' => '5000']],
            'array parameter' => [['q' => ['sala']]],
            'too long query' => [['q' => str_repeat('a', 81)]],
            'broken utf-8' => [['q' => "\xC3\x28"]],
        ];
    }

    /**
     * @param list<Room> $rooms
     *
     * @return list<int>
     */
    private static function ids(array $rooms): array
    {
        return array_map(static fn (Room $room): int => $room->id, $rooms);
    }
}
