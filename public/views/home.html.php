<?php

declare(strict_types=1);

use App\Support\Html;

/** @var App\Support\Translator $translator */
?>
<section class="hero">
    <p class="eyebrow"><?= Html::escape($translator->translate('home.eyebrow')) ?></p>
    <h1><?= Html::escape($translator->translate('home.title')) ?></h1>
    <p class="lead"><?= Html::escape($translator->translate('home.lead')) ?></p>
    <p class="notice"><?= Html::escape($translator->translate('home.notice')) ?></p>
</section>

<section class="home-section" aria-labelledby="features-title">
    <h2 id="features-title"><?= Html::escape($translator->translate('home.features.title')) ?></h2>

    <div class="feature-grid">
        <article class="feature-card">
            <h3><?= Html::escape($translator->translate('home.features.rooms.title')) ?></h3>
            <p><?= Html::escape($translator->translate('home.features.rooms.description')) ?></p>
        </article>

        <article class="feature-card">
            <h3><?= Html::escape($translator->translate('home.features.equipment.title')) ?></h3>
            <p><?= Html::escape($translator->translate('home.features.equipment.description')) ?></p>
        </article>

        <article class="feature-card">
            <h3><?= Html::escape($translator->translate('home.features.reservations.title')) ?></h3>
            <p><?= Html::escape($translator->translate('home.features.reservations.description')) ?></p>
        </article>
    </div>
</section>
