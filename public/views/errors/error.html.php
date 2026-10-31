<?php

declare(strict_types=1);

use App\Support\Html;

/** @var string $message */
/** @var int $status */
/** @var string $title */
/** @var App\Support\Translator $translator */
?>
<section class="state-card" aria-labelledby="error-title">
    <p class="state-card__code"><?= Html::escape($status) ?></p>
    <h1 id="error-title"><?= Html::escape($title) ?></h1>
    <p class="state-card__text"><?= Html::escape($message) ?></p>
    <div class="state-card__actions">
        <a class="button" href="/"><?= Html::escape($translator->translate('error.back_home')) ?></a>
        <a class="button button--secondary" href="/rooms"><?= Html::escape($translator->translate('error.browse_rooms')) ?></a>
    </div>
</section>
