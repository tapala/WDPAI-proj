<?php

declare(strict_types=1);

use App\Support\Html;

/** @var string $message */
/** @var int $status */
/** @var string $title */
/** @var App\Support\Translator $translator */
?>
<section class="error-page">
    <p class="error-code"><?= Html::escape($status) ?></p>
    <h1><?= Html::escape($title) ?></h1>
    <p class="lead"><?= Html::escape($message) ?></p>
    <p><a class="button" href="/"><?= Html::escape($translator->translate('error.back_home')) ?></a></p>
</section>
