<?php

declare(strict_types=1);

use App\Model\Room;
use App\Service\RoomFilter;
use App\Support\Html;

/** @var list<Room> $rooms */
/** @var App\Service\RoomFilter $filter */
/** @var int $totalRooms */
/** @var App\Support\Translator $translator */
?>
<section class="page-head">
    <p class="eyebrow"><?= Html::escape($translator->translate('rooms.eyebrow')) ?></p>
    <h1><?= Html::escape($translator->translate('rooms.title')) ?></h1>
    <p class="lead"><?= Html::escape($translator->translate('rooms.lead')) ?></p>
</section>

<form class="filter-bar" method="get" action="/rooms" role="search" aria-label="<?= Html::escape($translator->translate('rooms.filters.label')) ?>">
    <div class="field">
        <label for="filter-q"><?= Html::escape($translator->translate('rooms.filters.query')) ?></label>
        <input
            id="filter-q"
            name="q"
            type="search"
            value="<?= Html::escape($filter->query) ?>"
            maxlength="80"
            placeholder="<?= Html::escape($translator->translate('rooms.filters.query_placeholder')) ?>"
        >
    </div>

    <div class="field">
        <label for="filter-kind"><?= Html::escape($translator->translate('rooms.filters.kind')) ?></label>
        <select id="filter-kind" name="kind">
            <option value=""><?= Html::escape($translator->translate('rooms.kind.any')) ?></option>
            <?php foreach (Room::KINDS as $kind): ?>
                <option value="<?= Html::escape($kind) ?>"<?= $filter->kind === $kind ? ' selected' : '' ?>>
                    <?= Html::escape($translator->translate('rooms.kind.' . $kind)) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="field">
        <label for="filter-capacity"><?= Html::escape($translator->translate('rooms.filters.capacity')) ?></label>
        <input
            id="filter-capacity"
            name="min_capacity"
            type="number"
            inputmode="numeric"
            min="1"
            max="1000"
            step="1"
            value="<?= $filter->minimumCapacity === null ? '' : Html::escape($filter->minimumCapacity) ?>"
            placeholder="<?= Html::escape($translator->translate('rooms.filters.capacity_placeholder')) ?>"
        >
    </div>

    <div class="field">
        <label for="filter-sort"><?= Html::escape($translator->translate('rooms.filters.sort')) ?></label>
        <select id="filter-sort" name="sort">
            <option value="name"<?= $filter->sort === RoomFilter::SORT_NAME ? ' selected' : '' ?>>
                <?= Html::escape($translator->translate('rooms.sort.name')) ?>
            </option>
            <option value="capacity"<?= $filter->sort === RoomFilter::SORT_CAPACITY ? ' selected' : '' ?>>
                <?= Html::escape($translator->translate('rooms.sort.capacity')) ?>
            </option>
        </select>
    </div>

    <div class="filter-actions">
        <button class="button" type="submit"><?= Html::escape($translator->translate('rooms.filters.submit')) ?></button>
        <?php if ($filter->isActive()): ?>
            <a class="link-subtle" href="/rooms"><?= Html::escape($translator->translate('rooms.filters.reset')) ?></a>
        <?php endif; ?>
    </div>
</form>

<p class="results" role="status">
    <?= Html::escape($translator->translate('rooms.results', ['count' => count($rooms), 'total' => $totalRooms])) ?>
</p>

<?php if ($rooms === []): ?>
    <section class="empty-state" aria-labelledby="rooms-empty-title">
        <h2 id="rooms-empty-title"><?= Html::escape($translator->translate('rooms.empty.title')) ?></h2>
        <p><?= Html::escape($translator->translate('rooms.empty.message')) ?></p>
        <p><a class="button" href="/rooms"><?= Html::escape($translator->translate('rooms.filters.reset')) ?></a></p>
    </section>
<?php else: ?>
    <div class="room-grid">
        <?php foreach ($rooms as $room): ?>
            <article class="room-card">
                <p class="room-kind"><?= Html::escape($translator->translate('rooms.kind.' . $room->kind)) ?></p>
                <h2 class="room-name">
                    <a href="/rooms/<?= Html::escape($room->id) ?>"><?= Html::escape($room->name) ?></a>
                </h2>
                <p class="room-location"><?= Html::escape($room->location) ?></p>
                <p class="room-capacity">
                    <?= Html::escape($translator->translate('rooms.card.capacity', ['count' => $room->capacity])) ?>
                </p>

                <?php if ($room->equipment !== []): ?>
                    <ul class="tag-list" aria-label="<?= Html::escape($translator->translate('rooms.details.equipment')) ?>">
                        <?php foreach ($room->equipment as $equipmentKey): ?>
                            <li><?= Html::escape($translator->translate($equipmentKey)) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
