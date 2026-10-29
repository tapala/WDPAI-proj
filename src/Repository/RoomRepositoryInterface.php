<?php

declare(strict_types=1);

namespace App\Repository;

use App\Model\Room;

interface RoomRepositoryInterface
{
    /**
     * @return list<Room>
     */
    public function findAll(): array;

    public function findById(int $id): ?Room;
}
