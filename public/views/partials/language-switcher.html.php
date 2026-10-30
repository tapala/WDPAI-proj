<?php

declare(strict_types=1);

use App\Support\Html;

/** @var string $locale */
/** @var App\Support\Translator $translator */
/** @var string $languageSwitcherClass */

$languageSwitcherClass = $languageSwitcherClass ?? '';

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
<div class="language-switcher<?= $languageSwitcherClass === '' ? '' : ' ' . Html::escape($languageSwitcherClass) ?>" role="group" aria-label="<?= Html::escape($translator->translate('nav.language')) ?>">
    <a href="<?= Html::escape($languageUrl('pl')) ?>" lang="pl" hreflang="pl"<?= $locale === 'pl' ? ' aria-current="true"' : '' ?>>PL</a>
    <a href="<?= Html::escape($languageUrl('en')) ?>" lang="en" hreflang="en"<?= $locale === 'en' ? ' aria-current="true"' : '' ?>>EN</a>
</div>
