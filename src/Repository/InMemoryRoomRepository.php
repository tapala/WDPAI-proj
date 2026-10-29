<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Room;

/**
 * Temporary data source for the room catalogue.
 *
 * The records are hard-coded fixtures: the PostgreSQL schema and the PDO
 * repository arrive in Lab 7. Until then this class keeps the routing and view
 * layer honest — the pages show demo data, not invented availability.
 *
 * Descriptions are translation keys so the same fixture works in PL and EN.
 */
final class InMemoryRoomRepository implements RoomRepositoryInterface
{
    /**
     * @var list<Room>
     */
    private array $rooms;

    public function __construct()
    {
        $this->rooms = self::fixtures();
    }

    public function findAll(): array
    {
        return $this->rooms;
    }

    public function findById(int $id): ?Room
    {
        foreach ($this->rooms as $room) {
            if ($room->id === $id) {
                return $room;
            }
        }

        return null;
    }

    /**
     * @return list<Room>
     */
    private static function fixtures(): array
    {
        return [
            new Room(
                1,
                'Sala Konferencyjna A',
                'Budynek Główny, I piętro',
                Room::KIND_ROOM,
                12,
                'rooms.demo.1.description',
                ['equipment.projector', 'equipment.whiteboard', 'equipment.video'],
            ),
            new Room(
                2,
                'Sala Konferencyjna B',
                'Budynek Główny, I piętro',
                Room::KIND_ROOM,
                8,
                'rooms.demo.2.description',
                ['equipment.projector', 'equipment.whiteboard'],
            ),
            new Room(
                3,
                'Sala Wykładowa 201',
                'Budynek Główny, II piętro',
                Room::KIND_ROOM,
                40,
                'rooms.demo.3.description',
                ['equipment.projector', 'equipment.video'],
            ),
            new Room(
                4,
                'Sala Projektowa Studio',
                'Budynek Główny, parter',
                Room::KIND_ROOM,
                6,
                'rooms.demo.4.description',
                ['equipment.whiteboard', 'equipment.tv'],
            ),
            new Room(
                5,
                'Sala Cicha 3.12',
                'Budynek B, III piętro',
                Room::KIND_ROOM,
                4,
                'rooms.demo.5.description',
                ['equipment.whiteboard'],
            ),
            new Room(
                6,
                'Stanowisko pracy 01',
                'Budynek B, open space',
                Room::KIND_WORKSTATION,
                1,
                'rooms.demo.6.description',
                ['equipment.desk', 'equipment.monitor', 'equipment.dock'],
            ),
            new Room(
                7,
                'Stanowisko pracy 02',
                'Budynek B, open space',
                Room::KIND_WORKSTATION,
                1,
                'rooms.demo.7.description',
                ['equipment.desk', 'equipment.monitor', 'equipment.dock', 'equipment.phone'],
            ),
            new Room(
                8,
                'Stanowisko dostępne 03',
                'Budynek B, open space',
                Room::KIND_WORKSTATION,
                1,
                'rooms.demo.8.description',
                ['equipment.desk', 'equipment.monitor'],
            ),
        ];
    }
}
