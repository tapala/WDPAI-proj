<?php

declare(strict_types=1);

use App\Support\Html;

/** @var string $currentPath */
/** @var App\Support\Translator $translator */
/** @var bool $withIcons */

$withIcons = $withIcons ?? false;

$items = [
    [
        'href' => '/',
        'label' => 'nav.home',
        'icon' => 'home',
        'active' => $currentPath === '/',
    ],
    [
        'href' => '/rooms',
        'label' => 'nav.rooms',
        'icon' => 'rooms',
        'active' => str_starts_with($currentPath, '/rooms'),
    ],
];

foreach ($items as $item): ?>
    <a href="<?= Html::escape($item['href']) ?>"<?= $item['active'] ? ' aria-current="page"' : '' ?>>
        <?php if ($withIcons): ?>
            <?php if ($item['icon'] === 'home'): ?>
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <path d="M3 10.5 12 3l9 7.5"/>
                    <path d="M5.5 9.5V21h13V9.5"/>
                </svg>
            <?php else: ?>
                <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
                    <path d="M4 21h16"/>
                    <path d="M6.5 21V4.5h11V21"/>
                    <path d="M10 12h.01"/>
                </svg>
            <?php endif; ?>
        <?php endif; ?>
        <span><?= Html::escape($translator->translate($item['label'])) ?></span>
    </a>
<?php endforeach; ?>
