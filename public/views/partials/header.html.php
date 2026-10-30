<?php

declare(strict_types=1);

use App\Support\Html;

/** @var string $currentPath */
/** @var string $locale */
/** @var App\Support\Translator $translator */
?>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="/" aria-label="<?= Html::escape($translator->translate('nav.home_link')) ?>">
            <span class="brand-mark" aria-hidden="true">R</span>
            <span class="brand-name">Roomly</span>
        </a>

        <nav class="site-nav" aria-label="<?= Html::escape($translator->translate('nav.main')) ?>">
            <?php
            $withIcons = false;
            require __DIR__ . '/nav-items.html.php';
            ?>
        </nav>

        <?php
        $languageSwitcherClass = '';
        require __DIR__ . '/language-switcher.html.php';
        ?>
    </div>
</header>
