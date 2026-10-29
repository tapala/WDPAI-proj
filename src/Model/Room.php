<?php

declare(strict_types=1);

namespace App\Model;

use InvalidArgumentException;

final readonly class Room
{
    public const KIND_ROOM = 'room';
    public const KIND_WORKSTATION = 'workstation';

    public const KINDS = [self::KIND_ROOM, self::KIND_WORKSTATION];

    /**
     * @param list<string> $equipment translation keys of the equipment items
     */
    public function __construct(
        public int $id,
        public string $name,
        public string $location,
        public string $kind,
        public int $capacity,
        public string $description,
        public array $equipment = [],
    ) {
        if (!in_array($kind, self::KINDS, true)) {
            throw new InvalidArgumentException(sprintf('Unknown room kind "%s".', $kind));
        }

        if ($capacity < 1) {
            throw new InvalidArgumentException('A room must have a positive capacity.');
        }
    }

    public function isWorkstation(): bool
    {
        return $this->kind === self::KIND_WORKSTATION;
    }
}
