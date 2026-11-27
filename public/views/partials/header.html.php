<?php

declare(strict_types=1);

use App\Support\Html;

/** @var string $currentPath */
/** @var string $locale */
/** @var App\Support\Translator $translator */
?>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="/" aria-label="<?= Html::escape($translator->translate('nav.home_link')) ?>">roomly</a>

        <div class="header-actions">
            <?php
            $themeToggleClass = '';
            require __DIR__ . '/theme-toggle.html.php';
            ?>
            <a class="header-login" href="/login"<?= $currentPath === '/login' ? ' aria-current="page"' : '' ?>>
                <?= Html::escape($translator->translate('nav.login')) ?>
            </a>

            <?php
            $languageSwitcherClass = 'language-switcher--header';
            require __DIR__ . '/language-switcher.html.php';
            ?>
        </div>
    </div>
</header>
