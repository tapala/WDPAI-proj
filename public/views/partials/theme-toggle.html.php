<?php

declare(strict_types=1);

use App\Support\Html;

/** @var App\Support\Translator $translator */
/** @var string $themeToggleClass */

$themeToggleClass = $themeToggleClass ?? '';
?>
<button
    class="theme-toggle<?= $themeToggleClass === '' ? '' : ' ' . Html::escape($themeToggleClass) ?>"
    type="button"
    data-theme-toggle
    aria-pressed="false"
    aria-label="<?= Html::escape($translator->translate('theme.to_dark')) ?>"
    data-label-dark="<?= Html::escape($translator->translate('theme.to_dark')) ?>"
    data-label-light="<?= Html::escape($translator->translate('theme.to_light')) ?>"
    title="<?= Html::escape($translator->translate('theme.toggle')) ?>"
>
    <svg class="theme-toggle__icon theme-toggle__icon--moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
        <path d="M20 14.5A8 8 0 0 1 9.5 4a8 8 0 1 0 10.5 10.5Z"/>
    </svg>
    <svg class="theme-toggle__icon theme-toggle__icon--sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
        <circle cx="12" cy="12" r="4"/>
        <path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/>
    </svg>
</button>
