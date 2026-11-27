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
        <a class="sidebar__login" href="/login"<?= $currentPath === '/login' ? ' aria-current="page"' : '' ?>>
            <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                <circle cx="12" cy="8" r="3.5"/>
                <path d="M5 20a7 7 0 0 1 14 0"/>
            </svg>
            <span><?= Html::escape($translator->translate('nav.login')) ?></span>
        </a>

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
