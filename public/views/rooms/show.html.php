<?php

declare(strict_types=1);

use App\Support\Html;

/** @var App\Model\Room $room */
/** @var App\Support\Translator $translator */
?>
<nav class="breadcrumbs" aria-label="<?= Html::escape($translator->translate('rooms.breadcrumbs.label')) ?>">
    <a href="/rooms"><?= Html::escape($translator->translate('rooms.details.back')) ?></a>
</nav>

<article class="room-details">
    <p class="room-kind"><?= Html::escape($translator->translate('rooms.kind.' . $room->kind)) ?></p>
    <h1><?= Html::escape($room->name) ?></h1>
    <p class="lead"><?= Html::escape($translator->translate($room->description)) ?></p>

    <dl class="detail-list">
        <div class="detail-list__row">
            <dt><?= Html::escape($translator->translate('rooms.details.location')) ?></dt>
            <dd><?= Html::escape($room->location) ?></dd>
        </div>
        <div class="detail-list__row">
            <dt><?= Html::escape($translator->translate('rooms.details.kind')) ?></dt>
            <dd><?= Html::escape($translator->translate('rooms.kind.' . $room->kind)) ?></dd>
        </div>
        <div class="detail-list__row">
            <dt><?= Html::escape($translator->translate('rooms.details.capacity')) ?></dt>
            <dd><?= Html::escape($room->capacity) ?></dd>
        </div>
    </dl>

    <section class="room-equipment" aria-labelledby="room-equipment-title">
        <h2 id="room-equipment-title"><?= Html::escape($translator->translate('rooms.details.equipment')) ?></h2>

        <?php if ($room->equipment === []): ?>
            <p><?= Html::escape($translator->translate('rooms.details.equipment_empty')) ?></p>
        <?php else: ?>
            <ul class="tag-list">
                <?php foreach ($room->equipment as $equipmentKey): ?>
                    <li><?= Html::escape($translator->translate($equipmentKey)) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>

    <p class="notice"><?= Html::escape($translator->translate('rooms.details.availability_notice')) ?></p>
</article>
