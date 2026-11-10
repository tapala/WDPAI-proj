<?php

declare(strict_types=1);

use App\Support\Html;

/** @var string $currentPath */
/** @var string $locale */
/** @var App\Support\Translator $translator */
?>
<aside class="sidebar">
    <a class="sidebar__brand" href="/" aria-label="<?= Html::escape($translator->translate('nav.home_link')) ?>">roomly</a>
    <span class="sidebar__accent" aria-hidden="true"></span>

    <p class="sidebar__heading"><?= Html::escape($translator->translate('nav.menu')) ?></p>

    <nav class="sidebar__nav" aria-label="<?= Html::escape($translator->translate('nav.main')) ?>">
        <?php
        $withIcons = true;
        require __DIR__ . '/nav-items.html.php';
        ?>
    </nav>

    <div class="sidebar__footer">
        <div class="sidebar__tools">
            <?php
            $themeToggleClass = 'theme-toggle--dark';
            require __DIR__ . '/theme-toggle.html.php';

            $languageSwitcherClass = 'language-switcher--dark';
            require __DIR__ . '/language-switcher.html.php';
            ?>
        </div>
    </div>
</aside>
