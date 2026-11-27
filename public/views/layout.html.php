<?php

declare(strict_types=1);

use App\Support\Html;

/** @var string $content */
/** @var string $locale */
/** @var string $title */
/** @var App\Support\Translator $translator */
/** @var string|null $sectionLabel */
/** @var string $currentPath */

$currentPath = $currentPath ?? '/';
$sectionLabel = $sectionLabel ?? match (true) {
    $currentPath === '/login' => $translator->translate('auth.topbar'),
    $currentPath === '/rooms' => $translator->translate('rooms.topbar'),
    str_starts_with($currentPath, '/rooms') => $translator->translate('rooms.details.topbar'),
    default => $translator->translate('nav.home'),
};
?>
<!doctype html>
<html lang="<?= Html::escape($locale) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= Html::escape($translator->translate('app.description')) ?>">
    <title><?= Html::escape($title) ?></title>
    <script src="/js/theme-init.js"></script>
    <link rel="stylesheet" href="/css/style.css">
    <script src="/js/theme.js" defer></script>
</head>
<body>
    <a class="skip-link" href="#main-content"><?= Html::escape($translator->translate('nav.skip')) ?></a>

    <?php require __DIR__ . '/partials/sidebar.html.php'; ?>

    <div class="app-frame">
        <?php require __DIR__ . '/partials/header.html.php'; ?>

        <header class="topbar">
            <p class="topbar__section"><?= Html::escape($sectionLabel) ?></p>
        </header>

        <main id="main-content" class="site-main" tabindex="-1">
            <div class="container">
                <?= $content ?>
            </div>
        </main>

        <?php require __DIR__ . '/partials/footer.html.php'; ?>
    </div>

    <?php require __DIR__ . '/partials/mobile-nav.html.php'; ?>
</body>
</html>
