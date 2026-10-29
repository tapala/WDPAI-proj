<?php

declare(strict_types=1);

use App\Support\Html;

/** @var string $locale */
/** @var App\Support\Translator $translator */
/** @var string $currentPath */

$currentPath = $currentPath ?? '/';
$isRoomsPage = str_starts_with($currentPath, '/rooms');

/**
 * Keeps the current query string (filters, pagination) when the language
 * changes, so switching languages never drops the user's results.
 */
$languageUrl = static function (string $language): string {
    $parameters = $_GET;
    $parameters['lang'] = $language;

    return '?' . http_build_query($parameters);
};
?>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="/" aria-label="<?= Html::escape($translator->translate('nav.home_link')) ?>">
            <span class="brand-mark" aria-hidden="true">R</span>
            <span class="brand-name">Roomly</span>
        </a>

        <nav class="site-nav" aria-label="<?= Html::escape($translator->translate('nav.main')) ?>">
            <a href="/"<?= $currentPath === '/' ? ' aria-current="page"' : '' ?>>
                <?= Html::escape($translator->translate('nav.home')) ?>
            </a>
            <a href="/rooms"<?= $isRoomsPage ? ' aria-current="page"' : '' ?>>
                <?= Html::escape($translator->translate('nav.rooms')) ?>
            </a>
        </nav>

        <div class="language-switcher" role="group" aria-label="<?= Html::escape($translator->translate('nav.language')) ?>">
            <a href="<?= Html::escape($languageUrl('pl')) ?>" lang="pl" hreflang="pl"<?= $locale === 'pl' ? ' aria-current="true"' : '' ?>>PL</a>
            <a href="<?= Html::escape($languageUrl('en')) ?>" lang="en" hreflang="en"<?= $locale === 'en' ? ' aria-current="true"' : '' ?>>EN</a>
        </div>
    </div>
</header>
