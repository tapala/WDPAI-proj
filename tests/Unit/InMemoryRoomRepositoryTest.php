<?php

declare(strict_types=1);

namespace App\Tests\Unit;

use App\Model\Room;
use App\Repository\InMemoryRoomRepository;
use PHPUnit\Framework\TestCase;

final class InMemoryRoomRepositoryTest extends TestCase
{
    public function testReturnsTheWholeCatalogue(): void
    {
        $rooms = (new InMemoryRoomRepository())->findAll();

        self::assertNotEmpty($rooms);
        self::assertContainsOnlyInstancesOf(Room::class, $rooms);
    }

    public function testFindsRoomById(): void
    {
        $room = (new InMemoryRoomRepository())->findById(1);

        self::assertInstanceOf(Room::class, $room);
        self::assertSame(1, $room->id);
        self::assertSame(Room::KIND_ROOM, $room->kind);
    }

    public function testFindsWorkstationById(): void
    {
        $room = (new InMemoryRoomRepository())->findById(6);

        self::assertInstanceOf(Room::class, $room);
        self::assertTrue($room->isWorkstation());
    }

    public function testReturnsNullForUnknownRoom(): void
    {
        self::assertNull((new InMemoryRoomRepository())->findById(9999));
    }

    public function testEveryFixtureHasUniqueIdAndPositiveCapacity(): void
    {
        $rooms = (new InMemoryRoomRepository())->findAll();
        $ids = array_map(static fn (Room $room): int => $room->id, $rooms);

        self::assertSame($ids, array_unique($ids));

        foreach ($rooms as $room) {
            self::assertGreaterThan(0, $room->capacity);
        }
    }
}
