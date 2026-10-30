<?php

declare(strict_types=1);

use App\Support\Html;

/** @var string $currentPath */
/** @var App\Support\Translator $translator */
?>
<nav class="mobile-nav" aria-label="<?= Html::escape($translator->translate('nav.main')) ?>">
    <?php
    $withIcons = true;
    require __DIR__ . '/nav-items.html.php';
    ?>
</nav>
