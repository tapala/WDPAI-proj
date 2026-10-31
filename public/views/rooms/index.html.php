<?php

declare(strict_types=1);

use App\Model\Room;
use App\Service\RoomFilter;
use App\Support\Html;

/** @var list<Room> $rooms */
/** @var RoomFilter $filter */
/** @var App\Support\Translator $translator */

$capacityOptions = [4, 6, 8, 12, 20, 40];

// Keep a capacity coming from a hand-written URL visible instead of silently
// resetting the select to "any".
if ($filter->minimumCapacity !== null && !in_array($filter->minimumCapacity, $capacityOptions, true)) {
    $capacityOptions[] = $filter->minimumCapacity;
    sort($capacityOptions);
}
?>
<section class="catalogue-head">
    <h1><?= Html::escape($translator->translate('rooms.heading')) ?></h1>
    <p class="catalogue-head__lead"><?= Html::escape($translator->translate('rooms.lead')) ?></p>
</section>

<form class="catalogue-filters" method="get" action="/rooms" role="search" aria-label="<?= Html::escape($translator->translate('rooms.filters.label')) ?>">
    <div class="catalogue-filters__top">
        <div class="search-control">
            <svg class="search-control__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true" focusable="false">
                <circle cx="11" cy="11" r="6.5"/>
                <path d="m20 20-4.2-4.2"/>
            </svg>
            <label class="visually-hidden" for="filter-q"><?= Html::escape($translator->translate('rooms.filters.query')) ?></label>
            <input
                class="search-control__input"
                id="filter-q"
                name="q"
                type="search"
                value="<?= Html::escape($filter->query) ?>"
                maxlength="80"
                placeholder="<?= Html::escape($translator->translate('rooms.filters.query_placeholder')) ?>"
            >
        </div>

        <button class="button button--soft catalogue-filters__submit" type="submit">
            <svg class="button__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true" focusable="false">
                <path d="M4 7h16M4 12h16M4 17h16"/>
            </svg>
            <?= Html::escape($translator->translate('rooms.filters.submit')) ?>
        </button>
    </div>

    <div class="filter-pills">
        <div class="filter-pill">
            <label class="visually-hidden" for="filter-kind"><?= Html::escape($translator->translate('rooms.filters.kind')) ?></label>
            <select id="filter-kind" name="kind">
                <option value=""><?= Html::escape($translator->translate('rooms.kind.any')) ?></option>
                <?php foreach (Room::KINDS as $kind): ?>
                    <option value="<?= Html::escape($kind) ?>"<?= $filter->kind === $kind ? ' selected' : '' ?>>
                        <?= Html::escape($translator->translate('rooms.kind.' . $kind)) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-pill">
            <label class="visually-hidden" for="filter-capacity"><?= Html::escape($translator->translate('rooms.filters.capacity')) ?></label>
            <select id="filter-capacity" name="min_capacity">
                <option value=""><?= Html::escape($translator->translate('rooms.filters.capacity_any')) ?></option>
                <?php foreach ($capacityOptions as $capacity): ?>
                    <option value="<?= Html::escape($capacity) ?>"<?= $filter->minimumCapacity === $capacity ? ' selected' : '' ?>>
                        <?= Html::escape($translator->translate('rooms.filters.capacity_option', ['count' => $capacity])) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-pill">
            <label class="visually-hidden" for="filter-sort"><?= Html::escape($translator->translate('rooms.filters.sort')) ?></label>
            <select id="filter-sort" name="sort">
                <option value="name"<?= $filter->sort === RoomFilter::SORT_NAME ? ' selected' : '' ?>>
                    <?= Html::escape($translator->translate('rooms.sort.name')) ?>
                </option>
                <option value="capacity"<?= $filter->sort === RoomFilter::SORT_CAPACITY ? ' selected' : '' ?>>
                    <?= Html::escape($translator->translate('rooms.sort.capacity')) ?>
                </option>
            </select>
        </div>

        <?php if ($filter->isActive()): ?>
            <a class="filter-pills__reset" href="/rooms"><?= Html::escape($translator->translate('rooms.filters.reset')) ?></a>
        <?php endif; ?>
    </div>
</form>

<h2 class="results-line">
    <?= Html::escape($translator->translate('rooms.results.heading')) ?>
    <span class="results-line__count"><?= Html::escape($translator->translateCount('rooms.results.count', count($rooms))) ?></span>
</h2>

<?php if ($rooms === []): ?>
    <section class="empty-state" aria-labelledby="rooms-empty-title">
        <h2 id="rooms-empty-title"><?= Html::escape($translator->translate('rooms.empty.title')) ?></h2>
        <p><?= Html::escape($translator->translate('rooms.empty.message')) ?></p>
        <p><a class="button" href="/rooms"><?= Html::escape($translator->translate('rooms.filters.reset')) ?></a></p>
    </section>
<?php else: ?>
    <?php
    // Colour placeholders stand in for room photos until the upload module
    // (Lab 13) provides real images.
    $thumbVariants = ['blue', 'sand', 'green', 'lilac'];
    ?>
    <div class="room-grid">
        <?php foreach ($rooms as $index => $room): ?>
            <?php
            $meta = [$translator->translateCount('rooms.card.seats', $room->capacity)];
            foreach ($room->equipment as $equipmentKey) {
                $meta[] = $translator->translate($equipmentKey);
            }

            // The colour follows the card position so neighbouring cards never
            // share a shade; it is decorative, not room data.
            $thumbVariant = $thumbVariants[$index % count($thumbVariants)];
            ?>
            <article class="room-card">
                <div class="room-card__photo room-card__photo--<?= Html::escape($thumbVariant) ?>" aria-hidden="true"></div>

                <div class="room-card__body">
                    <p class="room-card__badge"><?= Html::escape($translator->translate('rooms.kind.' . $room->kind)) ?></p>

                    <h2 class="room-card__name">
                        <a class="room-card__link" href="/rooms/<?= Html::escape($room->id) ?>"><?= Html::escape($room->name) ?></a>
                    </h2>

                    <p class="room-card__location"><?= Html::escape($room->location) ?></p>
                    <p class="room-card__meta"><?= Html::escape(implode(' · ', $meta)) ?></p>

                    <span class="room-card__more" aria-hidden="true">
                        <?= Html::escape($translator->translate('rooms.card.details')) ?>
                        <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" focusable="false">
                            <path d="M2.5 8h11"/>
                            <path d="m9.5 4 4 4-4 4"/>
                        </svg>
                    </span>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
