<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\RoomRepositoryInterface;
use App\Service\RoomFilter;
use App\Support\HttpException;
use App\Support\Translator;
use App\Support\View;

final class RoomController extends AppController
{
    public function __construct(
        View $view,
        Translator $translator,
        private readonly RoomRepositoryInterface $rooms,
    ) {
        parent::__construct($view, $translator);
    }

    public function index(): void
    {
        $filter = RoomFilter::fromQuery($_GET);
        $allRooms = $this->rooms->findAll();

        $this->render('rooms/index', [
            'title' => $this->translator->translate('rooms.title'),
            'currentPath' => '/rooms',
            'rooms' => $filter->apply($allRooms),
            'filter' => $filter,
        ]);
    }

    public function show(string $id): void
    {
        $room = $this->rooms->findById((int) $id);

        if ($room === null) {
            throw HttpException::notFound('The requested room does not exist.');
        }

        $this->render('rooms/show', [
            'title' => $room->name,
            'currentPath' => '/rooms',
            'room' => $room,
        ]);
    }
}
