<?php

declare(strict_types=1);

use App\Support\Html;

/** @var App\Model\Room $room */
/** @var string $csrfToken */
/** @var string $today */
/** @var array{date: string, start_time: string, end_time: string} $reservation */
/** @var array<string, list<string>> $reservationErrors */
/** @var string|null $reservationFormError */
/** @var array<string, string>|null $flash */
/** @var App\Support\Translator $translator */

$fieldMeta = [
    'date' => ['id' => 'reservation-date', 'label' => $translator->translate('rooms.reservation.date')],
    'start_time' => ['id' => 'reservation-start', 'label' => $translator->translate('rooms.reservation.start')],
    'end_time' => ['id' => 'reservation-end', 'label' => $translator->translate('rooms.reservation.end')],
];

$errorItems = [];

foreach ($reservationErrors as $field => $messages) {
    foreach ($messages as $message) {
        $errorItems[] = ['field' => $field, 'message' => $message];
    }
}

$dateError = isset($reservationErrors['date']) ? $translator->translate($reservationErrors['date'][0]) : null;
$startError = isset($reservationErrors['start_time']) ? $translator->translate($reservationErrors['start_time'][0]) : null;
$endError = isset($reservationErrors['end_time']) ? $translator->translate($reservationErrors['end_time'][0]) : null;
$flashMessage = $flash['message'] ?? null;
?>
<aside class="booking-card" aria-labelledby="reservation-title">
    <h2 id="reservation-title"><?= Html::escape($translator->translate('rooms.reservation.title')) ?></h2>
    <p class="booking-card__demo"><?= Html::escape($translator->translate('rooms.reservation.demo_notice')) ?></p>

    <?php if (is_string($flashMessage) && $flashMessage !== ''): ?>
        <p class="form-flash form-flash--info" role="status"><?= Html::escape($translator->translate($flashMessage)) ?></p>
    <?php endif; ?>

    <?php if ($reservationFormError !== null || $errorItems !== []): ?>
        <div class="form-errors" role="alert">
            <p class="form-errors__title"><?= Html::escape($translator->translate('form.errors')) ?></p>
            <ul>
                <?php if ($reservationFormError !== null): ?>
                    <li><?= Html::escape($translator->translate($reservationFormError)) ?></li>
                <?php endif; ?>
                <?php foreach ($errorItems as $item): ?>
                    <?php $meta = $fieldMeta[$item['field']] ?? null; ?>
                    <li>
                        <?php if ($meta !== null): ?>
                            <a href="#<?= Html::escape($meta['id']) ?>"><?= Html::escape($meta['label']) ?>:</a>
                        <?php endif; ?>
                        <?= Html::escape($translator->translate($item['message'])) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form class="booking-form" method="post" action="/rooms/<?= Html::escape($room->id) ?>/reservation" novalidate>
        <input type="hidden" name="_token" value="<?= Html::escape($csrfToken) ?>">

        <div class="field">
            <label for="reservation-date"><?= Html::escape($translator->translate('rooms.reservation.date')) ?></label>
            <input
                id="reservation-date"
                name="date"
                type="date"
                min="<?= Html::escape($today) ?>"
                value="<?= Html::escape($reservation['date']) ?>"
                required
                <?= $dateError !== null ? 'aria-invalid="true" aria-describedby="reservation-date-error"' : '' ?>
            >
            <?php if ($dateError !== null): ?>
                <p class="field__error" id="reservation-date-error"><?= Html::escape($dateError) ?></p>
            <?php endif; ?>
        </div>

        <div class="booking-form__times">
            <div class="field">
                <label for="reservation-start"><?= Html::escape($translator->translate('rooms.reservation.start')) ?></label>
                <input
                    id="reservation-start"
                    name="start_time"
                    type="time"
                    value="<?= Html::escape($reservation['start_time']) ?>"
                    required
                    <?= $startError !== null ? 'aria-invalid="true" aria-describedby="reservation-start-error"' : '' ?>
                >
                <?php if ($startError !== null): ?>
                    <p class="field__error" id="reservation-start-error"><?= Html::escape($startError) ?></p>
                <?php endif; ?>
            </div>

            <div class="field">
                <label for="reservation-end"><?= Html::escape($translator->translate('rooms.reservation.end')) ?></label>
                <input
                    id="reservation-end"
                    name="end_time"
                    type="time"
                    value="<?= Html::escape($reservation['end_time']) ?>"
                    required
                    <?= $endError !== null ? 'aria-invalid="true" aria-describedby="reservation-end-error"' : '' ?>
                >
                <?php if ($endError !== null): ?>
                    <p class="field__error" id="reservation-end-error"><?= Html::escape($endError) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <button class="button button--block" type="submit">
            <?= Html::escape($translator->translate('rooms.reservation.submit')) ?>
        </button>
    </form>
</aside>
