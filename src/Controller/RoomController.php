<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Room;
use App\Repository\RoomRepositoryInterface;
use App\Security\CsrfToken;
use App\Service\RoomFilter;
use App\Support\FlashMessage;
use App\Support\FormValidator;
use App\Support\HttpException;
use App\Support\Translator;
use App\Support\View;

final class RoomController extends AppController
{
    public function __construct(
        View $view,
        Translator $translator,
        private readonly RoomRepositoryInterface $rooms,
        private readonly CsrfToken $csrf,
        private readonly FlashMessage $flash,
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
        $this->renderDetails($this->findRoom($id));
    }

    /**
     * Demo reservation form.
     *
     * Date/time input is validated, CSRF-protected and follows PRG, but no
     * reservation is stored — the reservation module arrives in Lab 12.
     */
    public function reserve(string $id): void
    {
        $room = $this->findRoom($id);

        if (!$this->csrf->isValid($_POST['_token'] ?? null)) {
            $this->renderDetails($room, [
                'reservationFormError' => 'form.csrf_invalid',
            ], 403);

            return;
        }

        $today = date('Y-m-d');

        $validator = (new FormValidator($_POST))
            ->required('date', 'validation.required')
            ->date('date', 'validation.date')
            ->dateNotBefore('date', $today, 'rooms.reservation.date_past')
            ->required('start_time', 'validation.required')
            ->time('start_time', 'validation.time')
            ->required('end_time', 'validation.required')
            ->time('end_time', 'validation.time')
            ->afterTime('start_time', 'end_time', 'rooms.reservation.end_after_start');

        if ($validator->hasErrors()) {
            $this->renderDetails($room, [
                'reservation' => [
                    'date' => $validator->value('date'),
                    'start_time' => $validator->value('start_time'),
                    'end_time' => $validator->value('end_time'),
                ],
                'reservationErrors' => $validator->errors(),
            ], 422);

            return;
        }

        $this->flash->set([
            'type' => 'info',
            'message' => 'rooms.reservation.demo_success',
        ]);

        $this->redirect('/rooms/' . $room->id);
    }

    private function findRoom(string $id): Room
    {
        $room = $this->rooms->findById((int) $id);

        if ($room === null) {
            throw HttpException::notFound('The requested room does not exist.');
        }

        return $room;
    }

    /**
     * @param array<string, mixed> $overrides
     */
    private function renderDetails(Room $room, array $overrides = [], int $status = 200): void
    {
        $this->render('rooms/show', array_merge([
            'title' => $room->name,
            'currentPath' => '/rooms/' . $room->id,
            'room' => $room,
            'csrfToken' => $this->csrf->token(),
            'flash' => $this->flash->pull(),
            'reservation' => [
                'date' => '',
                'start_time' => '',
                'end_time' => '',
            ],
            'reservationErrors' => [],
            'reservationFormError' => null,
            'today' => date('Y-m-d'),
        ], $overrides), $status);
    }
}
