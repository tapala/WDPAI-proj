<?php

declare(strict_types=1);

use App\Support\Html;

/** @var App\Model\Room $room */
/** @var App\Support\Translator $translator */
/** @var string $csrfToken */
/** @var array<string, string>|null $flash */
/** @var array{date: string, start_time: string, end_time: string} $reservation */
/** @var array<string, list<string>> $reservationErrors */
/** @var string|null $reservationFormError */
/** @var string $today */

$meta = implode(' · ', [
    $translator->translate('rooms.kind.' . $room->kind),
    $room->location,
    $translator->translateCount('rooms.card.seats', $room->capacity),
]);

// Colour placeholder until the upload module (Lab 13) provides room photos.
$thumbVariants = ['blue', 'sand', 'green', 'lilac'];
$thumbVariant = $thumbVariants[($room->id - 1) % count($thumbVariants)];
?>
<nav class="breadcrumbs" aria-label="<?= Html::escape($translator->translate('rooms.breadcrumbs.label')) ?>">
    <a href="/rooms"><?= Html::escape($translator->translate('rooms.details.back')) ?></a>
</nav>

<article>
    <section class="catalogue-head">
        <h1><?= Html::escape($room->name) ?></h1>
        <p class="catalogue-head__lead"><?= Html::escape($meta) ?></p>
    </section>

    <div class="room-detail__hero">
        <div class="room-detail__photo room-detail__photo--<?= Html::escape($thumbVariant) ?>" aria-hidden="true"></div>

        <?php require __DIR__ . '/partials/reservation-card.html.php'; ?>
    </div>

    <section class="room-detail__section" aria-labelledby="room-about-title">
        <h2 id="room-about-title"><?= Html::escape($translator->translate('rooms.details.about')) ?></h2>
        <p class="room-detail__text"><?= Html::escape($translator->translate($room->description)) ?></p>
    </section>

    <section class="room-detail__section" aria-labelledby="room-equipment-title">
        <h2 id="room-equipment-title"><?= Html::escape($translator->translate('rooms.details.equipment')) ?></h2>

        <?php if ($room->equipment === []): ?>
            <p class="room-detail__text"><?= Html::escape($translator->translate('rooms.details.equipment_empty')) ?></p>
        <?php else: ?>
            <ul class="equipment-grid">
                <?php foreach ($room->equipment as $equipmentKey): ?>
                    <li class="equipment-chip">
                        <svg class="equipment-chip__icon" viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                            <path d="m3 8.5 3.2 3L13 4.5"/>
                        </svg>
                        <span><?= Html::escape($translator->translate($equipmentKey)) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</article>
